<?php
namespace Youppers\CompanyBundle\Loader\ALTAECO;

use Youppers\CompanyBundle\Entity\Product;
use Youppers\CompanyBundle\Loader\AbstractPricelistLoader;
use Youppers\CompanyBundle\Loader\LoaderMapper;

class PricelistLoader extends AbstractPricelistLoader
{
	public function createMapper()
	{
		if ($this->brand && $this->brand->getCode() == 'VOG') {
			// codice	serie	descrizione	u.m.	scelta	validità	 euro 	 mq x collo 	 colli x plt 	 collo/pz 	 collo/kg
			$mapping = array(
				self::FIELD_CODE => 'codice',
				self::FIELD_COLLECTION => 'serie',
				self::FIELD_NAME => 'descrizione',
				self::FIELD_UOM => 'u.m.',
				self::FIELD_PRICE => ' euro ',
				self::FIELD_SURFACE => ' mq x collo ',
				self::FIELD_QUANTITY => ' collo/pz '
			);
		} else {
            // Bardelli
            $mapping = array(
                self::FIELD_BRAND => 'LVPLDV',
                self::FIELD_CODE => 'LVPART',
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

	private $code;
	private $name;

	/**
	 * Auto create missing code
	 * @param $row
	 */
	public function handleRow($row)
	{
		if (empty($row['codice'])) {
			$diff = $this->strdiff($this->name,$row['descrizione']);
			$row['codice'] = $this->code . '_' . $diff;
		} else {
			$this->code = $row['codice'];
			$this->name = $row['descrizione'];
		}
		parent::handleRow($row);
	}

	/**
	 * @param $old
	 * @param $new
	 * @return string difference between old and new
	 */
	private function strdiff($old,$new)
	{
		$diff='';
		$lo = strlen($old);
		$ln = strlen($new);
		$l=min($lo,$ln);
		$i = 0;
		for ($i=0; $i<$l; $i++) {
			$co = mb_substr($old, $i, 1);
			$cn = mb_substr($new, $i, 1);
			if ($cn != $co) {
				$diff .= $cn;
			}
		}
		if ($lo > $ln) {
			$diff .= substr($old,$ln,$lo-$ln);
		}
		if ($lo < $ln) {
			$diff .= substr($new,$lo,$ln-$lo);
		}
		return $diff;
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