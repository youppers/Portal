<?php
namespace Youppers\CompanyBundle\Scraper;

use Goutte\Client;
use Youppers\CompanyBundle\Entity\Brand;
use Doctrine\Common\Persistence\ManagerRegistry;
use Youppers\ProductBundle\Entity\ProductCollection;
use Youppers\ProductBundle\Entity\ProductVariant;
use Youppers\ProductBundle\Manager\ProductCollectionManager;
use Youppers\ProductBundle\Manager\ProductTypeManager;

abstract class BaseScraper extends AbstractScraper
{
	
	private $collectionManager;
	
	public function setManagerRegistry(ManagerRegistry $managerRegistry)
	{
		parent::setManagerRegistry($managerRegistry);
		$this->collectionManager = new ProductCollectionManager($managerRegistry);
		$this->productTypeManager = new ProductTypeManager($managerRegistry);
	}

    protected $scrapeCollections;

    public function setScrapeCollections($scrapeCollections)
    {
        $this->scrapeCollections = $scrapeCollections;
    }

    protected $scrapeProducts;

    public function setScrapeProducts($scrapeProducts)
    {
        $this->scrapeProducts = $scrapeProducts;
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

    protected function getBrandCollections(Brand $brand)
    {
        if ($this->scrapeCollections) {
            $this->scrapeBrandCollections($this->brand);
        }
        return $this->collectionManager->findBy(array('brand' => $brand));
    }
	
	public function scrape()
    {
        if ($this->collection) {
            $this->scrapeCollection($this->collection);
        } elseif ($this->brand) {
            $this->scrapeBrand($this->brand);
        } else {
            $this->getLogger()->info(sprintf("Begin scraping company '%s'",$this->company));
            foreach ($this->company->getBrands() as $brand) {
                $this->scrapeBrand($brand);
            }
            $this->getLogger()->info(sprintf("End scraping company '%s'",$this->company));
        }
    }

    public function scrapeBrand(Brand $brand)
    {
		$this->getLogger()->info(sprintf("Begin scraping brand '%s'",$brand));
		
		$this->client = new Client();
		
		$collections = $this->getBrandCollections($brand);
		
		foreach ($collections as $collection) {
            $this->scrapeCollection($collection);
		}
        $this->getLogger()->info(sprintf("End scraping brand '%s'",$brand));
	}

    public function scrapeCollection(ProductCollection $collection)
    {
        $this->getLogger()->info(sprintf("Begin scraping collection '%s'",$collection));
        foreach ($collection->getProductVariants() as $variant) {
            $this->scrapeVariant($variant);
        }
        $this->getLogger()->info(sprintf("End scraping collection '%s'",$collection));
    }

    public function scrapeVariant(ProductVariant $variant)
    {
        $this->getLogger()->info(sprintf("Begin scraping variant '%s'",$variant));
        // TODO
        $this->getLogger()->info(sprintf("End scraping variant '%s'",$variant));
    }

}
