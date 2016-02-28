<?php
namespace Youppers\CompanyBundle\Loader\CCI;

use Youppers\CompanyBundle\Entity\Brand;
use Youppers\CompanyBundle\Loader\AbstractPricelistLoader;
use Youppers\CompanyBundle\Loader\LoaderMapper;

class PricelistLoader extends AbstractPricelistLoader
{
	public function createMapper()
	{
		$mapping = array(
			self::FIELD_BRAND => "Marchio - Brand",
			self::FIELD_CODE => 'Codice Numerico Articolo - Number Code',
            self::FIELD_GTIN => 'Codice EAN13 - EAN13 Code',
			self::FIELD_NAME => function($row,&$data) {
                return trim(
					$row['Descrizione Progetto - Collection'] . ' ' .
                    $row['Descrizione Articolo'] . ' ' .
                    $row['Colore'] . ' ' .
                    $row['Articolo - Item'] . ' ' .
                    $row['Formato - Size'] . ' ' .
                    $row['Finitura'] . ' ' .
                    $row['Struttura'] . ' ' .
                    $row['Aspetto Superficiale'] . ' ' .
                    $row['Bordi']);
            },
			self::FIELD_COLLECTION => 'Descrizione Progetto - Collection',
			self::FIELD_UOM => 'Unita Misura - Sales Unit',
			self::FIELD_PRICE => 'EUR=PL>',
			self::FIELD_QUANTITY => 'BOX_PZ / PC in BOX',
            self::FIELD_SURFACE => 'BOX_MQ / SM in BOX',
			self::FIELD_DIM => 'Formato - Size',
			self::FIELD_COL => 'Colore',
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