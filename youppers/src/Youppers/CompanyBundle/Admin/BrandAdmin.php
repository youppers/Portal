<?php

namespace Youppers\CompanyBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Symfony\Component\Validator\Constraints as Assert;
use Sonata\AdminBundle\Route\RouteCollection;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AdminInterface;


#use Sonata\Bundle\DemoBundle\Entity\Inspection;

class BrandAdmin extends Admin
{
	
	protected function configureRoutes(RouteCollection $collection)
	{
		$collection->add('products', $this->getRouterIdParameter().'/products');
	}
	
	protected function configureTabMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
	{
		if (!$childAdmin && !in_array($action, array('edit', 'show'))) { return; }
	
		$admin = $this->isChild() ? $this->getParent() : $this;
		$id = $admin->getRequest()->get('id');
		
		if ($action != 'show') $menu->addChild('Show', array('uri' => $admin->generateUrl('show', array('id' => $id))));		
		if ($action != 'edit') $menu->addChild('Edit', array('uri' => $admin->generateUrl('edit', array('id' => $id))));

		$menu->addChild('Products', array('uri' => $admin->generateUrl('products', array('id' => $id))));		
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function configureShowFields(ShowMapper $showMapper)
	{
		//dump($this->getTemplate('show'));
		
		$showMapper
		->add('enabled')
		->add('company', null, array('route' => array('name' => 'show')))
		->add('name')
		->add('code')
		->add('description')
		->add('logo', null, array('label' => 'Brand Logo', 'template' => 'YouppersCommonBundle:CRUD:show_image.html.twig'))		
		->add('createdAt')
		->add('updatedAt')
		//->add('products')
		//->add('products', 'url', array('label' => 'Brand Logo', 'template' => 'YouppersTemplateBundle:Admin:field_dump.html.twig'))
		//->add('id', 'urla', array('label' => 'Brand Products', 'template' => 'YouppersTemplateBundle:Admin:field_dump.html.twig'))
		//->add('id', 'url');
		/*
		->add('products', 'url', array('route' => array(
						'name' => 'products',
						'absolute' => true,
						'parameters' => array('format' => 'xml'),
						'identifier_parameter_name' => 'id')				
						
			))
		*/
		/*
		->add('id','route',array(
				'label' => 'Products',
				'template' => 'YouppersCommonBundle:CRUD:route_show_field.html.twig',
				'route' => array('name' => 'products')
				)
			)
		*/
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
		->add('enabled', null, array('editable' => true))
		->add('company', null, array('route' => array('name' => 'show')))
		->addIdentifier('name', null, array('route' => array('name' => 'show')))
        ->addIdentifier('code', null, array('route' => array('name' => 'show')))
		->addIdentifier('logo', null, array('label' => 'Brand Logo', 'route' => array('name' => 'show'), 'template' => 'YouppersCommonBundle:CRUD:list_image.html.twig'))	
		->add('_action','actions',array(
				'label' => 'Products',
				'actions' => array(
					'child' => array(
						'route' => array('name' => 'products'),
						'template' => 'YouppersCommonBundle:CRUD:list__action_route.html.twig')
				)
			)
		)
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
		->add('company')
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
		->with('Brand', array('class' => 'col-md-8'));
		
		if (!$this->hasParentFieldDescription()) {
			$formMapper->add('company', 'sonata_type_model_list', array('constraints' => new Assert\NotNull()));
			//$formMapper->add('company', 'sonata_type_model_list');
		}
		
		$formMapper
		->add('name')
		->add('code')
		->add('description')
		->add('logo', 'sonata_type_model_list', array(
				'required' => false
		), array(
				'link_parameters' => array(
						'context'  => 'youppers_brand_logo',
						//'filter'   => array('context' => array('value' => 'youppers_brand_logo')),
						'provider' => ''
				)
		)
		)		
		->end();
		
		if (!$this->hasParentFieldDescription()) {
			
			$formMapper
			->with('Details', array('class' => 'col-md-4'))
			->add('enabled', 'checkbox', array('required'  => false))
			//->add('createdAt', 'sonata_type_datetime_picker', array('dp_side_by_side' => true))
			->end();
		}
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
