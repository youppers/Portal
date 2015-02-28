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

class ProfileAdmin extends YouppersAdmin
{

	/**
	 * {@inheritdoc}
	 */
	protected function configureShowFields(ShowMapper $showMapper)
	{
		$showMapper
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
		;
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function configureFormFields(FormMapper $formMapper)
	{
		
		$formMapper
			->add('user', 'sonata_type_model_list', array('required' => false, 'constraints' => new Assert\NotNull()));
	}

}
