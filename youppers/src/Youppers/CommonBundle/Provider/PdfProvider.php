<?php
namespace Youppers\CommonBundle\Provider;

use Sonata\MediaBundle\Provider\FileProvider;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\MediaBundle\Model\MediaInterface;
use Symfony\Component\Form\Form;

class PdfProvider extends FileProvider
{
	protected $iconPath = null;
	
	/**
	 * {@inheritdoc}
	 */	
	public function generatePublicUrl(MediaInterface $media, $format)
	{
		if ($format == 'reference') {
			$path = $this->getReferenceImage($media);
		} else {
			$path = $this->iconPath;
		}
	
		return $this->getCdn()->getPath($path, $media->getCdnIsFlushable());
	}
		
	public function setIcon($path)
	{
		$this->iconPath = $path;
	}
		
	
}