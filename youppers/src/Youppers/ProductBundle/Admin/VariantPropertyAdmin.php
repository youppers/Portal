<?php

namespace Youppers\ProductBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Symfony\Component\Validator\Constraints as Assert;
use Sonata\AdminBundle\Route\RouteCollection;

class VariantPropertyAdmin extends Admin
{
	protected function configureRoutes(RouteCollection $collection)
	{
		$collection->clearExcept(array('create','delete'));
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function configureFormFields(FormMapper $formMapper)
	{
		$formMapper
		//->add('enabled', null, array('required'  => false))
		->add('position','hidden',array('attr'=>array("hidden" => true)))			
		->add('attributeOption', 'sonata_type_model_list', array(
				'btn_delete'       => false,
				'required' => false, 'constraints' => new Assert\NotNull()),
				array('link_parameters' => array(
						's_filter'   => array('attributeStandard' => array('value' => $this->getStandards()))
					)
				)
			)
		;
	}
	
	private $standards = null;
	
	private function getStandards()
	{
		$logger = $this->getConfigurationPool()->getContainer()->get('logger');		

		if ($this->standards) {
			return $this->standards;
		}
		$standards = array();
						
		if ($variantPropertiesField = $this->getParentFieldDescription()) {
			if ($productVariantAdmin = $variantPropertiesField->getAdmin()) {
				if ($variant = $productVariantAdmin->getSubject()) {
					$logger->debug("VPA: getStandards variant=" . $variant);
					if ($collection = $variant->getProductCollection()) {
						foreach ($collection->getStandards() as $standard) {
							$standards[] = $standard->getId();
						}
					} else {						
						$logger->warning("VPA: getStandards variant->getProductCollection() NULL");
						// FIXME da gestire altri casi
					}
				}
			}
		}
		
		$logger->debug("VPA: getStandards=".implode(',',$standards));
		
		$this->standards = $standards;
		
		return $standards;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function getNewInstance()
	{
		$object = parent::getNewInstance();
		
		$object->setPosition(1);

		return $object;
	}
}
