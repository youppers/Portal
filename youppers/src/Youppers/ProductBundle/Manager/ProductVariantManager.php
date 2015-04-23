<?php

namespace Youppers\ProductBundle\Manager;

use Sonata\CoreBundle\Model\BaseEntityManager;
use Sonata\CoreBundle\Model\ManagerInterface;
use Doctrine\Common\Persistence\ManagerRegistry;

//use Youppers\ProductBundle\Entity\ProductType;
use Youppers\ProductBundle\Entity\ProductCollection;

class ProductVariantManager extends BaseEntityManager
{

	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct('Youppers\ProductBundle\Entity\ProductVariant', $registry);
	}
		
	public function findByCollection(ProductCollection $collection)
	{		
		return $this->findBy(array('productCollection' => $collection));
	}

}
