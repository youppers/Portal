<?php

namespace Youppers\ProductBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Youppers\CommonBundle\Admin\YouppersAdmin;

class ProductVariantAdmin extends YouppersAdmin
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
            ->add('enabled', null, array('editable' => true))
            ->add('productCollection')
        	->addIdentifier('name', null, array('route' => array('name' => 'show')))
            ->add('code')
            ->add('variantProperties', null, array('associated_property' => 'attributeOption'))
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
        	->with('Product Type', array('class' => 'col-md-8'))
            ->add('name')
            ->add('code')
            //->add('description')
            ->end()
            ->with('Options', array('class' => 'col-md-4'))
            ->add('enabled', null, array('required'  => false))
			->add('position','hidden',array('attr'=>array("hidden" => true)))			
            //->add('className')
            ->end()
            ->with('Properties', array('class' => 'col-md-12'))
            ->add('variantProperties', 'sonata_type_collection', 
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
            ->add('productCollection', null, array('route' => array('name' => 'show')))
        	->add('name')
            ->add('code')
            ->add('enabled')
            ->add('description')
            ->add('className')
            ->add('productAttributes', null, array('route' => array('name' => 'show'),'associated_property' => 'description'))
            //->add('productAttributes.attributeType')
            ->add('updatedAt')
            ->add('createdAt')
        ;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getNewInstance()
    {
    	$object = parent::getNewInstance();
    
    	$object->setPosition(1);
    
    	return $object;
    }
    
}
