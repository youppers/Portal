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

class QrAdmin extends YouppersAdmin
{

	/**
	 * {@inheritdoc}
	 */
	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
		->addIdentifier('text', 'text', array('route' => array('name' => 'show')))
		->add('targetType')
		->add('products')
		->add('boxes')
		->add('enabled')
		;				
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
		->add('url')
		->add('targetType')
		->add('enabled')
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureShowFields(ShowMapper $showMapper)
	{
		$showMapper
		->add('text')
		->add('targetType', 'text')
		->add('enabled')
		;
		if ($this->subject->getTargetType() == 'youppers_company_product') {
			$showMapper
			->add('products', null, array('route' => array('name' => 'show')))
			;
		}
		if ($this->subject->getTargetType() == 'youppers_dealer_box') {
			$showMapper
			->add('boxes', null, array('route' => array('name' => 'show')))
			;
		}
	
		$showMapper
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
			->add('url')
			->add('targetType')
			->add('enabled', null, array('required' => false))
		;
		
		if ($this->subject->getTargetType() == 'youppers_company_product') {
// 			$formMapper
// 				->add('products', 'sonata_type_model', array('multiple' => true, 'required' => false, 'by_reference' => true))
// 			;
		}
		if ($this->subject->getTargetType() == 'youppers_dealer_box') {
// 			$formMapper
// 				->add('boxes', 'sonata_type_model', array('multiple' => true, 'required' => false, 'by_reference' => false))
// 			;
// 			$formMapper
// 				->add('boxes', 'sonata_type_collection', 
// 					array(
// 						//'type_options' => array('delete' => false),
// 	            		'by_reference'       => true,
// 	            		'cascade_validation' => true,
// 						//'required' => false
// 				), array(
// 	                'edit' => 'inline',
// 	                'inline' => 'table'
// 	            ))
// 			;
		}
				
	}

}
