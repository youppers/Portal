<?php

namespace Youppers\ProductBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Youppers\CommonBundle\Admin\YouppersAdmin;
use Symfony\Component\Validator\Constraints as Assert;
use Youppers\ProductBundle\YouppersProductBundle;

class ProductVariantAdmin extends YouppersAdmin
{
	public function getParentAssociationMapping()
	{
		return 'productCollection';
	}
		
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
        	//->add('productCollection')
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
        	->with('Product Variant', array('class' => 'col-md-8'))
            ->add('productCollection', 'sonata_type_model_list', array('required' => false, 'constraints' => new Assert\NotNull()))
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
            ->add('variantProperties', null, array('associated_property' => 'attributeOption'))            
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
    	
    	if (isset($filterParameters['productCollection'])) {
    		$productCollection = $this->getModelManager()->find('Youppers\ProductBundle\Entity\ProductCollection',$filterParameters['productCollection']['value']);
    		$object->setProductCollection($productCollection);
    	}
    	
    	$object->setEnabled(true);
    	 
    	$object->setPosition(1);
    
    	return $object;
    }
    
}
