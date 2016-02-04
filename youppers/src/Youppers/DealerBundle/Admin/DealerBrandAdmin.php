<?php

namespace Youppers\DealerBundle\Admin;

use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Symfony\Component\Validator\Constraints as Assert;
use Youppers\CommonBundle\Admin\YouppersAdmin;

class DealerBrandAdmin extends YouppersAdmin
{
	/**
	 * {@inheritdoc}
	 */
	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
			->add('dealer', null, array(
				'route' => array(
					'name' => 'show'
				)
			))
			->add('enabled', null, array('editable' => true))
			->add('brand', null, array(
				'route' => array(
					'name' => 'show'
				)
			))
			->addIdentifier('code', null, array('editable' => true))
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
            ->add('dealer.code')
            ->add('enabled')
			->add('brand.code')
		;
	}

    /**
     * {@inheritdoc}
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('dealer')
            ->add('enabled')
            ->add('brand')
            ->add('code')
        ;
    }

	/**
	 * {@inheritdoc}
	 */
	protected function configureFormFields(FormMapper $formMapper)
	{
		if (!$this->hasParentFieldDescription()) {
			$formMapper
				->add('dealer', 'sonata_type_model_list');
		}
		$formMapper
			->add('enabled', null, array('required'  => false))
			->add('brand', 'sonata_type_model_list', array('btn_add' => false))
            ->add('code')
		;
	}

}
