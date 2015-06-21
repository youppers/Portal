<?php

namespace Youppers\CompanyBundle\Loader;

use Symfony\Component\Stopwatch\Stopwatch;

use Youppers\CompanyBundle\Entity\Brand;
use Youppers\CompanyBundle\Manager\BrandManager;

use Youppers\CompanyBundle\Entity\Product;
use Youppers\CompanyBundle\Manager\ProductManager;

use Youppers\ProductBundle\Entity\ProductCollection;
use Youppers\ProductBundle\Manager\ProductCollectionManager;

use Youppers\ProductBundle\Entity\ProductVariant;
use Youppers\ProductBundle\Manager\ProductVariantManager;

use Doctrine\Common\Collections\Criteria;
use Youppers\ProductBundle\Manager\ProductTypeManager;

abstract class AbstractProductLoader extends AbstractLoader {

    /**
     * @var BrandManager
     */
    private $brandManager;

    /**
     * @return BrandManager
     */
    protected function getBrandManager() {
        if (empty($this->brandManager)) {
            $this->brandManager = $this->container->get('youppers.company.manager.brand');
        }
        return $this->brandManager;
    }

    /**
     * @var ProductManager
     */
    private $productManager;

    /**
     * @return
     */
    protected function getProductManager() {
        if (empty($this->productManage)) {
            $this->productManage = $this->container->get('youppers.company.manager.product');
        }
        return $this->productManage;
    }



    /**
     * @var ProductCollectionManager
     */
    private $productCollectionManager;

    /**
     * @return ProductCollectionManager
     */
    protected function getProductCollectionManager() {
        if (empty($this->productCollectionManager)) {
            $this->productCollectionManager = $this->container->get('youppers.product.manager.product_collection');
        }
        return $this->productCollectionManager;
    }

    /**
     * @var ProductVariantManager
     */
    private $productVariantManager;

    /**
     * @return ProductVariantManager
     */
    protected function getProductVariantManager() {
        if (empty($this->productVariantManager)) {
            $this->productVariantManager = $this->container->get('youppers.product.manager.product_variant');
        }
        return $this->productVariantManager;
    }

    private $productTypeManager;

    protected function getProductTypeManager() {
        if (empty($this->productTypeManager)) {
            $this->productTypeManager = $this->container->get('youppers.product.manager.product_type');
        }
        return $this->productVariantManager;
    }

    protected abstract function getNewCollectionProductType(Brand $brand, $code);

	private $brands = array();
	
	private $createCollection = false;
	
	public function setCreateCollection($createCollection)
	{
		$this->createCollection = $createCollection;
	} 

	private $createVariant = false;
	
	public function setCreateVariant($createVariant)
	{
		$this->createVariant = $createVariant;
	}

	public function batchClear()
	{
        $this->getProductManager()->clear();
        $this->getProductVariantManager()->clear();
        $this->getProductCollectionManager()->clear();
	}

    public function load($filename,$skip=0)
    {
        $this->skip = $skip;

        $this->logger->info(sprintf("Loading products from '%s'.",$filename));
        if ($this->enable) {
            $this->logger->info("And enable products");
        }

        $reader = $this->createReader($filename);

        $this->numRows = 0;

        $reader->setHeaderRowNumber(0);

        $this->serializer = $this->container->get('serializer');

        $this->mapper = $this->createMapper();

        $this->logger->info("Using mapper: " . $this->mapper);

        if ($skip>0) {
            $this->logger->info(sprintf("Skip '%d' rows",$skip));
        }

        // speed up
        if (!$this->debug) {
            $this->container->get('doctrine')->getConnection()->getConfiguration()->setSQLLogger(null);
        }

        $stopwatch = new Stopwatch();
        $stopwatch->start('load');
        foreach ($reader as $row) {

            $this->numRows++;
            if ($this->numRows <= $skip) {
                continue;
            }

            $this->handleRow($row);

            if ($this->numRows % 500 == 0) {
                $this->logger->info(sprintf("Read %d rows",$this->numRows));
                if ($this->force) {
                    $this->em->flush();
                } else {
                    //
                }
                $this->batchClear();
            }
        }

        $this->em->flush();

        $event = $stopwatch->stop('load');
        $this->logger->info(sprintf("Load done, read %d rows in %d mS",$this->numRows,$event->getDuration()));
    }

    public function handleRow($row) {

        $this->mapper->setData($row);

        $brand = $this->handleBrand();

        if ($brand) {
            $product = $this->handleProduct($brand);
        }

        if ($product) {
            $collection = $this->handleCollection($product);
        }

        if ($collection) {
            $variant = $this->handleVariant($collection, $product);
        }

        die;

    }

    //private $disabledBrands = array();

    protected function handleBrand()
    {
        $brandCode = $this->mapper->remove('brand');
        if (empty($this->brand)) {
            if (empty($brandCode)) {
                throw new \Exception(sprintf("Brand column MUST be in the column '%s' OR must be set manually",$this->mapper->key('brand')));
            }
            $brand = $this->getBrandManager()->findOneBy(array('company' => $this->company, 'code' => $brandCode));
            if (empty($brand)) {
                throw new \Exception(sprintf("At row '%d': Brand '%s' not found for Company '%s'",$this->numRows,$brandCode,$this->company));
            }
        } else {
            $brand = $this->brand;
        }

        /*
        if ($this->skip == 0 && $this->force && $this->enable && !array_key_exists($brand->getId(),$this->disabledBrands)) {
            $query = $this->container->get('youppers.company.manager.product')->getEntityManager()
                ->createQuery('UPDATE Youppers\CompanyBundle\Entity\ProducT p SET p.enabled = false WHERE p.brand = :brand');
            $query->setParameter('brand', $brand);
            $query->execute();
            $this->disabledBrands[$brand->getId()] = $brand;
            $this->logger->info(sprintf("Disabled all products of brand '%s'",$brand));
        }
        */
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

        $product = $this->getProductManager()
            ->findOneBy(array('brand' => $brand, 'code' => $productCode));

        if (empty($product)) {
            $product = $this->getProductManager()->create();
            $product->setBrand($brand);
            $product->setCode($productCode);
        } elseif (!$this->force) {
            $product = clone $product;
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
                $this->logger->error(sprintf("Duplicated gtin '%s'",$productGtin));
                $product->setGtin(null);
            }
        }

        $productDescription = $this->mapper->remove('gtin');
        if (!empty($productDescription)) {
            $product->setProductDescription($productDescription);
        }

        $info = json_encode($this->mapper->getData());
        $product->setInfo($info);

        if (empty($product->getId())) {l
            if ($this->force && $this->createProduct) {
                $this->logger->info("Created new product: " . $product);
                $this->getProductManager()->save($product,false);
            } else {
                $this->logger->info("New product: " . $product);
            }
        } elseif (!$this->force) {
            $this->logger->debug("Updated product: " . $product);
        }

        return $product;
    }

    protected function handleCollection(Product $product)
	{
        $brand = $product->getBrand();
		$collectionCode = $this->mapper->get('collection');
		if ($collectionCode !== null) {
			$collection = $this->getProductCollectionManager()->findByCode($brand, $collectionCode);
			if (empty($collection) && $this->force && $this->createCollection) {				
				$collection = $this->getProductCollectionManager()->createCollection($brand, $collectionCode, $collectionCode, $this->getNewCollectionProductType($brand,$collectionCode));
				$this->getProductCollectionManager()->save($collection,false);
				$this->logger->info(sprintf("Created collection with code '%s' for Brand '%s'",$collectionCode,$brand));
			}
			if (empty($collection)) {
				if ($this->force && $this->createCollection) {
					throw new \Exception(sprintf("Created new collection with code '%s' of Brand '%s' not found",$collectionCode,$brand));
				}
				$this->logger->info(sprintf("New collection with code '%s' of Brand '%s'",$collectionCode,$brand));
			}
			//$this->logger->info("Collection: " . $collection);				
		}
		
		return $collection;
	}
	
	protected function handleVariant(ProductCollection $collection, Product $product)
	{
		$criteria = Criteria::create()
			->where(Criteria::expr()->eq("product", $product));		
		$variant = $collection->getProductVariants()->matching($criteria)->first();
		if (empty($variant)) {		
			$variant = $this->getProductVariantManager()
				->findOneBy(array('product' => $product));
			if (!empty($variant)) {
				$this->logger->error(sprintf("Product '%s' in collection '%s' instead of '%s'",$product,$variant->getProductCollection(),$collection));
			}
		}
		if (empty($variant)) {
			if ($this->force && $this->createVariant) {
				$variant = $this->getProductVariantManager()->create();
				$variant->setProduct($product);
				$variant->setEnabled(false);
				$variant->setPosition($this->numRows);
				$collection->addProductVariant($variant);
				$this->getProductVariantManager()->save($variant,false);
				$this->logger->info(sprintf("Created new variant '%s'",$variant));
			} else {
				$this->logger->info(sprintf("New variant '%s' - '%s'",$collection,$product->getNameCode()));
			}
		}

		return $variant;
	}


}