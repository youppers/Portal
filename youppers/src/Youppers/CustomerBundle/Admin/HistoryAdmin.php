<?php

namespace Youppers\CustomerBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Youppers\CommonBundle\Admin\YouppersAdmin;
use Symfony\Component\Validator\Constraints as Assert;

class HistoryAdmin extends Admin
{

	/**
	 * {@inheritdoc}
	 */
	protected function configureShowFields(ShowMapper $showMapper)
	{
		$showMapper
			->add('session', null, array('route' => array('name' => 'show')))
			->add('type', 'text')
			;
		if ($this->subject->getHistoryType() == 'qr_box') {
			$showMapper
			->add('box', null, array('route' => array('name' => 'show')))
			;
		}
		
		if ($this->subject->getHistoryType() == 'qr_variant' || $this->subject->getHistoryType() == 'variant_show') {
			$showMapper
			->add('variant', null, array('route' => array('name' => 'show')))
			;
		}

		if ($this->subject->getHistoryType() == 'item_add' || $this->subject->getHistoryType() == 'item_remove') {
			$showMapper
			->add('item', null, array('route' => array('name' => 'show')))
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
	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
		->addIdentifier('type', 'text', array('route' => array('name' => 'show')))
		->addIdentifier('createdAt', null, array('route' => array('name' => 'show')))
		->add('session', null, array('route' => array('name' => 'show')))
		->add('box', null, array('route' => array('name' => 'show')))
		->add('variant', null, array('route' => array('name' => 'show')))
		->add('item', null, array('route' => array('name' => 'show')))
		;				
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
		//->add('type', 'text')
		->add('session')
		;
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function configureFormFields(FormMapper $formMapper)
	{
		
		$formMapper
			->add('session', 'sonata_type_model_list', array('required' => false, 'constraints' => new Assert\NotNull()))
		;
		
		if ($this->subject->getHistoryType() == 'qr_box') {		
			$formMapper
				->add('box', 'sonata_type_model_list', array('required' => false, 'constraints' => new Assert\NotNull()))
			;				
		}
		
		if ($this->subject->getHistoryType() == 'qr_variant' || $this->subject->getHistoryType() == 'variant_show') {
			$formMapper
			->add('variant', 'sonata_type_model_list', array('required' => false, 'constraints' => new Assert\NotNull()))
			;
		}

		if ($this->subject->getHistoryType() == 'item_add' || $this->subject->getHistoryType() == 'item_remove') {		
			$formMapper
			->add('item', 'sonata_type_model_list', array('required' => false, 'constraints' => new Assert\NotNull()))
			;
		}
		
	}

}
