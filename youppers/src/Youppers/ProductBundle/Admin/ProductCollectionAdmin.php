<?php

namespace Youppers\ProductBundle\Admin;

use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Youppers\CommonBundle\Admin\YouppersAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Validator\Constraints as Assert;
use Sonata\AdminBundle\Route\RouteCollection;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class ProductCollectionAdmin extends YouppersAdmin
{
    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);
        if (!$this->isGranted('LIST')) {
            return $query;
        }

        $user = $this->getConfigurationPool()->getContainer()->get('security.token_storage')->getToken()->getUser();

        if (in_array('ROLE_SUPER_ADMIN',$user->getRoles())) {
            return $query;
        }

        $org = $user->getOrg();

        $query->join($query->getRootAliases()[0] . '.brand','b');
        $query->join('b.company','c');

        $query->andWhere(
            $query->expr()->eq('c.org', ':org')
        );
        $query->setParameter('org', $org);

        return $query;
    }

    protected function configureRoutes(RouteCollection $collection)
	{
		$collection->add('guessPreview', $this->getRouterIdParameter().'/guessPreview');
		$collection->add('guessWrite', $this->getRouterIdParameter().'/guessWrite');
		$collection->add('guessForce', $this->getRouterIdParameter().'/guessForce');
	}
	
	public function getExportFields()
	{
		return array(
				'id',
				'enabled',
				'company' => 'brand.company.code',
				'brand' => 'brand.code',
				'code',
				'name',
				'image',
				'type' => 'productType',
				'standards',
				'docs' => 'pdfGallery.galleryHasMedias',
		);
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function configureTabMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
	{
		parent::configureTabMenu($menu, $action,$childAdmin);
		
		if (empty($childAdmin) && in_array($action, array('edit', 'show'))) {
			$id = $this->getRequest()->get($this->getIdParameter());
			$menu->addChild('Variants', array('attributes' => array('icon' => 'glyphicon glyphicon-list-alt'), 'uri' => $this->generateUrl('youppers_product.admin.product_variant.list', array('id' => $id))));
			$menu->addChild('Guess Preview', array('attributes' => array('icon' => 'fa fa-binoculars'), 'uri' => $this->generateUrl('guessPreview', array('id' => $id))));
			if ($this->isGranted('EDIT')) $menu->addChild('Guess Write', array('attributes' => array('icon' => 'fa fa-thumbs-o-up'), 'uri' => $this->generateUrl('guessWrite', array('id' => $id))));
            if ($this->isGranted('EDIT')) $menu->addChild('Guess Force', array('attributes' => array('icon' => 'fa fa-thumbs-up'), 'uri' => $this->generateUrl('guessForce', array('id' => $id))));
		}
	}
	
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
			->add('brand')
            ->add('name')
            ->add('code')
            ->add('productType')
            ->add('enabled')
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name')
            ->add('codeAndAliases')
        	->add('countProductVariants', 'url', array('label'=> 'Variants', 'route' => array('identifier_parameter_name' => 'id','name' => 'admin_youppers_product_productcollection_productvariant_list')))
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
            ->add('alias')
            ->add('enabled', null, array('required'  => false))
            ->add('description')
	        ->end()
            ->with('Guesser', array('class' => 'col-md-6'))
            ->add('standards')
            ->add('defaults')
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
            ->add('alias')
            ->add('description')
        	->add('enabled')
        	->add('standards', null, array('route' => array('name' => 'show')))
            ->add('defaults', null, array('route' => array('name' => 'show')))
            ->add('image', null,array('template' => 'YouppersCommonBundle:CRUD:show_image.html.twig'))
            //->add('productVariants', null, array('route' => array('name' => 'show')))
            //->add('productAttributes.attributeType')
        	->add('pdfGallery', null, array('associated_property' => 'name', 'route' => array('name' => 'edit', 'parameters' => array('context' => 'pdf'))))        
            ->add('updatedAt')
            ->add('createdAt')
        ;
    }
    
    private $justSavedObject;
    
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

		if (isset($filterParameters['productType'])) {
			$productType = $this->getModelManager()->find('Youppers\ProductBundle\Entity\ProductType',$filterParameters['productType']['value']);
			//var_dump($productType->_toString());
			$object->setProductType($productType);
		}

    	/*
    	$session = $this->get('session');
    	if ($session->has('newProductCollectionInstance')) {
    		$data = $session->get('newProductCollectionInstance');
    		if (!isset($brand)) {
    			$object->setBrand($data['brand']);
    		}
    		$object->setType($data['type']);
    	}
    	*/
    	 
    	$object->setEnabled(true);
    
    	return $object;
    }
    
    /*
    public function postPersist($object)
    {    	
    	$session = $this->get('session');
    	$session->set('newProductCollectionInstance', array('brand' => $object->getBrand(), 'type' => $object->getType()));
    }
    */

}
