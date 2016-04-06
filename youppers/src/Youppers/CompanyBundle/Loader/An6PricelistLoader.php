<?php
namespace Youppers\CompanyBundle\Loader;

abstract class An6PricelistLoader extends AbstractPricelistLoader
{

    protected function createReader($filename)
    {
        return $this->createAn6Reader($filename);
    }

    /**
     * @param $filename string
     * @return An6PricelistReader
     */
    protected function createAn6Reader($filename)
    {
        $file = new \SplFileObject($filename);
        return new An6PricelistReader($file);
    }

    public function createMapper()
    {
        // TODO to be completed
        $mapping = array(
            self::FIELD_CODE => 'CodArt',
            self::FIELD_GTIN => 'EAN13',
            self::FIELD_NAME => array('DesArt1','DesArt2','Note'),
            self::FIELD_UOM => 'UmBase',
            self::FIELD_QUANTITY => 'Quantita',
            self::FIELD_PRICE => 'PrezzoListino',
            self::FIELD_SURFACE => function($row) { return $row['Moltiplicatore'] * $row['CoeffSuperficie']; },
        );
        $mapper = new LoaderMapper($mapping);
        return $mapper;
    }

}
