<?php
namespace Youppers\CustomerBundle\Service;

use Symfony\Component\DependencyInjection\ContainerAware;
use Doctrine\Common\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Youppers\ProductBundle\Entity\ProductVariant;

class ItemService extends ContainerAware
{
	private $managerRegistry;
	private $logger;
	private $debug;
	
	public function __construct(ManagerRegistry $managerRegistry, LoggerInterface $logger)
	{
		$this->managerRegistry = $managerRegistry;
		$this->logger = $logger;
	}	

	public function setContainer(ContainerInterface $container = null)
	{
		parent::setContainer($container);
		$this->debug = $this->container->getParameter('kernel.environment') == 'dev';
	}
	
	/**
	 * Find a session by Id
	 *
	 * @param guid $sessionId
	 * @return Session
	 */
	protected function getSession($sessionId)
	{
		return $this->managerRegistry->getRepository('YouppersCustomerBundle:Session')->find($sessionId);
	}
	
	/**
	 *
	 * @return \Doctrine\Common\Persistence\ObjectRepository for YouppersDealerBundle:Consultant
	 */
	protected function getRepository()
	{
		return $this->managerRegistry->getRepository('YouppersCustomerBundle:Item');	
	}

	protected function getVariant($variantId)
	{
		if (empty($variantId)) {
			throw new \Exception("Variant id not specified");
		}
		$variant = $this->managerRegistry->getRepository('YouppersProductBundle:ProductVariant')->find($variantId);
		if (empty($variant)) {
			throw new \Exception(sprintf("Variant '%s' not found",$variantId));
		}
		return $variant;
	}

	protected function getZone($zoneId)
	{
		if (empty($zoneId)) {
			throw new \Exception("Zone id not specified");
		}
		$zone = $this->managerRegistry->getRepository('YouppersCustomerBundle:Zone')->find($zoneId);
		if (empty($zone)) {
			throw new \Exception(sprintf("Zone'%s' not found",$zoneId));
		}
		return $zone;
	}

	protected function getItem($itemId)
	{
		if (empty($itemId)) {
			throw new \Exception("Item id not specified");
		}
		$item = $this->getRepository()->find($itemId);
		if (empty($item)) {
			throw new \Exception(sprintf("Item '%s' not found",$itemId));
		}
		return $item;
	}
	
	protected function getItemBy($session,$variant,$zone)
	{
		return $this->getRepository()->findOneBy(array(
				'session' => $session,
				'variant' => $variant,
				'zone' => $zone				
		));
	}

	public function create($sessionId,$item) {
		return $this->createMultiple($sessionId, array($item));
	}
	
	public function createMultiple($sessionId,$items)
	{
		if ($sessionId) {
			$session = $this->getSession($sessionId);
			if ($session === null) {
				$this->logger->error(sprintf("Session '%s' not found",$sessionId));
				throw new NotFoundResourceException("Session not found");
			}
		}
		
		$repo = $this->getRepository();
		$itemClass = $repo->getClassName();
		$em = $this->managerRegistry->getManagerForClass($itemClass);
		
		$newItems = array();
		foreach ($items as $item) {
			$variant = $this->getVariant($item['variantId']);			
			$zone = $this->getZone($item['zoneId']);
			$item = $this->getItemBy($session, $variant, $zone);
			if (empty($item)) {
				$item = new $itemClass;
				$item->setSession($session);
				$item->setVariant($variant);
				$item->setZone($zone);
				$em->persist($item);
			}				
			$item->setRemoved(false);
			$newItems[] = $item;			
		}		
		$em->flush();
		return $newItems;
	}
	
	public function listForSession($sessionId,$variantId=null)
	{
		if ($sessionId) {
			$session = $this->getSession($sessionId);
			if ($session === null) {
				$this->logger->error(sprintf("Session '%s' not found",$sessionId));
				throw new NotFoundResourceException("Session not found");
			}
		}
		
		if ($variantId === null) {
			return $this->getRepository()->findBy(array('session' => $session, 'removed' => false),array('zone' => 'ASC'));
		} else {
			$variant = $this->getVariant($variantId);
			return $this->getRepository()->findBy(array('session' => $session, 'removed' => false, 'variant' => $variant));
		}			
	}
	
	public function remove($itemId,$sessionId=null) 
	{
		$item = $this->getItem($itemId);
		$em = $this->managerRegistry->getManagerForClass(get_class($item));
		$item->setRemoved(true);
		$em->flush();
		return $item;
	}
}
