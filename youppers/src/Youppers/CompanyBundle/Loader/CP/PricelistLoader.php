<?php
namespace Youppers\CompanyBundle\Loader\CP;

use Youppers\CompanyBundle\Entity\Brand;
use Youppers\CompanyBundle\Loader\AbstractPricelistLoader;
use Youppers\CompanyBundle\Loader\LoaderMapper;

class PricelistLoader extends AbstractPricelistLoader
{
	public function createMapper()
	{
		$mapping = array(
            // 'brand' => 'MARCHIO',  // Brand unico, specificare il brand usando "loader CP CP"
			'code' => 'ARTICOLO',
			'name' => function($row) {
                return $row['SERIE'] . ' ' . $row['DESCRIZIONE'] . ' ' . $row['FORMATO'];
			},
			'collection' => 'SERIE',
			'uom' => 'UM',
            'price' => '1A SC.'
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