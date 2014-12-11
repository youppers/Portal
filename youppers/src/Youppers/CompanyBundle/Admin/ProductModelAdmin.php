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
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class ProductModelAdmin extends Admin
{
    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
            ->add('code')
        	->add('name')
			->add('product.name')
            ->add('isActive')
           ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $list)
    {
        $list
            ->add('product.name')
            ->addIdentifier('name')
            ->addIdentifier('code')
            ->add('price','currency',array('currency' => '€'))
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        if (!$this->hasParentFieldDescription()) {
        	$formMapper->with('Product', array('class' => 'col-md-12'))
            //$formMapper->add('brand', 'sonata_type_model_list', array('constraints' => new Assert\NotNull()));
            ->add('product', 'sonata_type_model_list', array('constraints' => new Assert\NotNull()))
            ->end();        	
        }

        $formMapper
        	->with('Model', array('class' => 'col-md-8'))
        	->add('name')
        	->add('code')
        	// TODO usare vendor/sonata-project/ecommerce/src/Component/Currency/CurrencyFormType.php
        	// TODO mettere simbolo valuta correttamente
            ->add('price','money')        
        	->add('description')
        	->end()
        	->with('Validity', array('class' => 'col-md-4'))
        	->add('validFrom', null, array('widget' => 'single_text'))
        	//->add('validFrom', null, array('widget' => 'single_text'))
            ->add('validTo', null, array('widget' => 'single_text'))
            //->add('validTo', null, array('widget' => 'single_text'))
        	->add('isActive', 'checkbox', array('required'  => false))        
        	;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getNewInstance()
    {
    	$object = parent::getNewInstance();
    
    	//$object->setCreatedAt(new \DateTime());
    	$object->setIsActive(true);
    
    	/*
    		$inspection = new Inspection();
    		$inspection->setDate(new \DateTime());
    		$inspection->setComment("Initial inpection");
    
    		$object->addInspection($inspection);
    	*/
    	return $object;
    }
    
}
