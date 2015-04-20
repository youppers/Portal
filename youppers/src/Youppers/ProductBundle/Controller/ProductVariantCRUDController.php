<?php

namespace Youppers\ProductBundle\Controller;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ProductVariantCRUDController extends CRUDController
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
        $clonedObject->setProduct(null);
        $clonedObject->setEnabled(false);
        $clonedObject->setCreatedAt(new \DateTime());
        foreach ($object->getVariantProperties() as $property) {
        	$clonedProperty = clone $property;
        	$clonedProperty->setCreatedAt(new \DateTime());
        	$clonedObject->addVariantProperty($clonedProperty);
        }
        
        $this->admin->create($clonedObject);

        $this->addFlash('sonata_flash_success', 'Cloned successfully');

        return new RedirectResponse($this->admin->generateUrl('edit', array('id' => $clonedObject->getId())));
    }
    	
}