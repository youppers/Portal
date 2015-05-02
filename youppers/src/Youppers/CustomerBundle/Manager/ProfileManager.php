<?php

namespace Youppers\CustomerBundle\Manager;

use Sonata\CoreBundle\Model\BaseEntityManager;
use Sonata\CoreBundle\Model\ManagerInterface;
use Doctrine\Common\Persistence\ManagerRegistry;

use Youppers\CompanyBundle\Entity\Brand;
use Youppers\ProductBundle\Entity\ProductCollection;
use Youppers\ProductBundle\Entity\ProductType;

class ProfileManager extends BaseEntityManager
{
	
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct('Youppers\CustomerBundle\Entity\Profile', $registry);
	}
	
}
