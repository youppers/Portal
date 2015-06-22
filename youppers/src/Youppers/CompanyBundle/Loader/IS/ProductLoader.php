<?php
namespace Youppers\CompanyBundle\Loader\IS;

use Youppers\CompanyBundle\Entity\Brand;
use Youppers\CompanyBundle\Loader\AbstractProductLoader;
use Youppers\CompanyBundle\Loader\LoaderMapper;

class ProductLoader extends AbstractProductLoader
{
	public function createMapper()
	{
		$mapping = array(
            self::FIELD_BRAND => 'Marchio',
            self::FIELD_COLLECTION => 'Collezione',
            self::FIELD_NAME => 'Nome',
            self::FIELD_DESCRIPTION => 'Descrizione',
            self::FIELD_CODE => 'Codice'
		);
		$mapper = new LoaderMapper($mapping);
		return $mapper;
	}
	
	private $newCollectionProductType;
	
	protected function getNewCollectionProductType(Brand $brand, $code)
	{
		if (!isset($this->newCollectionProductType)) {
			$this->newCollectionProductType= $this->getProductTypeManager()
				->findOneBy(array('code' => 'FULLBATH'));
		}
		return $this->newCollectionProductType;
	}
	
}