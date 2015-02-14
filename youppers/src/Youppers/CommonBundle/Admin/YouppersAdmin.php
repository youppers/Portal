<?php

namespace Youppers\CommonBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AdminInterface;

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
		if ($action == 'list') $menu->addChild('Create', array('uri' => $admin->generateUrl('create')));
		if ($childAdmin || in_array($action, array('edit', 'show'))) { 
			$id = $admin->getRequest()->get('id');
			if ($action != 'show') $menu->addChild('Show', array('uri' => $admin->generateUrl('show', array('id' => $id))));		
			if ($action != 'edit') $menu->addChild('Edit', array('uri' => $admin->generateUrl('edit', array('id' => $id))));
		}
	
	}
	
	/**
	 * {@inheritdoc}
	 * getSubject in Admin is Buggy
	 * see: https://github.com/sonata-project/SonataAdminBundle/pull/2697 
	 */
	public function getSubject()
	{
		if ($this->subject === null && $this->request) {
			$id = $this->request->get($this->getIdParameter());
			$this->subject = $this->getModelManager()->find($this->getClass(), $id);
		}
	
		return $this->subject;
	}
	
	
}
