<?php
namespace Youppers\CompanyBundle\Loader\CAESAR;

use Youppers\CompanyBundle\Entity\Product;
use Youppers\CompanyBundle\Loader\AbstractPricelistLoader;
use Youppers\CompanyBundle\Loader\LoaderMapper;

class PricelistLoader extends AbstractPricelistLoader
{
	public function createMapper()
	{
		// SERIE;ASSORTIMENTO;TECNOLOGIA;PEZZO;FORMATO;COLORE_SERIE;COD_9;COD_ART;ARTICOLO;UDM;EUR01>PL (2016);EUR01<PL (2016);MQ_PALLET;KG_PALLET;SCATOLE_EURO;PEZZI_SCATOLA;MQ_SCATOLA;KG_SCATOLA_LORDO
		$mapping = array(
			self::FIELD_COLLECTION => 'SERIE',
			self::FIELD_BRAND => 'ASSORTIMENTO',
			self::FIELD_CODE => 'COD_ART',
			self::FIELD_NAME => 'ARTICOLO',
			self::FIELD_UOM => 'UDM',
			self::FIELD_PRICE => 'EUR01<PL (2016)',
			self::FIELD_QUANTITY => 'PEZZI_SCATOLA',
            self::FIELD_SURFACE => 'MQ_SCATOLA',
		);
		$mapper = new LoaderMapper($mapping);
		return $mapper;
	}

	protected function getProductType(Product $product, $collectionCode)
	{
		return $this->findProductType('TILE');
	}

	function handleRow($row)
	{
		if (empty($row['SERIE'])) {
			return;
		}
		if (!preg_match('/^\w{4}/',$row['COD_ART'])) {
			return;
		}
		parent::handleRow($row);
	}

}