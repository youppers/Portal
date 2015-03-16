<?php

namespace Youppers\DealerBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Youppers\CommonBundle\Admin\YouppersAdmin;

class StoreAdmin extends YouppersAdmin
{
		
	/**
	 * {@inheritdoc}
	 */
	protected function configureShowFields(ShowMapper $showMapper)
	{
		$showMapper
		->add('enabled')
		->add('dealer', null, array('route' => array('name' => 'show')))
		->add('code')
		->add('name')
		->add('geoid', null, array('route' => array('name' => 'show')))
		->add('description')
		->add('consultants', null, array('route' => array('name' => 'show')))
		->add('boxes', null, array('associated_property' => 'name', 'route' => array('name' => 'show')))
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
		->add('enabled', null, array('editable' => true))
		->add('dealer', null, array('route' => array('name' => 'show')))		
		->addIdentifier('code', null, array('route' => array('name' => 'show')))
		->addIdentifier('name', null, array('route' => array('name' => 'show')))
		->add('geoid')
		->add('boxes', null, array('associated_property' => 'name'))
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
		->add('enabled')
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureFormFields(FormMapper $formMapper)
	{
		$formMapper
		->with('Store', array('class' => 'col-md-8'));
		
		if (!$this->hasParentFieldDescription()) {
			$formMapper->add('dealer', 'sonata_type_model_list', array('required' => false, 'constraints' => new Assert\NotNull()));
		}
		
		$formMapper
		->add('name')
		->add('code')
		->add('description')
		->end()
		->with('Details', array('class' => 'col-md-4'))
		->add('geoid', 'sonata_type_model_list', array('required' => false, 'constraints' => new Assert\NotNull()))
		->add('enabled', 'checkbox', array('required'  => false))
		->end();
		
		if (!$this->hasParentFieldDescription()) {
			$formMapper
				->with('Boxes', array('class' => 'col-md-12'))
				->add('boxes', 'sonata_type_collection', array(
					'by_reference'       => false,
					'cascade_validation' => true,
					'required' => false
				), array(
					'edit' => 'inline',
					'inline' => 'table'
				))
				->end();
		}		
	}

	/**
	 * {@inheritdoc}
	 */
	public function getNewInstance()
	{
		$object = parent::getNewInstance();
		
		$filterParameters = $this->getFilterParameters();
		
		if (isset($filterParameters['dealer'])) {
			$dealer = $this->getModelManager()->find('Youppers\DealerBundle\Entity\Dealer',$filterParameters['dealer']['value']);
			$object->setDealer($dealer);
		}
		
		$object->setEnabled(true);

		return $object;
	}
}
