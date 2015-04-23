<?php

namespace Youppers\ProductBundle\Manager;

use Sonata\CoreBundle\Model\BaseEntityManager;
use Sonata\CoreBundle\Model\ManagerInterface;
use Doctrine\Common\Persistence\ManagerRegistry;

class VariantPropertyManager extends BaseEntityManager
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct('Youppers\ProductBundle\Entity\VariantProperty', $registry);
	}
		
}
