<?php

namespace Youppers\CompanyBundle\Loader;

use Youppers\CompanyBundle\Entity\Brand;
use Youppers\CompanyBundle\Entity\Pricelist;
use Youppers\CompanyBundle\Entity\Product;
use Youppers\CompanyBundle\Entity\ProductPrice;
use Youppers\CompanyBundle\Manager\PricelistManager;
use Youppers\CompanyBundle\Manager\ProductPriceManager;
use Youppers\ProductBundle\Entity\ProductCollection;
use Youppers\ProductBundle\Manager\ProductCollectionManager;
use Youppers\ProductBundle\Entity\ProductVariant;
use Youppers\ProductBundle\Manager\ProductVariantManager;
use Doctrine\Common\Collections\Criteria;

abstract class AbstractPricelistLoader extends AbstractLoader
{
	const FIELD_UOM = 'uom';
	const FIELD_PRICE =	'price';
	const FIELD_QUANTITY = 'quantity';
	const FIELD_SURFACE = 'surface';

	/**
	 * @var Pricelist
	 */
	protected $pricelist;
	
	/**
	 * @var boolean
	 * Load Collection and Variant of the Product
	 */
	private $loadProduct;

	public function setLoadProduct($loadProduct)
	{
		$this->loadProduct = $loadProduct;
	}

	public function setChangeCollection($flag)
	{
		$this->changeCollection = $flag;
		if ($this->changeCollection && !$this->loadProduct) {
			$this->logger->warning("Change Collection is not performed if the loading of the product Collection and Variant is not enabled");
		}
	}

	private $guess;

	public function setGuess($flag)
	{
		$this->guess = $flag;
		if ($this->guess && !$this->loadProduct) {
			$this->logger->warning("Guess is not performed if the loading of the product Collection and Variant is not enabled");
		}
	}



	protected abstract function getNewCollectionProductType(Brand $brand, $code);

	public function setPricelist($pricelist)
	{
		$this->pricelist = $pricelist;
		
		$this->company = $pricelist->getCompany();
	}
	
    /**
     * @var PricelistManager
     */
    private $pricelistManager;

    /**
     * @return PricelistManager
     */
    protected function getPricelistManager() {
        if (empty($this->pricelistManager)) {
            $this->pricelistManager = $this->container->get('youppers.company.manager.pricelist');
        }
        return $this->pricelistManager;
    }

    /**
     * @var ProductPriceManager
     */
    private $productPriceManager;

    /**
     * @return ProductPriceManager
     */
    protected function getProductPriceManager() {
        if (empty($this->productPriceManager)) {
            $this->productPriceManager = $this->container->get('youppers.company.manager.product_price');
        }
        return $this->productPriceManager;
    }

	public function load($filename,$skip=0)
	{
		if (empty($this->pricelist)) {
			throw new \Exception("Pricelist MUST be set before loading prices.");
		}
	
		$this->logger->info(sprintf("Loading pricelist from '%s'.",$filename));
		if ($this->enable) {
			$this->logger->info("And enable products");
		}
	
		if ($skip>0) {
			$this->logger->info(sprintf("Skip '%d' rows",$skip));
		} else if ($this->append) {
			$this->logger->info("Appending to existing pricelist");
		} else {
			$query = $this->em->createQuery('DELETE Youppers\CompanyBundle\Entity\ProductPrice p WHERE p.pricelist = :pricelist');
			$query->setParameter('pricelist', $this->pricelist);
			if ($this->force) {
				$numDeleted = $query->execute();
				if ($numDeleted > 0) {
					$this->logger->info(sprintf("Deleted '%d' prices before reloading pricelist.",$numDeleted));
				}
			} else {
				$this->logger->debug("SQL: " . $query->getSql());
			}
		}

		parent::load($filename,$skip);
	}

    protected function batch()
    {
        if ($this->force) {
            $this->getProductManager()->flush();
            $this->getProductPriceManager()->getObjectManager()->flush();
			$this->getProductCollectionManager()->flush();
			$this->getProductVariantManager()->flush();
        } else {
            $this->getProductManager()->getObjectManager()->clear();
            $this->getProductPriceManager()->getObjectManager()->clear();
			$this->getProductCollectionManager()->getObjectManager()->clear();
			$this->getProductVariantManager()->getObjectManager()->clear();
        }
    }

    protected function handleBrand()
	{
		$brand = parent::handleBrand();

		if ($this->skip == 0 && $this->force && $this->enable && !array_key_exists($brand->getId(),$this->disabledBrands)) {
			$query = $this->em->createQuery('UPDATE Youppers\CompanyBundle\Entity\ProducT p SET p.enabled = false WHERE p.brand = :brand');
			$query->setParameter('brand', $brand);
			$query->execute();
			$this->disabledBrands[$brand->getId()] = $brand;
			$this->logger->info(sprintf("Disabled all products of brand '%s'",$brand));
		}		
		return $brand;
	}
	
	/**
	 * 
	 * @param Brand $brand
	 * @throws \Exception
	 * @return Product
	 */
	protected function handleProduct(Brand $brand)
	{
		$productCode = $this->mapper->remove('code');
		
		if (empty($productCode)) {
			throw new \Exception(sprintf("Product code not found in the column '%s'",$this->mapper->key('code')));
		}
		
		$product = $this->getProductManager()->findOneByBrandAndCode($brand, $productCode);
		if (empty($product)) {
			$product = $this->getProductManager()->create();
			$product->setBrand($brand);
			$product->setCode($productCode);
		}
		
		if ($this->enable) {
			$product->setEnabled(true);
		}
		$name = $this->mapper->remove('name');
		if (empty($name) && empty($product->getName())) {
			throw new \Exception(sprintf("Product name not found in the column '%s'",$this->mapper->key('name')));
		}
		$product->setName($name);
			
		$productGtin = $this->mapper->remove('gtin');
		if (!empty($productGtin)) {
            $product->setGtin($productGtin);
            if (!$this->checkUniqueGtin($product)) {
                $this->logger->error(sprintf("Duplicated gtin '%s' at row %d",$productGtin,$this->numRows));
                $product->setGtin(null);
            }
		}

		$info = json_encode($this->mapper->getData());
		if (json_last_error()>0) {
			$this->logger->error(json_last_error_msg());
		}
		$product->setInfo($info);

		if (empty($product->getId())) {
            $this->getProductManager()->save($product,false);
            $this->logger->info("New product: " . $product);
		} else {
			$this->logger->debug("Updated product: " . $product);
		}		
		
		return $product;
	}
	
	/**
	 * 
	 * @param Product $product
	 * @throws \Exception
	 * @return ProductPrice
	 */
	protected function handlePrice(Product $product)
	{
		$price = $this->getProductPriceManager()
		    ->findOneBy(array('product' => $product, 'pricelist' => $this->pricelist));
		if ($this->force && !empty($price)) {
			throw new \Exception(sprintf("Duplicated price at row %d: %s",$this->numRows,implode(',',$this->mapper->getData())));
		}
		
		$price = $this->getProductPriceManager()->create();
		$price->setPriceList($this->pricelist);
		$price->setProduct($product);
		$price->setPrice($this->normalizePrice($this->mapper->remove(self::FIELD_PRICE)));
		$price->setUom($this->mapper->remove(self::FIELD_UOM));
		$price->setQuantity($this->normalizeQuantity($this->mapper->remove(self::FIELD_QUANTITY)));
		$price->setSurface($this->normalizeSurface($this->mapper->remove(self::FIELD_SURFACE)));
		$this->getProductPriceManager()->save($price,false);
		return $price;
	}

	/**
	 * Normalize price, es: € 1.200,30 => 1200.30
	 * @param $price string
	 * @return string
	 */
	protected function normalizePrice($price)
	{
		$price1 = strtr($price,array(" " => "", "€" => "","." => "","," => "."));
		if (preg_match('/([0-9]*)\.([0-9]*)/',$price1,$matches)) {
			$price2 = $matches[1] . '.' . $matches[2];
			return $price2;
		} else if (preg_match('/([0-9]*)/',$price1,$matches)) {
			$price3 = $matches[1];
			return $price3;
		} else {
			throw new \InvalidArgumentException("Invalid price: " . $price);
		}
	}

	/**
	 * @param $quantity string
	 * @return string
	 */
	protected function normalizeQuantity($quantity)
	{
		if (empty($quantity)) {
			return $quantity;
		}
		if (preg_match('/([0-9]*)[\.,]([0-9]*)/',$quantity,$matches)) {
			$quantity2 = $matches[1] . '.' . $matches[2];
			return $quantity2;
		} else if (preg_match('/([0-9]*)/',$quantity,$matches)) {
			$quantity1 = $matches[1];
			return $quantity1;
		} else {
			throw new \InvalidArgumentException("Invalid quantity: " . $quantity);
		}
	}

	/**
	 * @param $surface string
	 * @return string
	 */
	protected function normalizeSurface($surface)
	{
		if (empty($surface)) {
			return $surface;
		}
		if (preg_match('/([0-9]*)[\.,]([0-9]*)/',$surface,$matches)) {
			$surface2 = $matches[1] . '.' . $matches[2];
			return $surface2;
		} else {
			throw new \InvalidArgumentException("Invalid surface: " . $surface);
		}
	}

	/**
	 *
	 * @param Product $product
	 * @return null|object
	 * @throws \Exception
	 */
	protected function handleCollection(Product $product, Brand $brand)
	{
		//$brand = $product->getBrand();
		$collectionName= $this->mapper->get(self::FIELD_COLLECTION);
		$collectionCode= $this->mapper->get(self::FIELD_COLLECTION_CODE);
		if (empty($collectionCode)) {
			$collectionCode = $this->container->get('youppers.common.service.codify')->codify($collectionName);
		}
		if ($collectionCode == null) {
			return null;
		} else {
			$collection = $this->getProductCollectionManager()->findByCode($brand, $collectionCode);
			if (empty($collection)) {
				$collection = $this->getProductCollectionManager()->createCollection($brand, $collectionName, $collectionCode, $this->getNewCollectionProductType($brand,$collectionCode));
			}
		}

		if (empty($collection->getId())) {
			$this->getProductCollectionManager()->save($collection,false);
			if ($this->force) {
				$this->logger->info(sprintf("Created new collection '%s'",$collection));
			} else {
				$this->logger->info(sprintf("New collection with code '%s' of Brand '%s'",$collectionCode,$brand));
			}
		} else {
			if ($this->force) {
				$this->logger->debug(sprintf("Updated collection '%s'",$collection));
			} else {
				$this->logger->debug(sprintf("Collection '%s'",$collection));
			}
		}

		return $collection;
	}

	/**
	 * @param ProductCollection $collection
	 * @param Product $product
	 * @return null|object
	 *
	 */
	protected function handleVariant(ProductCollection $collection, Product $product)
	{
		$criteria = Criteria::create()
			->where(Criteria::expr()->eq("product", $product));
		$variant = $collection->getProductVariants()->matching($criteria)->first();
		if (empty($variant)) {
			$variant = $this->getProductVariantManager()->findOneByProduct($product);
			if (!empty($variant)) {
				if ($this->changeCollection) {
					$this->logger->warning(sprintf("Variant '%s' changed from collection '%s' to '%s'",$product,$variant->getProductCollection(),$collection));
					$variant->setProductCollection($collection);
				} else {
					$this->logger->error(sprintf("Variant '%s' in collection '%s' instead of '%s'",$product,$variant->getProductCollection(),$collection));
				}
			}
		}
		if (empty($variant)) {
			$variant = $this->getProductVariantManager()->create();
			$variant->setProduct($product);
			$variant->setEnabled(false);
			$variant->setPosition($this->numRows);
			if ($this->force) {
				$collection->addProductVariant($variant);
			} else {
				$variant->setProductCollection($collection);
			}
		}

		if (empty($variant->getId())) {
			$this->getProductVariantManager()->save($variant,false);
			if ($this->force) {
				$this->logger->info(sprintf("Created new variant '%s'",$variant));
			} else {
				$this->logger->info(sprintf("New variant '%s'",$variant));
			}
		} else {
			if ($this->force) {
				$this->logger->debug(sprintf("Updated variant '%s'",$variant));
			} else {
				$this->logger->debug(sprintf("Variant '%s'",$variant));
			}
		}

		return $variant;
	}

	private $guesser = null;

	protected function doGuess(ProductVariant $variant)
	{
		if (empty($this->guesser)) {
			$this->guesser = $this->container->get('youppers.product.variant.guesser_factory')->create($this->company->getCode());
			$this->guesser->setForce($this->force);
		}
		$this->guesser->guessVariant($variant);
	}

	/**
	 * @param $row
	 * @throws \Exception
     */
	public function handleRow($row) {
		
		$this->mapper->setData($row);
		
		$brand = $this->handleBrand();
		$product = $this->handleProduct($brand);
		$price = $this->handlePrice($product);
		if ($this->loadProduct) {
			$collection = $this->handleCollection($product, $brand);
			$variant = $this->handleVariant($collection, $product);
			if ($this->guess) {
				$this->doGuess($variant);
			}
		}
	}
	
	
}
