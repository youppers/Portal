<?php

namespace Youppers\CommonBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class QrController extends Controller
{

	/**
	 * @Route("/qr/{id}")
	 *
	 * cerca il qr
	 */
	public function findAction($id)
	{
		$qr = $this->getDoctrine()
		->getRepository('YouppersCommonBundle:Qr')
		->find($id);

		if ($qr == null) {
			throw new ResourceNotFoundException('QR code not found');
		}

		if (!$qr->getEnabled() && false === $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
        	throw $this->createAccessDeniedException('Only allowed to admin',new DisabledException('Disabled QRCode'));
    	}
    	
    	$targetType = $qr->getTargetType();
    	
		$target = $this->getDoctrine()
			->getRepository($this->get('youppers.common.qr')->getClassName($targetType))
			->findOneBy(array('enabled' => true, 'qr' => $qr));
		 
		if ($target) {
			return $this->redirectToRoute($this->get('youppers.common.qr')->getRouteName($targetType),
					array("id" => $target->getId()));
		} else {
			throw new ResourceNotFoundException('QR code target not found');
		}
	}
}
