<?php

namespace Youppers\DealerBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Youppers\CommonBundle\Admin\YouppersAdmin;

class StoreAdmin extends YouppersAdmin
{

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('qrprint', $this->getRouterIdParameter().'/qrprint');
    }

    protected function configureTabMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {
        parent::configureTabMenu($menu, $action,$childAdmin);

        if (empty($childAdmin) && in_array($action, array('edit', 'show'))) {
            $id = $this->getRequest()->get($this->getIdParameter());
            if ($action == 'show') $menu->addChild('box_qr_print', array('uri' => $this->generateUrl('qrprint', array('id' => $id))));
        }
    }

    /**
	 * {@inheritdoc}
	 */
	protected function configureShowFields(ShowMapper $showMapper)
	{
		$showMapper
		->add('name')
		->add('code')
		->add('email')
		->add('enabled')
		->add('description')
		->add('geoid', null, array('route' => array('name' => 'show')))
		->add('dealer', null, array('route' => array('name' => 'show')))
		->add('consultants', null, array('route' => array('name' => 'show')))
		->add('boxes', null, array('associated_property' => 'name', 'route' => array('name' => 'show')))
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
		->addIdentifier('name', null, array('route' => array('name' => 'show')))
		->add('code')
		->add('enabled', null, array('editable' => true))
		->add('geoid')
		->add('dealer', null, array('route' => array('name' => 'show')))		
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
		->add('name')
		->add('code')
		->add('enabled')
		->add('dealer')
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureFormFields(FormMapper $formMapper)
	{
		$formMapper
		->with('Store', array('class' => 'col-md-6'));
				
		$formMapper
		->add('name')
		->add('code')
		->add('email')
		->add('description')
		->end()
		->with('Details', array('class' => 'col-md-6'))
		->add('geoid', 'sonata_type_model_list', array('required' => false, 'constraints' => new Assert\NotNull()))
		;
		if (!$this->hasParentFieldDescription()) {
			$formMapper->add('dealer', 'sonata_type_model_list', array('required' => false, 'constraints' => new Assert\NotNull()));
		}
		$formMapper
		->add('enabled', 'checkbox', array('required'  => false))
		->end();
		
		if (!$this->hasParentFieldDescription()) {
			$formMapper
				->with('Boxes', array('class' => 'col-md-12'))
				->add('boxes', 'sonata_type_collection', array(
					'by_reference'       => false,
					'cascade_validation' => true,
					'required' => false
				), array(
					'edit' => 'inline',
					'inline' => 'table'
				))
				->end();
		}		
	}

	/**
	 * {@inheritdoc}
	 */
	public function getNewInstance()
	{
		$object = parent::getNewInstance();
		
		$filterParameters = $this->getFilterParameters();
		
		if (isset($filterParameters['dealer'])) {
			$dealer = $this->getModelManager()->find('Youppers\DealerBundle\Entity\Dealer',$filterParameters['dealer']['value']);
			$object->setDealer($dealer);
		}
		
		$object->setEnabled(true);

		return $object;
	}
}
