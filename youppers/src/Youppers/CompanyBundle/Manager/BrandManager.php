<?php

namespace Youppers\CompanyBundle\Manager;

use Sonata\CoreBundle\Model\BaseEntityManager;
use Sonata\CoreBundle\Model\ManagerInterface;
use Doctrine\Common\Persistence\ManagerRegistry;

class BrandManager extends BaseEntityManager
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct('Youppers\CompanyBundle\Entity\Brand', $registry);
	}
		
}
