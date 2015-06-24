<?php
namespace Youppers\CompanyBundle\Loader\ISTEST;

use Youppers\CompanyBundle\Entity\Brand;
use Youppers\CompanyBundle\Loader\LoaderMapper;
use Youppers\CompanyBundle\Loader\IS\PricelistLoader as IS;

class PricelistLoader extends IS
{
	public function createMapper()
	{
		$mapping = array(
				'code' => 'Material Code',
				'brand' => function($row) { $matches=preg_split("/\-/",$row['CDS Italia']); if (array_key_exists(1,$matches)) return $matches[1]; },
				'name' => 'Description',
				//'gtin' => 'EAN13',
				'price' => 'Price',
				'collection' => 'Suite',
				'type' => 'Categoria',
				'uom' => function($row) { return 'PCE'; }
		);
		$mapper = new LoaderMapper($mapping);
		return $mapper;
	}
	
}