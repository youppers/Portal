<?php

namespace Youppers\CommonBundle\Qr;

use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityManager;
use Youppers\CommonBundle\Entity\Qr;
use Assetic\Exception\Exception;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class QrService extends Controller
{

	private $session;
	private $em;	
	
	/**
	 * @param \Symfony\Component\HttpFoundation\Session\Session $session
	 */
	public function __construct(Session $session)
	{
		$this->session = $session;
	}
	
	/**
	 * Get the class of the entity of the given type 
	 * @param string $targetType 
	 * @return string 
	 */
	function getClassName($targetType) {
		$params = $this->container->getParameter('youppers_common.qr');
		return $params[$targetType]['entity'];
	}

	/**
	 * Get the route to show the entity of the given type 
	 * @param string $targetType 
	 * @return string 
	 */
	function getRouteName($targetType) {
		$params = $this->container->getParameter('youppers_common.qr');
		return  $params[$targetType]['route'];
	}

	/**
	 * Get the type of the given entity class name
	 * @param string $className
	 * @return string
	 */	
	private function getTargetType($className) {
		foreach ($this->container->getParameter('youppers_common.qr') as $targetType => $options) {
			if ($options['entity'] == $className) {
				return $targetType;
			}
		}
		throw new InvalidArgumentException('Unsupported class ' . $className);		
	}
	
	private function getManager() 
	{
		if (null === $this->em) {
			$this->em = $this->get('doctrine')->getManager();	
		}
		return $this->em;
	}
	
	/**
	 * Generate and assign a QRcode to the object
	 * Cannot assign a new QRCode if the object already have one
	 * The same QRCode can be assigned to more than one object, but only one at a time can be active
	 * The initial state is "disabled", must be used the "enable" action
	 * @param Entity $object
	 */
	public function assign($object, $qr = null) {
		$targetType = $this->getTargetType(get_class($object));
		if ($qr === null) {
			$qr = $object->getQr();				
			if ($qr !== null) {
				$this->session->getFlashBag()->add('sonata_flash_error','object_already_have_qr');
				return;
			}
			$qr = new Qr();
			$qr->setTargetType($targetType);
			$object->setQr($qr);			
			$this->getManager()->persist($qr);
			$this->getManager()->flush();
			$this->session->getFlashBag()->add('sonata_flash_success','assigned_new_qr');				
		} else {
			if ($qr->getTargetType() == $targetType) {
				$object->setQr($qr);				
				$this->getManager()->flush();
			} else {
				throw new InvalidArgumentException('Wrong type, given ' . $targetType . ' expected ' . $qr->getTargetType());
			}
			$this->session->getFlashBag()->add('sonata_flash_success','assigned_qr');				
		}		
	}

	/**
	 * find all other item of the same class that have the same qr and disable these, then enable itself
	 * @param unknown $object
	 */
	public function enable($object) {
		$className = get_class($object);
		if ($className == false) {
			throw new InvalidArgumentException('Not an object');
		}
		
		$qr = $object->getQr();		
		if ($qr == null) {
			$this->session->getFlashBag()->add('sonata_flash_error','qr_not_assigned');
			return;				
		}
		
		if ($qr->getTargetType() !== $this->getTargetType($className)) {
			throw new InvalidArgumentException('Wrong type, qr:' . $qr->getTargetType() . ' object:' . $this->getTargetType($className));
		}
		
		if (!$qr->getEnabled()) {
			$qr->setEnabled(true);
		}
		
		// find all other item of the same class that have the same qr and disable these
		$this->getManager()->beginTransaction();
		
		$repository = $this->getManager()->getRepository($className);
		
		foreach ($repository->findBy(array('qr' => $qr, 'enabled' => true)) as $other) {
			if ($other === $object) {
				continue;
			}
			$other->setEnabled(false);
			$this->session->getFlashBag()->add('sonata_flash_info','disabled ' . $other);
		}
		
		$object->setEnabled(true);
		$this->session->getFlashBag()->add('sonata_flash_info','enabled ' . $object);
		
		$this->getManager()->commit();
		
		$this->getManager()->flush();		
		
		$this->session->getFlashBag()->add('sonata_flash_success','enabled_qr');
		
	}
}
