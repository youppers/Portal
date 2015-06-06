<?php

namespace Youppers\CustomerBundle\Controller;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SessionCRUDController extends CRUDController
{
    public function itemsAction()
    {
        $id = $this->get('request')->get($this->admin->getIdParameter());

        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        return new RedirectResponse($this->admin->generateUrl('item', array('id' => $object->getId())));
    }

    public function emailAction()
    {
        $id = $this->get('request')->get($this->admin->getIdParameter());

        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        $flashBag = $this->admin->getRequest()->getSession()->getFlashBag();

        $result = $this->get('youppers.customer.service.session')->send($id);

        try {
            $res = explode("\r\n\r\n",$result);
            $flashBag->add('sonata_flash_success',nl2br($res[0])); // show headers
        } catch (\Exception $e) {
            $flashBag->add('sonata_flash_error',$e->getMessage());
        }

        return $this->showAction();
    }
}