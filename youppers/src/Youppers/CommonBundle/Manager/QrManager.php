<?php

namespace Youppers\CommonBundle\Manager;

use Sonata\CoreBundle\Model\BaseEntityManager;
use Sonata\CoreBundle\Model\ManagerInterface;
use Doctrine\Common\Persistence\ManagerRegistry;

class QrManager extends BaseEntityManager
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct('Youppers\CommonBundle\Entity\Qr', $registry);
	}
		
}
