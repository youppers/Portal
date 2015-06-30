<?php

namespace Youppers\CompanyBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Youppers\CommonBundle\Admin\YouppersAdmin;

#use Sonata\Bundle\DemoBundle\Entity\Inspection;

class CompanyAdmin extends YouppersAdmin
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
		->add('enabled')
		->add('description')
		->add('url')
		->add('logo', null,array('label' => 'Company Logo', 'template' => 'YouppersCommonBundle:CRUD:show_image.html.twig'))
		->add('createdAt')
		->add('updatedAt')
		->add('brands', null, array(
                 'route' => array(
                     'name' => 'show'
                 ),
				'associated_property' => 'name'
             ))
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
		->add('logo', null, array('label' => 'Company Logo', 'template' => 'YouppersCommonBundle:CRUD:list_image.html.twig'))
		->add('brands', null, array('associated_property' => 'name'))
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
		->with('Company', array('class' => 'col-md-8'))
		->add('name')
		->add('code')		
		->add('description')
		->add('url', null, array('required' => false))
		->add('logo', 'sonata_type_model_list', array(
					'required' => false
				), array(
					'link_parameters' => array(
						'context'  => 'youppers_company_logo',
						'filter'   => array('context' => array('value' => 'youppers_company_logo')),
						'provider' => ''
					)
				)
			)
		->end()
		->with('Details', array('class' => 'col-md-4'))
        ->add('org', 'sonata_type_model_list')
		->add('enabled', 'checkbox', array('required'  => false))
		->end()
		->with('Brands', array('class' => 'col-md-12'))
		->add('brands', 'sonata_type_collection', array(
				'by_reference'       => false,
				'cascade_validation' => true,
		) , array(
				'edit' => 'inline',
				'inline' => 'table'
		))
		->end()
		
		/*
		->with('Options', array('class' => 'col-md-6'))
		->add('engine', 'sonata_type_model_list')
		->add('color', 'sonata_type_model_list')
		->end()
		->with('inspections', array('class' => 'col-md-12'))
		->add('inspections', 'sonata_type_collection', array(
				'by_reference'       => false,
				'cascade_validation' => true,	
		) , array(
				'edit' => 'inline',
				'inline' => 'table'
		))
		->end()
		*/
		;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getNewInstance()
	{
		$object = parent::getNewInstance();
		
		//$object->setCreatedAt(new \DateTime());
		$object->setEnabled(true);

		/*
		$inspection = new Inspection();
		$inspection->setDate(new \DateTime());
		$inspection->setComment("Initial inpection");

		$object->addInspection($inspection);
		*/
		return $object;
	}
}
