<?php

namespace Youppers\ProductBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Symfony\Component\Validator\Constraints as Assert;
use Sonata\AdminBundle\Route\RouteCollection;

class AttributeOptionAdmin extends Admin
{
	protected function configureRoutes(RouteCollection $collection)
	{
		//$collection->clearExcept(array('create','delete'));
	}

	/**
	 * @param DatagridMapper $datagridMapper
	 */
	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
		->add('attributeStandard.attributeType')
		->add('attributeStandard')
		;
	}
	
	/**
	 * @param ListMapper $listMapper
	 */
	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
		->add('enabled', null, array('editable' => true))

		->add('attributeStandard.attributeType', null, array('route' => array('name' => 'show')))
		->add('attributeStandard', null, array('route' => array('name' => 'show')))
		->add('valueWithEquivalences')
		;
	}
		
	/**
	 * {@inheritdoc}
	 */
	protected function configureFormFields(FormMapper $formMapper)
	{
		$formMapper
		->add('enabled', null, array('required'  => false))
		->add('position','hidden',array('attr'=>array("hidden" => true)))			
		->add('value')
		->add('equivalentOption')
		;

	}	

	/**
	 * {@inheritdoc}
	 */
	public function getNewInstance()
	{
		$object = parent::getNewInstance();
		
		$object->setPosition(1);
		$object->setEnabled(true);

		return $object;
	}
}
