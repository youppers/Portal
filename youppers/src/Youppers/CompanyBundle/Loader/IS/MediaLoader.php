<?php
namespace Youppers\CompanyBundle\Loader\IS;

use Youppers\CompanyBundle\Loader\AbstractMediaLoader;
use Youppers\CompanyBundle\Loader\LoaderMapper;

class MediaLoader extends AbstractMediaLoader
{
	public function createMapper()
	{
		$mapping = array(
            self::FIELD_BRAND => 'Marchio',
            self::FIELD_TYPE => 'Tipo',
            self::FIELD_CODE => 'Codice',
            self::FIELD_NAME => 'Nome',
            self::FIELD_RES => 'Immagine'
		);
		$mapper = new LoaderMapper($mapping);
		return $mapper;
	}

}