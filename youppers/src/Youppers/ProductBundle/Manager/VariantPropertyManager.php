<?php

namespace Youppers\ProductBundle\Manager;

use Sonata\CoreBundle\Model\BaseEntityManager;
use Doctrine\Common\Persistence\ManagerRegistry;
use Youppers\ProductBundle\Entity\AttributeOption;

class VariantPropertyManager extends BaseEntityManager
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct('Youppers\ProductBundle\Entity\VariantProperty', $registry);
	}

	/**
	 * @param AttributeOption $option
	 * @return array
	 */
	public function findByOption(AttributeOption $option)
	{
		return $this->findBy(array('attributeOption' => $option));
	}		
}
