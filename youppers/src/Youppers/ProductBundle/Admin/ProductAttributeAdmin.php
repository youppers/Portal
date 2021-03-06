<?php

namespace Youppers\ProductBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Symfony\Component\Validator\Constraints as Assert;
use Sonata\AdminBundle\Route\RouteCollection;

class ProductAttributeAdmin extends Admin
{
	protected function configureRoutes(RouteCollection $collection)
	{
		$collection->clearExcept(array('create','delete'));
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function configureFormFields(FormMapper $formMapper)
	{
		$formMapper
		->add('enabled', null, array('required'  => false))
		->add('variant', null, array('required'  => false))
		->add('position','hidden',array('attr'=>array("hidden" => true)))			
		//->add('attributeType', 'sonata_type_model_list')
		->add('attributeType', 'sonata_type_model_list')
		;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getNewInstance()
	{
		$object = parent::getNewInstance();
		
		$object->setPosition(1);

		return $object;
	}
}
