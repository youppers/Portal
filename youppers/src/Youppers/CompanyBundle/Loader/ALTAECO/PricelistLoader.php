<?php
namespace Youppers\CompanyBundle\Loader\ALTAECO;

use Youppers\CompanyBundle\Entity\Product;
use Youppers\CompanyBundle\Loader\AbstractPricelistLoader;
use Youppers\CompanyBundle\Loader\LoaderMapper;

class PricelistLoader extends AbstractPricelistLoader
{
	public function createMapper()
	{
		if ($this->brand && $this->brand->getCode() == 'VOGUE') {
			// codice	serie	descrizione	u.m.	scelta	validità	 euro 	 mq x collo 	 colli x plt 	 collo/pz 	 collo/kg
			$mapping = array(
				self::FIELD_CODE => 'codice',
				self::FIELD_COLLECTION => 'serie',
				self::FIELD_NAME => 'descrizione',
				self::FIELD_UOM => 'u.m.',
				self::FIELD_PRICE => ' euro ',
				self::FIELD_SURFACE => ' mq x collo ',
				self::FIELD_QUANTITY => 'collo/pz'
			);
		} else {
            // Bardelli
            $mapping = array(
                self::FIELD_BRAND => 'LVPSOC',
                self::FIELD_CODE => 'LVPART',
                self::FIELD_COLLECTION => 'LVPSER',
                self::FIELD_COLLECTION_CODE => 'LVPSER',
                self::FIELD_NAME => 'LVPDES',
                self::FIELD_UOM => 'LVPUMV',
                self::FIELD_PRICE => 'LVPPRP',
                // self::FIELD_SURFACE => '',
                // self::FIELD_QUANTITY => '',
            );
		}
		$mapper = new LoaderMapper($mapping);
		return $mapper;
	}

	protected function getProductType(Product $product, $collectionCode)
	{
		if ($product->getBrand()->getCode() == 'VOGUE' && $collectionCode == 'POOL' && preg_match('/^COD/',$product->getName())) {
			return $this->findProductType('ALTRI');
        } elseif ($product->getBrand()->getCode() == 'BARDELLI' && json_decode($product->getInfo())['LVPLIN'] == 'COM') {
            return $this->findProductType('ALTRI');
		} else {
			return $this->findProductType('TILE');
		}
	}


}