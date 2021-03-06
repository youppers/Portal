<?php

namespace Youppers\ProductBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Youppers\CommonBundle\Admin\YouppersAdmin;

class AttributeOptionAdmin extends YouppersAdmin
{
	public function getExportFields()
	{
		return array(
				'id',
				'enabled',
				'type' => 'attributeStandard.attributeType.code',
				'standard' => 'attributeStandard.name',
				'position',
				'value',
				'alias',
				'symbol' => 'attributeStandard.symbol',
				'image',
		);
	}
	
	protected function configureRoutes(RouteCollection $collection)
	{
		//$collection->clearExcept(array('create','delete'));
	}

	/**
	 * @param DatagridMapper $datagridMapper
	 */
	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
		->add('value')
		->add('attributeStandard', null, array(), null, array('expanded' => false, 'multiple' => true))
		->add('enabled')
		;
	}
	
	/**
	 * Default Datagrid values
	 *
	 * @var array
	 */
	protected $datagridValues = array(
			'_sort_by' => 'value',
			'enabled' => array('value' => true)
	);
	
	public function getFilterParameters()
	{
		$parameters = parent::getFilterParameters();
		if ($this->hasRequest()) {
			$standardFilters = $this->request->query->get('s_filter', array());
			if ($standardFilters != array()) {
				$parameters['attributeStandard']['value'] = $standardFilters['attributeStandard']['value'];
			} 
		}
		return $parameters;
	}
		
	/**
	 * @param ListMapper $listMapper
	 */
	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
		->addIdentifier('valueWithSymbol')
		->add('image', null, array('template' => 'YouppersCommonBundle:CRUD:list_image.html.twig'))		
		->add('alias')
		->add('enabled', null, array('editable' => true))
		->add('position', null, array('editable' => true))			
		->add('attributeStandard.attributeType', null, array('label' => 'Attribute Type', 'route' => array('name' => 'show')))
		->add('attributeStandard', null, array('associated_property' => 'name', 'route' => array('name' => 'show')))
		;
	}

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('attributeStandard')
            ->add('position')
            ->add('value')
            ->add('image')
            ->add('alias')
            ->add('enabled')
            ->add('updatedAt')
            ->add('createdAt')
        ;
    }

	/**
	 * {@inheritdoc}
	 */
	protected function configureFormFields(FormMapper $formMapper)
	{
		if (!$this->hasParentFieldDescription()) {
			$formMapper
			->add('attributeStandard', null, array('required'  => false));				
		}
		$formMapper
		->add('position','hidden',array('attr'=>array("hidden" => true)))			
		->add('value');
        if ($this->getParentFieldDescription() == null || !$this->getParentFieldDescription()->getOption('hideOptionsImage')) {
            $formMapper
                ->add('image', 'sonata_type_model_list',
                    array(
                        'btn_add'       => $this->getParentFieldDescription() == null ? 'link_add' : false,
                        'required' => false
                    ), array(
                        'link_parameters' => array(
                            'context' => 'youppers_attribute',
                            'filter' => array('context' => array('value' => 'youppers_attribute'))
                        )
                    )
                );
        }
        $formMapper
		->add('alias', 'textarea', array('required'  => false, 'help'=>'Use ; to separate aliases','attr' => array('cols' => '40', 'rows' => '1')))
		->add('enabled', null, array('required'  => false))
		;

	}	

	/**
	 * {@inheritdoc}
	 */
	public function getNewInstance()
	{
		$object = parent::getNewInstance();
		
		$object->setPosition(1);
		$object->setEnabled(true);

		return $object;
	}
}
