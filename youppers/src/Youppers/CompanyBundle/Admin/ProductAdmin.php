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
use Youppers\CommonBundle\Admin\YouppersAdmin;
use Symfony\Component\Validator\Constraints as Assert;

class ProductAdmin extends YouppersAdmin
{
	protected function configureRoutes(RouteCollection $collection)
	{
		$collection->add('qr', $this->getRouterIdParameter().'/qr');
		$collection->add('clone', $this->getRouterIdParameter().'/clone');
		$collection->add('enable', $this->getRouterIdParameter().'/enable');
	}	
		
	protected function configureTabMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
	{
		parent::configureTabMenu($menu, $action,$childAdmin);
		
		if ($action == 'show') {	
			$admin = $this->isChild() ? $this->getParent() : $this;
			$id = $admin->getRequest()->get('id');
	
			$menu->addChild('Assign Qr', array('uri' => $admin->generateUrl('qr', array('id' => $id))));		
			$menu->addChild('Clone', array('uri' => $admin->generateUrl('clone', array('id' => $id))));
			$menu->addChild('Enable', array('uri' => $admin->generateUrl('enable', array('id' => $id))));
		}		
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function configureShowFields(ShowMapper $showMapper)
	{
		$showMapper
		->add('enabled')	
		->add('brand.company', null, array('route' => array('name' => 'show')))
		->add('brand', null, array('label' => 'Brand Name', 'associated_property' => 'name', 'route' => array('name' => 'show')))
		->add('brand.code', null, array('label' => 'Brand Code'))
		->add('variant', null, array('route' => array('name' => 'show')))
		->add('name')
		->add('gtin')
		->add('code')
		->add('description')
		->add('url')
		->add('productPrices')
		->add('createdAt')
		->add('updatedAt')
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
		->addIdentifier('name', null, array('route' => array('name' => 'show')))
		->add('code')
		->add('gtin')
		->add('enabled', null, array('editable' => true))
		->add('brand', null, array(
                 'route' => array(
                     'name' => 'show'
                 )
             ))
		->add('qr', null, array('label' => 'QR code', 'route' => array('name' => 'youppers_common_qr_prod'), 'template' => 'YouppersCommonBundle:CRUD:list_qr.html.twig'))		
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
		->add('name')
		->add('code')
		->add('gtin')
		->add('enabled')
		->add('brand.company')
		->add('brand')
		;
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function configureFormFields(FormMapper $formMapper)
	{
		$formMapper
		->with('Product', array('class' => 'col-md-6'))
		->add('code')
		->add('gtin')
		->add('name')
		->add('description')
		->add('url', null, array('required' => false))
		->end()
		->with('Details', array('class' => 'col-md-6'))
		->add('brand', 'sonata_type_model_list', array('required' => false, 'constraints' => new Assert\NotNull()))
		->add('enabled', 'checkbox', array('required'  => false))
		->end()
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
