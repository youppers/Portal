<?php

namespace Youppers\CustomerBundle\Manager;

use Sonata\CoreBundle\Model\BaseEntityManager;
use Doctrine\Common\Persistence\ManagerRegistry;
use Sonata\UserBundle\Model\UserInterface;

class ZoneManager extends BaseEntityManager
{
	
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct('Youppers\CustomerBundle\Entity\Zone', $registry);
	}
	
	public function findAllForUser(UserInterface $user)
	{
		$qb = $this->getRepository()->createQueryBuilder('z');
		$qb->leftjoin('z.profile','p')
			->where('p.user = :user')
			->setParameter('user', $user)
			->orWhere('z.profile is null')
			->orderBy('z.name','ASC');
		return $qb->getQuery()->execute();
	}
}
