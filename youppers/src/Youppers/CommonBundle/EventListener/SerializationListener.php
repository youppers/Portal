<?php
namespace Youppers\CommonBundle\EventListener;

use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;

/**
 * Add data after serialization
 *
 */
class SerializationListener implements EventSubscriberInterface
{
	private $defaultFormat = 'list'; // TODO da rendere configurabile

	/**
	 * @inheritdoc
	 */
	static public function getSubscribedEvents()
	{
		return array(
				array('event' => 'serializer.post_serialize', 'class' => 'Application\Sonata\MediaBundle\Entity\Media', 'method' => 'onPostSerialize'),
		);
	}

	public function onPostSerialize(ObjectEvent $event)
	{
		global $kernel;
		$imageProvider = $kernel->getContainer()->get('sonata.media.provider.image');
		$image = $event->getObject();
		$formats = $imageProvider->getFormats();
		if (array_key_exists($image->getContext() . '_' . $this->defaultFormat,$formats)) {
			$format = $imageProvider->getFormatName($image, $this->defaultFormat);
		} else {
			$format = 'reference';
		}
		$event->getVisitor()->addData('url',$imageProvider->generatePublicUrl($image, $format));
	}
}