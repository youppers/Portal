<?php

namespace Youppers\CommonBundle\Service;

use Symfony\Component\DependencyInjection\ContainerAware;

class CodifyService extends ContainerAware
{
	public function codify($text)
	{
		$slug = $this->container->get('sonata.core.slugify.cocur')->slugify(trim($text),'');
		return strtoupper($slug);		
	}
}