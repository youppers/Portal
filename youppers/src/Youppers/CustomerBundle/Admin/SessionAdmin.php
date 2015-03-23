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

class SessionAdmin extends YouppersAdmin
{

	/**
	 * {@inheritdoc}
	 */
	protected function configureShowFields(ShowMapper $showMapper)
	{
		$showMapper
		->add('profile', null, array('route' => array('name' => 'show')))
		->add('store', null, array('route' => array('name' => 'show')))
		->add('consultant', null, array('route' => array('name' => 'show')))
		->add('name')
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
		->add('profile', null, array('route' => array('name' => 'show')))
		->add('store', null, array('route' => array('name' => 'show')))
		->add('consultant', null, array('route' => array('name' => 'show')))
		->add('name')
		->add('createdAt')
		->add('_action', 'actions', array(
				'actions' => array(
						'edit' => array(),
				)
		))		
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
		->add('profile')
		->add('store')
		->add('consultant')
		->add('name')
		;
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function configureFormFields(FormMapper $formMapper)
	{
		
		$formMapper
			->add('profile', 'sonata_type_model_list', array('required' => false))
			->add('store', 'sonata_type_model_list', array('required' => false))
			->add('consultant', 'sonata_type_model_list', array('required' => false))
			->add('name')
		;
	}

}
