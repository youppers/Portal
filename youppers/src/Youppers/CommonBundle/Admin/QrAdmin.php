<?php

namespace Youppers\CommonBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Youppers\CommonBundle\Admin\YouppersAdmin;
use Symfony\Component\Validator\Constraints as Assert;

class QrAdmin extends YouppersAdmin
{

    private $targets;

    public function setSubject($subject) {
        parent::setSubject($subject);
        $this->targets = array();
        foreach ($this->getFormFieldDescriptions() as $field) {
            $mapping = $field->getAssociationMapping();
            if ($mapping) {
                $targets = (call_user_func(array($subject,'get' . ucfirst($field->getName()))));
                $this->targets[$field->getName()] = $targets? clone $targets : null;
            }
        }
        //dump($this->targets);
    }

    public function prePersist($org) {
        return $this->preUpdate($org);
    }

    public function preUpdate($qr)
    {
        foreach ($this->getFormFieldDescriptions() as $field) {
            $mapping = $field->getAssociationMapping();
            if ($mapping) {
                $newTargets = call_user_func(array($qr,'get' . ucfirst($field->getName())));
                foreach ($this->targets[$field->getName()] as $target) {
                    if (!$newTargets->contains($target)) {
                        $target->setQr(null);
                    }
                }
                foreach ($newTargets as $target) {
                    $target->setQr($qr);
                }
            }
        }
    }

    /**
	 * {@inheritdoc}
	 */
	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
		->addIdentifier('text', 'text', array('route' => array('name' => 'show')))
		->add('targetType')
		->add('products')
		->add('boxes')
		->add('enabled')
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
		->add('url')
        ->add('products.code')
        ->add('boxes.code')
        ->add('medias.name')
		->add('targetType', 'doctrine_orm_choice', array(), 'choice', array('choices' => $this->getTargetTypeChoices()))
		->add('enabled')
		;
	}

    /**
	 * {@inheritdoc}
	 */
	protected function configureShowFields(ShowMapper $showMapper)
	{
		$showMapper
		->add('url')
		->add('targetType', 'text')
		->add('enabled')
		;

        if ($targetsField = $this->getTargetsField()) {
            $showMapper
                ->add($targetsField)
            ;
        };

		$showMapper
        ->add('text', null, array('label' => 'QRCode', 'route' => array('name' => 'youppers_common_qr_redirecttotarget'), 'template' => 'YouppersCommonBundle:CRUD:qr.html.twig'))
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
			->add('url')
			->add('targetType', 'choice', array('choices' => $this->getTargetTypeChoices()))
			->add('enabled', null, array('required' => false))
		;

		if (($this->hasRequest() && $this->getRequest()->isXmlHttpRequest())) {
 			$targetsFields = array('products','boxes','medias');
		} elseif ($targetsField = $this->getTargetsField()) {
            $targetsFields = array($targetsField);
        } else {
            $targetsFields = array();
        }
        foreach ($targetsFields as $targetsField) {
            $formMapper
                ->add($targetsField, 'sonata_type_model_autocomplete',
                    array(
                        'property' => 'name',
                        'placeholder' => 'Search using the name of the target',
                        'attr' => array('style' => 'width: 100%;'),
                        'multiple' => true,
                        'required' => false,
                        'by_reference' => false)
                );
        }
	}

    function getTargetsField() {
        $targets = $this->getConfigurationPool()->getContainer()->getParameter('youppers_common.qr');
        if ($targetType = $this->subject->getTargetType()) {
            $targetEntity = $targets[$this->subject->getTargetType()]['entity'];
            $entityManager = $this->getConfigurationPool()->getContainer()->get('doctrine')->getManagerForClass($targetEntity);

            $mapping = $entityManager->getClassMetadata($targetEntity)->getAssociationMapping('qr');
            return $mapping['inversedBy'];
        }
    }


    function getTargetTypeChoices() {
        $choices = array();
        foreach ($this->getConfigurationPool()->getContainer()->getParameter('youppers_common.qr') as $targetType => $options) {
            $choices[$targetType] = $options['name'] ? : $targetType;
        }
        return $choices;
    }

}
