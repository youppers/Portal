<?php
namespace Youppers\ProductBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ConsistentBrand extends Constraint
{
	public $message = 'The Variant and Product must belong to the same Brand';
	
	public function validatedBy()
	{
		return get_class($this).'Validator';
	}
	
	public function getTargets()
	{
		return self::CLASS_CONSTRAINT;
	}
	
}