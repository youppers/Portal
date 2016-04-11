<?php
namespace Youppers\CompanyBundle\Loader\CERCAT;

use Youppers\CompanyBundle\Entity\Brand;
use Youppers\CompanyBundle\Loader\AbstractPricelistLoader;
use Youppers\CompanyBundle\Loader\LoaderMapper;

class PricelistLoader extends AbstractPricelistLoader
{
	public function createMapper()
	{
		$mapping = array(
            self::FIELD_COLLECTION => self::FIELD_COLLECTION,
			self::FIELD_CODE => self::FIELD_CODE,
			self::FIELD_NAME => self::FIELD_NAME,
			self::FIELD_PRICE => self::FIELD_PRICE,
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

    protected function createReader($filename)
    {
        $reader = parent::createCsvReader($filename);
        $reader->setStrict(false);
        return $reader;
    }

    private $currentCollection;

	function handleRow($row)
	{
		// the collection name is on a line alone in the 2nd column
        if (empty($row['code'])) {
            if (empty($row['name'])) {
                return;
            } else {
                $this->currentCollection = $row['name'];
                return;
            }
        }
        if ($row == null) {
            return;
        }
        if (empty($this->currentCollection)) {
            return;
        }
        $row[self::FIELD_COLLECTION] = $this->currentCollection;
		$row[self::FIELD_UOM] = 'PZ';
		$row[self::FIELD_QUANTITY] = 1;
		parent::handleRow($row);
	}

}