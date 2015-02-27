<?php

namespace Youppers\CustomerBundle\Controller\Api;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\View;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use JMS\Serializer\SerializationContext;

use Sonata\DatagridBundle\Pager\PagerInterface;

use Sonata\CoreBundle\Form\FormHelper;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Youppers\CustomerBundle\Model\SessionManagerInterface;

class SessionController
{
    /**
     * @var SessionManagerInterface
     */
    protected $sessionManager;
    
    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;
        
    /**
     * Constructor
     *
     * @param sessionManagerInterface $sessionManager
     * @param FormFactoryInterface     $formFactory
     */
    public function __construct(SessionManagerInterface $sessionManager, FormFactoryInterface $formFactory)
    {
        $this->sessionManager = $sessionManager;
    	$this->formFactory = $formFactory;
    }

    
    /**
     * Start a session
     *
     * @ApiDoc(
     *  input={"class"="Youppers\CustomerBundle\Form\SessionType", "groups"={"sonata_api_write"}},
     *  output={"class"="Youppers\CustomerBundle\Entity\Session", "groups"={"sonata_api_read"}},
     *  statusCodes={
     *      200="Returned when successful",
     *      400="Returned when an error has occurred while session creation"
     *  }
     * )
     *
     * @param Request $request A Symfony request
     *
     * @return Session
     *
     * @throws NotFoundHttpException
     */
    public function postSessionAction(Request $request)
    {
    	return $this->handleStartSession($request);
    }
    
    /**
     * Start a session
     *
     * @ApiDoc(
     *  input={"class"="Youppers\CustomerBundle\Form\SessionType", "name"="session", "groups"={"sonata_api_write"}},
     *  output={"class"="Youppers\CustomerBundle\Entity\Session", "groups"={"sonata_api_read"}},
     *  statusCodes={
     *      200="Returned when successful",
     *      400="Returned when an error has occurred while session creation"
     *  }
     * )
     *
     * @param Request $request A Symfony request
     *
     * @return Session
     *
     * @throws NotFoundHttpException
     */
    public function getSessionAction(Request $request)
    {
    	return $this->handleStartSession($request);
    }
    
        
    /**
     * Start a session
     *
     * @param Request      $request Symfony request
     *
     * @return \FOS\RestBundle\View\View|FormInterface
     */    
    protected function handleStartSession($request)
    {
    	$session = $this->sessionManager->create();
    	$this->sessionManager->save($session);
    	return $session;
    }
    
    /**
     * Write a session, this method is used by both POST and PUT action methods
     *
     * @param Request      $request Symfony request
     * @param string|null $id      A session identifier
     *
     * @return \FOS\RestBundle\View\View|FormInterface
     */
    protected function handleWriteSession($request, $id = null)
    {
    	$session = $id ? $this->getSession($id) : null;
    
    	$form = $this->formFactory->createNamed(null, 'youppers_customer_api_form_session', $session, array(
    			'csrf_protection' => false
    	));
    
    	FormHelper::removeFields($request->request->all(), $form);
    
    	$form->bind($request);
    
    	if ($form->isValid()) {
    		$session = $form->getData();
    		$this->sessionManager->save($session);
    
    		$view = \FOS\RestBundle\View\View::create($session);
    		$serializationContext = SerializationContext::create();
    		$serializationContext->setGroups(array('sonata_api_read'));
    		$serializationContext->enableMaxDepthChecks();
    		$view->setSerializationContext($serializationContext);
    
    		return $view;
    	}
    
    	return $form;
    }    
}