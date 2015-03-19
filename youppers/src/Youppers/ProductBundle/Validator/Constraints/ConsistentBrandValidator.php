<?php
namespace Youppers\ProductBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Process\Exception\InvalidArgumentException;
use Youppers\CompanyBundle\Entity\Product;
use Youppers\ProductBundle\Entity\ProductVariant;

class ConsistentBrandValidator extends ConstraintValidator
{
	
	public function validate($value, Constraint $constraint)
	{
		if ($value instanceof ProductVariant) {
			$variant = $value;
			$product = $variant->getProduct();
		} else if ($value instanceof Product) {
			$product = $value;
			$variant = $product->getVariant();
		} else {
			dump($value);
			throw new InvalidArgumentException();
		}
		
		if (null === $variant || null === $product 
			|| null === $product->getBrand()
			|| null === $variant->getProductCollection()
			|| null === $variant->getProductCollection()->getBrand()) {
				// unable to compare
		} else {				
			if ($product->getBrand() !== $variant->getProductCollection()->getBrand()) {
				$this->context->buildViolation($constraint->message)
					->addViolation();
			}
		}
	}	
}	