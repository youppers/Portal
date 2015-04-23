<?php

namespace Youppers\ProductBundle\Manager;

use Doctrine\ORM\EntityManager;
use Youppers\ProductBundle\Entity\ProductType;

class ProductTypeManager
{
	/**
	 * @var EntityManager
	 */
	protected $em;
	
	/**
	 * @var EntityRepository
	 */
	protected $repository;
	
	/**
	 * @var string
	 */
	protected $class;
	
	/**
	 * Constructor.
	 *
	 * @param EntityManager            $em
	 * @param string                   $class
	 */
	public function __construct(EntityManager $em, $class = 'YouppersProductBundle:ProductType')
	{
		$this->em = $em;
		$this->repository = $em->getRepository($class);
	
		$metadata = $em->getClassMetadata($class);
		$this->class = $metadata->name;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function findById($id)
	{
		return $this->repository->find($id);
	}
	
	/**
	 *
	 * @param string $code Product Type code
	 */	
	public function findByCode($code)
	{
		return $this->repository->findOneBy(array('code' => $code));
	}
		
	/**
	 * {@inheritdoc}
	 */
	public function getClass()
	{
		return $this->class;
	}

}
