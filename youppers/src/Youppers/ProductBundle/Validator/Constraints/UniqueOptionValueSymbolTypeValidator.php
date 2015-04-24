<?php
namespace Youppers\ProductBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Process\Exception\InvalidArgumentException;
use Doctrine\Common\Collections\Criteria;
use Youppers\ProductBundle\Entity\AttributeOption;

class UniqueOptionValueSymbolTypeValidator extends ConstraintValidator
{
	
	public function validate($value, Constraint $constraint)
	{		
		if ($value instanceof AttributeOption) {
			$option = $value;			
		} else {
			throw new InvalidArgumentException();
		}
		
		if (!(null === $option || empty($valueWithSymbol = $option->getValueWithSymbol())
				|| null === ($standard = $option->getAttributeStandard())
				|| null === ($type = $option->getAttributeType()))) {
			$criteria = Criteria::create()
				->where(Criteria::expr()->eq("symbol", $standard->getSymbol()));
			$standards = $type->getAttributeStandards()->matching($criteria);
			foreach ($standards as $typeStandard) {
				if ($typeStandard === $standard) {
					continue;
				} 
				foreach ($typeStandard->getAttributeOptions() as $option) {
					if (trim(strtoupper($option->getValueWithSymbol())) == trim(strtoupper($valueWithSymbol))) {
						$this->context->buildViolation(sprintf($constraint->message,$valueWithSymbol,$typeStandard))
							->addViolation();
					}
				}
			}
		}
	}	
}	