<?php

namespace Youppers\CompanyBundle\Component;

use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;

class UomChoiceList extends ChoiceList
{

	static function create()
	{
		// MQ, PZ, ML, KG, CP, LT
		return new UomChoiceList(
			array('MQ','PZ','ML','KG','CP','LT'),
			array('Metri Quadri','Pezzi','Metri Lineari','Kilogrammi','Coppie','Litri'),
			array('MQ','PZ','ML','KG','CP','LT')
		);
	}

	/**
	 * @return string normalized value
	 * @param $value string
	 */
	static function normalize($value)
	{
		if (empty($value)) {
			return $value;
		}
		switch (strtoupper(trim($value))) {
			case 'PZ':
			case 'PC':
			case 'PCE':
			case 'NAR':
			case 'PZ*':
			case 'AL PEZZO':
				return 'PZ';
			case 'MQ':
			case 'M2':
			case 'MTK':
			case 'AL M2':
				return 'MQ';
			case 'ML':
			case 'M':
				return 'ML';
			case 'KG':
				return 'KG';
			case 'CP':
				return 'CP';
			case 'LT':
			case 'L':
				return 'LT';
			default:
				throw new \UnexpectedValueException(sprintf("invalid Uom '%s'",$value));
		}

	}
	
}