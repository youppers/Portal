<?php

namespace Youppers\CommonBundle\Qr;

use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityManager;
use Youppers\CommonBundle\Entity\Qr;
use Assetic\Exception\Exception;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Youppers\CommonBundle\Manager\QrManager;
use Youppers\DealerBundle\Entity\Store;

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
		
		if (method_exists($object,'getUrl') && !empty($object->getUrl())) {
			$qr->setUrl($object->getUrl());
			$this->session->getFlashBag()->add('sonata_flash_info','enabled QRCode url ' . $object->getUrl());
		}
		
		$this->getManager()->commit();
		
		$this->getManager()->flush();		
		
		$this->session->getFlashBag()->add('sonata_flash_success','enabled_qr');
		
	}
	
	public function findById($id) {
		return $this->getManager()
		->getRepository('YouppersCommonBundle:Qr')
		->find($id);
	}
	
	/**
	 * Find a QRCode using arbitray url (3rd part QRCodes) 
	 * 
	 * @param string $url
	 * @return Qr
	 */
	public function findByUrl($url) {
		
		return $this->getManager()
			->getRepository('YouppersCommonBundle:Qr')
			->findOneBy(array('url' => $url));
	}
	
	/**
	 * Find a QRCode
	 * 
	 * @param string $text QRCode text
	 * @param uuid $sessionId
	 * @param uuid $id QRCode Id if supplied have precence over text
	 * @return Qr
	 */
	public function find($text, $sessionId, $id = null) {
		$logger = $this->get('logger');
		
		$logger->debug("Searching qr with text '" . $text . "'");
		
		$request = Request::create($text);
		
		if (empty($id)) {
			try {
				$route = $this->container->get('router')->match($request->getRequestUri());
				if ($route['_route'] == 'youppers_common_qr_redirecttotarget') {
					$id = $route['id'];
					$qr = $this->findById($id);
				} else {
					$qr = null;		
				}
			} catch (ResourceNotFoundException $e) {
				$logger->debug("Qr NOT match any route, trying url");
				$qr = $this->findByUrl($text);
				if (empty($qr)) {
					$qrManager = new QrManager($this->get('doctrine'));
					$qr = $qrManager->create();
					$qr->setUrl($text);
					$qr->setEnabled(false);
					$qr->setTargetType('');
					$qrManager->save($qr);
				}
			}
		} else {
			$qr = $this->findById($id);
		}

		if ($qr === null) {
			$logger->warning("Not found qr '$text'");
		} else {
			if (!empty($qr->getTargetType()) && !$qr->getEnabled() && false === $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
				$logger->error("Found a qr not enabled '$text': "  . $qr->getTargetType());
				throw $this->createAccessDeniedException('Disabled QRCode is only allowed to admin',new DisabledException('Disabled QRCode'));
			}				
			$logger->info("Found qr '$text': "  . $qr->getTargetType());

			if (empty($sessionId)) {
				$session = null;
			} else {
				$session = $this->getManager()->getRepository('YouppersCustomerBundle:Session')->find($sessionId);
			}

			if ($qr->getTargetType() == 'youppers_dealer_box') {
				$box = $qr->getTargets()->first();
				$this->get('youppers.customer.session')->setSessionStoreUsingBox($session,$box);
				
				if (false === $this->get('youppers.customer.session')->isBoxInStoreOfSession($session,$box)) {
					return null;
				}
				$this->container->get('youppers_common.analytics.tracker')->sendQrFindBox($box,$session);				
				$this->container->get('youppers.customer.service.history')->newHistoryQrBox($box,$session);				
			}				

			if ($qr->getTargetType() == 'youppers_company_product') {
				$product = $qr->getTargets()->first();
				$this->container->get('youppers_common.analytics.tracker')->sendQrFindProduct($product,$session);
				if ($variant = $product->getVariant()) {
					$this->container->get('youppers.customer.service.history')->newHistoryQrVariant($variant,$session);						
				}
			}
			
		}
						
		return $qr;
	}

    public function pdfBoxesStore(Store $store)
    {
        $boxes = $store->getBoxes()->filter(function ($box) { return $box->getEnabled(); });
        $pdf = $this->pdfBoxes($boxes);
        $pdf->SetSubject($store);
		return $pdf;
    }

    public function pdfBoxes($boxes)
    {
        $pdf = $this->container->get('white_october.tcpdf')->create();

        $pdf = new \TCPDF('L');
        $pdf->SetMargins(0,0);

        $pdf->SetTitle("Stampa QRCode del Negozio");
        $pdf->SetAuthor("Youppers");

        // remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetAutoPageBreak(false);

        $pdf->setCellPaddings(5, 1, 5, 0);
        $pdf->setCellMargins(0, 0, 0, 0);

        //$fontname = $pdf->addTTFfont('/path-to-font/DejaVuSans.ttf', 'TrueTypeUnicode', '', 32);

        $dimensions = $pdf->getPageDimensions();
        $slices = array('w' => 4, 'h' => 2);

        $w = $dimensions['wk'] / $slices['w'];
        $h = $dimensions['hk'] / $slices['h'];

        $sizeQr = 70; //min($w,$h);

        // set style for barcode
        $style = array(
            'border' => 0,
            'vpadding' => 'auto',
            'hpadding' => 'auto',
            'fgcolor' => array(0,0,0),
            'bgcolor' => false, //array(255,255,255)
            'module_width' => 1, // width of a single module in points
            'module_height' => 1 // height of a single module in points
        );

        $i = 0;
        foreach ($boxes as $box) {
            if (empty($box)) {
                continue;
            }
            $qr = $box->getQr();
            if (empty($qr)) {
                continue;
            }
            if (($i % ($slices['w'] * $slices['h'])) == 0) {
                $pdf->AddPage('L', 'A4');
            }
            $x = ($w * (($i / $slices['h']) % $slices['w'])) % $dimensions['wk'];
            $y = ($h * ($i % $slices['h'])) % $dimensions['hk'];

            $pdf->Image('bundles/youpperscommon/14-12-11_Youppers_logo.png',$x+$w/4, $y+5 ,$w/2);

            $pdf->write2DBarcode($qr->getText(), 'QRCODE,M', $x + ($w - $sizeQr) /2, $y + 15, $sizeQr, $sizeQr, $style, 'N');

            $pdf->setCellHeightRatio(0.8);
            $pdf->SetXY($x, $y + 15 + $sizeQr);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell($w, 0, $box->getStore()->getName(), 0, 1, 'C', null, null, 1);
            $pdf->SetXY($x, $pdf->GetY());
            $pdf->SetFont('helvetica', '', 12);
            $pdf->Cell($w, 0, $box->getName() . ' [' . $box->getCode() . ']', 0, 1, 'C', null, null, 1);

            $pdf->setCellHeightRatio(0.3);
            $pdf->SetXY($x, $pdf->GetY());
            $pdf->SetFont('helvetica', '', 8);
            $maxh = $h - ($pdf->GetY() - $y);
            $pdf->MultiCell($w, 0, $box->getDescription(), 0, 'C', null, null, null, null, null, null, null, null, $maxh);

            $i++;
        }

        return $pdf;
    }

    public function getTarget($id) {

        $qr = $this->findById($id);
        if (empty($qr)) {
            $this->container->get('logger')->warning("Qr Code with id='$id' not found");
            return null;
        }

        $targetType = $qr->getTargetType();
        if (empty($targetType)) {
            $this->container->get('logger')->warning("Qr Code with id='$id' has empty target type");
            return null;
        }

        $targetTypes = $this->container->getParameter('youppers_common.qr');
        if (!array_key_exists($targetType,$targetTypes)) {
            $this->container->get('logger')->warning("Qr Code with id='$id' has invalid target type '$targetType'");
            return null;
        }

        $targetEntity = $targetTypes[$targetType]['entity'];

        $targetManager = $this->getDoctrine()->getManagerForClass($targetEntity);

        return $targetManager->getRepository($targetEntity)->findOneBy(array('qr' => $qr, 'enabled' => true));

    }
}
