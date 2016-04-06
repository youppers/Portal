<?php
namespace Youppers\CompanyBundle\Loader\NOVELLINI;

use Youppers\CompanyBundle\Entity\Brand;
use Youppers\CompanyBundle\Loader\An6PricelistLoader;
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
class PricelistLoader extends An6PricelistLoader
{

    public function createMapper()
    {
        $mapping = array(
            self::FIELD_CODE => 'Note',  // codice Novellini lungo più di 15 caratteri
            self::FIELD_GTIN => 'EAN13',
            self::FIELD_NAME => array('DesArt1','DesArt2'),
            self::FIELD_UOM => 'UmBase',
            self::FIELD_QUANTITY => 'Quantita',
            self::FIELD_STATUS => 'Stato',
            self::FIELD_PRICE => 'PrezzoListino',
            self::FIELD_SURFACE => function($row) { return $row['Moltiplicatore'] * $row['CoeffSuperficie']; },
        );
        $mapper = new LoaderMapper($mapping);
        return $mapper;
    }

    protected function getNewCollectionProductType(Brand $brand, $code)
    {
        if (!isset($this->newCollectionProductType)) {
            $this->newCollectionProductType= $this->getProductTypeManager()
                ->findOneBy(array('code' => 'BATHTUB'));
        }
        return $this->newCollectionProductType;
    }

}
