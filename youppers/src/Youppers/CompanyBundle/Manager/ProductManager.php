<?php

namespace Youppers\CompanyBundle\Manager;

use Sonata\CoreBundle\Model\BaseEntityManager;
use Sonata\CoreBundle\Model\ManagerInterface;
use Doctrine\Common\Persistence\ManagerRegistry;

class ProductManager extends BaseEntityManager
{

    private $gtinCache = array();

    public function save($entity, $andFlush = true) {
        $gtin = $entity->getGtin();
        if ($gtin != null) {
            $this->gtinCache[$gtin] = $entity;
        }
        return parent::save($entity,$andFlush);
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
		
}
