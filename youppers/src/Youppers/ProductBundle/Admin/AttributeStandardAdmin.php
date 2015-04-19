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
	public function getParentAssociationMapping()
	{
		return 'attributeType';
	}
	
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper

        	->add('name')
            ->add('symbol')
            ->add('enabled')
            ->add('attributeType')
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name', null, array('route' => array('name' => 'show')))
            ->add('symbol')
            ->add('enabled', null, array('editable' => true))
            ->add('attributeType', null, array('route' => array('name' => 'show')))
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
            ->add('symbol')
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
            ->add('symbol')
            ->add('enabled')
            ->add('description')
            ->add('attributeOptions', null, array('associated_property' => 'valueWithSymbol'))
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
    
    	$filterParameters = $this->getFilterParameters();
    
    	if (isset($filterParameters['attributeType'])) {
    		$attributeType = $this->getModelManager()->find('Youppers\ProductBundle\Entity\AttributeType',$filterParameters['attributeType']['value']);
    		$object->setAttributeType($attributeType);
    	}
    
    	$object->setEnabled(true);
    
    	return $object;
    }
    
}
