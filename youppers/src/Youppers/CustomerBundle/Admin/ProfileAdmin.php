<?php

namespace Youppers\CustomerBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Youppers\CommonBundle\Admin\YouppersAdmin;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ProfileAdmin extends YouppersAdmin
{
	
	private $tokenStorage = null;
	
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
	 * {@inheritdoc}
	 */
	protected function configureShowFields(ShowMapper $showMapper)
	{
		$showMapper
		->add('name')
		->add('user', null, array('route' => array('name' => 'show')))
		->add('sessions', null, array('route' => array('name' => 'show')))
		->add('createdAt')
		->add('updatedAt')
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
		->add('_action', 'actions', array(
				'actions' => array(
						'edit' => array(),
				)
		))		
		->addIdentifier('name', null, array('route' => array('name' => 'show')))
		->add('user', null, array('route' => array('name' => 'show')))
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
		->add('user')
		->add('name')
		;
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function configureFormFields(FormMapper $formMapper)
	{
		
		$formMapper
			->add('user', 'sonata_type_model_list')
			->add('name')
		;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getNewInstance()
	{
		$object = parent::getNewInstance();
		
		if ($this->tokenStorage) {
			$user = $this->tokenStorage->getToken()->getUser();
			if ($user) {
				$object->setUser($user);
			}
		}
	
		return $object;
	}
	
}
