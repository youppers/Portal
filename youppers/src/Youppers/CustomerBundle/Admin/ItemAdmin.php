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

class ItemAdmin extends YouppersAdmin
{

	/**
	 * {@inheritdoc}
	 */
	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
		->add('session', null, array('route' => array('name' => 'show')))
		->add('variant', null, array('route' => array('name' => 'show')))
		->add('removed')
		->add('zones', null, array('associated_property' => 'name'))
		->add('_action', 'actions', array(
				'actions' => array(
						'show' => array(),
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
		->add('session')
		->add('zones')
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureShowFields(ShowMapper $showMapper)
	{
		$showMapper
		->add('session', null, array('route' => array('name' => 'show')))
		->add('variant', null, array('route' => array('name' => 'show')))
		->add('removed')
		->add('zones')
		->add('createdAt')
		->add('updatedAt')
		;
	}	
	
	/**
	 * {@inheritdoc}
	 */
	protected function configureFormFields(FormMapper $formMapper)
	{
		
		$formMapper
			->add('session', 'sonata_type_model_list', array('required' => false, 'constraints' => new Assert\NotNull()))
			->add('variant', 'sonata_type_model_list', array('required' => false, 'constraints' => new Assert\NotNull()))
			->add('removed', null, array('required'  => false))
			->add('zones')
		;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getNewInstance()
	{
		$object = parent::getNewInstance();
	
		$object->setRemoved(false);

		return $object;
	}
	
}
