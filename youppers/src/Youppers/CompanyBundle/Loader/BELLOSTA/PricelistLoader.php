<?php
namespace Youppers\CompanyBundle\Loader\BELLOSTA;

use Youppers\CompanyBundle\Entity\Brand;
use Youppers\CompanyBundle\Loader\AbstractPricelistLoader;
use Youppers\CompanyBundle\Loader\LoaderMapper;

/**
 *
 * Nella versione Angaisa Nov201601.an6:
 * - I codici obsoleti hanno Stato E e prezzo 0 (zero)
 * - Per i codici novellini con lunghezza > 15 caratteri (standard Angaisa) nel campo Codice è riportato l'EAN13.
 * - In ogni caso il codice vero è riportato anche nel campo Angaisa Note.
 *
 * Class PricelistLoader
 * @package Youppers\CompanyBundle\Loader\NOVELLINI
 */
class PricelistLoader extends AbstractPricelistLoader
{

    public function createMapper()
    {
        $mapping = array(
            self::FIELD_CODE => 'CodArt',
            self::FIELD_GTIN => 'EAN13',
            self::FIELD_NAME => 'DesArt1',
            self::FIELD_UOM => 'UmBase',
            self::FIELD_QUANTITY => 'Quantità',
            self::FIELD_STATUS => 'Stato',
            self::FIELD_PRICE => 'PrezzoListino',
            self::FIELD_COLLECTION => 'DesArt2',
        );
        $mapper = new LoaderMapper($mapping);
        return $mapper;
    }

    protected function getNewCollectionProductType(Brand $brand, $code)
    {
        if (!isset($this->newCollectionProductType)) {
            $this->newCollectionProductType= $this->getProductTypeManager()
                ->findOneBy(array('code' => 'TAP'));
        }
        return $this->newCollectionProductType;
    }

}
