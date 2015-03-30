<?php

namespace Youppers\ProductBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Youppers\CommonBundle\Admin\YouppersAdmin;
use Symfony\Component\Validator\Constraints as Assert;
use Sonata\AdminBundle\Route\RouteCollection;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AdminInterface;

class ProductCollectionAdmin extends YouppersAdmin
{
	/**
	 * {@inheritdoc}
	 */
	protected function configureTabMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
	{
		if ($childAdmin) {
			if ($action == 'list') $menu->addChild('Create', array('uri' => $childAdmin->generateUrl('create')));
			if (in_array($action, array('edit', 'show'))) {
				$id = $this->getRequest()->get('childId');
				if ($action != 'show') $menu->addChild('Show', array('uri' => $childAdmin->generateUrl('show', array('id' => $id))));
				if ($action != 'edit') $menu->addChild('Edit', array('uri' => $childAdmin->generateUrl('edit', array('id' => $id))));
			}	
		} else {
			parent::configureTabMenu($menu, $action,$childAdmin);				
			if (in_array($action, array('edit', 'show'))) {
				$id = $this->getRequest()->get('id');
				$menu->addChild('Variants', array('uri' => $this->generateUrl('youppers_product.admin.product_variant.list', array('id' => $id))));
			}
		}
	}
	
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('brand.company.name')
        	->add('brand.company.code')
        	->add('brand.name')
        	->add('brand.code')
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
            ->add('image', null, array('template' => 'YouppersCommonBundle:CRUD:list_image.html.twig'))        	 
        	->add('enabled', null, array('editable' => true))
        	->add('brand', null, array('route' => array('name' => 'show')))
        	->add('productType', null, array('route' => array('name' => 'show')))
        	->add('pdfGallery', null, array('associated_property' => 'name', 'route' => array('name' => 'edit', 'parameters' => array('context' => 'pdf'))))        
            ;
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
        	->with('Product Collection', array('class' => 'col-md-6'))
			->add('brand', 'sonata_type_model_list', array('btn_add' => false, 'required' => false, 'constraints' => new Assert\NotNull()))
        	->add('productType', 'sonata_type_model_list', array('btn_add' => false, 'required' => false, 'constraints' => new Assert\NotNull()))
        	->add('name')
            ->add('code')
        	->add('description')
	        ->end()
            ->with('Options', array('class' => 'col-md-6'))
            ->add('enabled', null, array('required'  => false))
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
                        /*
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
            */
        
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
            ->add('code')
        	->add('description')
        	->add('enabled')
            ->add('image', null,array('template' => 'YouppersCommonBundle:CRUD:show_image.html.twig'))
            //->add('productVariants', null, array('route' => array('name' => 'show')))
            //->add('productAttributes.attributeType')
        	->add('pdfGallery', null, array('associated_property' => 'name', 'route' => array('name' => 'edit', 'parameters' => array('context' => 'pdf'))))        
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

    	if (isset($filterParameters['brand__code'])) {
    		$brand = $this->getModelManager()->findOneBy('Youppers\CompanyBundle\Entity\Brand',array('code' => $filterParameters['brand__code']['value']));
    		$object->setBrand($brand);
    	}

    	if (!isset($brand) && isset($filterParameters['brand__name'])) {
    		$brand = $this->getModelManager()->findOneBy('Youppers\CompanyBundle\Entity\Brand',array('name' => $filterParameters['brand__name']['value']));
    		$object->setBrand($brand);
    	}

    	$object->setEnabled(true);
    
    	return $object;
    }
    

}
