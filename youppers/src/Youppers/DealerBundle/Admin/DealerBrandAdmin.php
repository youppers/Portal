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
			->add('code', null, array('identifier' => true, 'code' => 'getDefaultCode'))
			->add('brand', null, array(
				'route' => array(
					'name' => 'show'
				)
			))
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
            ->add('enabled')
			->add('dealer')
			->add('code')
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
			->add('code')
            ->add('brand')
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
			->add('code', null, array('attr' => array('size' => 5)))
			->add('brand', 'sonata_type_model_list', array('btn_add' => false))
		;
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
