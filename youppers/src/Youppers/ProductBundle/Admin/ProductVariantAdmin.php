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
	 * {@inheritdoc}
	 */
	public function toString($object)
	{
		if (is_object($object) && $object === $this->subject && method_exists($object, 'getProduct') && null !== $object->getProduct()) {
			return $object->getProduct()->getNameCode();
		}
	
		return parent::toString($object);
	}
	
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
    	if (!($this->isChild() && $this->getParentAssociationMapping() === 'productCollection')) {    	 
        	$datagridMapper
            ->add('productCollection.brand.company.name')
            ->add('productCollection.brand.company.code')
        	->add('productCollection.brand.name')
            ->add('productCollection.brand.code')
            ->add('productCollection.name');
    	}
        $datagridMapper
    		->add('product.name')
            ->add('product.code')
            ->add('enabled')
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('_action', 'actions', array(
                'actions' => array(
                    //'show' => array(),
                    'edit' => array(),
                    //'delete' => array(),
                )
            ))
        	->addIdentifier('image', null, array('route' => array('name' => 'show'), 'template' => 'YouppersCommonBundle:CRUD:list_image.html.twig'))        	 
            ->add('enabled', null, array('editable' => true));
       	if (!($this->isChild() && $this->getParentAssociationMapping() === 'productCollection')) {
        	$listMapper
       		->add('productCollection');
       	}
       	$listMapper
            ->add('product.name')
            ->add('product.code')
            ->add('variantProperties', null, array('associated_property' => 'attributeOption'))
        ;
    }

    private function getVariantPropertiesHelp()
    {
    	$collection = $this->getSubject()->getProductCollection();
    	if ($collection) {
    		$attributes = array();
    		foreach($collection->getProductType()->getProductAttributes() as $attribute) {
				$attributes[] = '' . $attribute->getDescription();
    		}
    		return 'Variant must have these attributes: ' . implode(', ',$attributes);
    	} else {
    		return false;
    	}    	 
    }
    
    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
        	->with('Product Variant', array('class' => 'col-md-6'));
    	if (!$this->isChild()) {    	 
        	$formMapper
            ->add('productCollection', 'sonata_type_model_list', array(
            		'btn_delete'       => false,
            		'required' => false, 'constraints' => new Assert\NotNull()));
		}
       	$formMapper
            ->add('product', 'sonata_type_model_list', array(
            		'btn_add'       => false,
            		'btn_delete'       => false,
            		'required' => false, 'constraints' => new Assert\NotNull()))
            //->add('product.name')
            //->add('product.code')
            //->add('description')
        	->end()
            ->with('Options', array('class' => 'col-md-6'))
            ->add('enabled', null, array('required'  => false))
            ->add('position','hidden',array('attr'=>array("hidden" => true)))			
            //->add('className')
            ->end()
            ->with('Media', array('class' => 'col-md-6'))
            ->add('pdfGallery', 'sonata_type_model_list', 
	        	array(
	        		'required' => false
	        	), array(
	        		'link_parameters' => array(
	        				'context'  => 'pdf',
	        				'filter'   => array('context' => array('value' => 'pdf'))
	        		)
	        	)
	        )	        
            ->add('image', 'sonata_type_model_list', 
	        	array(
	        		'required' => false
	        	), array(
	        		'link_parameters' => array(
	        				'context'  => 'youppers_product',
	        				'filter'   => array('context' => array('value' => 'youppers_product'))
	        		)
	        	)
	        )	        
	        ->end()
            ->with('Properties', array('class' => 'col-md-12', 'description' => $this->getVariantPropertiesHelp()))
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
        	->add('product', null, array('route' => array('name' => 'show')))            
        	->add('product.name')
        	->add('product.code')
        	->add('enabled')
            ->add('pdfGallery')
            ->add('image', null,array('template' => 'YouppersCommonBundle:CRUD:show_image.html.twig'))
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
