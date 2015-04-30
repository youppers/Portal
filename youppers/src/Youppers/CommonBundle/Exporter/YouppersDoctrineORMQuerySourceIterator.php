<?php

namespace Youppers\CommonBundle\Exporter;

use Exporter\Source\SourceIteratorInterface;
use Exporter\Source\DoctrineORMQuerySourceIterator;

class YouppersDoctrineORMQuerySourceIterator extends DoctrineORMQuerySourceIterator implements SourceIteratorInterface {

	protected function getValue($value)
	{
		if (is_array($value) || $value instanceof \Traversable) {
			$result = [];
			foreach ($value as $item) {
				$result[] = $this->getValue($item);
			}
			$value = json_encode($result);
		} else {
			$value = parent::getValue($value);
		}
	
		return $value;
	}
	
}