<?php
namespace Youppers\ProductBundle\Guesser;

use Youppers\ProductBundle\Guesser\AbstractGuesser;
use Youppers\ProductBundle\Entity\ProductVariant;
use Youppers\ProductBundle\Entity\VariantProperty;
use Youppers\CompanyBundle\Entity\ProductPrice;
use Youppers\CompanyBundle\Entity\Company;
use Youppers\CompanyBundle\Entity\Brand;
use Youppers\ProductBundle\Entity\ProductCollection;
use Youppers\ProductBundle\Manager\ProductCollectionManager;
use Youppers\ProductBundle\Manager\ProductVariantManager;
use Youppers\ProductBundle\Manager\VariantPropertyManager;
use Youppers\ProductBundle\Entity\AttributeType;
use Doctrine\Common\Persistence\ManagerRegistry;

abstract class BaseVariantGuesser extends AbstractGuesser
{

	protected $collectionManager;
	protected $variantManager;
	protected $variantPropertyManager;
	
	public function setManagerRegistry(ManagerRegistry $managerRegistry)
	{
		parent::setManagerRegistry($managerRegistry);
		$this->collectionManager = new ProductCollectionManager($managerRegistry);
		$this->variantManager = new ProductVariantManager($managerRegistry);
		$this->variantPropertyManager = new VariantPropertyManager($managerRegistry);
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
	
	public function guessCollection(ProductCollection $collection)
	{
		$variants = $this->variantManager->findByCollection($collection);
		$this->getLogger()->info(sprintf("Guessing %d variants for collection '%s'",count($variants),$collection));
		$guessers = $this->getCollectionGuessers($collection);
		foreach ($variants as $variant) {
			$this->guessVariant($variant,$guessers);
		}
		$this->variantManager->getEntityManager()->flush();
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
		return new BasePropertyGuesser($type,$this->variantPropertyManager);
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
     }
	
	protected function getVariantGuessers(ProductVariant $variant)
	{
		return $this->getCollectionGuessers($variant->getProductCollection());
	}
	
	public function guessVariant(ProductVariant $variant, $guessers) {
		$product = $variant->getProduct();
		if (empty($product)) {
			$this->getLogger()->critical("Variant without product: " . $variant->getId());
			return;
		}
		$text = $variant->getProduct()->getName();
        $info = json_decode($variant->getProduct()->getInfo(), true);
		foreach ($this->getVariantGuessers($variant) as $guesser) {
			if ($this->debug) dump($text);
            if ($guesser instanceof BasePropertyGuesser) {
                $code = $guesser->getType()->getCode();
                if (array_key_exists($code,$info)) {
                    if ($guesser->guessVariant($variant, $info[$code])) {
                        continue;
                    } else {
                        dump($info[$code]);
                    }
                }
            }
			if ($guesser->guessVariant($variant, $text)) {
                continue;
            }
			if ($this->debug) dump($text);
		}
	}

}
