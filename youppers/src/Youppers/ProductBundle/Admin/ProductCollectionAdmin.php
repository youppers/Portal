<?php

namespace Youppers\ProductBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Youppers\CommonBundle\Admin\YouppersAdmin;
use Symfony\Component\Validator\Constraints as Assert;

class ProductCollectionAdmin extends YouppersAdmin
{
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
            ->add('enabled')
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('enabled', null, array('editable' => true))
        	->add('brand', null, array('route' => array('name' => 'show')))
        	->add('productType', null, array('route' => array('name' => 'show')))        	
            ->addIdentifier('name', null, array('route' => array('name' => 'show')))
            ->add('productVariants')
            ->add('_action', 'actions', array(
                'actions' => array(
                    //'show' => array(),
                    'edit' => array(),
                    //'delete' => array(),
                )
            ))
        ;
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
        	->with('Product Collection', array('class' => 'col-md-8'))
			->add('brand', 'sonata_type_model_list', array('constraints' => new Assert\NotNull()))
        	->add('productType', 'sonata_type_model_list', array('constraints' => new Assert\NotNull()))
        	->add('name')
            //->add('description')
            ->end()
            ->with('Options', array('class' => 'col-md-4'))
            ->add('enabled', null, array('required'  => false))
            ->end()
            ->with('Variants', array('class' => 'col-md-12'))
            ->add('productVariants', 'sonata_type_collection', 
            		array('by_reference' => false),
            		array(
		                'edit' => 'inline',
		                'inline' => 'table',
		                'sortable' => 'position',
            		)
            	)
            ->end()
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('brand')
            ->add('productType')
        	->add('name')
            ->add('enabled')
            //->add('description')
            ->add('productVariants', null, array('route' => array('name' => 'show')))
            //->add('productAttributes.attributeType')
            ->add('updatedAt')
            ->add('createdAt')
        ;
    }
}
