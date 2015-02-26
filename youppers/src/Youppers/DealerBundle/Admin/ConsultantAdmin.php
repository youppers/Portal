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

class ConsultantAdmin extends YouppersAdmin
{		
	/**
	 * {@inheritdoc}
	 */
	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper		
		->add('enabled', null, array('editable' => true))
		->addIdentifier('code', null, array('route' => array('name' => 'show')))
		->addIdentifier('fullname', null, array('route' => array('name' => 'show')))
		->add('photo', null, array('template' => 'YouppersCommonBundle:CRUD:list_image.html.twig'))		
		->add('dealer', null, array('route' => array('name' => 'show')))		
		->add('stores', null, array('associated_property' => 'name'))
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
		->add('dealer')
		->add('code')
		->add('fullname')
		->add('enabled')
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureShowFields(ShowMapper $showMapper)
	{
		$showMapper
		->add('enabled')
		->add('dealer', null, array('route' => array('name' => 'show')))
		->add('stores', null, array('associated_property' => 'name', 'route' => array('name' => 'show')))
		->add('code')
		->add('fullname')
		->add('description')
		->add('photo', null, array('template' => 'YouppersCommonBundle:CRUD:show_image.html.twig'))
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
		->with('Consultant', array('class' => 'col-md-6'));
		
		if (!$this->hasParentFieldDescription()) {
			$formMapper->add('dealer', 'sonata_type_model_list', array('required' => false, 'constraints' => new Assert\NotNull()));
		}
		
		$formMapper
		->add('stores')
		->add('code')
		->add('fullname')
		->add('description')
		->end()
		->with('Details', array('class' => 'col-md-4'))
		->add('enabled', 'checkbox', array('required'  => false))
		->end()
		->with('Photo', array('class' => 'col-md-6'))
		->add('photo', 'sonata_type_model_list', 
				array('required' => false), 
				array('link_parameters' => array(
						'context'  => 'youppers_consultant_photo'
				))
			)
		->end();
		
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
