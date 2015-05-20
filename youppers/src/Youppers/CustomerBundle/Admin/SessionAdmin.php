<?php

namespace Youppers\CustomerBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Youppers\CommonBundle\Admin\YouppersAdmin;
use Symfony\Component\Validator\Constraints as Assert;
use Sonata\AdminBundle\Route\RouteCollection;

class SessionAdmin extends YouppersAdmin
{

	protected function configureRoutes(RouteCollection $collection)
	{
		$collection->add('items', $this->getRouterIdParameter().'/item/list');
        $collection->add('email', $this->getRouterIdParameter().'/email');
	}

    protected function configureTabMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {
        parent::configureTabMenu($menu, $action,$childAdmin);

        if (empty($childAdmin) && in_array($action, array('edit', 'show'))) {
            $id = $this->getRequest()->get($this->getIdParameter());
            $menu->addChild('Email', array('uri' => $this->generateUrl('email', array('id' => $id))));
        }
    }

	/**
	 * {@inheritdoc}
	 */
	protected function configureShowFields(ShowMapper $showMapper)
	{
		$showMapper
		->add('profile', null, array('route' => array('name' => 'show')))
		->add('store', null, array('route' => array('name' => 'show')))
		->add('consultant', null, array('route' => array('name' => 'show')))
		->add('name')
		->add('removed')
		->add('items', null, array('route' => array('name' => 'show'), 'associated_property' => 'description'))
		->add('history', null, array('route' => array('name' => 'show'), 'associated_property' => 'description'))
		->add('createdAt')
		->add('updatedAt')
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
		->add('profile', null, array('route' => array('name' => 'show')))
		->add('store', null, array('route' => array('name' => 'show')))
		->add('consultant', null, array('route' => array('name' => 'show')))
		->add('name')
		->add('removed')
		->add('createdAt')
		->add('_action', 'actions', array(
				'actions' => array(
						'show' => array(),
						'items' => array('template' => 'YouppersCustomerBundle:CRUD:list__action_items.html.twig'),
                        'email' => array('template' => 'YouppersCustomerBundle:CRUD:list__action_email.html.twig'),
				)
		))		
		;
	}

	/**
	 * Default Datagrid values
	 *
	 * @var array
	 */
	protected $datagridValues = array(
			'removed' => array('value' => 2),
			'_page' => 1,            // display the first page (default = 1)
			'_sort_order' => 'DESC', // reverse order (default = 'ASC')
			'_sort_by' => 'createdAt'  // name of the ordered field
			// (default = the model's id field, if any)
	
			// the '_sort_by' key can be of the form 'mySubModel.mySubSubModel.myField'.
	);

	
	/**
	 * {@inheritdoc}
	 */
	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
		->add('profile.name')
		->add('store.code')
		->add('consultant.fullname')
		->add('name')
		->add('removed')
		;
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function configureFormFields(FormMapper $formMapper)
	{
		
		$formMapper
			->add('profile', 'sonata_type_model_list', array('required' => false))
			->add('store', 'sonata_type_model_list', array('required' => false))
			->add('consultant', 'sonata_type_model_list', array('required' => false))
			->add('name')
			->add('removed', null, array('required'  => false))
		;
	}

}
