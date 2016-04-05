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
            self::FIELD_COLLECTION => 'CodiceFAM',
            self::FIELD_CODE => 'CodArt',
            self::FIELD_NAME => 'DesArt1',
            self::FIELD_PRICE => 'PrezzoListino',
        );
        $mapper = new LoaderMapper($mapping);
        return $mapper;
    }

}
