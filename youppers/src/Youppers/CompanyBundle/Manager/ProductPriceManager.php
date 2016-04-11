<?php

namespace Youppers\CompanyBundle\Manager;

use Sonata\CoreBundle\Model\BaseEntityManager;
use Youppers\CompanyBundle\Entity\Product;
use Youppers\CompanyBundle\Entity\Pricelist;

class ProductPriceManager extends BaseEntityManager
{

    private $priceCache = array();

    public function save($entity, $andFlush = true) {
        if ($andFlush) {
            $this->priceCache = array();
        } else {
            $key = $entity->getPricelist()->getCode() . '-' . $entity->getProduct()->getCode();
            $this->priceCache[$key] = $entity;
        }
        return parent::save($entity,$andFlush);
    }

    public function flush()
    {
        $this->getEntityManager()->flush();
        $this->priceCache = array();
    }

    public function findOneByProductAndList(Product $product, Pricelist $pricelist)
    {
        $key = $pricelist->getCode() . '-' . $product->getCode();
        if (array_key_exists($key,$this->priceCache)) {
            return $this->priceCache[$key];
        }
        return $this->findOneBy(array('product' => $product, 'pricelist' => $pricelist));
    }

}
