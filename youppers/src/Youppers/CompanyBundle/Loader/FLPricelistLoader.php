<?php
namespace Youppers\CompanyBundle\Loader;

use Youppers\CompanyBundle\Entity\Brand;
use Youppers\ProductBundle\Entity\ProductCollection;
class FLPricelistLoader extends AbstractPricelistCollectionLoader
{
	public function createMapper()
	{
		// "Brand","Serie","descrizione","Formato","Materiale","descrizione materiale","UM","nota","Gruppo Prezzi","Listino"
		// "REX","ALABASTRI DI REX","LUCIDO","60X120",739805,"ALABASTRI REX MADREPERLA LAP 60X120 RET","M2",,"GR-1340",109		
		$mapping = array(
			'brand' => 'Brand',
			'collection' => 'Serie',
			'code' => 'Materiale',
			'properties' => array('FIN' => 'descrizione', 'DIM' => 'Formato'),
			'name' => 'descrizione materiale',
			'gtin' => false,
			'price' => 'Listino',
			'priceGroup' => 'Gruppo Prezzi',
			'uom' => 'UM'
		);
		$mapper = new LoaderMapper($mapping);
		return $mapper;
	}
	
	private $newCollectionProductType;
	
	protected function getNewCollectionProductType(Brand $brand, $code)
	{
		if (!isset($this->newCollectionProductType)) {	
			$this->newCollectionProductType= $this->getProductTypeRepository()
				->findOneBy(array('code' => 'tile'));
		}
		return $this->newCollectionProductType;	
	}
	
}