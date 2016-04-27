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
use Youppers\CompanyBundle\Entity\ProductPrice;
use Youppers\DealerBundle\Entity\Dealer;
use Youppers\DealerBundle\Entity\DealerBrand;
use Youppers\ProductBundle\Entity\AttributeOption;
use Youppers\ProductBundle\Entity\ProductVariant;

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
				$columnTypes = array(
					'SIGLA' => 'String',
					'SERIE' => 'String',
					'CODICE' => 'String',
					'PREZZO' => 'Number',
					'FORMATO' => 'String',
				);
				$writer = new XmlExcelWriter($filename, true, $columnTypes);

                $source = new ProductPriceIterator($this->getProductPriceManager($dealer)->getEntityManager(), $dealerBrand, $pricelist);

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
            'FORMATO' => 'product.variant.variantProperties',
            'TONI' => 'product.variant.variantProperties',
            'IMBALLO' => 'surface',
			//'SERIE_OVERFLOW' => 'product.variant.productCollection.name',
			//'DESCRIZIONE_OVERFLOW' => 'product.name',
        );

        parent::__construct($query,$fields);
    }

    function current()
    {
		$data = parent::current();
		$descrizione = $data['DESCRIZIONE'];
		$serie = $data['SERIE'];
		$data['TONI'] = 'N';

		$current = $this->iterator->current();

		/** @var ProductPrice $price */
		$price = $current[0];

		/** @var ProductVariant $variant */
		$variant = $price->getProduct()->getVariant();

		if ($variant) {
			$properties = $variant->getVariantProperties();

			/** @var AttributeOption $dimOption */
			$dimOption = null;
			/** @var AttributeOption $itemOption */
			$itemOption = null;

			foreach ($properties as $property) {
				if ($property->getAttributeType()->getCode() == 'DIM') {
					$dimOption = $property->getAttributeOption();
				}
				if ($property->getAttributeType()->getCode() == 'ITEM') {
					$itemOption = $property->getAttributeOption();
				}
			}
			$newFormato = '';

			if (!empty($dimOption)) {
				$dimValue = $dimOption->getValue();
				$dimStandard = $dimOption->getAttributeStandard();

				if ($dimStandard->getName() == 'Lato x Lato in mm') {
					$factor = 0.1;
				} else {
					$factor = 1;
				}
				if (preg_match('/([0-9,\.]+)X([0-9,\.]+)/i', $dimValue, $matches)) {
					$dim1 = (str_replace(",", ".",$matches[1]) * $factor);
					$dim2 = (str_replace(",", ".",$matches[2]) * $factor);
					$newFormato =  $dim1 . 'X' . $dim2;
					$newFormato = str_replace(".", ",", $newFormato);
					$newFormatoRegexp = '/'
						. preg_quote($dim1)
						. '[\s]*X[\s]*'
						. preg_quote($dim2)
						. '/i';
					$data['FORMATO'] = $newFormato;
					// leva il formato dalla descrizione
					$descrizione = trim(preg_replace('/' . preg_quote($dimValue,'/') . '/i','',$descrizione));
					$descrizione = trim(preg_replace($newFormatoRegexp,'',$descrizione));
				}
			}

			$deletes = array();

			$deletes[] = $serie;
			// leva la serie dalla descrizione
			foreach (explode(' ',$serie) as $serieW) {
				if (!empty($serieW) && !in_array($serieW,$deletes)) {
					$deletes[] = $serieW;
				}
			}

			foreach (explode(';',$variant->getProductCollection()->getAlias()) as $aliasSerie) {
				if (!empty($aliasSerie) && !in_array($aliasSerie,$deletes)) {
					$deletes[] = $aliasSerie;
				}
				foreach (explode(' ',$aliasSerie) as $aliasSerieW) {
					if (!empty($aliasSerieW) && !in_array($aliasSerieW,$deletes)) {
						$deletes[] = $aliasSerieW;
					}
				}
			}
			usort($deletes,function($a, $b) { return strlen($b) - strlen($a);});

			foreach ($deletes as $delete) {
				$descrizione = trim(preg_replace('/\b' . preg_quote($delete,'/') . '\b/i','',$descrizione));
			}

			if ($variant->getProductCollection()->getProductType()->getCode() == 'TILE') {
				// a) per i listini delle ceramiche le descrizioni vengono date nell’ordine: FORMATO + SERIE + DESCRIZIONE ARTICOLO
				$descrizione = $newFormato . ' ' . $serie . ' ' . $descrizione;
			} elseif ($itemOption && $itemOption->getValue() == 'Piatto doccia') {
				// b) per gli altri listini: SERIE + ARTICOLO, tranne per i piatti doccia che vanno inseriti come segue: PIATTO DOCCIA + SERIE + MISURA
				$descrizione = 'PIATTO DOCCIA' . ' ' . $serie . ' ' . $newFormato . ' ' . $descrizione;;
			} else {
				$descrizione = $serie . ' ' . $descrizione;;
			}

			if ($variant->getProductCollection()->getProductType()->getCode() == 'TILE') {
				$data['TONI'] = 'S';	
			}

		}

		// la colonna FORMATO viene compilata solo nel caso dei MQ o dei ML
		if ($data['UM'] == 'MQ' || $data['UM'] == 'ML') {
		} else {
			$data['FORMATO'] = '';
			$data['IMBALLO'] = '';
		}

		$serie = strtr($serie,array('“' => ' ', '”' => ' ','#' => ' '));
		$serie = preg_replace('/([\s]+)/',' ',$serie);  // replace multiple spaces with one space
		$data['SERIE'] = strtoupper(substr($serie,0,10));
		//$data['SERIE_OVERFLOW'] = strtoupper(substr($serie,10));

		$descrizione = preg_replace('/' . chr(0xC2).chr(0xA0) . '/',' ',$descrizione);  // replace non breaking spaces
		$descrizione = strtr($descrizione,array('“' => ' ', '”' => ' ','#' => ' '));
		$descrizione = preg_replace('/([\s]+)/',' ',$descrizione);  // replace multiple spaces with one space
		$descrizione = trim($descrizione); // trim

		$data['DESCRIZIONE'] = strtoupper(substr($descrizione,0,70));
		//$data['DESCRIZIONE_OVERFLOW'] = strtoupper(substr($descrizione,70));

		$data['SIGLA'] = substr($this->dealerBrandCode,0,3);
		$data['CODICE'] = substr(trim($data['CODICE']),0,20);

		if ($data['UM'] == 'SET') {
			$data['UM'] = 'CP';
		}

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

