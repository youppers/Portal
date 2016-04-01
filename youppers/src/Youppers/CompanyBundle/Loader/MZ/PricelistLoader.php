<?php
namespace Youppers\CompanyBundle\Loader\MZ;

use Youppers\CompanyBundle\Entity\Brand;
use Youppers\CompanyBundle\Loader\AbstractPricelistLoader;
use Youppers\CompanyBundle\Loader\LoaderMapper;

class PricelistLoader extends AbstractPricelistLoader
{
	/**
	 * @return LoaderMapper
	 */
/*
NAME;LIST;CODE SERIE;SERIE;ITEM;CODE ITEM;CODE CATALOG;CODE RAG CATALOG;CATALOG;CODE UOM;UNIT AMOUNT LIST;QTY PC-BOX;QTY BOX - PALLET;QTY M2-BOX;QTY_M2_PALLET;QTY KG - BOX;QTY KG - PALLET;QTY GROSS WEIGHT - BOX;QTY GROSS WEIGHT - PALLET;CODE EAN;ITEM STATUS CODE;THICKNESS;DOMESTIC GROUP;SEGMENT;FLAG CATALOG;DATE ACTIVE;END DATE ACTIVE;INTRASTAT;PLAIN FLAG;DAL-TILE ITEM;DAL-TILE DATA
RA_R1_EUR_016;RAGNO ITALIA 2016 EURO;546;060X500 ARTEAK  BATTISCOPA;060X500  BT.ARTEAK CIL;00R0AN15;R0AN;QJ-04;ARTEAK;m;13,2;15;96;0,45;43,2;9;864;9,25;887,6;8010885281359;10;MM 09;3-BASSA;BASIC;Y;01/03/2016;;69089091;N;;N
 */
	public function createMapper()
	{
		$mapping = array(
			self::FIELD_CODE => 'CODE CATALOG',
			self::FIELD_NAME => 'ITEM',
			self::FIELD_COLLECTION => 'CATALOG',
			self::FIELD_UOM => 'CODE UOM',
			self::FIELD_QUANTITY => 'QTY PC-BOX',
			self::FIELD_SURFACE => 'QTY M2-BOX',
			self::FIELD_GTIN => 'CODE EAN',
			self::FIELD_PRICE => 'UNIT AMOUNT LIST',
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