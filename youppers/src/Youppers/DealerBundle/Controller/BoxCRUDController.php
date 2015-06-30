<?php

namespace Youppers\DealerBundle\Controller;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class BoxCRUDController extends CRUDController
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
        $object->setName(trim(preg_replace('/([^@]+)(.*)/','$1',$object->getName())) . " @" . date('c'));
        $maxLength = $this->container->get('youppers.dealer.manager.box')->getEntityManager()->getClassMetadata(get_class($object))->getFieldMapping('code')['length'];
        $object->setCode(substr($object->getCode(),0,$maxLength-strlen(''.time())).time());
        $this->admin->update($object);
        $clonedObject->setEnabled(false);
        foreach ($object->getBoxProducts() as $boxProduct) {
        	$clonedObject->addBoxProduct(clone $boxProduct);
        }
        $clonedObject->setCreatedAt(new \DateTime());
        
        $this->admin->create($clonedObject);

        $this->addFlash('sonata_flash_success', 'Cloned successfully');

        return new RedirectResponse($this->admin->generateUrl('edit', array('id' => $clonedObject->getId())));
    }
    	
    public function enableAction()
    {
        $id = $this->get('request')->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);
        
        $this->get('youppers.common.qr')->enable($object);
        
        return $this->redirect('show');             
    }
    
	public function qrAction()
    {
        $id = $this->get('request')->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);
        
        $this->get('youppers.common.qr')->assign($object);
        
        return $this->redirect('show');         
    }
    
    public function batchActionPrint(ProxyQueryInterface $selectedModelQuery)
    {
    	$selectedBoxes = $selectedModelQuery->execute();

        $storesBoxes = array();
        foreach ($selectedBoxes as $box) {
            $store = $box->getStore();
            if (array_key_exists($store->getId(),$storesBoxes)) {
                $storesBoxes[$store->getId()]['boxes'][] = $box;
            } else {
                $storesBoxes[$store->getId()]['store'] = $store;
                $storesBoxes[$store->getId()]['boxes'] = array($box);
            }
        }

        if (count($storesBoxes) == 0) {
            $this->get('session')->getFlashBag()->add('notice','No box selected');
        } elseif (count($storesBoxes) > 1) {
            $this->get('session')->getFlashBag()->add('notice','Please select only boxes of the same store');
        } else {
            foreach ($storesBoxes as $storeBoxes) {
                $pdf = $this->container->get('youppers.common.qr')->pdfBoxes($storeBoxes['boxes']);
                $pdf->Output($storeBoxes['store'] . '.pdf', 'D');
                return;
            }
        }

        return $this->listAction();
    }
}