<?php
namespace Youppers\CompanyBundle\Loader\ARTELINEA;

use Youppers\CompanyBundle\Entity\Brand;
use Youppers\CompanyBundle\Loader\AbstractPricelistCollectionLoader;
use Youppers\CompanyBundle\Loader\LoaderMapper;

class PricelistLoader extends AbstractPricelistCollectionLoader
{
	public function createMapper()
	{
		$mapping = array(
			'code' => function($row) {
                $code = trim($row['Codice Articolo']);
                $marca = trim($row['Marca / Descrizione']);
                if (!empty($marca)) {
                    $marca = preg_replace("/\ /","-",$marca);;
                    $code .= '-' . trim($marca);
                }
                return $code;
            },
			'name' => function($row) {
                $name = trim($row['Codice Articolo']);
                $marca = trim($row['Marca / Descrizione']);
                if (!empty($marca)) {
                    $name .= " " . $marca;
                }
				return $name;
			}, 
			'uom' => function($row) { return 'PCE'; },
            'price' => 'Prezzo Listino'
		);
		$mapper = new LoaderMapper($mapping);
		return $mapper;
	}
	
	private $newCollectionProductType;
	
	protected function getNewCollectionProductType(Brand $brand, $code)
	{
		if (!isset($this->newCollectionProductType)) {
			$this->newCollectionProductType= $this->productTypeManager
				->findOneBy(array('code' => 'BASEMOBILEBAGNO'));
		}
		return $this->newCollectionProductType;
	}
	
}