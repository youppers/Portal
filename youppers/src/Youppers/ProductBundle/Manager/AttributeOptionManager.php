<?php

namespace Youppers\ProductBundle\Manager;

use Sonata\CoreBundle\Model\BaseEntityManager;
use Sonata\CoreBundle\Model\ManagerInterface;
use Doctrine\Common\Persistence\ManagerRegistry;

class AttributeOptionManager extends BaseEntityManager
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct('Youppers\ProductBundle\Entity\AttributeOption', $registry);
	}
		
}
