<?php
namespace Youppers\CompanyBundle\Loader\ELIOS;

use Youppers\CompanyBundle\Entity\Brand;
use Youppers\CompanyBundle\Loader\AbstractPricelistLoader;
use Youppers\CompanyBundle\Loader\LoaderMapper;

class PricelistLoader extends AbstractPricelistLoader
{
	public function createMapper()
	{
		$mapping = array(
            'brand' => 'MARCHIO',
			'code' => 'CODICE',
			'name' => function($row) {
                $name = $row['ARTICOLO'];
				$dim = $row['FORMATO'];
				if (stripos($name,$dim) === false) {
					$name .= ' {' . $dim . '}';
				}
				return $name;
			}, 
			'collection' => 'SERIE',
			'uom' => 'UMV',
            'gtin' => 'APFBCO',
            'price' => 'LISTINO'
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