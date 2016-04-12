<?php

namespace Youppers\CompanyBundle\Component;

use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;

class UomChoiceList extends ChoiceList
{

	static function create()
	{
		// MQ, PZ, ML, KG, CP, LT
		return new UomChoiceList(
			array('','PZ','MQ','ML','KG','CP','LT'),
			array('---','Pezzi','Metri Quadri','Metri Lineari','Kilogrammi','Coppie','Litri')
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
			case 'NR':
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
			case 'COMP.':
				return 'CP';
			case 'LT':
			case 'L':
				return 'LT';
			default:
				throw new \UnexpectedValueException(sprintf("invalid Uom '%s'",$value));
		}

	}
	
}