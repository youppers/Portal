<?php

namespace Youppers\CommonBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class QrController extends Controller
{

	/**
	 * @Route("/qr/{id}")
	 *
	 * cerca il qr
	 */
	public function redirectToTargetAction($id)
	{
		$qr = $this->container->get('youppers.common.qr')->find('', null, $id);

		if ($qr && $target = $this->container->get('youppers.common.qr')->getTarget($id)) {
			$targetType = $qr->getTargetType();    	
			return $this->redirectToRoute($this->get('youppers.common.qr')->getRouteName($targetType),
			array("id" => $target->getId()));
		} else {
			throw new ResourceNotFoundException('QR code target not found');
		}
	}
}
