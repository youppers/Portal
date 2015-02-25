<?php

namespace Youppers\CompanyBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Route\RouteCollection;

class ProductAdmin extends Admin
{
	protected function configureRoutes(RouteCollection $collection)
	{
		$collection->add('qr', $this->getRouterIdParameter().'/qr');
		$collection->add('clone', $this->getRouterIdParameter().'/clone');
		$collection->add('enable', $this->getRouterIdParameter().'/enable');
	}	
		
	protected function configureTabMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
	{
		if (!$childAdmin && !in_array($action, array('edit', 'show'))) { return; }
	
		$admin = $this->isChild() ? $this->getParent() : $this;
		$id = $admin->getRequest()->get('id');
	
		if ($action != 'show') $menu->addChild('Show', array('uri' => $admin->generateUrl('show', array('id' => $id))));
		if ($action != 'edit') $menu->addChild('Edit', array('uri' => $admin->generateUrl('edit', array('id' => $id))));
		if ($action == 'show') $menu->addChild('Assign Qr', array('uri' => $admin->generateUrl('qr', array('id' => $id))));		
		if ($action == 'show') $menu->addChild('Clone', array('uri' => $admin->generateUrl('clone', array('id' => $id))));
		if ($action == 'show') $menu->addChild('Enable', array('uri' => $admin->generateUrl('enable', array('id' => $id))));		
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function configureShowFields(ShowMapper $showMapper)
	{
		$showMapper
		->add('enabled')	
		->add('brand.company', null, array('route' => array('name' => 'show')))
		->add('brand', null, array('associated_property' => 'name', 'route' => array('name' => 'show')))
		->add('name')
		->add('code')
		->add('description')
		->add('url')
		->add('createdAt')
		->add('updatedAt')
		//->add('productModels', null, array('route' => array('name' => 'show')))
		/*
		->add('productModels', 'sonata_type_collection', array(
				'by_reference'       => false,
				'cascade_validation' => true,
		) , array(
				'edit' => 'inline',
				'inline' => 'table'
		))
		*/
		->add('qr', null, array('label' => 'QRCode', 'route' => array('name' => 'youppers_common_qr_prod'), 'template' => 'YouppersCommonBundle:CRUD:show_qr.html.twig'))		
		->add('qr.products', null, array('route' => array('name' => 'show'), 'associated_property' => 'name'))
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
		->add('enabled', null, array('editable' => true))
		//->add('id')
		//->add('brand.code')		
		->add('brand', null, array(
                 'route' => array(
                     'name' => 'show'
                 )
             ))
		->add('code')
		->addIdentifier('name', null, array('route' => array('name' => 'show')))
		//->add('productModels')
		->add('qr', null, array('label' => 'QR code', 'route' => array('name' => 'youppers_common_qr_prod'), 'template' => 'YouppersCommonBundle:CRUD:list_qr.html.twig'))		
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
		->add('brand.company')
		->add('brand')
		->add('enabled')
		;
	}
	
	/**
	 * Default Datagrid values
	 *
	 * @var array
	 */
	protected $datagridValues = array(
			//'enabled' => array('value' => 1)
	);

	/**
	 * {@inheritdoc}
	 */
	protected function configureFormFields(FormMapper $formMapper)
	{
		$formMapper
		->with('Product', array('class' => 'col-md-8'))
		->add('brand')
		->add('code')
		->add('name')
		->add('description')
		->add('url', null, array('required' => false))
		->end()
		->with('Details', array('class' => 'col-md-4'))
		->add('enabled', 'checkbox', array('required'  => false))
		->add('qr', null, array('property' => 'id'))
		//->add('qr.products')		
		->end()
		/*
		->with('Options', array('class' => 'col-md-6'))
		->add('engine', 'sonata_type_model_list')
		->add('color', 'sonata_type_model_list')
		->end()
		*/
		->with('Prices', array('class' => 'col-md-12'))
			->add('productPrices', 'sonata_type_collection', array(
				'by_reference'       => false,
				'cascade_validation' => true,	
			) , array(
				'edit' => 'inline',
				'inline' => 'table'
			))
		->end()
		;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getNewInstance()
	{
		$object = parent::getNewInstance();
		
		$filterParameters = $this->getFilterParameters();
		
		if (isset($filterParameters['brand'])) {
			$brand = $this->getModelManager()->find('Youppers\CompanyBundle\Entity\Brand',$filterParameters['brand']['value']);
			$object->setBrand($brand);		
		}
		
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
