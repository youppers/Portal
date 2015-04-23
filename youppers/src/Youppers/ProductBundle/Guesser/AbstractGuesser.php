<?php

namespace Youppers\ProductBundle\Guesser;

use Doctrine\Common\Collections\Criteria;
use Ddeboer\DataImport\Reader\CsvReader;
use Doctrine\Common\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Youppers\CompanyBundle\YouppersCompanyBundle;
use Monolog\Logger;
use Youppers\CompanyBundle\Entity\ProductPrice;
use Youppers\CompanyBundle\Entity\Company;
use Youppers\CompanyBundle\Entity\Brand;
use Youppers\ProductBundle\Entity\ProductVariant;
use Youppers\ProductBundle\Entity\ProductCollection;
use Youppers\ProductBundle\Manager\ProductCollectionManager;
use Youppers\ProductBundle\Manager\ProductVariantManager;
use Youppers\ProductBundle\Manager\VariantPropertyManager;

abstract class AbstractGuesser extends ContainerAware
{
	
	const COMMA = ',';
	const DOT = '.';
	const PER = 'x';
	
	private $todos = array();
	
	private $parent = null; // Parent Guesser
	
	protected function setParent(AbstractGuesser $parent)
	{
		$this->parent = $parent;
		$this->setManagerRegistry($parent->getManagerRegistry());
	}
	
	public function getTodos()
	{
		return $this->todos;
	}
	
	protected function addTodo($todo)
	{
		if ($this->parent) {
			$this->parent->addTodo($todo);
		} else if (!in_array($todo,$this->todos)) {
			$this->todos[] = $todo;
		}
	}
			
	protected $managerRegistry;
	protected $em;
	protected $collectionManager;
	protected $variantManager;
	protected $variantPropertyManager;

	public function setManagerRegistry(ManagerRegistry $managerRegistry)
	{
		$this->managerRegistry = $managerRegistry;
		$this->em = $managerRegistry->getManager();
		$this->collectionManager = new ProductCollectionManager($managerRegistry);
		$this->variantManager = new ProductVariantManager($managerRegistry);
		$this->variantPropertyManager = new VariantPropertyManager($managerRegistry);
	}

	public function getManagerRegistry()
	{
		if ($this->parent) {
			return $this->parent->getManagerRegistry();
		} else {
			return $this->managerRegistry;
		}
	}
	
	protected $logger;
	
	public function setLogger(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}
	
	public function getLogger()
	{
		if ($this->parent) {
			return $this->parent->getLogger();
		} else {
			return $this->logger;
		}
	}
	
		
	protected $force = false;
	
	public function setForce($force) {
		$this->force = $force;
	}

	protected $debug = false;
	
	public function setDebug($debug)
	{
		$this->debug = $debug;
	}

	protected $company;
	
	public function setCompany(Company $company)
	{
		$this->company = $company;
	}

	protected $brand;
	
	public function setBrand(Brand $brand)
	{
		$this->brand = $brand;
	}
	
	protected $collection = null;
	
	public function setCollection($collectionCode)
	{
		if (empty($collectionCode)) {
			return;
		}
		$this->collection = $this->collectionManager->findByCode($this->brand, $collectionCode); 
		
		if (empty($this->collection)) {
			throw new \Exception(sprintf("Collection '%s' not found",$collectionCode));
		}

	}

	public function guess()
	{
		if ($this->collection) {
			$this->guessCollection($this->collection);
		} else {
			$collections = $this->collectionManager->findByBrand($this->brand);
			foreach ($collections as $collection) {
				$this->guessCollection($collection);
			}
		}
	}
	
	/**
	 * 
	 */
	public function guessCollection(ProductCollection $collection)
	{		
		$variants = $this->variantManager->findByCollection($collection);
		$this->logger->info(sprintf("Guessing %d variants for collection '%s'",count($variants),$collection));
		$guessers = $this->getCollectionGuessers($collection);
		foreach ($variants as $variant) {
			$this->guessVariant($variant,$guessers);
		}
	}	
	
	protected function getCollectionGuessers($collection)
	{
		// TODO parametrico
		$guessers = array();
		$guesser = new BaseDimensionPropertyGuesser();
		$guesser->setParent($this);
		$guessers[] = $guesser;
		return $guessers;
	}

}
