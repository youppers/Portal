<?php

namespace Youppers\DealerBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Symfony\Component\Validator\Constraints as Assert;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Route\RouteCollection;

class BoxAdmin extends Admin
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
		->add('store', null, array('route' => array('name' => 'show')))
		->add('name')
		->add('code')
		->add('enabled')
		->add('description')
		->add('createdAt')
		->add('updatedAt')		
		->add('boxProducts', null, array('route' => array('name' => 'edit'), 'associated_property' => 'nameProduct'))
		->add('qr', null, array('label' => 'QRCode', 'route' => array('name' => 'youppers_common_qr_box'), 'template' => 'YouppersCommonBundle:CRUD:show_qr.html.twig'))		
		->add('qr.boxes', null, array('route' => array('name' => 'show'), 'associated_property' => 'name'))
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
		->add('enabled')
		->add('store', null, array(
                 'route' => array(
                     'name' => 'show'
                 )
             ))
        ->add('boxProducts', null, array('associated_property' => 'nameProduct'), array('width' => '100px'))
		->add('qr', null, array('label' => 'QR code', 'route' => array('name' => 'youppers_common_qr_box'), 'template' => 'YouppersCommonBundle:CRUD:list_qr.html.twig'))		
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
		->add('store')
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
		->with('Box', array('class' => 'col-md-8'));
		if (!$this->hasParentFieldDescription()) {
			$formMapper
			->add('store', 'sonata_type_model_list', array('required' => false, 'constraints' => new Assert\NotNull()));
		}		
		$formMapper
		->add('code')
		->add('name')
		->add('description')
		->end()
		->with('Details', array('class' => 'col-md-4'))
		->add('enabled', 'checkbox', array('required'  => false))
		->end();
		if (!$this->hasParentFieldDescription()) {
			$formMapper
				->with('Products', array('class' => 'col-md-12'))
				->add('boxProducts', 'sonata_type_collection', 
					array(
						//'type_options' => array('delete' => false),
	            		'by_reference'       => false,
	            		'cascade_validation' => true,
						//'required' => false
				), array(
	                'edit' => 'inline',
	                'inline' => 'table',
	                'sortable' => 'position',
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
		
		if (isset($filterParameters['store'])) {
			$store = $this->getModelManager()->find('Youppers\DealerBundle\Entity\Store',$filterParameters['store']['value']);
			$object->setStore($store);
		}
		
		$object->setEnabled(true);

		return $object;
	}
}
