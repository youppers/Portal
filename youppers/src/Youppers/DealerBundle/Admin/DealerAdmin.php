<?php

namespace Youppers\DealerBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AdminInterface;

#use Sonata\Bundle\DemoBundle\Entity\Inspection;

class DealerAdmin extends Admin
{
	protected function configureTabMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
	{
		if (!$childAdmin && !in_array($action, array('edit', 'show'))) { return; }
	
		$admin = $this->isChild() ? $this->getParent() : $this;
		$id = $admin->getRequest()->get('id');
	
		if ($action != 'edit') {
			$menu->addChild('Edit', array('uri' => $admin->generateUrl('edit', array('id' => $id))));
		}
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function configureShowFields(ShowMapper $showMapper)
	{
		$showMapper
		->add('enabled')
		->add('name')
		->add('code')
		->add('description')
		->add('createdAt')
		->add('updatedAt')
		->add('stores', null, array('route' => array('name' => 'show')))
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
		->add('enabled', null, array('editable' => true))
		->addIdentifier('name', null, array('route' => array('name' => 'show')))
		->add('code')
		->add('stores')
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
		->add('name')
		->add('enabled')
		;
	}
	
	/**
	 * Default Datagrid values
	 *
	 * @var array
	 */
	protected $datagridValues = array(
			//'isActive' => array('value' => 1)
	);

	/**
	 * {@inheritdoc}
	 */
	protected function configureFormFields(FormMapper $formMapper)
	{
		$formMapper
		->with('Dealer', array('class' => 'col-md-8'))
		->add('name')
		->add('code')
		->add('description')
		->end()
		->with('Details', array('class' => 'col-md-4'))
		->add('enabled', 'checkbox', array('required'  => false))
		->end()
		/*
		->with('Options', array('class' => 'col-md-6'))
		->add('engine', 'sonata_type_model_list')
		->add('color', 'sonata_type_model_list')
		->end()
		->with('inspections', array('class' => 'col-md-12'))
		->add('inspections', 'sonata_type_collection', array(
				'by_reference'       => false,
				'cascade_validation' => true,	
		) , array(
				'edit' => 'inline',
				'inline' => 'table'
		))
		->end()
		*/
		;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getNewInstance()
	{
		$object = parent::getNewInstance();
		
		$object->setEnabled(true);

		/*
		$inspection = new Inspection();
		$inspection->setDate(new \DateTime());
		$inspection->setComment("Initial inpection");

		$object->addInspection($inspection);
		*/
		return $object;
	}
}
