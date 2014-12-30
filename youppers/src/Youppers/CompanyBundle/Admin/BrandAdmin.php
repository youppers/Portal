<?php

namespace Youppers\CompanyBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Symfony\Component\Validator\Constraints as Assert;

#use Sonata\Bundle\DemoBundle\Entity\Inspection;

class BrandAdmin extends Admin
{
	/**
	 * {@inheritdoc}
	 */
	protected function configureShowFields(ShowMapper $showMapper)
	{
		$showMapper
		->add('name')
		->add('logo')
		->add('company')
		->add('createdAt')
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
		->add('isActive')
		->add('company')
		->addIdentifier('name')
		->add('logo', null, array('label' => 'Brand Logo', 'template' => 'SonataMediaBundle:MediaAdmin:list_image.html.twig'))		
		->add('code')
		#->add('products')  // TODO link per elencare prodotti con filtro di Brand
		// SEE https://groups.google.com/forum/#!topic/sonata-users/-nVqpVBINHc
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
		->add('company')
		->add('name')
		->add('code')
		->add('isActive')
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureFormFields(FormMapper $formMapper)
	{
		$formMapper
		->with('Brand', array('class' => 'col-md-8'));
		
		if (!$this->hasParentFieldDescription()) {
			$formMapper->add('company', 'sonata_type_model_list', array('constraints' => new Assert\NotNull()));
			//$formMapper->add('company', 'sonata_type_model_list');
		}
		
		$formMapper
		->add('name')
		->add('code')
		->add('description')
		->add('logo', 'sonata_type_model_list', array(
				'required' => false
		), array(
				'link_parameters' => array(
						'context'  => 'youppers_brand_logo',
						//'filter'   => array('context' => array('value' => 'youppers_brand_logo')),
						'provider' => ''
				)
		)
		)		
		->end();
		
		if (!$this->hasParentFieldDescription()) {
			
		// TODO use sonata_type_datetime_picker
			$formMapper
			->with('Options', array('class' => 'col-md-4'))
			->add('isActive', 'checkbox', array('required'  => false))
			->add('createdAt', 'sonata_type_datetime_picker', array('dp_side_by_side' => true))
			->end();
		}
		#->end()
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
		
		$object->setCreatedAt(new \DateTime());
		$object->setIsActive(true);

		/*
		$inspection = new Inspection();
		$inspection->setDate(new \DateTime());
		$inspection->setComment("Initial inpection");

		$object->addInspection($inspection);
		*/
		return $object;
	}
}
