<?php

namespace Youppers\CommonBundle\Qr;

use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityManager;
use Youppers\CommonBundle\Entity\Qr;
use Assetic\Exception\Exception;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

class QrService
{

	private $session;
	private $em;	
	
	/**
	 * @param \Symfony\Component\HttpFoundation\Session\Session $session
	 */
	public function __construct(Session $session, EntityManager $em)
	{
		$this->session = $session;
		$this->em = $em;
	}
	
	/**
	 * Get the class of the entity of the given type 
	 * @param string $type 
	 * @return string 
	 */
	function getClassName($type) {
		// TODO put these in configuration
		if ($type == 'youpper_dealer_box') {
			return 'Youppers\DealerBundle\Entity\Box';
		}
		if ($type == 'youpper_company_product') {
			return 'Youppers\CompanyBundle\Entity\Product';
		}
		if ($type == 'youpper_company_variation') {
			return 'Youppers\CompanyBundle\Entity\Variation';
		}		
		throw new InvalidArgumentException('Unsupported type ' . $type);		
	}

	/**
	 * Get the route to show the entity of the given type 
	 * @param string $type 
	 * @return string 
	 */
	function getRouteName($type) {
		// TODO put these in configuration
		if ($type == 'youpper_dealer_box') {
			return 'youppers_dealer_box_show';
		}
		if ($type == 'youpper_company_product') {
			return 'youppers_company_product_show';
		}
		if ($type == 'youpper_company_variation') {
			return 'youppers_company_variation_show';
		}		
		throw new InvalidArgumentException('Unsupported type ' . $type);		
	}

	/**
	 * Get the type of the given entity class name
	 * @param string $className
	 * @return string
	 */	
	function getType($className) {
		// TODO put these in configuration
		if ($className == 'Youppers\DealerBundle\Entity\Box') {
			return 'youpper_dealer_box';
		}
		if ($className == 'Youppers\CompanyBundle\Entity\Product') {
			return 'youpper_company_product';
		}
		if ($className == 'Youppers\CompanyBundle\Entity\Variation') {
			return 'youpper_company_variation';
		}
		throw new InvalidArgumentException('Unsupported class ' . $className);		
	}
	
	/**
	 * Generate and assign a QRcode to the object
	 * Cannot assign a new QRCode if the object already have one
	 * The same QRCode can be assigned to more than one object, but only one at a time can be active
	 * The initial state is "disabled", must be used the "enable" action
	 * @param Entity $object
	 */
	public function assign($object, $qr = null) {
		$type = $this->getType(get_class($object));
		if ($qr === null) {
			$qr = $object->getQr();				
			if ($qr !== null) {
				$this->session->getFlashBag()->add('sonata_flash_error','object_already_have_qr');
				return;
			}
			$qr = new Qr();
			$qr->setType($type);
			$object->setQr($qr);			
			$this->em->persist($qr);
			$this->em->flush();
			$this->session->getFlashBag()->add('sonata_flash_success','assigned_new_qr');				
		} else {
			if ($qr->getType() == $type) {
				$object->setQr($qr);				
				$this->em->flush();
			} else {
				throw new InvalidArgumentException('Wrong type, given ' . $type . ' expected ' . $qr->getType());
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
		
		if ($qr->getType() !== $this->getType($className)) {
			throw new InvalidArgumentException('Wrong type, qr:' . $qr->getType() . ' object:' . $this->getType($className));
		}
		
		if (!$qr->getEnabled()) {
			$qr->setEnabled(true);
		}
		
		// find all other item of the same class that have the same qr and disable these
		$this->em->beginTransaction();
		
		$repository = $this->em->getRepository($className);
		
		foreach ($repository->findBy(array('qr' => $qr, 'enabled' => true)) as $other) {
			if ($other === $object) {
				continue;
			}
			$other->setEnabled(false);
			$this->session->getFlashBag()->add('sonata_flash_info','disabled ' . $other);
		}
		
		$object->setEnabled(true);
		$this->session->getFlashBag()->add('sonata_flash_info','enabled ' . $object);
		
		$this->em->commit();
		
		$this->em->flush();		
		
		$this->session->getFlashBag()->add('sonata_flash_success','enabled_qr');
		
	}
}
