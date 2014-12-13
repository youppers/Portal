<?php

namespace Youppers\DealerBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;

#use Sonata\Bundle\DemoBundle\Entity\Inspection;

class StoreAdmin extends Admin
{
	/**
	 * {@inheritdoc}
	 */
	protected function configureShowFields(ShowMapper $showMapper)
	{
		$showMapper
		->add('name')
		->add('Dealer')
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
		->addIdentifier('name')
		->add('code')
		->add('dealer', null, array(
                 'route' => array(
                     'name' => 'show'
                 )
             ))
				#->add('products')  // TODO link per elencare prodotti con filtro di Store
		// SEE https://groups.google.com/forum/#!topic/sonata-users/-nVqpVBINHc
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
		->add('dealer')
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
		->with('Store', array('class' => 'col-md-8'))
		->add('dealer')
		->add('name')
		->add('code')
		->add('description')
		->end()
		->with('Details', array('class' => 'col-md-4'))
		->add('isActive', 'checkbox', array('required'  => false))
		->add('createdAt')
		->end()
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
		
		//$object->setCreatedAt(new \DateTime());
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
