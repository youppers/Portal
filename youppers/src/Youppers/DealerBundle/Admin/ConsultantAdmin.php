<?php

namespace Youppers\DealerBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Youppers\CommonBundle\Admin\YouppersAdmin;

class ConsultantAdmin extends YouppersAdmin
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

        $query->join($query->getRootAliases()[0] . '.dealer','d');

        $query->andWhere(
            $query->expr()->eq('d.org', ':org')
        );
        $query->setParameter('org', $org);

        return $query;
    }

	/**
	 * {@inheritdoc}
	 */
	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper		
		->addIdentifier('fullname', null, array('route' => array('name' => 'show')))
		->add('code')
		->add('enabled')
		->add('available', null, array('editable' => true))
		->add('user', null, array('route' => array('name' => 'show')))		
		->add('photo', null, array('template' => 'YouppersCommonBundle:CRUD:list_image.html.twig'))		
		->add('dealer', null, array('route' => array('name' => 'show')))		
		//->add('stores', null, array('associated_property' => 'name'))
		->add('stores')
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
		->add('fullname')
		->add('code')
		->add('enabled')
		->add('available')
		->add('dealer')
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureShowFields(ShowMapper $showMapper)
	{
		$showMapper
		->add('fullname')
		->add('code')
		->add('enabled')
		->add('available')
		->add('description')
		->add('photo', null, array('template' => 'YouppersCommonBundle:CRUD:show_image.html.twig'))
		->add('user', null, array('route' => array('name' => 'show')))
		->add('dealer', null, array('route' => array('name' => 'show')))
		->add('stores')
		->add('createdAt')
		->add('updatedAt')
		;
	}
		
	/**
	 * {@inheritdoc}
	 */
	protected function configureFormFields(FormMapper $formMapper)
	{
		$formMapper
		->with('Consultant', array('class' => 'col-md-6'));
				
		$formMapper
		->add('fullname', null, array('help' => 'Specify Given and Family name'))
		->add('code')
		->add('description')
		->add('photo', 'sonata_type_model_list', 
				array('required' => false), 
				array('link_parameters' => array(
						'context'  => 'youppers_consultant_photo'
				))
		)
		->end()
		->with('Details', array('class' => 'col-md-6'))
		->add('user', 'sonata_type_model_list', array('required' => false))
		;
		if (!$this->hasParentFieldDescription()) {
			$formMapper->add('dealer', 'sonata_type_model_list', array('required' => false));
		}
		$formMapper
		->add('stores')
		->add('enabled', 'checkbox', array('required'  => false))
		->add('available', null, array('required'  => false))
		->end();
		
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
