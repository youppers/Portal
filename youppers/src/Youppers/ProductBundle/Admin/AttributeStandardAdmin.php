<?php

namespace Youppers\ProductBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Youppers\CommonBundle\Admin\YouppersAdmin;

class AttributeStandardAdmin extends YouppersAdmin
{
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('attributeType')
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
            ->add('attributeType', null, array('route' => array('name' => 'show')))
            ->addIdentifier('name', null, array('route' => array('name' => 'show')))
            ->add('code')
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
        	->with('Attribute Standard', array('class' => 'col-md-8'))
            ->add('attributeType')
        	->add('name')
            ->add('code')
            ->add('description')
            ->end()
            ->with('Options', array('class' => 'col-md-4'))
            ->add('enabled', null, array('required'  => false))
            ->end()
            ->with('Values', array('class' => 'col-md-12'))
            ->add('attributeOptions', 'sonata_type_collection', array('by_reference' => false), array(
                'edit' => 'inline',
                'inline' => 'table',
                'sortable'  => 'position'
            ))
            ->end()
            ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
        	->add('attributeType', null, array('route' => array('name' => 'show')))
            ->add('name')
            ->add('code')
            ->add('enabled')
            ->add('description')
            //->add('attributeOptions', null, array('associated_property' => 'valueWithEquivalence'))
            ->add('attributeOptions', null, array('associated_property' => 'valueWithEquivalences'))
            ->add('updatedAt')
            ->add('createdAt')
        ;
    }
}
