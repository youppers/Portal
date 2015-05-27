<?php

namespace Youppers\CommonBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Youppers\CommonBundle\Exporter\YouppersDoctrineORMQuerySourceIterator;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;

/**
 * Common behaviors of admin:
 *   TabMenu with Show and Edit
 * @author sergio
 *
 */
abstract class YouppersAdmin extends Admin
{
	public function getDataSourceIterator()
	{
		$datagrid = $this->getDatagrid();
		$datagrid->buildPager();
		$fields = $this->getExportFields();
	
		$query = $datagrid->getQuery();
	
		$query->select('DISTINCT ' . $query->getRootAlias());
	
		if ($query instanceof ProxyQuery) {
			$query->addOrderBy($query->getSortBy(), $query->getSortOrder());
	
			$query = $query->getQuery();
		}
	
		return new YouppersDoctrineORMQuerySourceIterator($query, $fields);
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function configureTabMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
	{
		parent::configureTabMenu($menu, $action,$childAdmin);		
		/*
		dump(array(
			'action' => $action,
			'this' => $this,	
			'childAdmin' => $childAdmin,
			'isChild' => $this->isChild(),
		    'getParent' => $this->getParent(),
		    'getModelManager' => $this->getModelManager(),
			'getExportFields' => $this->getExportFields(),
		));
		*/
		if ($childAdmin) {
			$childAdmin->configureTabMenu($menu, $action);
		} else {
			if ($action == 'list' && $this->hasRoute('create') && $this->isGranted('CREATE')) $menu->addChild($this->trans('link_action_create', array(), 'SonataAdminBundle'), array('attributes' => array('icon' => 'glyphicon glyphicon-plus-sign'), 'uri' => $this->generateUrl('create')));
			if (in_array($action, array('edit', 'show'))) {
				$id = $this->getRequest()->get($this->getIdParameter());
				if ($action != 'show' && $this->hasRoute('show') && $this->isGranted('VIEW')) $menu->addChild($this->trans('link_action_show', array(), 'SonataAdminBundle'), array('attributes' => array('icon' => 'glyphicon glyphicon-eye-open'), 'uri' => $this->generateUrl('show', array('id' => $id))));
				if ($action != 'edit' && $this->hasRoute('edit') && $this->isGranted('EDIT')) $menu->addChild($this->trans('link_action_edit', array(), 'SonataAdminBundle'), array('attributes' => array('icon' => 'glyphicon glyphicon-edit'),'uri' => $this->generateUrl('edit', array('id' => $id))));
				if ($action != 'edit' && $this->hasRoute('list') && $this->isGranted('LIST')) $menu->addChild($this->trans('link_action_list', array(), 'SonataAdminBundle'), array('attributes' => array('icon' => 'glyphicon glyphicon-list'),'uri' => $this->generateUrl('list')));
			}
		}
	}
	
	/**
	 * If fieldDescription > options > disabled == true disable the link to the field admin
	 * {@inheritdoc}
	 */
	public function attachAdminClass(FieldDescriptionInterface $fieldDescription)
	{
		if (array_key_exists('disabled',$fieldDescription->getOption('route')) && $fieldDescription->getOption('route')['disabled']) {
			return;
		} else {
			return parent::attachAdminClass($fieldDescription);
		}
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function toString($object)
	{
 		if (is_object($object) && $object === $this->subject && method_exists($object, 'getName') && null !== $object->getName()) {
 			return $object->getName();
 		}
	
		return parent::toString($object);
	}
	
	/**
	 * {@inheritdoc}
	 * getSubject in Admin is Buggy
	 * see: https://github.com/sonata-project/SonataAdminBundle/pull/2697 
	 */
	public function getSubject()
	{		
		if ($this->subject === false) {
			return null;
		}
		
		if ($this->subject === null && $this->request) {
			$id = $this->request->get($this->getIdParameter());
			if (empty($id)) {
				$this->subject = false;
			} else {
				$this->subject = $this->getModelManager()->find($this->getClass(), $id);
			}
		}
	
		return $this->subject;
	}
	
}
