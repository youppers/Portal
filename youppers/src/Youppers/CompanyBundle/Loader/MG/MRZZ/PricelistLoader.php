<?php
namespace Youppers\CompanyBundle\Loader\IS;

use Youppers\CompanyBundle\Entity\Brand;
use Youppers\CompanyBundle\Loader\AbstractPricelistCollectionLoader;
use Youppers\CompanyBundle\Loader\LoaderMapper;

class PricelistLoader extends AbstractPricelistCollectionLoader
{
	public function createMapper()
	{
		$mapping = array(
			'code' => 'codice_catalogo',
			'descrizione' => 'Description',
			'collection' => 'nome_serie',
			'DIM' => 'formato',
			'COLOR' => 'colore'
		);
		$mapper = new LoaderMapper($mapping);
		return $mapper;
	}
	
	private $newCollectionProductType;
	
	protected function getNewCollectionProductType(Brand $brand, $code)
	{
		if (!isset($this->newCollectionProductType)) {
			$this->newCollectionProductType= $this->productTypeManager
				->findOneBy(array('code' => 'TILE'));
		}
		return $this->newCollectionProductType;
	}
	
}