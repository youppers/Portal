<?php

namespace Youppers\CommonBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Youppers\CommonBundle\Admin\YouppersAdmin;
use Symfony\Component\Validator\Constraints as Assert;
use Sonata\AdminBundle\Route\RouteCollection;

/**
 * 
 * @author sergio
 *
 */
class GeoidAdmin extends YouppersAdmin
{
	
	protected function configureRoutes(RouteCollection $collection)
	{
		$collection->clearExcept(array('list','show'));
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
		->addIdentifier('criteriaId', null, array('route' => array('name' => 'show')))
		->add('name')
		->add('canonicalName')
		->add('countryCode')
		->add('parent', null, array('route' => array('name' => 'show')))
		->add('targetType')
		->add('status')
		;
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
		->add('criteriaId')
		->add('name')
		->add('canonicalName')
		->add('countryCode')
		->add('targetType')
		;
	}
	
	
	/**
	 * {@inheritdoc}
	 */
	protected function configureShowFields(ShowMapper $showMapper)
	{
		$showMapper
			->add('criteriaId')
			->add('name')
			->add('canonicalName')
			->add('countryCode')
			->add('parent', null, array('route' => array('name' => 'show')))
			->add('targetType')
			->add('status')
			->add('createdAt')
			->add('updatedAt')
			->add('enabled')
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureFormFields(FormMapper $formMapper)
	{
		$formMapper
			->with('Geographical Targeting', array('class' => 'col-md-8'))
			->add('criteriaId')				
			->add('name')
			->add('canonicalName')
			->add('countryCode')
			->add('parent', 'sonata_type_model_list', array('required' => false, 'constraints' => new Assert\NotNull()))
			->add('targetType')
			->add('status')
			->end()
			->with('Options', array('class' => 'col-md-4'))
			->add('enabled')
			->end()
		;		
	}

	/**
	 * {@inheritdoc}
	 */
	public function getNewInstance()
	{
		$object = parent::getNewInstance();
		
		$object->setEnabled(true);

		return $object;
	}
}
