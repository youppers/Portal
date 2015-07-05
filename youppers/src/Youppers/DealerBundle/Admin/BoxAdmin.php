<?php

namespace Youppers\DealerBundle\Admin;

use Youppers\CommonBundle\Admin\YouppersAdmin;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Symfony\Component\Validator\Constraints as Assert;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Route\RouteCollection;

class BoxAdmin extends YouppersAdmin
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

        $query->join($query->getRootAliases()[0] . '.store','s');
        $query->join('s.dealer','d');

        $query->andWhere(
            $query->expr()->eq('d.org', ':org')
        );
        $query->setParameter('org', $org);

        return $query;
    }

    public function getBatchActions()
	{
		$actions = parent::getBatchActions();
		
		if ($this->hasRoute('list') && $this->isGranted('VIEW')) {
            $actions = array_merge(array('print' => array(
					'label'            => $this->trans('action_print', array(), 'messages'),
					'ask_confirmation' => false, // by default always true
			)), $actions);
		}
		
		return $actions;
	    
	}
	
	protected function configureRoutes(RouteCollection $collection)
	{
		$collection->add('qr', $this->getRouterIdParameter().'/qr');
		$collection->add('clone', $this->getRouterIdParameter().'/clone');
		$collection->add('enable', $this->getRouterIdParameter().'/enable');
	}
	
	protected function configureTabMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
	{
		parent::configureTabMenu($menu, $action,$childAdmin);
		
		if (empty($childAdmin) && in_array($action, array('edit', 'show'))) {	
			$id = $this->getRequest()->get($this->getIdParameter());
			if ($action == 'show') {
                if ($this->getSubject()->getEnabled()) {
                    $menu->addChild('box_clone_action', array('attributes' => array('icon' => 'fa fa-files-o'), 'uri' => $this->generateUrl('clone', array('id' => $id))));
                }
                if ($this->getSubject()->getQr() == null) {
                    $menu->addChild('box_qr_action', array('attributes' => array('icon' => 'fa fa-qrcode'),'uri' => $this->generateUrl('qr', array('id' => $id))));
                } elseif (!$this->getSubject()->getEnabled() || !$this->getSubject()->getQr()->getEnabled()) {
                    $menu->addChild('box_enable_action', array('attributes' => array('icon' => 'fa fa-check-square-o'), 'uri' => $this->generateUrl('enable', array('id' => $id))));
                }
            }
		}		
	}

    public function getParentAssociationMapping()
    {
        return 'store';
    }

	/**
	 * {@inheritdoc}
	 */
	protected function configureShowFields(ShowMapper $showMapper)
	{
		$showMapper
		->add('store', null, array('route' => array('name' => 'show')))
		->add('name')
		->add('code')
		->add('enabled')
        ->add('qr.boxes', null, array('route' => array('name' => 'show'), 'associated_property' => 'getNameCodeStatus'))
		->add('description')
		->add('image', null,array('template' => 'YouppersCommonBundle:CRUD:show_image.html.twig'))
		->add('createdAt')
		->add('updatedAt')		
		->add('boxProducts', null, array('route' => array('name' => 'edit'), 'associated_property' => 'nameProduct'))
		->add('qr', null, array('label' => 'QRCode', 'route' => array('name' => 'youppers_common_qr_box'), 'template' => 'YouppersCommonBundle:CRUD:show_qr.html.twig'))
		;
	}	

	/**
	 * {@inheritdoc}
	 */
	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
		->addIdentifier('name')
		->add('code')
		->add('enabled')
        ->add('image', null, array('route' => array('name' => 'show'), 'template' => 'YouppersCommonBundle:CRUD:list_image.html.twig'))
		->add('store', null, array(
                 'route' => array(
                     'name' => 'show'
                 )
             ))
        ->add('boxProducts', null, array('associated_property' => 'nameProduct'), array('width' => '100px'))
		->add('qr', null, array('label' => 'QR code', 'route' => array('name' => 'youppers_common_qr_box'), 'template' => 'YouppersCommonBundle:CRUD:list_qr.html.twig'))		
		;
        if ($this->isChild()) {
            $listMapper->remove($this->getParentAssociationMapping());
        }
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
        ->add('name')
		->add('code')
		->add('store')
		->add('enabled')
		;
        if ($this->isChild()) {
            $datagridMapper->remove($this->getParentAssociationMapping());
        }
	}
	
	/**
	 * Default Datagrid values
	 *
	 * @var array
	 */
	protected $datagridValues = array(
			//'enabled' => array('value' => 1)
	);

    protected $formOptions = array(
        'cascade_validation' => true
    );

	/**
	 * {@inheritdoc}
	 */
	protected function configureFormFields(FormMapper $formMapper)
	{
		$formMapper
		->with('Box', array('class' => 'col-md-8'));
		if (!$this->hasParentFieldDescription()) {
			$formMapper
			->add('store', 'sonata_type_model_list', array('required' => false, 'constraints' => new Assert\NotNull()));
		}		
		$formMapper
		->add('name')
		->add('code', null, array('required'  => false))
		->add('description')
		->end()
		->with('Details', array('class' => 'col-md-4'))
		->add('enabled', 'checkbox', array('required'  => false))
		->add('image', 'sonata_type_model_list',
				array(
						'required' => false
				), array(
						'link_parameters' => array(
								'context'  => 'youppers_box',
								'filter'   => array('context' => array('value' => 'youppers_box'))
						)
				)
		)
		->end();
		if (!$this->hasParentFieldDescription()) {
			$formMapper
				->with('Products', array('class' => 'col-md-12'))
				->add('boxProducts', 'sonata_type_collection', 
					array(
						//'type_options' => array('delete' => false),
	            		'by_reference'       => false,
	            		'cascade_validation' => true,
						//'required' => false
				), array(
	                'edit' => 'inline',
	                'inline' => 'table',
	                'sortable' => 'position',
	            ))
			->end();
		}

        if ($this->isChild()) {
            $formMapper->remove($this->getParentAssociationMapping());
        }
	}

	/**
	 * {@inheritdoc}
	 */
	public function getNewInstance()
	{
		$object = parent::getNewInstance();

		$filterParameters = $this->getFilterParameters();
		
		if (isset($filterParameters['store'])) {
			$store = $this->getModelManager()->find('Youppers\DealerBundle\Entity\Store',$filterParameters['store']['value']);
			$object->setStore($store);
		}
		
		$object->setEnabled(true);

		return $object;
	}

    public function preUpdate($object) {
        $this->prePersist($object);
    }

    public function prePersist($object)
    {
        $code = $object->getCode();
        if (empty($code)) {
            $code = $object->getName();
            $code = $this->getConfigurationPool()->getContainer()->get('youppers.common.service.codify')->codify($code);
            $object->setCode($code);
        }
    }
}
