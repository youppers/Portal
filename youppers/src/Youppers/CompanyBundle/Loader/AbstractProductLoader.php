<?php

namespace Youppers\CompanyBundle\Loader;

use Symfony\Component\Stopwatch\Stopwatch;

use Youppers\CompanyBundle\Entity\Company;
use Youppers\CompanyBundle\Manager\CompanyManager;

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

    const BATCH_SIZE = 100;

    const FIELD_BRAND = 'brand';
    const FIELD_COLLECTION = 'collection';
    const FIELD_NAME = 'name';
    const FIELD_DESCRIPTION = 'desciption';
    const FIELD_CODE = 'code';
    const FIELD_GTIN = 'gtin';

    /**
     * @var CompanyManager
     */
    private $companyManager;

    /**
     * @return CompanyManager
     */
    protected function getCompanyManager() {
        if (empty($this->companyManager)) {
            $this->companyManager = $this->container->get('youppers.company.manager.company');
        }
        return $this->companyManager;
    }

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
     * @return ProductManager
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
        return $this->productTypeManager;
    }

    protected abstract function getNewCollectionProductType(Brand $brand, $code);

	private $brands = array();

	public function batch()
	{
        if ($this->force) {
            $this->getProductManager()->getEntityManager()->flush();
            $this->getProductCollectionManager()->getEntityManager()->flush();
            $this->getProductVariantManager()->getEntityManager()->flush();
        } else {
            $this->getProductManager()->getObjectManager()->clear();
            $this->getProductCollectionManager()->getEntityManager()->clear();
            $this->getProductVariantManager()->getEntityManager()->clear();
        }
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

            if ($this->numRows % self::BATCH_SIZE == 0) {
                $this->logger->info(sprintf("Read %d rows",$this->numRows));
                $this->batch();
            }
        }

        $this->batch();

        $event = $stopwatch->stop('load');
        $this->logger->info(sprintf("Load done, read %d rows in %d mS",$this->numRows,$event->getDuration()));
    }

    public function handleRow($row) {

        $this->mapper->setData($row);

        $brand = $this->handleBrand();

        $product = $this->handleProduct($brand);

        $collection = $this->handleCollection($product, $brand);

        $variant = $this->handleVariant($collection, $product);
    }

    //private $disabledBrands = array();

    protected function handleBrand()
    {
        $brandCode = $this->mapper->remove(self::FIELD_BRAND);

        if (empty($this->brand)) {
            if (empty($brandCode)) {
                throw new \Exception(sprintf("Brand MUST be in the column '%s' OR must be set manually",$this->mapper->key('brand')));
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
        $productCode = $this->container->get('youppers.common.service.codify')->codify($this->mapper->remove(self::FIELD_CODE));

        if (empty($productCode)) {
            throw new \Exception(sprintf("Product code not found in the column '%s'",$this->mapper->key(self::FIELD_CODE)));
        }

        $product = $this->getProductManager()
            ->findOneBy(array('brand' => $brand, 'code' => $productCode));

        if (empty($product)) {
            $product = $this->getProductManager()->create();
            $product->setBrand($brand);
            $product->setCode($productCode);
        }
        if ($this->enable) {
            $product->setEnabled(true);
        }
        $name = $this->mapper->remove(self::FIELD_NAME);
        $description = $this->mapper->remove(self::FIELD_DESCRIPTION);
        if (empty($name) && empty($product->getName())) {
            $a = preg_split('/[\.\n]/',$description,2);
            $name = $a[0];
            if (empty($name)) {
                throw new \Exception(sprintf("Product name not found in the column '%s'",$this->mapper->key(self::FIELD_NAME)));
            }
        }
        if (!empty($name)) $product->setName($name);
        if (!empty($description)) $product->setDescription($description);

        $productGtin = $this->mapper->remove(self::FIELD_GTIN);
        if (!empty($productGtin)) {
            $product->setGtin($productGtin);
            if (!$this->checkUniqueGtin($product)) {
                $this->logger->error(sprintf("Duplicated gtin '%s'",$productGtin));
                $product->setGtin(null);
            }
        }

        $info = json_encode($this->mapper->getData());
        $product->setInfo($info);

        if (empty($product->getId())) {
            $this->getProductManager()->save($product,false);
            if ($this->force) {
                $this->logger->info("Created new product: " . $product);
            } else {
                $this->logger->info("New product: " . $product);
            }
        } else {
            if ($this->force) {
                $this->logger->debug("Updated product: " . $product);
            } else {
                $this->logger->debug("Product: " . $product);
            }
        }

        return $product;
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
		$collectionCode = $this->container->get('youppers.common.service.codify')->codify($collectionName);
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


}