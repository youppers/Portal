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
use Youppers\CommonBundle\Admin\YouppersAdmin;


#use Sonata\Bundle\DemoBundle\Entity\Inspection;

class BrandAdmin extends YouppersAdmin
{
	
	protected function configureRoutes(RouteCollection $collection)
	{
		$collection->add('products', $this->getRouterIdParameter().'/products');
		//$collection->add('clone', $this->getRouterIdParameter().'/clone');
	}
		
	/**
	 * {@inheritdoc}
	 */
	protected function configureShowFields(ShowMapper $showMapper)
	{		
		$showMapper
		->add('company', null, array('route' => array('name' => 'show')))
		->add('name')
		->add('code')
		->add('enabled')
		->add('description')
		->add('url')
            ->add('dealers', null, array('route' => array('name' => 'show')))
            ->add('logo', null, array('label' => 'Brand Logo', 'template' => 'YouppersCommonBundle:CRUD:show_image.html.twig'))
		->add('createdAt')
		->add('updatedAt')
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
		->add('enabled', null, array('editable' => true))
		->add('logo', null, array('label' => 'Brand Logo', 'template' => 'YouppersCommonBundle:CRUD:list_image.html.twig'))	
		->add('company', null, array('route' => array('name' => 'show')))
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
		->add('enabled')
		->add('company')
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureFormFields(FormMapper $formMapper)
	{
		$formMapper
		->with('Brand', array('class' => 'col-md-6'));

		$formMapper
		->add('name')
		->add('code')
		->add('description')
		->add('url', null, array('required' => false))
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
			->with('Details', array('class' => 'col-md-6'))
			->add('company', 'sonata_type_model_list', array('required' => false, 'constraints' => new Assert\NotNull()))
			->add('enabled', 'checkbox', array('required'  => false))
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
		
		if (isset($filterParameters['company'])) {
			$brand = $this->getModelManager()->find('Youppers\CompanyBundle\Entity\Company',$filterParameters['company']['value']);
			$object->setCompany($brand);
		}
		
		$object->setEnabled(true);

		return $object;
	}
}
