<?php
namespace Youppers\CompanyBundle\Loader\FAP;

use Youppers\CompanyBundle\Entity\Product;
use Youppers\CompanyBundle\Loader\AbstractPricelistLoader;
use Youppers\CompanyBundle\Loader\LoaderMapper;

class PricelistLoader extends AbstractPricelistLoader
{
	public function createMapper()
	{
		$mapping = array(
			self::FIELD_CODE => self::FIELD_CODE,
			self::FIELD_COLLECTION => function($row) {
				$name = $row[self::FIELD_NAME];
				if (empty($name)) {
					return null;
				}
				$a = explode(" ",$name);
				return $a[0];
			}, 
			self::FIELD_NAME => function($row) {
				$name = $row[self::FIELD_NAME];
				if (empty($name)) {
					return null;
				}
				$a = explode(" ",$name,2);
				if (count($a) == 2) {
					return $a[1];
				} else {
					return null;
				}
			},
			self::FIELD_UOM => function($row) {
				$uom = mb_substr(mb_convert_encoding(trim($row[self::FIELD_UOM]),'ASCII','UTF-8'),0,2);
				if ($uom == 'MI') {
					return 'SET';
				}
				if ($uom == 'M?') {
					return 'MQ';
				}
				return $uom;
			},
			self::FIELD_PRICE => self::FIELD_PRICE,
			self::FIELD_SURFACE => self::FIELD_SURFACE,
			self::FIELD_QUANTITY => self::FIELD_QUANTITY,
		);
		$mapper = new LoaderMapper($mapping);
		return $mapper;
	}

	protected function createReader($filename)
	{
		$reader = parent::createCsvReader($filename);
		$reader->setStrict(false);
		return $reader;
	}

	function handleRow($row)
	{
		if (empty($row[self::FIELD_CODE])) {
			return;
		}
		if (!preg_match('/^f\w{3}/',$row[self::FIELD_CODE])) {
			return;
		}
		parent::handleRow($row);
	}

	protected function getProductType(Product $product, $collectionCode)
	{
		return $this->findProductType('TILE');
	}

}