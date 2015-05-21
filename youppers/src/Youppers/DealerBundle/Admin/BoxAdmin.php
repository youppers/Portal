<?php

namespace Youppers\DealerBundle\Admin;

use Youppers\CommonBundle\Admin\YouppersAdmin;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Symfony\Component\Validator\Constraints as Assert;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Route\RouteCollection;

class BoxAdmin extends YouppersAdmin
{
	public function getBatchActions()
	{
		$actions = parent::getBatchActions();
		
		if ($this->hasRoute('list') && $this->isGranted('SHOW')) {			
			$actions['print'] = array(
					'label'            => $this->trans('action_print', array(), 'messages'),
					'ask_confirmation' => false, // by default always true
			);
		}
		
		return $actions;
	    
	}
	
	protected function configureRoutes(RouteCollection $collection)
	{
		$collection->add('qr', $this->getRouterIdParameter().'/qr');
		$collection->add('clone', $this->getRouterIdParameter().'/clone');
		$collection->add('enable', $this->getRouterIdParameter().'/enable');
	}
	
	protected function configureTabMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
	{
		parent::configureTabMenu($menu, $action,$childAdmin);
		
		if (empty($childAdmin) && in_array($action, array('edit', 'show'))) {	
			$id = $this->getRequest()->get($this->getIdParameter());	
			if ($action == 'show') $menu->addChild('box_qr_action', array('uri' => $this->generateUrl('qr', array('id' => $id))));
			if ($action == 'show') $menu->addChild('box_clone_action', array('uri' => $this->generateUrl('clone', array('id' => $id))));
			if ($action == 'show') $menu->addChild('box_enable_action', array('uri' => $this->generateUrl('enable', array('id' => $id))));
		}		
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
		->add('image', null,array('template' => 'YouppersCommonBundle:CRUD:show_image.html.twig'))
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
		->add('image', 'sonata_type_model_list',
				array(
						'required' => false
				), array(
						'link_parameters' => array(
								'context'  => 'youppers_box',
								'filter'   => array('context' => array('value' => 'youppers_box'))
						)
				)
		)
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
