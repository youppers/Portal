<?php

namespace Youppers\DealerBundle\Manager;

use Sonata\CoreBundle\Model\BaseEntityManager;
use Doctrine\Common\Persistence\ManagerRegistry;

class DealerManager extends BaseEntityManager
{
	
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct('Youppers\DealerBundle\Entity\Dealer', $registry);
	}
	
}
