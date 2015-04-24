<?php
namespace Youppers\ProductBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Value + Symbol must be unique for this Attribute Type
 * @Annotation
 */
class UniqueOptionValueSymbolType extends Constraint
{
	public $message = "Value + Symbol '%s' already in standard '%s'";
	
	public function validatedBy()
	{
		return get_class($this).'Validator';
	}
	
	public function getTargets()
	{
		return self::CLASS_CONSTRAINT;
	}
	
}