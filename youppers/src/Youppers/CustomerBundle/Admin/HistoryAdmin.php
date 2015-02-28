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
		->add('session', null, array('route' => array('name' => 'show')))
		->addIdentifier('type', 'text', array('route' => array('name' => 'show')))
		->addIdentifier('createdAt', null, array('route' => array('name' => 'show')))
		//->addIdentifier('type', null, array('route' => array('name' => 'show')))
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
		
		if ($this->getClass() == "Youppers\CustomerBundle\Entity\HistoryQrBox") {
			$formMapper
				->add('box', 'sonata_type_model_list', array('required' => false, 'constraints' => new Assert\NotNull()))
			;				
		}
		
		if ($this->getClass() == "Youppers\CustomerBundle\Entity\HistoryQrVariant") {
			$formMapper
			->add('variant', 'sonata_type_model_list', array('required' => false, 'constraints' => new Assert\NotNull()))
			;
		}

		if ($this->getClass() == "Youppers\CustomerBundle\Entity\HistoryAdd" || $this->getClass() == "Youppers\CustomerBundle\Entity\HistoryRemove") {
			$formMapper
			->add('item', 'sonata_type_model_list', array('required' => false, 'constraints' => new Assert\NotNull()))
			;
		}
		
	}

}
