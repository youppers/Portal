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
		->addIdentifier('fullname', null, array('route' => array('name' => 'show')))
		->add('code')
		->add('enabled', null, array('editable' => true))
		->add('user', null, array('route' => array('name' => 'show')))		
		->add('photo', null, array('template' => 'YouppersCommonBundle:CRUD:list_image.html.twig'))		
		->add('dealer', null, array('route' => array('name' => 'show')))		
		//->add('stores', null, array('associated_property' => 'name'))
		->add('stores')
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
		->add('fullname')
		->add('code')
		->add('enabled')
		->add('dealer')
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureShowFields(ShowMapper $showMapper)
	{
		$showMapper
		->add('fullname')
		->add('code')
		->add('enabled')
		->add('description')
		->add('photo', null, array('template' => 'YouppersCommonBundle:CRUD:show_image.html.twig'))
		->add('user', null, array('route' => array('name' => 'show')))
		->add('dealer', null, array('route' => array('name' => 'show')))
		->add('stores')
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
				
		$formMapper
		->add('fullname', null, array('help' => 'Specify Given and Family name'))
		->add('code')
		->add('description')
		->add('photo', 'sonata_type_model_list', 
				array('required' => false), 
				array('link_parameters' => array(
						'context'  => 'youppers_consultant_photo'
				))
		)
		->end()
		->with('Details', array('class' => 'col-md-6'))
		->add('user', 'sonata_type_model_list')
		;
		if (!$this->hasParentFieldDescription()) {
			$formMapper->add('dealer', 'sonata_type_model_list', array('required' => false));
		}
		$formMapper
		->add('stores')
		->add('enabled', 'checkbox', array('required'  => false))
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
