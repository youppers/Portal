<?php

namespace Youppers\CommonBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;

/**
 * Common behaviors of admin:
 *   TabMenu with Show and Edit
 * @author sergio
 *
 */
abstract class YouppersAdmin extends Admin
{
	/**
	 * {@inheritdoc}
	 */
	protected function configureTabMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
	{
		$admin = $this->isChild() ? $this->getParent() : $this;
		if ($action == 'list' && $admin->hasRoute('create')) $menu->addChild($this->trans('link_action_create', array(), 'SonataAdminBundle'), array('uri' => $admin->generateUrl('create')));
		if ($childAdmin || in_array($action, array('edit', 'show'))) { 
			$id = $admin->getRequest()->get('id');
			if ($action != 'show' && $admin->hasRoute('show')) $menu->addChild($this->trans('link_action_show', array(), 'SonataAdminBundle'), array('uri' => $admin->generateUrl('show', array('id' => $id))));		
			if ($action != 'edit' && $admin->hasRoute('edit')) $menu->addChild($this->trans('link_action_edit', array(), 'SonataAdminBundle'), array('uri' => $admin->generateUrl('edit', array('id' => $id))));
			if ($action != 'edit' && $admin->hasRoute('list')) $menu->addChild($this->trans('link_action_list', array(), 'SonataAdminBundle'), array('uri' => $admin->generateUrl('list')));
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
