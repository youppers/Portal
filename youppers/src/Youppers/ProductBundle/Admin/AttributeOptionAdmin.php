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
		->addIdentifier('image', null, array('route' => array('name' => 'show'), 'template' => 'YouppersCommonBundle:CRUD:list_image.html.twig'))		
		->addIdentifier('valueWithEquivalences')
		;
	}
		
	/**
	 * {@inheritdoc}
	 */
	protected function configureFormFields(FormMapper $formMapper)
	{
		if (!$this->hasParentFieldDescription()) {
			$formMapper
			->add('attributeStandard', null, array('required'  => false));				
		}
		$formMapper
		->add('enabled', null, array('required'  => false))
		->add('position','hidden',array('attr'=>array("hidden" => true)))			
		->add('value')
		->add('image', 'sonata_type_model_list',
				array(
						'required' => false
				), array(
						'link_parameters' => array(
								'context'  => 'youppers_attribute',
								'filter'   => array('context' => array('value' => 'youppers_attribute'))
						)
				)
		)		
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
