<?php
namespace Youppers\CompanyBundle\Loader;

class ISPricelistLoader extends AbstractPricelistLoader
{
	public function createMapper()
	{
		$mapping = array(
			'code' => 'Material Code',
			'brand' => function($row) { $matches=preg_split("/\-/",$row['CDS Italia']); if (array_key_exists(1,$matches)) return $matches[1]; },
			'name' => 'Description',
			'gtin' => 'EAN13',
			'price' => function($row) { return strtr($row['Price'],array(" " => "", "€" => "","." => "","," => ".")); },
			'collection' => 'Suite',
			'type' => 'Categoria',	
			'uom' => function($row) { return 'PCE'; }
		);
		$mapper = new LoaderMapper($mapping);
		return $mapper;
	}
}