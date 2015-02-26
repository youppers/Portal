<?php

namespace Youppers\CompanyBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Route\RouteCollection;
use Youppers\CommonBundle\Admin\YouppersAdmin;
use Symfony\Component\Validator\Constraints as Assert;

class PricelistAdmin extends YouppersAdmin
{
	protected function configureRoutes(RouteCollection $collection)
	{
		$collection->add('enable', $this->getRouterIdParameter().'/enable');
	}	
		
	protected function configureTabMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
	{
		
		if ($childAdmin) {
			if ($action == 'list') $menu->addChild('Add', array('uri' => $childAdmin->generateUrl('create')));
			if (in_array($action, array('edit', 'show'))) {
				$id = $this->getRequest()->get('childId');
				if ($action != 'show') $menu->addChild('Show', array('uri' => $childAdmin->generateUrl('show', array('id' => $id))));
				if ($action != 'edit') $menu->addChild('Edit', array('uri' => $childAdmin->generateUrl('edit', array('id' => $id))));
			}
		} else {
			parent::configureTabMenu($menu, $action,$childAdmin);
			if (in_array($action, array('edit', 'show'))) {
				$id = $this->getRequest()->get('id');
				$menu->addChild('Products Prices', array('uri' => $this->generateUrl('youppers.company.admin.product_price.list', array('id' => $id))));
			}
		}
		
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function configureShowFields(ShowMapper $showMapper)
	{
		$showMapper
		->add('enabled')	
		->add('brand.company', null, array('route' => array('name' => 'show')))
		->add('brand', null, array('associated_property' => 'name', 'route' => array('name' => 'show')))
		->add('code')
		->add('currency')
		->add('validFrom')
		->add('validTo')
		->add('createdAt')
		->add('updatedAt');
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
		->add('enabled', null, array('editable' => true))
		->add('brand', null, array(
                 'route' => array(
                     'name' => 'show'
                 )
             ))
		->addIdentifier('code', null, array('route' => array('name' => 'show')))
		->add('currency')
		->add('validFrom')
		->add('validTo')
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
		->add('brand.company')
		->add('brand')
		->add('currency')		
		->add('enabled')
		;
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function configureFormFields(FormMapper $formMapper)
	{
		$formMapper
		->with('Brand', array('class' => 'col-md-6'))
		->add('brand', 'sonata_type_model_list', array('required' => false, 'constraints' => new Assert\NotNull()))
		->add('code')
		->add('currency', 'sonata_currency')		
		->end()
		->with('Validity', array('class' => 'col-md-6'))
		->add('validFrom', 'sonata_type_datetime_picker',array('dp_side_by_side' => true, 'dp_language'=>$this->getRequest()->getLocale()))
		->add('validTo', 'sonata_type_datetime_picker',array('dp_side_by_side' => true, 'dp_language'=>$this->getRequest()->getLocale()))
		->add('enabled', 'checkbox', array('required'  => false))
		->end()
		;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getNewInstance()
	{
		$object = parent::getNewInstance();
		
		$filterParameters = $this->getFilterParameters();
		
		if (isset($filterParameters['brand'])) {
			$brand = $this->getModelManager()->find('Youppers\CompanyBundle\Entity\Brand',$filterParameters['brand']['value']);
			$object->setBrand($brand);		
		}
		
		$object->setCurrency('EUR'); //TODO configurabile
		$object->setValidFrom((new \DateTime())->setTime(0,0,0));
		$object->setValidTo((new \DateTime())->setTime(0,0,0)->setDate($object->getValidFrom()->format('Y'),12,31));
		$object->setEnabled(true);

		return $object;
	}
}
