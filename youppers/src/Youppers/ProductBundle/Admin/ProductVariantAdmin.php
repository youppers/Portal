<?php

namespace Youppers\ProductBundle\Admin;

use Youppers\CommonBundle\Admin\YouppersAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Validator\Constraints as Assert;
use Youppers\ProductBundle\YouppersProductBundle;
use Youppers\ProductBundle\Entity\ProductCollection;
use Youppers\ProductBundle\Entity\AttributeType;
use Sonata\AdminBundle\Route\RouteCollection;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AdminInterface;

class ProductVariantAdmin extends YouppersAdmin
{

    public function getBatchActions()
    {
        $actions = parent::getBatchActions();

        if ($this->isGranted('EDIT')) {
            $actions = array_merge(array('enable' => array('label' => $this->trans('action_enable', array(), 'messages'))),$actions);
        }

        return $actions;
    }

	public function getExportFields()
	{		
		return array(
				'id',
				'enabled',
				'company' => 'product.brand.company.code',
				'brand' => 'product.brand.code',
				'collection' => 'productCollection.code',
				'code' => 'product.code',
				'name' => 'product.name',
				'image',
				'properties' => 'variantProperties',
				'docs' => 'pdfGallery.galleryHasMedias',
		);
	}

    public function getDataSourceIterator()
    {
        return $this->getModelManager()->getDataSourceIterator($this->getDatagrid(), $this->getExportFields());
    }

    public function getParentAssociationMapping()
	{
		return 'productCollection';
	}

	protected function configureRoutes(RouteCollection $collection)
	{
		$collection->add('clone', $this->getRouterIdParameter().'/clone');
	}

	protected function configureTabMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
	{
		parent::configureTabMenu($menu, $action,$childAdmin);
			
		if (empty($childAdmin) && in_array($action, array('edit', 'show'))) {
			$id = $this->getRequest()->get($this->getIdParameter());	
			$menu->addChild('Clone', array('uri' => $this->generateUrl('clone', array('id' => $id))));
		}
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

	private function getOptions(ProductCollection $collection,AttributeType $attributeType)
	{
		
		$em = $this->modelManager->getEntityManager('YouppersProductBundle:AttributeOption');
				
		$qb = $em->getRepository('YouppersProductBundle:AttributeOption')->createQueryBuilder('o');
		
		$query = $qb
			->join('YouppersProductBundle:VariantProperty','p', 'WITH', 'p.attributeOption = o')
			->join('YouppersProductBundle:ProductVariant','v', 'WITH', 'p.productVariant = v')
			->join('o.attributeStandard','s')
			->where('v.productCollection = :collection')
			->setParameter('collection', $collection)
			->andWhere('s.attributeType = :attributeType')
			->setParameter('attributeType', $attributeType)
			->addOrderBy('o.position', 'ASC')
			->getQuery();
		
		$options = array();
		foreach ($query->getResult() as $option) {
			$options[$option->getId()] = $option->getValueWithSymbol();
		}
		return $options;				
	}

	private $selectedOptions = array();
	private $numAttributes; 
	
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
    	} else {
    		$collection = $this->getParent()->getSubject();
    		$productTypeAttributes = $collection->getProductType()->getProductAttributes();
    		$this->numAttributes = count($productTypeAttributes);
    		foreach ($productTypeAttributes as $productTypeAttribute) {
    			$attributeType = $productTypeAttribute->getAttributeType();    			
	        	$datagridMapper
	    		->add('attribute_'.$attributeType->getCode(), 'doctrine_orm_callback', array(
		    			'label' => $attributeType->getName() . ($productTypeAttribute->getVariant() ? ' (Variant)':''),
	    		        'callback'   => array($this, 'getAttributeFilter'),
	                	'field_type' => 'choice'
            		),null,array(
            			'choices' => $this->getOptions($collection,$attributeType)
            		)
	    		);
    		}
    	}
        $datagridMapper
    		->add('product.name')
            ->add('product.code')
            ->add('enabled')
        ;
    }
        
    public function getAttributeFilter($queryBuilder, $alias, $field, $value) {
    	$this->selectedOptions[] = $value['value'];

    	if (count($this->selectedOptions) == $this->numAttributes) {
    		$options = array();
    		foreach ($this->selectedOptions as $option) {
    			if (!empty($option)) {
    				$options[] = $this->modelManager
    					->getEntityManager('YouppersProductBundle:AttributeOption')
    					->find('YouppersProductBundle:AttributeOption',$option);
    			}
    		}

    		if (count($options) == 0) {
    			return true;
    		}
    		
    		$productService = $this->getConfigurationPool()->getContainer()->get('youppers.product.service.product');
    		$variants = $productService->findVariants($this->getParent()->getSubject(), $options, false);
    		
    		$ids = array();
    		foreach ($variants as $variant) {
    			$ids[] = $variant->getId();
    		}
    		$queryBuilder
    			->andWhere($alias . '.id IN (:ids)')
    			->setParameter('ids', $ids);
    	}
    
    	return true;
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
            ->add('scrapedAt')
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
