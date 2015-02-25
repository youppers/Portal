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

class ProductPriceAdmin extends Admin
{
	protected function configureRoutes(RouteCollection $collection)
	{
		$collection->clearExcept(array('create','delete'));
	}
	
    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
        	// TODO usare vendor/sonata-project/ecommerce/src/Component/Currency/CurrencyFormType.php
        	// TODO mettere simbolo valuta correttamente
            ->add('product')
            ->add('pricelist')        
        	->add('price','money')        
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
