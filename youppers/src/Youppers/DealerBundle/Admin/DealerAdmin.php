<?php

namespace Youppers\DealerBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Youppers\CommonBundle\Admin\YouppersAdmin;

class DealerAdmin extends YouppersAdmin
{

    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);

        $user = $this->getConfigurationPool()->getContainer()->get('security.context')->getToken()->getUser();

        if (in_array('ROLE_SUPER_ADMIN',$user->getRoles())) {
            return $query;
        }

        $org = $user->getOrg();

        $query->andWhere(
            $query->expr()->eq($query->getRootAliases()[0] . '.org', ':org')
        );
        $query->setParameter('org', $org);

        return $query;
    }

    /**
	 * {@inheritdoc}
	 */
	protected function configureShowFields(ShowMapper $showMapper)
	{
		$showMapper
        ->add('org', null, array('route' => array('name' => 'show')))
		->add('name')
		->add('code')
		->add('email')
		->add('enabled')
		->add('description')
		->add('createdAt')
		->add('updatedAt')
        ->add('brands', null, array('route' => array('name' => 'show')))
		->add('stores', null, array('associated_property' => 'name', 'route' => array('name' => 'show')))
		->add('consultants', null, array('associated_property' => 'fullname', 'route' => array('name' => 'show')))
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
		->add('stores', null, array('associated_property' => 'name'))
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
		;
	}
	
	/**
	 * Default Datagrid values
	 *
	 * @var array
	 */
	protected $datagridValues = array(
			//'isActive' => array('value' => 1)
	);

	/**
	 * {@inheritdoc}
	 */
	protected function configureFormFields(FormMapper $formMapper)
	{
		$formMapper
		->with('Dealer', array('class' => 'col-md-8'))
		->add('name')
		->add('code')
		->add('email', null, array('required'  => false))
		->add('description')
		->end()
		->with('Details', array('class' => 'col-md-4'))
        ->add('org', 'sonata_type_model_list')
		->add('enabled', 'checkbox', array('required'  => false))
            ->add('brands')
		->end()
		;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getNewInstance()
	{
		$object = parent::getNewInstance();
		
		$object->setEnabled(true);

		return $object;
	}
}
