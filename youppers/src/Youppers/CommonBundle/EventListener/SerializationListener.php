<?php
namespace Youppers\CommonBundle\EventListener;

use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use Symfony\Component\DependencyInjection\ContainerAware;
use Application\Sonata\MediaBundle\Document\Media;

/**
 * Add data after serialization
 *
 */
class SerializationListener extends ContainerAware implements EventSubscriberInterface
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
		$media = $event->getObject();
		$mediaProvider = $this->container->get($media->getProviderName());
		$formats = $mediaProvider->getFormats();
		if (array_key_exists($media->getContext() . '_' . $this->defaultFormat,$formats)) {
			$format = $mediaProvider->getFormatName($media, $this->defaultFormat);
		} else {
			$format = 'admin';
		}
		$event->getVisitor()->addData('url',$mediaProvider->generatePublicUrl($media, $format));
		$event->getVisitor()->addData('url.reference',$mediaProvider->generatePublicUrl($media, 'reference'));
	}
}