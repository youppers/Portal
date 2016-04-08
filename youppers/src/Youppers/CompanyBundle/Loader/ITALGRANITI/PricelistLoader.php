<?php
namespace Youppers\CompanyBundle\Loader\ITALGRANITI;

use Youppers\CompanyBundle\Entity\Brand;
use Youppers\CompanyBundle\Loader\AbstractPricelistLoader;
use Youppers\CompanyBundle\Loader\LoaderMapper;

class PricelistLoader extends AbstractPricelistLoader
{
	public function createMapper()
	{
		// serie;formato;Articolo;descrizione;spessore;stato art;um ven;listino euro;PZ-CO;MQ-CO;KG-CO;CO-PL;MQ-PL;KG-PL;KG-PZ
		$mapping = array(
			self::FIELD_CODE => 'Articolo',
			self::FIELD_NAME => 'descrizione',
			self::FIELD_STATUS => 'stato art',
			self::FIELD_COLLECTION => 'serie',
			self::FIELD_UOM => 'um ven',
			self::FIELD_PRICE => 'listino euro',
			self::FIELD_QUANTITY => 'PZ-CO',
            self::FIELD_SURFACE => 'MQ-CO',
		);
		$mapper = new LoaderMapper($mapping);
		return $mapper;
	}

	private $newCollectionProductType;

	protected function getNewCollectionProductType(Brand $brand, $code)
	{
		if (!isset($this->newCollectionProductType)) {
			$this->newCollectionProductType= $this->getProductTypeManager()
				->findOneBy(array('code' => 'TILE'));
		}
		return $this->newCollectionProductType;
	}

}