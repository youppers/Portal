<?php
namespace Youppers\DealerBundle\Service;

use Doctrine\ORM\EntityManager;
use Exporter\Handler;
use Exporter\Source\DoctrineORMQuerySourceIterator;
use Exporter\Writer\XmlExcelWriter;
use Sonata\CoreBundle\Model\BaseEntityManager;
use Symfony\Component\DependencyInjection\ContainerAware;
use Youppers\CompanyBundle\Entity\Company;
use Psr\Log\LoggerInterface;
use Doctrine\Common\Collections\Criteria;
use Youppers\CompanyBundle\Entity\Pricelist;
use Youppers\DealerBundle\Entity\Dealer;
use Youppers\DealerBundle\Entity\DealerBrand;

class PricelistService extends ContainerAware
{
	private $managerRegistry;
	private $logger;
	
	public function __construct(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}

	protected $debug = false;

	public function setDebug($debug)
	{
		$this->debug = $debug;
	}
	/**
	 * @return BaseEntityManager
	 */
	private function getDealerManager() {
		return $this->container->get('youppers.dealer.manager.dealer');
	}

    /**
     * @return BaseEntityManager|null
     */
    private function getProductPriceManager() {
        return $this->container->get('youppers.company.manager.product_price');
    }

	public function getCompanyPricelists(Company $company) {
		$now = (new \DateTime());
		$criteria = Criteria::create()
			->where(Criteria::expr()->eq('company',$company))
			->andWhere(Criteria::expr()->eq('enabled',true))
			->andWhere(Criteria::expr()->lte('validFrom',$now))
			->andWhere(Criteria::expr()->gte('validTo',$now));
		return $company->getPricelists()->matching($criteria);
	}

	/**
	 * @param $dealerCode string
	 * @return Dealer
	 */
	private function getDealerByCode($dealerCode) {
		$dealer = $this->getDealerManager()->findOneBy(array('code' => $dealerCode));
		if (empty($dealer)) {
			throw new \InvalidArgumentException(sprintf("Dealer with code '%s' not found",$dealerCode));
		}
		return $dealer;
	}

	/**
	 * Export
	 * @param $dealerCode Code of the dealer
	 * @param $path Where to werite the pricelist
	 * @param string $brandCode Optional code of the brand
	 * @param boolean $force Overwrite existing file
	 */
	public function export($dealerCode, $path, $brandCode = null, $force = false) {

		if (!$this->debug) $this->getDealerManager()->getConnection()->getConfiguration()->setSQLLogger(null);  // save memory

		$dealer = $this->getDealerByCode($dealerCode);
		$absolutePath = realpath($path);
		if (empty($absolutePath)) {
			throw new \InvalidArgumentException("Invalid path supplied: $path");
		}

		$this->logger->info(sprintf("Exporting pricelists for dealer '%s' in: %s", $dealer, $absolutePath));

		$criteria = Criteria::create()
			->where(Criteria::expr()->eq("enabled", true));

		$dealerBrands = $dealer->getDealerBrands()->matching($criteria);
		if (count($dealerBrands) < 1) {
			throw new \InvalidArgumentException("Dealer don't have brands enabled");
		}

		if (!empty($brandCode)) {
			$dealerBrands = $dealerBrands->filter(
				function($dealerBrand) use ($brandCode) {
					return ($dealerBrand->getCode() == $brandCode
						|| (empty($dealerBrand->getCode()) && ($dealerBrand->getBrand()->getCode() == $brandCode)));
				}
			);
			if (count($dealerBrands) < 1) {
				throw new \InvalidArgumentException("Invalid brand code or not enabled: $brandCode");
			}
		}

		foreach ($dealerBrands as $dealerBrand) {
			$this->logger->debug(sprintf("Writing pricelists for '%s'", $dealerBrand));
			foreach ($this->getCompanyPricelists($dealerBrand->getBrand()->getCompany()) as $pricelist) {
				$filename = $absolutePath . DIRECTORY_SEPARATOR . $dealer->getCode()
                    . '-' . ($dealerBrand->getCode()?: $dealerBrand->getBrand()->getCode())
					. '-' . $pricelist->getCode()
                    . '.xml';
                if (file_exists($filename)) {
					if ($force) {
						unlink($filename);
					} else {
						$mtime = filemtime($filename);
						$pltime = $pricelist->getUpdatedAt()->getTimestamp();
						if ($pltime > $mtime) {
							$this->logger->debug("Removing export file older than pricelist");
							unlink($filename);
						} else {
							$this->logger->info(sprintf("Existing pricelist '%s' use force or update pricelist to overwite file: %s", $pricelist, $filename));
							continue;
						}
					}
                }
				$this->logger->info(sprintf("Writing pricelist '%s' in: %s", $pricelist, $filename));
				$writer = new XmlExcelWriter($filename);

                $source = new ProductPriceIterator($this->getProductPriceManager()->getEntityManager(), $dealerBrand, $pricelist);

                Handler::create($source,$writer)->export();
				if ($source->getRecords() == 0) {
					$this->logger->info(sprintf("No prices for brand '%s' in pricelist '%s'", $dealerBrand->getBrand(),$pricelist));
					unlink($filename);
				} else {
					$this->logger->info(sprintf("Written %d prices in: %s", $source->getRecords(), $filename));
				}
			}
			$this->getDealerManager()->getEntityManager()->clear(); // save memory
		}

	}



}

class ProductPriceIterator extends DoctrineORMQuerySourceIterator {

    private $dealerBrand;
    private $brandCode;
    private $dealerBrandCode;

	private $records = 0;

	function next()
	{
		$this->records++;
		return parent::next();
	}

	function getRecords()
	{
		return $this->records;
	}

    function __construct(EntityManager $em, DealerBrand $dealerBrand, Pricelist $pricelist)
    {
        $this->dealerBrand =$dealerBrand;
        $this->brandCode = $this->dealerBrand->getBrand()->getCode();
        $this->dealerBrandCode = $this->dealerBrand->getCode() ?: $this->dealerBrand->getBrand()->getCode();

        $query = $em->createQuery('SELECT p FROM Youppers\CompanyBundle\Entity\ProductPrice p JOIN p.product prod WHERE p.pricelist = :pricelist AND prod.brand = :brand');
        $query->setParameter('pricelist',$pricelist);
        $query->setParameter('brand',$dealerBrand->getBrand());

        $fields = array(
            'SIGLA' => 'product.brand.code',
            'SERIE' => 'product.variant.productCollection.name',
            'CODICE' => 'product.code',
            'DESCRIZIONE' => 'product.name',
            'UM' => 'uom',
            'PREZZO' => 'price',
            'FORMATO' => 'product.variant.formato',
            'TONI' => 'product.variant.toni',
            'IMBALLO' => 'surface',
        );

        parent::__construct($query,$fields);
    }

    function current()
    {
        $data = parent::current();

        $data['SIGLA'] = $this->dealerBrandCode;
        return $data;
    }

    function valid()
    {
        while (parent::valid()) {
            if (parent::current()['SIGLA'] == $this->brandCode) {
                return true;
            }
            $this->next();
        }
        return false;
    }


}

