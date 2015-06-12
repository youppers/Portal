<?php
namespace Youppers\CustomerBundle\Service;

use Symfony\Component\DependencyInjection\ContainerAware;
use Youppers\CustomerBundle\Entity\Session;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Sonata\CoreBundle\Form\FormHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Form;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use FOS\UserBundle\Model\User;
use Youppers\CustomerBundle\Entity\Profile;
use Youppers\CustomerBundle\YouppersTCPDF;

class SessionService extends ContainerAware
{
	private $managerRegistry;
	private $logger;
	private $tokenStorage = null;
	
	public function __construct(ManagerRegistry $managerRegistry, LoggerInterface $logger)
	{
		$this->managerRegistry = $managerRegistry;
		$this->logger = $logger;
	}	
	
	/**
	 * Used to set user as current authenticated user
	 *
	 * @param TokenStorageInterface $tokenStorage
	 */
	public function setTokenStorage(TokenStorageInterface $tokenStorage)
	{
		$this->tokenStorage = $tokenStorage;
	}
	
	/**
	 * Create a new session, optionally associated to a store (that must exists)
	 * @param uuid $storeId
	 * @return Session
	 */
	public function newSession($storeId = null)
	{
		$repo = $this->managerRegistry->getRepository('YouppersCustomerBundle:Session');
		$sessionClass = $repo->getClassName();
		$em = $this->managerRegistry->getManagerForClass($sessionClass);
		$session = new $sessionClass;
		if ($storeId) {
			$store = $em->find('YouppersDealerBundle:Store', $storeId);
			if (empty($store)) {
				throw new \Exception("Invalid storeId " . $storeId);
			} else {
				$session->setStore($store);
			}
		} else {
			$store = null;
		}
		
		if ($this->tokenStorage) {
			$user = $this->tokenStorage->getToken()->getUser();
			if ($user) {
				$profile = $this->getDefaultProfile($user);
				$session->setProfile($profile);
			}
		}
		
		$em->persist($session);
		$em->flush();
	
		$this->container->get('youppers_common.analytics.tracker')->sendNewSession($session);
	
		return $session;
	}
		
	public function getDefaultProfile(User $user)
	{
		$repo = $this->managerRegistry->getRepository('YouppersCustomerBundle:Profile');
		
		$profile = $repo->findOneBy(array('user' => $user, 'isDefault' => true));
		
		if (empty($profile)) {
			$profile = $this->newDefaultProfile($user);
		}
		
		return $profile;
	}

	private function newDefaultProfile(User $user)
	{
		$repo = $this->managerRegistry->getRepository('YouppersCustomerBundle:Profile');
		$profileClass = $repo->getClassName();
		$em = $this->managerRegistry->getManagerForClass($profileClass);
		$profile = new $profileClass;
		$profile->setUser($user);
		$profile->setIsDefault(true);
		$profile->setName('');
		$em->persist($profile);
		$em->flush();
		return $profile;
	}
	
	
	/**
	 * set the store of the session if not set
	 * 
	 * @param id|Session $session
	 * @param id|Store $store
	 * @param string $force Update also if already set
	 */
	public function setSessionStore($session, $store, $force = false) {
		if ($session === null || $store === null) {
			return;
		}		
		if (!is_object($session)) {
			$session = $this->managerRegistry->getRepository('YouppersCustomerBundle:Session')->find($session);
		}
		if ($session !== null && ($force === true || $session->getStore() === null) ) {
			if (!is_object($store)) {
				$store = $this->managerRegistry->getRepository('YouppersDealerBundle:Store')->find($store);
			}
			if ($store !== null) {
				$this->logger->info(sprintf("Updating session '%s' store '%s'",$session,$store));
				$session->setStore($store);
				$this->managerRegistry->getManagerForClass('YouppersCustomerBundle:Session')->flush();
				$this->container->get('youppers_common.analytics.tracker')->sendNewSession($session);				
			}
		}		
	}
	
	/**
	 * set the session store using the box's store
	 * 
	 * @param id|Session $session
	 * @param id|Box $box
	 */
	public function setSessionStoreUsingBox($session, $box)
	{
		if ($session === null || $box === null) {
			return;
		}		
		if (!is_object($box)) {
			$box = $this->managerRegistry->getRepository('YouppersDealerBundle:Box')->find($box);
		}
		if ($box !== null) {
			$this->setSessionStore($session, $box->getStore());
		}
	}
	
	/**
	 * Check to identifiy box that are not in the correct store
	 * 
	 * @param id|Session $session
	 * @param id|Box $box
	 * @return boolean true if the box store match session store, false if don't match, null if don't know
	 */
	public function isBoxInStoreOfSession($session,$box) 
	{
		if ($session === null || $box === null) {
			return;
		}
		if (!is_object($box)) {
			$box = $this->managerRegistry->getRepository('YouppersDealerBundle:Box')->find($box);
		}
		if (!is_object($session)) {
			$session = $this->managerRegistry->getRepository('YouppersCustomerBundle:Session')->find($session);
		}
		if ($box !== null && $session !== null 
				&& $session->getStore() !== null
				&& $box->getStore() !== null) {
			if ($session->getStore() === $box->getStore()) {
				return true;
			} else {
				$this->logger->warning(sprintf("Box '%s' is in store '%s' but the session '%s' is in store '%s'",
						$box, $box->getStore(),$session,$session->getStore()
						));
				return false;
			}
		}		
	}

	/**
	 * 
	 * @param guid $sessionId
	 * @param array $data form data eg 
	 */
	public function read($sessionId)
	{
		$session = $this->getSession($sessionId);
		
		if (empty($session)) {
			throw new \Exception("Invalid sessionId ".$sessionId);
		}
		
		if ($this->tokenStorage) {
			$user = $this->tokenStorage->getToken()->getUser();
			if ($user && empty($session->getProfile())) {
				$profile = $this->getDefaultProfile($user);
				$session->setProfile($profile);
				$this->managerRegistry->getManagerForClass(get_class($session))->flush();
			}
		}
		return $session;		
	}
	
	/**
	 * Update a session
	 * 
	 * @param guid $sessionId
	 * @param array $data
	 * @return Ambigous <\Youppers\CustomerBundle\Service\Entity, \Symfony\Component\Form\Form>
	 */
	public function update($sessionId, $data)
	{
		if ($sessionId) {
			$session = $this->getSession($sessionId);
			if ($session === null) {
				$this->logger->error(sprintf("Session '%s' not found",$sessionId));
				throw new \Exception("Session not found");
			}
		}
				
		return $this->handleWrite($session,$data);
	}
	
	/**
	 * Find a session by Id
	 * 
	 * @param guid $sessionId
	 * @return Session
	 */
	public function getSession($sessionId) 
	{
		return $this->getRepository()->find($sessionId);
	}
	
	public function getRepository()
	{
		return $this->managerRegistry->getRepository('YouppersCustomerBundle:Session');
	}
	
	/**
	 * 
	 * @param guid $id sessionId
	 * @param array $data
	 * @return Entity|Form
	 */
	protected function handleWrite($session,$data)
	{
		$form = $this->container->get('form.factory')->createNamed(null, 'youppers_customer_session_form', $session, array(
				'csrf_protection' => false
		));
		
		$form->submit($data,false);		

		if ($form->isValid()) {
			$session = $form->getData();
			$om = $this->managerRegistry->getManagerForClass('YouppersCustomerBundle:Session');
			$om->persist($session);
			$om->flush();
			$this->logger->debug("Update session");
			return $session;
		} else {
			$this->logger->warn("Invalid session: " . $form->getErrors());
			return $form;
		}
	}
	
	/**
	 * Send the session via email
	 * @param unknown $sessionId
	 * @throws \Exception
	 * @return Ambigous <object, mixed>
	 */
	public function send($sessionId)
	{
		$session = $this->getSession($sessionId);

		if (empty($session)) {
			throw new \Exception("Invalid sessionId ".$sessionId);
		}
		
		$profile = $session->getProfile();
		$store = $session->getStore();
		
		if (empty($store)) {
			throw new \Exception("Store not selected for session");
		}
		
		$fromAddress = null;
		$fromName = null;
		
		if ($consultant = $session->getConsultant()) {
			if ($consultant->getUser()) {
				$fromAddress = $consultant->getUser()->getEmail();
			}
			$fromName = trim($consultant->getFullname());
			if (empty($fromName)) {
				$fromName = trim($consultant->getUser()->getFullname());
			}
		}
		if (empty($fromAddress)) {
			$fromAddress = $store->getEmail();
		}
		if (empty($fromName)) {
			$fromName = $store->getName();
		}			 
		if (empty($fromAddress)) {
			$fromAddress = $store->getDealer()->getEmail();
		}

		$toAddress = null;
		$toName = null;

        if (!empty($profile)) {
            $toAddress = $profile->getUser()->getEmail();
            $toName = trim($profile->getUser()->getFullname());
        }
		if (empty($toAddress)) {
			$toAddress = $fromAddress;
		}		
		if (empty($toName)) {
			$toName = $session->getName();
		}
		if (empty($toName)) {
			$toName = "Cliente";
		}
				
		$this->logger->info(sprintf("Sending Session via email to '%s'",$toAddress));
		
		$mailer = $this->container->get('mailer');		

		$message = $mailer->createMessage();		
		
		$message->setFrom($fromAddress, $fromName);
		$message->setTo($toAddress,$toName);
		$message->setCc($fromAddress,$fromName);

        $subject = sprintf("Visita al negozio %s",$session->getStore()->getName());

		$message->setSubject($subject);

		$qb = $this->managerRegistry->getRepository('YouppersCustomerBundle:Item')->createQueryBuilder('i');
		$qb
			->addSelect('z')
			->leftJoin('i.zone', 'z')
			->andWhere('i.session = :session')
			->setParameter('session',$session)
			->addOrderBy('z.name')
		;
		$items = $qb->getQuery()->execute();

        $medias = array();
        $images = array();
        $imagesurl = array();
		
		foreach ($items as $item) {
			if ($item->getRemoved()) {
				continue;
			}
			$variant = $item->getVariant();
			
			$media = $variant->getImage();

            if (empty($media)) {
                $media = $variant->getProductCollection()->getImage();
            }

            if (empty($media)) {
                $media = $variant->getProduct()->getBrand()->getLogo();
            }

            if (empty($media)) {
                $media = $variant->getProduct()->getBrand()->getCompany()->getLogo();
            }

            if ($media) {
                $mediaProvider = $this->container->get($media->getProviderName());
                $url = $mediaProvider->generatePublicUrl($media,'reference');
                $imagesurl[$item->getId()] = $url;
                $images[$item->getId()] = $media;
            } else {
                $images[$item->getId()] = "#";
            }

            $itemMedias = array();

            $gallery = $variant->getProductCollection()->getPdfGallery();
            if ($gallery) {
                foreach ($gallery->getGalleryHasMedias() as $galleryMedia) {
                    $media = $galleryMedia->getMedia();
                    $itemMedia = array('media' => $media);
                    $mediaProvider = $this->container->get($media->getProviderName());
                    $url = $mediaProvider->generatePublicUrl($media, 'reference');
                    $itemMedia['reference'] = $url;
                    $url = $mediaProvider->generatePublicUrl($media, 'admin');
                    //$message->attach(\Swift_Attachment::fromPath($path));
                    $itemMedia['icon'] = $url;
                    $itemMedias[] = $itemMedia;
                }
            }

            $gallery = $variant->getPdfGallery();
			if ($gallery) {
				foreach ($gallery->getGalleryHasMedias() as $galleryMedia) {
					$media = $galleryMedia->getMedia();
                    if (empty($media)) {
                        $this->logger->warning(sprintf("Gallery %s has empty media",$gallery));
                        continue;
                    }
                    $itemMedia = array('media' => $media);
					$mediaProvider = $this->container->get($media->getProviderName());
					$url = $mediaProvider->generatePublicUrl($media, 'reference');
                    $itemMedia['reference'] = $url;
                    $url = $mediaProvider->generatePublicUrl($media, 'admin');
					//$message->attach(\Swift_Attachment::fromPath($path));
                    $itemMedia['icon'] = $url;
                    $itemMedias[] = $itemMedia;
				}
			}
            $medias[$item->getId()] = $itemMedias;
		}
        $templating = $this->container->get('templating');

        $data = array(
            'session' => $session,
            'items' => $items,
            'images' => $images,
            'imagesurl' => $imagesurl,
            'medias' => $medias,
            'fromName' => $fromName,
            'toName' => $toName
        );
        //dump($data);
        //echo($templating->render('YouppersCustomerBundle:Emails:session.html.twig',$data)); dump($data); die();
        $message->setBody(
            $templating->render('YouppersCustomerBundle:Emails:session.html.twig',$data),
            'text/html'
        );
        $message->addPart(
            $templating->render('YouppersCustomerBundle:Emails:session.txt.twig',$data),
            'text/plain'
        );

        $pdf = $this->container->get("white_october.tcpdf")->create();

        $pdf = new YouppersTCPDF();

        // set document information
        $pdf->SetCreator("Youppers Italia S.r.l.");
        $pdf->SetAuthor($fromName);
        $pdf->SetTitle("Visita " . $session);
        $pdf->SetSubject($subject);
        $pdf->SetKeywords('Youppers');

        $pdf->setHeaderText($subject);
        $pdf->setHeaderImage('bundles/youpperscommon/14-12-11_Youppers_logo.png');

        $html = $templating->render('YouppersCustomerBundle:Emails:session.html.twig',$data);

        //echo($html); die;

        // add a page
        $pdf->AddPage();

        $pdf->writeHTML($html, true, false, true, false, '');

        // reset pointer to the last page
        $pdf->lastPage();

        $message->attach(\Swift_Attachment::newInstance($pdf->Output('','S'), $session . '.pdf','application/pdf'));

		$failed = array();
		$mailer->send($message,$failed);
		
		if (empty($failed)) {
			$this->logger->info("Sent email " . $message);		
			return $message->toString();
		} else {
			$this->logger->error("Failed sending to  " . implode(', ',$failed) . " of " . $message);		
			throw new \Exception("Send failed to: " . implode(', ',$failed));		
		}
	}

	public function remove($sessionId)
	{
		$session = $this->getSession($sessionId);
		$session->setRemoved(true);
		$em = $this->managerRegistry->getManagerForClass(get_class($session));
		$em->flush();
		return $session;
	}

	public function clean($delete = false)
	{
		$qb = $this->getRepository()->createQueryBuilder('s');
		$qb
			->addSelect('h')
			->leftJoin('s.history', 'h')
			->addSelect('i')
			->leftJoin('s.items', 'i')			
			->where('s.updatedAt < :when')
			->andWhere('h.session IS NULL')
			->andWhere('i.session IS NULL')
			->andWhere('s.profile IS NULL')
			->andWhere('s.store IS NULL')
			->setParameter('when', (new \DateTime())->sub(new \DateInterval('P1D')))
			;
		$sessions = $qb->getQuery()->execute();

		if ($delete) {
			$em = $this->managerRegistry->getManagerForClass('YouppersCustomerBundle:Session');
			foreach ($sessions as $session) {
				$em->remove($session);
			}
			$em->flush();
		}
		
		return count($sessions);
	}
	
}
