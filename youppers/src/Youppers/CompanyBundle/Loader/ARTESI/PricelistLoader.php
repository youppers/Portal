<?php
namespace Youppers\CompanyBundle\Loader\ARTESI;

use Youppers\CompanyBundle\Entity\Brand;
use Youppers\CompanyBundle\Entity\Product;
use Youppers\CompanyBundle\Loader\AbstractPricelistLoader;
use Youppers\CompanyBundle\Loader\LoaderMapper;

class PricelistLoader extends AbstractPricelistLoader
{
	public function createMapper()
	{
		$mapping = array(
			self::FIELD_CODE => function($row) {
				$codice = $row['CODICE LISTINO'];
				$categoriePrezzo = $row['CATEGORIE PREZZO'];
				if (!empty($categoriePrezzo) && preg_match('/\w=(.*)/',$categoriePrezzo,$m)) {
					$codice .= '-' . $m[1];
				}
				return $codice;
			},
			self::FIELD_COLLECTION => 'MODELLO',
			self::FIELD_NAME => 'DESCRIZIONE',
			self::FIELD_PRICE => 'PREZZO',
		);
		$mapper = new LoaderMapper($mapping);
		return $mapper;
	}

	protected function getProductType(Product $product, $collectionCode)
	{
		return $this->findProductType('BATHFURN');
	}

	protected function guessProductCollection(Product $product,Brand $brand)
	{
		// TODO
        parent::guessProductCollection($product,$brand);
	}

}