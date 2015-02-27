<?php

namespace Youppers\CustomerBundle\Entity;

use Sonata\AdminBundle\Datagrid\PagerInterface;

//use Sonata\ClassificationBundle\Model\CategoryInterface;
use Youppers\CustomerBundle\Model\SessionManagerInterface;

use Sonata\CoreBundle\Model\BaseEntityManager;

use Sonata\DatagridBundle\Pager\Doctrine\Pager;
use Sonata\DatagridBundle\ProxyQuery\Doctrine\ProxyQuery;

class SessionManager extends BaseEntityManager implements SessionManagerInterface
{
	
	/**
	 * {@inheritdoc}
	 */
	public function getPager(array $criteria, $page, $limit = 10, array $sort = array())
	{
		$parameters = array();
	
		$query = $this->getRepository()
		->createQueryBuilder('c')
		->select('c');
	
		/*
		if (isset($criteria['enabled'])) {
			$query->andWhere('c.enabled = :enabled');
			$parameters['enabled'] = (bool) $criteria['enabled'];
		}
		*/
	
		$query->setParameters($parameters);
	
		$pager = new Pager();
		$pager->setMaxPerPage($limit);
		$pager->setQuery(new ProxyQuery($query));
		$pager->setPage($page);
		$pager->init();
	
		return $pager;
	}
	
}