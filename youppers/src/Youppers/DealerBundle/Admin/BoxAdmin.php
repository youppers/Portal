<?php

namespace Youppers\DealerBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Symfony\Component\Validator\Constraints as Assert;

#use Sonata\Bundle\DemoBundle\Entity\Inspection;

class BoxAdmin extends Admin
{
	/**
	 * {@inheritdoc}
	 */
	protected function configureShowFields(ShowMapper $showMapper)
	{
		$showMapper
		->add('store')
		->add('name')
		->add('code')
		->add('isActive')
		->add('description')
		//->add('products')
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
		->add('isActive')
		//->add('id')
		->add('store.code')		
		->add('store', null, array(
                 'route' => array(
                     'name' => 'show'
                 )
             ))
		->addIdentifier('code')
		->addIdentifier('name')
		//->add('BoxProducts')
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
		->add('code')
		->add('name')
		->add('store.name')
		->add('isActive')
		//->add('store')
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
		if (!$this->hasParentFieldDescription()) {
			$formMapper->with('Store', array('class' => 'col-md-12'))
			//$formMapper->add('brand', 'sonata_type_model_list', array('constraints' => new Assert\NotNull()));
			->add('store', 'sonata_type_model_list', array('constraints' => new Assert\NotNull()))
			->end();
		}
		
		$formMapper
		->with('Box', array('class' => 'col-md-8'))
		//->add('store')
		->add('code')
		->add('name')
		->add('description')
		->end()
		->with('Details', array('class' => 'col-md-4'))
		->add('isActive', 'checkbox', array('required'  => false))
		->end()
		/*
		->with('Options', array('class' => 'col-md-6'))
		->add('engine', 'sonata_type_model_list')
		->add('color', 'sonata_type_model_list')
		->end()
		*/
		/*
		->with('Products', array('class' => 'col-md-12'))
			->add('BoxProducts', 'sonata_type_collection', array(
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
