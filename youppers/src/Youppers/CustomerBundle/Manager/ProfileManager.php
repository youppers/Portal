<?php

namespace Youppers\CustomerBundle\Manager;

use Sonata\CoreBundle\Model\BaseEntityManager;
use Doctrine\Common\Persistence\ManagerRegistry;

class ProfileManager extends BaseEntityManager
{
	
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct('Youppers\CustomerBundle\Entity\Profile', $registry);
	}
	
}
