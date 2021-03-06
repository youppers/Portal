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
	public function getParentAssociationMapping()
	{
		return 'session';
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
		->add('session', null, array('route' => array('name' => 'show')))
		->add('variant', null, array('route' => array('name' => 'show')))
		->add('removed')
		->add('zone', null, array('associated_property' => 'name'))
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
		->add('session')
		->add('zone')
		->add('removed')
		;
	}

	/**
	 * Default Datagrid values
	 *
	 * @var array
	 */
	protected $datagridValues = array(
		'removed' => array('value' => 2)
	);

	/**
	 * {@inheritdoc}
	 */
	protected function configureShowFields(ShowMapper $showMapper)
	{
		$showMapper
		->add('session', null, array('route' => array('name' => 'show')))
		->add('variant', null, array('route' => array('name' => 'show')))
		->add('removed')
		->add('zone')
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
			->add('zone')
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
