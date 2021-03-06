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
	
	protected $formOptions = array(
			'cascade_validation' => true
	);

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        if (!$this->isChild() && $this->getParentAssociationMapping() == 'attributeType') {
            $datagridMapper
                ->add('attributeType');
        }
        $datagridMapper

        	->add('name')
            ->add('symbol')
            ->add('attributeOptions.value')
            ->add('enabled')
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('attributeType', null, array('route' => array('name' => 'show')))
            ->addIdentifier('name')
            ->add('symbol')
            ->add('attributeOptions', null, array('associated_property' => 'value'))
            ->add('enabled', null, array('editable' => true))
        ;
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
        	->with('Attribute Standard', array('class' => 'col-md-8'));
        if (!$this->isChild() && $this->getParentAssociationMapping() == 'attributeType') {
            $formMapper
                ->add('attributeType');
        }

        $formMapper
        	->add('name')
            ->add('symbol')
            ->add('enabled', null, array('required'  => false))
            ->add('description')
            ->end()
            ->with('Guesser', array('class' => 'col-md-4'))
            ->add('isVariantImage', null, array('required'  => false))
            ->add('removeMatchingWords', null, array('required'  => false))
            ->add('usesOnlyAlias', null, array('required'  => false))
            ->add('requiredOptions')
            ->end()
            ->with('Values', array('class' => 'col-md-12', 'description' => 'Use ; to separate aliases'))
            ->add('attributeOptions', 'sonata_type_collection', array('by_reference' => false), array(
                'edit' => 'inline',
                'inline' => 'table',
                'sortable'  => 'position',
                'hideOptionsImage' => $this->getSubject() && $this->getSubject()->getAttributeType() ? $this->getSubject()->getAttributeType()->getHideOptionsImage() : false
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
            ->add('isVariantImage')
            ->add('removeMatchingWords')
            ->add('usesOnlyAlias')
            ->add('requiredOptions')
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
