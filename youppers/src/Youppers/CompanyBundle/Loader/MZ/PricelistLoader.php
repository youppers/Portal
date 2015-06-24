<?php
namespace Youppers\CompanyBundle\Loader\MZ;

use Youppers\CompanyBundle\Entity\Brand;
use Youppers\CompanyBundle\Loader\AbstractPricelistLoader;
use Youppers\CompanyBundle\Loader\LoaderMapper;

class PricelistLoader extends AbstractPricelistLoader
{
	public function createMapper()
	{
		$mapping = array(
			'code' => 'codice_catalogo',
			'name' => function($row) {
				$matches=preg_split("/x/",$row['formato']);
				$larghezza = intval($matches[0]);
				$lunghezza = floatval($matches[1]);
				$dim = sprintf('%dx%d',$larghezza/10,$lunghezza*100);
				$name = $row['descrizione'];
				if (stripos($name,$dim) === false) {
					$name .= ' ' . $dim;
				}
				return $name;
			}, 
			'collection' => 'nome_serie',
			'uom' => function($row) { return 'M2'; },
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