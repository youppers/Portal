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
use Sonata\AdminBundle\Route\RouteCollection;
use Youppers\CompanyBundle\Component\UomChoiceList;

class ProductPriceAdmin extends Admin
{
	public function getParentAssociationMapping()
	{
		return 'pricelist';
	}
	
	/**
	 * @param DatagridMapper $datagridMapper
	 */
	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
		->add('product.name')
		;
	}
	
	/**
	 * @param ListMapper $listMapper
	 */
	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
		->add('product', null, array('route' => array('name' => 'show')))
		->add('price')
		->add('uom')
		;
	}
	
    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
    	if (!$this->hasParentFieldDescription() && !$this->isChild()) {
    		$formMapper
        		->with('Product and Pricelist', array('class' => 'col-md-8'))        
    			->add('product', 'sonata_type_model_list', array('required' => false, 'constraints' => new Assert\NotNull()))
    		    ->add('pricelist', 'sonata_type_model_list', array('required' => false, 'constraints' => new Assert\NotNull()))
    		    ->end()
    		    ;    		
    	} else if (($this->isChild() && $this->getParent() instanceof PricelistAdmin) || $this->getParentFieldDescription()->getAdmin() instanceof PricelistAdmin) {
    		$formMapper
        		->with('Product', array('class' => 'col-md-8'))        
    			->add('product', 'sonata_type_model_list', array('required' => false, 'constraints' => new Assert\NotNull()))
    			->end()
        		;    		    	
    	} else if ($this->isChild() || $this->getParentFieldDescription()->getAdmin() instanceof ProductAdmin) {
    		$formMapper
    			->with('Pricelist', array('class' => 'col-md-8'))    		
    		    ->add('pricelist', 'sonata_type_model_list', array('required' => false, 'constraints' => new Assert\NotNull()))
    		    ->end()
    		    ;
    	}
    	 
        $formMapper
        	->with('Price', array('class' => 'col-md-8'))        
        	->add('price')
        	->add('uom', 'choice', array('choice_list' => UomChoiceList::create()))        
        	// TODO usare vendor/sonata-project/ecommerce/src/Component/Currency/CurrencyFormType.php
        	// TODO mettere simbolo valuta correttamente
            //->add('price','money')     
            ->end()   
        	;
    }       
}
