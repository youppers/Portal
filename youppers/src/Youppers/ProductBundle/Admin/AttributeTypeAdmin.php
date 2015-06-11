<?php

namespace Youppers\ProductBundle\Admin;

use Youppers\CommonBundle\Admin\YouppersAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AdminInterface;

class AttributeTypeAdmin extends YouppersAdmin
{
	/**
	 * {@inheritdoc}
	 */
	protected function configureTabMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
	{
		parent::configureTabMenu($menu, $action,$childAdmin);
		
		if (empty($childAdmin) && in_array($action, array('edit', 'show'))) {
			$id = $this->getRequest()->get($this->getIdParameter());
			$menu->addChild('Standards', array('uri' => $this->generateUrl('youppers_product.admin.attribute_standard.list', array('id' => $id))));
		}
	}
	
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
            ->add('attributeStandards', null, array('associated_property' => 'name'))
        ;
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
        	->with('Attribute Type', array('class' => 'col-md-8'))
            ->add('name')
            ->add('code')
            ->add('description')
            ->end()
            ->with('Options', array('class' => 'col-md-4'))
            ->add('enabled', null, array('required'  => false))
            ->add('hideOptionsImage', null, array('required'  => false))
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
            ->add('hideOptionsImage')
            ->add('description')
            ->add('updatedAt')
            ->add('createdAt')
        ;
    }
}
