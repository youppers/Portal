<?php

namespace Youppers\CompanyBundle\Loader;

use Symfony\Component\Stopwatch\Stopwatch;

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
        $this->logger->info(sprintf("Loading media from '%s'.", $filename));
        parent::load($filename,$skip);
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