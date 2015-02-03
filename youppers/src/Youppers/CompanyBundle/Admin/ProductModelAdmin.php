<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Youppers\CompanyBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Symfony\Component\Validator\Constraints as Assert;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AdminInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class ProductModelAdmin extends Admin
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
		->add('enabled')
		->add('product.brand.company', null, array('route' => array('name' => 'show')))
		->add('product.brand', null, array('route' => array('name' => 'show')))
		->add('product', null, array('route' => array('name' => 'show')))
		->add('name')
		->add('code')
		->add('description')
		->add('validFrom')
		->add('validTo')
		->add('price')
		->add('createdAt')
		->add('updatedAt')		
		//->add('id', null, array('label' => 'QR code', 'template' => 'YouppersCommonBundle:CRUD:show_qr.html.twig'))
		;
	}
	
	/**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
            ->add('code')
        	->add('name')
			->add('product.name')
            ->add('enabled')
           ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $list)
    {
        $list
            //->add('_action', 'actions', array('actions' => array('edit' => array())))
        	->add('enabled', null, array('editable' => true))
        	->add('product.name')
            ->add('code')
        	->addIdentifier('name', null, array('route' => array('name' => 'show')))
            ->add('price','currency',array('currency' => '€'))
			->add('id', null, array('template' => 'YouppersCustomerBundle:Qr:list_field.html.twig', 'size' => 100))
            ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
    	/*
        if (!$this->hasParentFieldDescription()) {
        	$formMapper->with('Product', array('class' => 'col-md-12'))
            //$formMapper->add('brand', 'sonata_type_model_list', array('constraints' => new Assert\NotNull()));
            //->add('product', 'sonata_type_model_list', array('constraints' => new Assert\NotNull()))
        	->add('product', 'sonata_type_model_list')
            ->end();        	
        }
        */

        $formMapper
        	->with('Model', array('class' => 'col-md-8'));
        
        if (!$this->hasParentFieldDescription()) {
        	$formMapper->add('product', 'sonata_type_model_list');
        }
        $formMapper
        	->add('name')
        	->add('code')
        	// TODO usare vendor/sonata-project/ecommerce/src/Component/Currency/CurrencyFormType.php
        	// TODO mettere simbolo valuta correttamente
            ->add('price','money')        
        	->add('description')
        	->end()
        	->with('Details', array('class' => 'col-md-4'))
        	->add('enabled', 'checkbox', array('required'  => false))        
        	->add('validFrom', null, array('widget' => 'single_text'))
        	//->add('validFrom', null, array('widget' => 'single_text'))
            ->add('validTo', null, array('widget' => 'single_text'))
            //->add('validTo', null, array('widget' => 'single_text'))
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
