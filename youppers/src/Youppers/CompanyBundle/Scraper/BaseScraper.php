<?php
namespace Youppers\CompanyBundle\Scraper;

use Goutte\Client;
use GuzzleHttp\Client as GuzzleClient;
use Sonata\MediaBundle\Model\Media;
use Symfony\Component\HttpFoundation\File\File;
use Youppers\CompanyBundle\Entity\Brand;
use Doctrine\Common\Persistence\ManagerRegistry;
use Youppers\ProductBundle\Entity\ProductCollection;
use Youppers\ProductBundle\Entity\ProductVariant;
use Youppers\ProductBundle\Manager\ProductCollectionManager;
use Youppers\ProductBundle\Manager\ProductTypeManager;
use Youppers\ProductBundle\Manager\ProductVariantManager;

abstract class BaseScraper extends AbstractScraper
{
	
	private $collectionManager;
    private $productTypeManager;
    private $variantManager;

	public function setManagerRegistry(ManagerRegistry $managerRegistry)
	{
		parent::setManagerRegistry($managerRegistry);
		$this->collectionManager = new ProductCollectionManager($managerRegistry);
		$this->productTypeManager = new ProductTypeManager($managerRegistry);
        $this->variantManager = new ProductVariantManager($managerRegistry);
	}

    /**
     * @return \Sonata\MediaBundle\Model\MediaManagerInterface
     */
    public function getMediaManager()
    {
        return $this->container->get('sonata.media.manager.media');
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
        $this->client = new Client();

        if ($this->collection) {
            $this->scrapeCollection($this->collection);
        } elseif ($this->brand) {
            $this->scrapeBrand($this->brand);
        } else {
            $this->getLogger()->info(sprintf("Scraping company '%s'",$this->company));
            foreach ($this->company->getBrands() as $brand) {
                $this->scrapeBrand($brand);
            }
            $this->getLogger()->debug(sprintf("End scraping company '%s'",$this->company));
        }
    }

    public function scrapeBrand(Brand $brand)
    {
		$this->getLogger()->info(sprintf("Scraping brand '%s'",$brand));
		
		$collections = $this->getBrandCollections($brand);
		
		foreach ($collections as $collection) {
            $this->scrapeCollection($collection);
		}
        $this->getLogger()->debug(sprintf("End scraping brand '%s'",$brand));
	}

    public function scrapeCollection(ProductCollection $collection)
    {
        $this->getLogger()->info(sprintf("Scraping collection '%s'",$collection));
        foreach ($collection->getProductVariants() as $variant) {
            $this->scrapeVariant($variant);
        }
        $this->getLogger()->debug(sprintf("End scraping collection '%s'",$collection));
    }

    public function scrapeVariant(ProductVariant $variant)
    {
        if ($variant->getScrapedAt()->diff(new \DateTime())->days < 1) {
            $this->getLogger()->debug(sprintf("Skip already scraped today variant '%s'",$variant));
            return;
        }
        $this->getLogger()->info(sprintf("Scraping variant '%s'",$variant));
        $this->doVariantScrape($variant);
        $this->getLogger()->debug(sprintf("End scraping variant '%s'",$variant));
        $variant->setScrapedAt(new \DateTime());
        $this->variantManager->save($variant);
    }

    protected function addVariantImage(ProductVariant $variant, $uri) {
        $currentMedia = $variant->getImage();

        if (!empty($currentMedia)) {
            //if ($currentMedia->getProviderReference() == $uri) {
                $this->getLogger()->debug(sprintf("Variant '%s' already have image '%s'",$variant->getProduct()->getNameCode(),$uri));
                return;
            //}
        }

        $media = $this->getMediaManager()->create();

        $tmpFile = tempnam(sys_get_temp_dir(), 'guzzle-download');
        $guzzle = new GuzzleClient();
        $response = $guzzle->get($uri,['save_to' => $tmpFile]);
        if ($response->getStatusCode() != 200) {
            $this->getLogger()->error(sprintf("Error retrieving '%s': %s",$uri,$response->getReasonPhrase()));
            return;
        }
        $media->setBinaryContent($tmpFile);
        $media->setContext('youppers_product');
        $media->setProviderName('sonata.media.provider.image');
        $media->setProviderReference($uri);
        $media->setName($variant->getProduct()->getFullCode());
        $this->getLogger()->info(sprintf("Saved image '%s' as '%s'",trim($uri),$media->getName()));
        $this->getMediaManager()->save($media);

        $variant->setImage($media);
    }

    protected abstract function doVariantScrape(ProductVariant $variant);

}
