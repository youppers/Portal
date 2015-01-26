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

#use Sonata\Bundle\DemoBundle\Entity\Inspection;

class BoxAdmin extends Admin
{
	protected function configureTabMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
	{
		if (!$childAdmin && !in_array($action, array('edit', 'show'))) { return; }
	
		$admin = $this->isChild() ? $this->getParent() : $this;
		$id = $admin->getRequest()->get('id');
	
		if ($action != 'show') $menu->addChild('Show', array('uri' => $admin->generateUrl('show', array('id' => $id))));
		if ($action != 'edit') $menu->addChild('Edit', array('uri' => $admin->generateUrl('edit', array('id' => $id))));
			
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
		->add('boxProducts', null, array('route' => array('name' => 'edit'), 'associated_property' => 'nameProduct'))
		->add('id', null, array('label' => 'QR code', 'template' => 'YouppersCommonBundle:CRUD:show_qr.html.twig'))		
		//->add('products')
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
		->add('enabled')
		//->add('id')
		->add('store.code')		
		->add('store', null, array(
                 'route' => array(
                     'name' => 'show'
                 )
             ))
		->addIdentifier('code', null, array('route' => array('name' => 'show')))
		->addIdentifier('name', null, array('route' => array('name' => 'show')))
		->add('boxProducts', null, array('associated_property' => 'nameProduct'))
		->add('id', null, array('label' => 'QR code', 'template' => 'YouppersCommonBundle:CRUD:list_qr.html.twig'))		
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
		//->add('store')
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
			//$formMapper->add('brand', 'sonata_type_model_list', array('constraints' => new Assert\NotNull()));
			$formMapper
			->add('store', 'sonata_type_model_list', array('constraints' => new Assert\NotNull()));
		}		
		$formMapper
		->add('code')
		->add('name')
		->add('description')
		->end()
		->with('Details', array('class' => 'col-md-4'))
		->add('enabled', 'checkbox', array('required'  => false))
		->end();
		/*
		->with('Options', array('class' => 'col-md-6'))
		->add('engine', 'sonata_type_model_list')
		->add('color', 'sonata_type_model_list')
		->end()
		*/
		if (!$this->hasParentFieldDescription()) {
			$formMapper
				->with('Products', array('class' => 'col-md-12'))
				->add('boxProducts', 'sonata_type_collection', array(
	            		'by_reference'       => false,
	            		'cascade_validation' => true,
						'required' => false
				), array(
	                'edit' => 'inline',
	                'inline' => 'table',
	                'sortable' => 'position',  // FIXME non mostra la posizione aggiornata secondo posizione
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
