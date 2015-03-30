<?php

namespace Youppers\ProductBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Youppers\CommonBundle\Admin\YouppersAdmin;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AdminInterface;

class AttributeTypeAdmin extends YouppersAdmin
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
				$menu->addChild('Standards', array('uri' => $this->generateUrl('youppers_product.admin.attribute_standard.list', array('id' => $id))));
			}
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
            ->add('description')
            ->add('attributeStandards', null, array('associated_property' => 'name', 'route' => array('name' => 'show')))
            ->add('updatedAt')
            ->add('createdAt')
        ;
    }
}
