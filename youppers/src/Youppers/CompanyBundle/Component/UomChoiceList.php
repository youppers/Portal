<?php

namespace Youppers\CompanyBundle\Component;

use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;

class UomChoiceList extends ChoiceList {

	static function create() {
		return new UomChoiceList(
				array('CT','PCE','KGM','MTR','MTK','LTR','CU','TU'), 
				array('cartone','pezzi','chilogrammi','metri','metri quadri','litri','Consumer Unit','Trade Unit'),
				array('MTK')
				);
	}
	
}