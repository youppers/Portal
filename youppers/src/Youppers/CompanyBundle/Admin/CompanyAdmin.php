<?php

namespace Youppers\CompanyBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;

#use Sonata\Bundle\DemoBundle\Entity\Inspection;

class CompanyAdmin extends Admin
{
	/**
	 * {@inheritdoc}
	 */
	protected function configureShowFields(ShowMapper $showMapper)
	{
		$showMapper
		->add('name')
		->add('createdAt')
		->add('description')
		->add('brands')
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
		->add('isActive')
		->addIdentifier('name')
		->add('brands')
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
		->add('name')
		->add('isActive')
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
		->with('Company', array('class' => 'col-md-8'))
		->add('name')
		->add('description')
		->end()
		->with('Details', array('class' => 'col-md-4'))
		->add('isActive', 'checkbox', array('required'  => false))
		->add('createdAt')
		->end()
		->with('Brands', array('class' => 'col-md-12'))
		->add('brands', 'sonata_type_collection', array(
				'by_reference'       => false,
				'cascade_validation' => true,
		) , array(
				'edit' => 'inline',
				'inline' => 'table'
		))
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
