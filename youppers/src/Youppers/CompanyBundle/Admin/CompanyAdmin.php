<?php

namespace Youppers\CompanyBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AdminInterface;

#use Sonata\Bundle\DemoBundle\Entity\Inspection;

class CompanyAdmin extends Admin
{
	protected function configureTabMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
	{
		if (!$childAdmin && !in_array($action, array('edit', 'show'))) { return; }
	
		$admin = $this->isChild() ? $this->getParent() : $this;
		$id = $admin->getRequest()->get('id');
	
		if ($action != 'show') $menu->addChild('Show', array('uri' => $admin->generateUrl('show', array('id' => $id))));		
		if ($action != 'edit') $menu->addChild('Edit', array('uri' => $admin->generateUrl('edit', array('id' => $id))));
		 
		/*		
		$menu->addChild(
				'Brands', 
				array('uri' => $admin->generateUrl('youppers.company.admin.company.brand.list', array('id' => $id)))
			);
		*/ 
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function configureShowFields(ShowMapper $showMapper)
	{
		$showMapper
		->add('enabled')
		->add('name')
		->add('code')
		->add('description')
		->add('logo', null,array('label' => 'Company Logo', 'template' => 'YouppersCommonBundle:CRUD:show_image.html.twig'))
		->add('url')
		->add('createdAt')
		->add('updatedAt')
		->add('brands', null, array(
                 'route' => array(
                     'name' => 'show'
                 )
             ))
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
		->add('enabled', null, array('editable' => true))
		->addIdentifier('name', null, array('route' => array('name' => 'show')))
		->add('code')		
		//->add('logo', null, array('label' => 'Company Logo', 'template' => 'SonataMediaBundle:MediaAdmin:list_image.html.twig'))
		->addIdentifier('logo', null, array('label' => 'Company Logo', 'route' => array('name' => 'show'), 'template' => 'YouppersCommonBundle:CRUD:list_image.html.twig'))
		->add('brands')
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
		->add('enabled')
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
		->add('code')		
		->add('description')
		->add('logo', 'sonata_type_model_list', array(
					'required' => false
				), array(
					'link_parameters' => array(
						'context'  => 'youppers_company_logo',
						'filter'   => array('context' => array('value' => 'youppers_company_logo')),
						'provider' => ''
					)
				)
			)
		->add('url', null, array('required' => false))
		->end()
		->with('Details', array('class' => 'col-md-4'))
		->add('enabled', 'checkbox', array('required'  => false))
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
		
		//$object->setCreatedAt(new \DateTime());
		$object->setEnabled(true);

		/*
		$inspection = new Inspection();
		$inspection->setDate(new \DateTime());
		$inspection->setComment("Initial inpection");

		$object->addInspection($inspection);
		*/
		return $object;
	}
}
