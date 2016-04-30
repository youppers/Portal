<?php
namespace Youppers\ProductBundle\Guesser;

use Youppers\ProductBundle\Entity\AttributeStandard;

abstract class AbstractDimensionPropertyGuesser extends BasePropertyGuesser
{	

	/**
	 * @return int Number of dimensions
	 */
	protected abstract function getDimensions();

	public function normalizeValue($value,AttributeStandard $standard = null)
	{
		$atoms = array();
		for ($i=1; $i <= $this->getDimensions();$i++) {
			$atoms[] = '([0-9]+[,\.]?[0-9]*)';
		}
		$regexp = '/^' . implode('\s*X\s*',$atoms) . '$/i';
		
		if (preg_match($regexp,$value,$matches)) {
			$dims=array();
			for ($i=1; $i <= $this->getDimensions();$i++) {
				$dim = trim($matches[$i]);
				$dim = str_replace(",",".",$dim);
				$dim = 0 + $dim;  // implicit conversion to number
				$dim = str_replace(".",",",$dim); // implicit conversion to string
				$dims[] = $dim;
			}
			$normalized = implode('X',$dims);
			if ($this->getDebug()) {
				$this->getLogger()->debug(sprintf("Dimension value '%s' normalized '%s'",$value,$normalized));
			}
			return $normalized;
		} else {
			if (empty($standard)) {
				$this->getLogger()->debug(sprintf("Value '%s' should match '%s'",$value,$regexp));
				return parent::normalizeValue($value,$standard);
			} else {
				$this->getLogger()->warning(sprintf("Value '%s' must match '%s'",$value,$regexp));
				return null;
			}
		}
	}

}