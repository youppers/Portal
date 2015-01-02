<?php

namespace Youppers\CompanyBundle\Controller;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CRUDController extends Controller
{
public function productsAction()
    {
        $id = $this->get('request')->get($this->admin->getIdParameter());
        
        return new RedirectResponse($this->generateUrl(
        		'admin_youppers_company_product_list',
        			array('filter'   => array('brand' => array('value' => $id))), true
        		));
    }
}