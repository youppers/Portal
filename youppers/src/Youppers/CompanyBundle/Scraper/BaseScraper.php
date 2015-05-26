<?php
namespace Youppers\ScraperBundle\Scraper;

use Goutte\Client;
use Youppers\CompanyBundle\Entity\Brand;
use Doctrine\Common\Persistence\ManagerRegistry;
use Youppers\ProductBundle\Manager\ProductCollectionManager;
use Youppers\ProductBundle\Manager\ProductTypeManager;

abstract class BaseScraper extends AbstractScraper
{
	
	private $collectionManager;
	
	public function setManagerRegistry(ManagerRegistry $managerRegistry)
	{
		parent::setManagerRegistry($managerRegistry);
		$this->collectionManager = new ProductCollectionManager($this->em);		
		$this->productTypeManager = new ProductTypeManager($this->em);		
	}
		
	protected $createCollection;
	
	public function setCreateCollection($createCollection)
	{
		$this->createCollection = $createCollection;
	}
	
	private $newCollectionProductType;
	
	protected function getNewCollectionProductType(Brand $brand, $code)
	{
		if (!isset($this->newCollectionProductType)) {
			$this->newCollectionProductType = $this->productTypeManager->findByCode('tile');
		}
		return $this->newCollectionProductType;
	}
	
	protected function getCollection($text)
	{
		$collectionCode = $this->container->get('youppers.common.service.slugify')->codify($text);
		$collection = $this->collectionManager->findByCode($this->brand, $collectionCode);
		if (empty($collection) && $this->force && $this->createCollection) {
			$collectionName = $text;
			$collection = $this->collectionManager->create($this->brand, $collectionName, $collectionCode, $this->getNewCollectionProductType($this->brand,$collectionCode));
			$this->collectionManager->save($collection);
			$this->logger->info(sprintf("Created collection with code '%s' for Brand '%s'",$collectionCode,$this->brand));
		}
		if (empty($collection)) {
			if ($this->force && $this->createCollection) {
				throw new \Exception(sprintf("Collection '%s' [%s] of Brand '%s' not created",$text,$collectionCode,$this->brand));
			} else {
				$this->logger->warning(sprintf("Collection '%s' [%s] of Brand '%s' not found",$text,$collectionCode,$this->brand));
			}
		}
		return $collection;				
	}
	
	protected $client;
	
	
	public function scrape()
	{
		$this->getLogger()->info("Begin scraping of ".$this->brand);
		
		$this->client = new Client();
		
		$collections = $this->scrapeCollections();
		
		foreach ($collections as $collection) {
			$this->getLogger()->info(sprintf("Collection: '%s'",$collection));				
		}		
		$this->getLogger()->info("End scraping.");
	}
	
}
