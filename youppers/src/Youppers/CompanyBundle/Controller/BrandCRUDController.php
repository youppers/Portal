<?php

namespace Youppers\CompanyBundle\Controller;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;

class BrandCRUDController extends CRUDController
{
    public function cloneAction()
    {
        $id = $this->get('request')->get($this->admin->getIdParameter());

        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        $clonedObject = clone $object;  // Careful, you may need to overload the __clone method of your object
                                        // to set its id to null
        $clonedObject->setName($object->getName()." (Clone)");
        $clonedObject->setCode($object->getCode()." (Clone)");
        $clonedObject->setEnabled(false);
        $clonedObject->setCreatedAt(new \DateTime());
        
        $this->admin->create($clonedObject);

        $this->addFlash('sonata_flash_success', 'Cloned successfully');

        return new RedirectResponse($this->admin->generateUrl('edit', array('id' => $clonedObject->getId())));
    }
    	
	public function productsAction()
    {
        $id = $this->get('request')->get($this->admin->getIdParameter());
        
        return new RedirectResponse($this->generateUrl(
        		'admin_youppers_company_product_list',
        			array('filter'   => array('brand' => array('value' => $id))), true
        		));
    }
}