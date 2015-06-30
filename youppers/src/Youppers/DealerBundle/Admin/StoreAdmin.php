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

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('qrprint', $this->getRouterIdParameter().'/qrprint');
    }

    protected function configureTabMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {
        parent::configureTabMenu($menu, $action,$childAdmin);

        if (empty($childAdmin) && in_array($action, array('edit', 'show'))) {
            $id = $this->getRequest()->get($this->getIdParameter());
            $menu->addChild($this->trans('box_qr_print', array(), 'SonataAdminBundle'), array('attributes' => array('icon' => 'glyphicon glyphicon-qrcode'), 'uri' => $this->generateUrl('qrprint', array('id' => $id))));
            $menu->addChild($this->trans('boxes'), array('attributes' => array('icon' => 'glyphicon glyphicon-list-alt'), 'uri' => $this->generateUrl('youppers.dealer.admin.box.list', array('id' => $id))));

            //if ($action == 'show') $menu->addChild('box_qr_print', array('uri' => $this->generateUrl('qrprint', array('id' => $id))));
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
        ->add('logo', null,array('template' => 'YouppersCommonBundle:CRUD:show_image.html.twig'))
		->add('geoid', null, array('route' => array('name' => 'show')))
		->add('dealer', null, array('route' => array('name' => 'show')))
		->add('consultants', null, array('route' => array('name' => 'show')))
		->add('boxes', null, array('associated_property' => 'nameCodeStatus', 'route' => array('name' => 'show')))
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
        ->add('logo', 'sonata_type_model_list',
            array(
                'required' => false
            ), array(
                'link_parameters' => array(
                    'context'  => 'youppers_dealer',
                    'filter'   => array('context' => array('value' => 'youppers_dealer'))
                )
            )
        )
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
