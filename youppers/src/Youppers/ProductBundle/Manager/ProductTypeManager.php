<?php

namespace Youppers\ProductBundle\Manager;

use Sonata\CoreBundle\Model\BaseEntityManager;
use Sonata\CoreBundle\Model\ManagerInterface;
use Doctrine\Common\Persistence\ManagerRegistry;

use Youppers\ProductBundle\Entity\ProductType;

class ProductTypeManager extends BaseEntityManager
{
	
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct('Youppers\ProductBundle\Entity\ProductType', $registry);
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function findById($id)
	{
		return $this->find($id);
	}
	
	/**
	 *
	 * @param string $code Product Type code
	 */	
	public function findByCode($code)
	{
		return $this->findOneBy(array('code' => $code));
	}

}
