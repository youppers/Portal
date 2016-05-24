<?php
namespace Youppers\CompanyBundle\Loader\CQUBO;

use Youppers\CompanyBundle\Entity\Brand;
use Youppers\CompanyBundle\Entity\Product;
use Youppers\CompanyBundle\Loader\AbstractPricelistLoader;
use Youppers\CompanyBundle\Loader\LoaderMapper;

class PricelistLoader extends AbstractPricelistLoader
{
	public function createMapper()
	{
		$mapping = array(
			self::FIELD_CODE => 'Codice',
			self::FIELD_NAME => 'Descrizione',
			self::FIELD_PRICE => 'Prezzo',
		);
		$mapper = new LoaderMapper($mapping);
		return $mapper;
	}

	protected function getProductType(Product $product, $collectionCode)
	{
		return $this->findProductType('FULLBATH');
	}

    protected function createReader($filename)
    {
        $reader = parent::createCsvReader($filename);
        $reader->setStrict(false);
        return $reader;
    }

	/** @var  Product */
    private $currentProduct = null;

	function handleRow($row)
	{
		// continuation, use for description
        if (empty($row['Codice'])) {
            if (empty($row['Descrizione'])) {
                return;
			} elseif ($this->currentProduct === null) {
				return;
            } else {
				$description = $this->currentProduct->getDescription();
				if (empty($description)) {
					$this->currentProduct->setDescription($row['Descrizione']);
				} else {
					$this->currentProduct->setDescription($description . "\r\n" . $row['Descrizione']);
				}
				$this->logger->info("Description: " . $this->currentProduct->getDescription());
                return;
            }
        }
 		parent::handleRow($row);
	}

	function handleProduct(Brand $brand)
	{
		$this->currentProduct = parent::handleProduct($brand);
		return $this->currentProduct;
	}

}