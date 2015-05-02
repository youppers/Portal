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
use Youppers\CustomerBundle\Manager\ProfileManager;

class ProfileService extends ContainerAware
{
	private $managerRegistry;
	private $logger;
	private $tokenStorage = null;
	private $profileManager;
	
	public function __construct(ManagerRegistry $managerRegistry, LoggerInterface $logger)
	{
		$this->managerRegistry = $managerRegistry;
		$this->logger = $logger;
		$this->profileManager = new ProfileManager($managerRegistry);
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
	
	public function listForUser()
	{
		if ($this->tokenStorage
				&& ($token = $this->tokenStorage->getToken())
				&& ($user = $token->getUser())) { 
			return $this->profileManager->findBy(array('user' => $user));
		}
		throw new \Exception("User not logged in");		
	}

	public function create($data)
	{
		return $this->handleWrite(null, $data);
	}
		
	/**
	 * 
	 * @param guid $profileId
	 * @param array $data form data eg 
	 */
	public function read($profileId)
	{
		$profile = $this->profileManager->find($profileId);
		
		if (empty($profile)) {
			throw new \Exception("Invalid profileId ".$profileId);
		}
		return $profile;		
	}
	
	/**
	 * 
	 * @param unknown $profileId
	 * @param unknown $data
	 * @return Ambigous <\Youppers\CustomerBundle\Entity\Profile, \Symfony\Component\Form\Form>
	 */
	public function update($profileId, $data)
	{
		if ($profileId) {
			$profile = $this->read($profileId);
		}
				
		return $this->handleWrite($profile,$data);
	}

	public function delete($profileId)
	{
		if ($profileId) {
			$profile = $this->read($profileId);
			if ($profile) {
				return $this->profileManager->delete($profile);
			}
		}
		throw new \Exception("Invalid profileId ".$profileId);
	}
	
	/**
	 * 
	 * @param guid $id sessionId
	 * @param array $data
	 * @return Profile|Form
	 */
	protected function handleWrite($profile,$data)
	{
		$form = $this->container->get('form.factory')->createNamed(null, 'youppers_customer_profile_form', $profile, array(
				'csrf_protection' => false
		));
		
		$form->submit($data,false);		

		if ($form->isValid()) {
			$profile = $form->getData();
			$this->profileManager->save($profile);
			return $profile;
		} else {
			$this->logger->warn("Invalid profile: " . $form->getErrors());
			return $form;
		}
	}
	

}
