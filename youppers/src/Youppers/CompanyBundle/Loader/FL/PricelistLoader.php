<?php
namespace Youppers\CompanyBundle\Loader\FL;

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
			'brand' => 'Brand',
			'collection' => 'Serie',
			'code' => 'Materiale',
			'properties' => array('FIN' => 'descrizione', 'DIM' => 'Formato'),
			'name' => 'descrizione materiale',
			'gtin' => false,
			'price' => 'Listino',
			'uom' => 'UM'
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
