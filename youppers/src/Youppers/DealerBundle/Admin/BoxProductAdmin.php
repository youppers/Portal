<?php

namespace Youppers\DealerBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Symfony\Component\Validator\Constraints as Assert;
use Sonata\AdminBundle\Route\RouteCollection;

class BoxProductAdmin extends Admin
{
	protected function configureRoutes(RouteCollection $collection)
	{
		$collection->clearExcept(array('create','delete'));
	}
	
	/**
	 * {@inheritdoc}
	 */
	/*
	protected function configureShowFields(ShowMapper $showMapper)
	{
		$showMapper
		->add('store')
		->add('name')
		->add('code')
		->add('isActive')
		->add('description')
		->add('id', null, array('label' => 'QR code', 'template' => 'YouppersCustomerBundle:Qr:show_field.html.twig'))		
		//->add('products')
		;
	}
	*/

	/**
	 * {@inheritdoc}
	 */
	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
		//->add('isActive')
		//->add('id')
		->add('position')		
		->addIdentifier('name')		
		->add('box.code')		
		->add('box', null, array(
                 'route' => array(
                     'name' => 'show'
                 )
             ))
		->add('product.code')		
		->add('product', null, array(
                 'route' => array(
                     'name' => 'show'
                 )
             ))
             //->addIdentifier('code')
		//->addIdentifier('name')
		//->add('BoxProducts')
		//->add('id', null, array('label' => 'QR code', 'template' => 'YouppersCustomerBundle:Qr:list_field.html.twig'))		
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
		->add('product.code')
		->add('name')
		->add('box.name')
		->add('box.store')
		//->add('isActive')
		//->add('store')
		;
	}
	
	/**
	 * Default Datagrid values
	 *
	 * @var array
	 */
	/*
	protected $datagridValues = array(
			//'isActive' => array('value' => 1)
	);
	*/
	
	/**
	 * {@inheritdoc}
	 */
	protected function configureFormFields(FormMapper $formMapper)
	{
		if (!$this->hasParentFieldDescription()) {
			$formMapper
			->add('position')				
			->add('box', 'sonata_type_model_list');
		} else {
			$formMapper
			->add('position','hidden',array('attr'=>array("hidden" => true)));				
		}
		$formMapper
		->add('enabled')
		->add('name')
		->add('product', 'sonata_type_model_list')
		;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getNewInstance()
	{
		$object = parent::getNewInstance();
		
		//$object->setCreatedAt(new \DateTime());
		$object->setPosition(1);

		/*
		$inspection = new Inspection();
		$inspection->setDate(new \DateTime());
		$inspection->setComment("Initial inpection");

		$object->addInspection($inspection);
		*/
		return $object;
	}
}
