<?php

namespace Youppers\DealerBundle\Controller;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class StoreCRUDController extends CRUDController
{
    public function qrprintAction()
    {
        $id = $this->get('request')->get($this->admin->getIdParameter());

        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        $pdf = $this->container->get('youppers.common.qr')->pdfBoxesStore($object);

        $pdf->Output($object . '.pdf', 'D');

        //return new RedirectResponse($this->generateUrl('youppers_dealer_box_list', array('store' => $object->getId())));
    }

}