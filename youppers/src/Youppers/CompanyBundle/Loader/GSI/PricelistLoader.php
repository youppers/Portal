<?php
namespace Youppers\CompanyBundle\Loader\GSI;

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
            self::FIELD_CODE => 'CODICE',
            self::FIELD_GTIN => 'EAN-13',
            self::FIELD_NAME => 'DESCART_ITA',
            self::FIELD_PRICE => 'PREZZO',
            self::FIELD_COLLECTION => function($row) {
                if ($row['FAM'] == 'ACCESSORI') {
                    return $row['SOTTOFAM'];
                } else {
                    return $row['FAM'];
                }
            },
        );
        $mapper = new LoaderMapper($mapping);
        return $mapper;
    }

    protected function getNewCollectionProductType(Brand $brand, $code)
    {
        if (!isset($this->newCollectionProductType)) {
            $this->newCollectionProductType= $this->getProductTypeManager()
                ->findOneBy(array('code' => 'FULLBATH'));
        }
        return $this->newCollectionProductType;
    }

}
