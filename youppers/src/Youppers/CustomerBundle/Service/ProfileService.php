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

	private function getUser()
	{
		if ($this->tokenStorage
			&& ($token = $this->tokenStorage->getToken())
			&& ($user = $token->getUser())) {
				return $user;
		}
	}
	
	public function listForUser()
	{
		if ($user = $this->getUser()) { 
			return $this->profileManager->findBy(array('user' => $user));
		}
		throw new \Exception("User not logged in");		
	}

	public function create($data)
	{
		if ($user = $this->getUser()) {
			$data['user'] = $user->getId();
			return $this->handleWrite(null, $data);
		}
		throw new \Exception("User not logged in");		
	}
		
	/**
	 * 
	 * @param guid $profileId
	 * @param array $data form data eg 
	 */
	public function read($profileId)
	{
		if ($user = $this->getUser()) {
			$profile = $this->profileManager->find($profileId);
			
			if (empty($profile)) {
				throw new \Exception("Invalid profileId ".$profileId);
			}
			if ($profile->getUser() != $user) {
				throw new \Exception("User not allowed to read this profile");
			}					
			return $profile;		
		}
		throw new \Exception("User not logged in");		
	}
	
	/**
	 * 
	 * @param unknown $profileId
	 * @param unknown $data
	 * @return Ambigous <\Youppers\CustomerBundle\Entity\Profile, \Symfony\Component\Form\Form>
	 */
	public function update($profileId, $data)
	{
		if ($user = $this->getUser()) {
			if ($profileId) {
				$profile = $this->read($profileId);
			}
			if ($profile->getUser() != $user) {
				throw new \Exception("User not allowed to update this profile");
			}
			if (array_key_exists('user',$data) && $data['user'] != $user->getId() && !$user->hasRole('ROLE_SUPER_ADMIN')) {
				throw new \Exception("User not allowed to changed the user of the profile");
			}				
			$normalizer = $this->container->get('fos_rest.normalizer.camel_keys');
			return $this->handleWrite($profile,$normalizer->normalize($data));
		}
		throw new \Exception("User not logged in");
	}

	public function delete($profileId)
	{
		if ($user = $this->getUser()) {
			if ($profileId) {
				$profile = $this->read($profileId);
				if ($profile->getUser() != $user) {
					throw new \Exception("User not allow to update this profile");
				}					
				if ($profile) {
					return $this->profileManager->delete($profile);
				}
			}
			throw new \Exception("Invalid profileId ".$profileId);
		}
		throw new \Exception("User not logged in");
	}
	
	/**
	 * 
	 * @param unknown $profile
	 * @param unknown $data
	 * @return \Symfony\Component\Form\mixed|\Symfony\Component\Form\FormInterface
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
