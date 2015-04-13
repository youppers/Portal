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

abstract class AbstractGuesser extends ContainerAware
{
	
	const TYPE_DIMENSION = 'DIM';
	const COMMA = ',';
	const DOT = '.';
	const PER = 'x';
	
	protected $managerRegistry;
	protected $em;

	public function setManagerRegistry(ManagerRegistry $managerRegistry)
	{
		$this->managerRegistry = $managerRegistry;
		$this->em = $managerRegistry->getManager();
	}

	protected $logger;
	
	public function setLogger(LoggerInterface $logger)
	{
		$this->logger = $logger;
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
		$this->collection = $this
			->managerRegistry
			->getRepository('YouppersProductBundle:ProductCollection')
			->findOneBy(array('brand' => $this->brand, 'code' => $collectionCode));
		
		if (empty($this->collection)) {
			throw new \Exception(sprintf("Collection '%s' not found",$collectionCode));
		}

	}

	public function guess()
	{
		if ($this->collection) {
			$this->guessCollection($this->collection);
		} else {
			$collections = $this
				->managerRegistry
				->getRepository('YouppersProductBundle:ProductCollection')
				->findBy(array('brand' => $this->brand));
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
		$variants = $this
			->managerRegistry
			->getRepository('YouppersProductBundle:ProductVariant')
			->findBy(array('productCollection' => $collection));
		$this->logger->info(sprintf("Guessing %d variants for collection '%s'",count($variants),$collection));
		foreach ($variants as $variant) {
			$this->guessVariant($variant);
		}
	}	

	protected abstract function guessVariant(ProductVariant $variant);

	public abstract function getTodos();
	
}
