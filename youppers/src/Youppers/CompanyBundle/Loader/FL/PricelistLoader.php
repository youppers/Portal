<?php
namespace Youppers\CompanyBundle\Loader\FL;

use Youppers\CompanyBundle\Entity\Product;
use Youppers\CompanyBundle\Loader\AbstractPricelistLoader;
use Youppers\CompanyBundle\Loader\LoaderMapper;
use Youppers\CompanyBundle\Entity\Brand;

class PricelistLoader extends AbstractPricelistLoader
{
	public function createMapper()
	{
		// "Brand","Serie","descrizione","Formato","Materiale","descrizione materiale","UM","nota","Gruppo Prezzi","Listino"
		// "REX","ALABASTRI DI REX","LUCIDO","60X120",739805,"ALABASTRI REX MADREPERLA LAP 60X120 RET","M2",,"GR-1340",109		
		$mapping = array(
			self::FIELD_BRAND => 'BRAND',
			self::FIELD_COLLECTION => 'Serie/ Collection',
			self::FIELD_CODE => 'cod./code',
			self::FIELD_NAME => 'descrizione/item description',
			self::FIELD_GTIN => 'cod EAN',
			self::FIELD_PRICE => 'Price List',
			self::FIELD_UOM => 'UM',
			self::FIELD_QUANTITY => 'pz/sc',
			self::FIELD_SURFACE => array('m2/sc m2/box',' m2/lastra '),
		);
		$mapper = new LoaderMapper($mapping);
		return $mapper;
	}
	
	protected function getProductType(Product $product)
	{
		$info = json_decode($product->getInfo(), true);
		$tipologia = $info['Tipologia'];
		if ($tipologia == 'Materiale non ceram.') {
			$typecode = 'ALTRI';
		} else {
			$typecode = 'TILE';
		}
		return $this->getProductTypeManager()->findOneBy(array('code' => $typecode));
	}

	protected function getNewCollectionProductType(Brand $brand, $code) {
		throw new \RuntimeException("Deprecated method");
	}
	
}
