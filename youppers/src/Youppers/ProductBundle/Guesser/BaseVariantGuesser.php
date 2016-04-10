<?php
namespace Youppers\ProductBundle\Guesser;

use Youppers\ProductBundle\Entity\AttributeStandard;
use Youppers\ProductBundle\Guesser\AbstractGuesser;
use Youppers\ProductBundle\Entity\ProductVariant;
use Youppers\ProductBundle\Entity\VariantProperty;
use Youppers\CompanyBundle\Entity\ProductPrice;
use Youppers\CompanyBundle\Entity\Company;
use Youppers\CompanyBundle\Entity\Brand;
use Youppers\ProductBundle\Entity\ProductCollection;
use Youppers\ProductBundle\Manager\AttributeOptionManager;
use Youppers\ProductBundle\Manager\ProductCollectionManager;
use Youppers\ProductBundle\Manager\ProductVariantManager;
use Youppers\ProductBundle\Manager\VariantPropertyManager;
use Youppers\ProductBundle\Manager\AttributeStandardManager;
use Youppers\ProductBundle\Entity\AttributeType;
use Doctrine\Common\Persistence\ManagerRegistry;

abstract class BaseVariantGuesser extends AbstractGuesser
{

	protected $collectionManager;
	protected $variantManager;
	protected $variantPropertyManager;
	protected $attributeStandardManager;
	protected $attributeOptionManager;

	public function setManagerRegistry(ManagerRegistry $managerRegistry)
	{
		parent::setManagerRegistry($managerRegistry);
		// TODO Usare getProductCollectionManager come in AbstractLoader
		$this->collectionManager = new ProductCollectionManager($managerRegistry);
		$this->variantManager = new ProductVariantManager($managerRegistry);
		$this->variantPropertyManager = new VariantPropertyManager($managerRegistry);
		$this->attributeStandardManager = new AttributeStandardManager($managerRegistry);
		$this->attributeOptionManager = new AttributeOptionManager($managerRegistry);
	}
	
	protected $company;
	
	public function setCompany(Company $company)
	{
		$this->company = $company;
	}
	
	protected $brand;
	
	public function setBrand(Brand $brand)
	{
		$this->brand = $brand;
	}
	
	protected $collection = null;
	
	public function setCollection($collectionCode)
	{
		if (empty($collectionCode)) {
			return;
		}
		$this->collection = $this->collectionManager->findByCode($this->brand, $collectionCode);
	
		if (empty($this->collection)) {
			throw new \Exception(sprintf("Collection '%s' not found",$collectionCode));
		}
	
	}

	public function guess()
	{
		if ($this->collection) {
			$this->guessCollection($this->collection);
		} else {
			$collections = $this->collectionManager->findByBrand($this->brand);
			foreach ($collections as $collection) {
				$this->guessCollection($collection);
			}
		}
	}

	/**
	 * @param ProductCollection $collection
	 * Check if Collection has all the required stdandards
	 * if standardName is set, try to associate the standard with that name
	 */
	protected function checkCollectionStandards(ProductCollection $collection, $guessers) {
		foreach ($collection->getProductType()->getProductAttributes() as $attribute) {
			$collectionStandard = null;
			$type = $attribute->getAttributeType();
			foreach ($collection->getStandards() as $attributeStandard) {
				$attributeType = $attributeStandard->getAttributeType();
				if ($attributeType->getCode() == $type->getCode()) {
					$collectionStandard = $attributeStandard;
					$this->getLogger()->debug(sprintf("Collection '%s' has standard '%s'",$collection,$collectionStandard));
					continue 2; // next type
				}
			}
			foreach ($guessers as $guesser) {
				if ($guesser->getType()->getCode() === $type->getCode()) {
					$defaultStandardName = $guesser->getDefaultStandardName();
					continue 1; // exit foreach
				}
			}
			if ($collectionStandard == null && $defaultStandardName != null) {
				// find standard
				$this->getLogger()->info(sprintf("Finding standard named '%s' of type '%s' for collection '%s'",$defaultStandardName,$type,$collection));
				$collectionStandard = $this->attributeStandardManager->findOneBy(array('name' => $defaultStandardName, 'attributeType' => $type));
				if ($collectionStandard == null) {
					$todo = sprintf("<error>Cannot find standard</error> named <info>%s</info> of type <info>%s</info>",$defaultStandardName,$type);
					$this->addTodo($todo);
					$this->getLogger()->warning(sprintf("Cannot find standard named '%s' of type '%s' for collection '%s'",$defaultStandardName,$type,$collection));
				} else {
					$this->getLogger()->info(sprintf("Using standard '%s' for collection '%s'",$collectionStandard,$collection));
					// assign standard to collection
					$collection->addStandard($collectionStandard);
					if (!$this->getForce()) {
						$todo = sprintf("<question>Assign standard</question> <info>%s</info> to collection <info>%s</info>",$collectionStandard,$collection);
						$this->addTodo($todo);
					}
					//$this->collectionManager->getEntityManager()->flush();
				}
			}
			if ($collectionStandard == null) {
				$this->getLogger()->debug(sprintf("Collection '%s' don't have standard for type '%s'",$collection,$type));
			}
		}
	}
	
	public function guessCollection(ProductCollection $collection)
	{
		$variants = $this->variantManager->findByCollection($collection);
		$this->getLogger()->info(sprintf("Guessing %d variants for collection '%s'",count($variants),$collection));
		$guessers = $this->getCollectionGuessers($collection);
		$this->checkCollectionStandards($collection,$guessers);
		foreach ($variants as $variant) {
			$this->guessVariant($variant,$guessers);
		}
		if ($this->getForce()) {
			$this->collectionManager->getObjectManager()->flush();
		} else {
			$this->collectionManager->getObjectManager()->detach($collection);
		}
	}
	
	/**
	 * Ovveride this to support new guessers
	 * 
	 * @param ProductCollection $collection
	 * @param AttributeType $type
	 * @return \Youppers\ProductBundle\Guesser\BaseDimensionPropertyGuesser|\Youppers\ProductBundle\Guesser\BaseColorPropertyGuesser|\Youppers\ProductBundle\Guesser\BaseRettificaPropertyGuesser
	 */
	protected function getCollectionTypeGuesser(ProductCollection $collection, AttributeType $type)
	{
		return new BasePropertyGuesser($type,$this->variantPropertyManager,$this->attributeOptionManager);
	}

	private $guessers = array();

	protected function getCollectionGuessers(ProductCollection $collection)
	{
		if ($this->guessers) {
			if (array_key_exists($collection->getId(),$this->guessers)) {
				return $this->guessers[$collection->getId()];
			} else {
				$this->guessers[$collection->getId()] = array();
			}
		}
		
		foreach ($collection->getProductType()->getProductAttributes() as $attribute) {
			$type = $attribute->getAttributeType();
			$guesser = $this->getCollectionTypeGuesser($collection, $type);
			if (empty($guesser)) {
				$this->getLogger()->critical(sprintf("No guesser for collection '%s' type '%s'",$collection,$type));
			} else {
				$guesser->setParent($this);
				$guesser->setIsVariant($attribute->getVariant());
				$this->guessers[$collection->getId()][] = $guesser;
			}
		}

        foreach ($collection->getStandards() as $attributeStandard) {
            $attributeType = $attributeStandard->getAttributeType();
            foreach ($this->guessers[$collection->getId()] as $guesser) {
                if ($guesser->getType() == $attributeType) {
                    continue 2;
                }
            }
            $newGuesser = $this->getCollectionTypeGuesser($collection, $attributeType);
            $newGuesser->setParent($this);
            $newGuesser->setIsVariant(false);
            $this->guessers[$collection->getId()][] = $newGuesser;
        }
        return $this->guessers[$collection->getId()];
     }
	
	protected function getVariantGuessers(ProductVariant $variant)
	{
		return $this->getCollectionGuessers($variant->getProductCollection());
	}
	
	public function guessVariant(ProductVariant $variant, $guessers = null) {
		$product = $variant->getProduct();
		if (empty($product)) {
			$this->getLogger()->critical("Variant without product: " . $variant->getId());
			return;
		}
		$name = $variant->getProduct()->getName();
        $info = json_decode($variant->getProduct()->getInfo(), true);
        if ($guessers == null) {
            $guessers = $this->getVariantGuessers($variant);
        }
		foreach ($guessers as $guesser) {
			if ($this->getDebug()) dump($name);
            if ($guesser instanceof BasePropertyGuesser) {
                $code = $guesser->getTypeColumn();
                if (array_key_exists($code,$info) && $text = $info[$code]) {
                    $this->getLogger()->debug(sprintf("Guess %s of '%s' using product info['%s']='%s'",$code, $variant,$code,$text));
                    if ($guesser->guessVariant($variant, $text, true)) {
                        continue;
                    } else {
                        if ($this->getDebug()) dump($info[$code]);
                    }
                }
            }
			if ($guesser->guessVariant($variant, $name)) {
                continue;
            }
			if ($this->getDebug()) dump($name);
		}
	}

}
