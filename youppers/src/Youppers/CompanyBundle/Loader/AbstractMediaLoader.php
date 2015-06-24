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

use Application\Sonata\MediaBundle\Entity\Media;

abstract class AbstractMediaLoader extends AbstractLoader {


    private $prefix;

    public function setPrefix($prefix)
    {
        if (is_string($prefix)) {
            $this->prefix = trim($prefix);
        } else {
            throw new \InvalidArgumentException("Prefix must be a string");
        }
    }

    private $type;

    private $types = array(
        'company' => array(),
        'brand' => array(),
        'product' => array('context' => 'youppers_product'),
        'collection' => array(),
        'attribute' => array()
    );

    public function setType($type)
    {
        if (empty($type)) {
            return;
        }
        if (is_string($type)) {
            if (!array_key_exists($type,$this->types)) {
                throw new \InvalidArgumentException(sprintf("Invalid media Type '%s', valid options are: %s",$type, implode(', ',array_keys($this->types))));
            }
            $this->type = trim($type);
        } else {
            throw new \InvalidArgumentException("Media Type must be a string");
        }
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

        $this->logger->info(sprintf("Loading media from '%s'.",$filename));

        $reader = $this->createReader($filename);

        $this->numRows = 0;

        $reader->setHeaderRowNumber(0);

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

        $mediaType = $this->handleType();

        if ($mediaType == 'product') {
            $variant = $this->handleProductVariant($brand);
            if (empty($variant)) {
                return;
            }
            $currentMedia = $variant->getImage();

            if (!empty($currentMedia)) {
                $this->logger->warning(sprintf("Variant '%s' already has an image",$variant->getProduct()->getNameCode()));
                return;
            }
            $name = $this->container->get('sonata.core.slugify.cocur')->slugify($variant->getProduct()->getFullCode() . '-' . $variant->getProduct()->getName());
            $media = $this->handleMedia($mediaType,$name,$this->types[$mediaType]['context'],$name);
            if (empty($media)) {
                return;
            }
            if ($media->getProviderName() == 'sonata.media.provider.image') {
                $variant->setImage($media);
            }
        }
    }

    /**
     * @return Brand
     * @throws \Exception
     */
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

        return $brand;
    }

    protected function handleType()
    {
        $type = $this->mapper->remove(self::FIELD_TYPE);
        if (empty($type)) {
            $type = $this->type;
        }
        if (empty($type)) {
            throw new \Exception(sprintf("Media type MUST be in the column '%s' OR must be set manually",$this->mapper->key(self::FIELD_TYPE)));
        }
        return $type;
    }
    /**
     *
     * @param Brand $brand
     * @throws \Exception
     * @return ProductVariant
     */
    protected function handleProductVariant(Brand $brand)
    {
        $productCode = $this->container->get('youppers.common.service.codify')->codify($this->mapper->remove(self::FIELD_CODE));

        if (empty($productCode)) {
            throw new \Exception(sprintf("Product code not found in the column '%s'",$this->mapper->key(self::FIELD_CODE)));
        }

        $product = $this->getProductManager()
            ->findOneBy(array('brand' => $brand, 'code' => $productCode));

        if (empty($product)) {
            $this->logger->error(sprintf("Product '%s' not found",$productCode));
            return;
        }

        $variant = $product->getVariant();
        if (empty($variant)) {
            $this->logger->error(sprintf("Product '%s' has no variant",$product));
            return;
        }

        return $variant;
    }

    /**
     * @return \Sonata\MediaBundle\Model\MediaManagerInterface
     */
    protected function getMediaManager()
    {
        return $this->container->get('sonata.media.manager.media');
    }

    /**
     * @param $mediaType
     * @return Media
     */
    protected function handleMedia($mediaType, $name = null, $context = 'default')
    {
        $uri = $this->mapper->get(self::FIELD_RES);
        if (empty($uri)) {
            $this->logger->debug(sprintf("Resource not specified at row %d",$this->numRows));
            return;
        }

        $uri = $this->prefix . $uri;

        if (file_exists($uri)) {
            $this->logger->info(sprintf("Loading media '%s'",$uri));
        } else {
            $this->logger->error(sprintf("Media file not found '%s'",$uri));
            return;
        }

        $media = $this->getMediaManager()->create();
        $media->setBinaryContent($uri);
        $media->setContext($context);
        $media->setProviderName('sonata.media.provider.image');
        $media->setProviderReference($uri);
        if (empty($name)) {
            $name = $uri;
        }
        $media->setName($name);
        $this->logger->info(sprintf("Saved image '%s' as '%s'",trim($uri),$media->getName()));
        $this->getMediaManager()->save($media);

        return $media;
    }

}