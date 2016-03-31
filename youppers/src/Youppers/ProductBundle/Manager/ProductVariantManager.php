<?php

namespace Youppers\ProductBundle\Manager;

use Sonata\CoreBundle\Model\BaseEntityManager;
use Sonata\CoreBundle\Model\ManagerInterface;
use Doctrine\Common\Persistence\ManagerRegistry;

//use Youppers\ProductBundle\Entity\ProductType;
use Youppers\CompanyBundle\Entity\Product;
use Youppers\ProductBundle\Entity\ProductVariant;
use Youppers\ProductBundle\Entity\ProductCollection;

class ProductVariantManager extends BaseEntityManager
{

	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct('Youppers\ProductBundle\Entity\ProductVariant', $registry);
	}

	public function save($entity, $andFlush = true) {
		return $this->saveProductVariant($entity, $andFlush);
	}

	private $cache = array();

	private function saveProductVariant(ProductVariant $productVariant, $andFlush = true)
	{
		if ($andFlush) {
			$this->cache = array();
		} else {
			$this->cache[$productVariant->getProduct()->getId()] = $productVariant;
		}
		return parent::save($productVariant,$andFlush);
	}

	public function findOneByProduct(Product $product)
	{
		$key = $product->getId();
		if (array_key_exists($key,$this->cache)) {
			return $this->cache[$key];
		}
		return $this->findOneBy(array('product' => $product));
	}

	public function findByCollection(ProductCollection $collection)
	{		
		return $this->findBy(array('productCollection' => $collection));
	}

}
