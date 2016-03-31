<?php

namespace Youppers\CompanyBundle\Manager;

use Sonata\CoreBundle\Model\BaseEntityManager;
use Sonata\CoreBundle\Model\ManagerInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Youppers\CompanyBundle\Entity\Brand;
use Youppers\CompanyBundle\Entity\Product;

class ProductManager extends BaseEntityManager
{

    private $gtinCache = array();

    private $productCache = array();

    public function save($entity, $andFlush = true) {
        $gtin = $entity->getGtin();
        if ($gtin != null) {
            $this->gtinCache[$gtin] = $entity;
        }
        if ($andFlush) {
            $this->productCache = array();
        } else {
            $key = $entity->getBrand()->getCode() . '-' . $entity->getCode();
            $this->productCache[$key] = $entity;
        }
        return parent::save($entity,$andFlush);
    }

    public function flush()
    {
        $this->getEntityManager()->flush();
        $this->productCache = array();
    }

    public function findOneByGtin($gtin) {
        if (array_key_exists($gtin,$this->gtinCache)) {
            return $this->gtinCache[$gtin];
        } else {
            $product = $this->findOneBy(array('gtin' => $gtin));
            if ($product != null) {
                $this->gtinCache[$gtin] = $product;
            }
            return $product;
        }
    }

    public function findOneByBrandAndCode(Brand $brand, $productCode)
    {
        $key = $brand->getCode() . '-' . $productCode;
        if (array_key_exists($key,$this->productCache)) {
            return $this->productCache[$key];
        }
        return $this->findOneBy(array('brand' => $brand, 'code' => $productCode));
    }
		
}
