<?php

namespace Youppers\CompanyBundle\Admin;

use Youppers\CommonBundle\Admin\YouppersAdmin;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Validator\Constraints as Assert;

class PricelistAdmin extends YouppersAdmin
{
	protected function configureRoutes(RouteCollection $collection)
	{
		$collection->add('enable', $this->getRouterIdParameter().'/enable');
	}	
		
	protected function configureTabMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
	{		
		parent::configureTabMenu($menu, $action,$childAdmin);
		
		if (empty($childAdmin) && in_array($action, array('edit', 'show'))) {
			$id = $this->getRequest()->get($this->getIdParameter());
			$menu->addChild('Products Prices', array('uri' => $this->generateUrl('youppers.company.admin.product_price.list', array('id' => $id))));
		}		
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function configureShowFields(ShowMapper $showMapper)
	{
		$showMapper
		->add('code')
		->add('currency')
		->add('enabled')	
		->add('company', null, array('associated_property' => 'name', 'route' => array('name' => 'show')))
		->add('company.code', null, array('label' => 'Company Code'))
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
		->addIdentifier('code', null, array('route' => array('name' => 'show')))
		->add('currency')
		->add('enabled', null, array('editable' => true))
		->add('company', null, array(
                 'route' => array(
                     'name' => 'show'
                 )
             ))
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
		->add('code')
		->add('currency')		
		->add('enabled')
		->add('company')
		;
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function configureFormFields(FormMapper $formMapper)
	{
		$formMapper
		->with('Price List', array('class' => 'col-md-6'))
		->add('code')
		->add('currency', 'sonata_currency')		
		->add('company', 'sonata_type_model_list', array('required' => false, 'constraints' => new Assert\NotNull()))
		->end()
		->with('Details', array('class' => 'col-md-6'))
		->add('enabled', 'checkbox', array('required'  => false))
		->add('validFrom', 'sonata_type_datetime_picker',array('dp_side_by_side' => true, 'dp_language'=>$this->getRequest()->getLocale()))
		->add('validTo', 'sonata_type_datetime_picker',array('dp_side_by_side' => true, 'dp_language'=>$this->getRequest()->getLocale()))
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
		
		if (isset($filterParameters['company'])) {
			$company = $this->getModelManager()->find('Youppers\CompanyBundle\Entity\Company',$filterParameters['company']['value']);
			$object->setCompany($company);		
		}
		
		$object->setCurrency('EUR'); //TODO configurabile
		$object->setValidFrom((new \DateTime())->setTime(0,0,0));
		$object->setValidTo((new \DateTime())->setTime(0,0,0)->setDate($object->getValidFrom()->format('Y'),12,31));
		$object->setEnabled(true);

		return $object;
	}
}
