<?php

namespace Youppers\ProductBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Youppers\CommonBundle\Admin\YouppersAdmin;

class ProductTypeAdmin extends YouppersAdmin
{
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
            ->add('code')
            ->add('enabled')
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
        	->addIdentifier('name', null, array('route' => array('name' => 'show')))
            ->add('code')
            ->add('enabled', null, array('editable' => true))
            ->add('productAttributes', null, array('associated_property' => 'description'))
        ;
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
        	->with('Product Type', array('class' => 'col-md-6'))
            ->add('name')
            ->add('code')
            ->add('enabled', null, array('required'  => false))
            ->add('description')
            ->end()
            ->with('Guesser', array('class' => 'col-md-6'))
            ->add('standards')
            ->add('defaults')
            ->end()
            ->with('Attributes', array('class' => 'col-md-12'))
            ->add('productAttributes', 'sonata_type_collection', 
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
            ->add('name')
            ->add('code')
            ->add('enabled')
            ->add('description')
            ->add('productAttributes', null, array('route' => array('name' => 'show'),'associated_property' => 'description'))
            ->add('standards', null, array('route' => array('name' => 'show')))
            ->add('defaults', null, array('route' => array('name' => 'show')))
            //->add('productAttributes.attributeType')
            ->add('updatedAt')
            ->add('createdAt')
        ;
    }
}
