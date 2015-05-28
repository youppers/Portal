<?php

namespace Youppers\CommonBundle\Controller;

use FOS\OAuthServerBundle\Controller\TokenController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class YouppersOAuthTokenController extends TokenController
{

    protected $container;

    /**
     * Sets the Container associated with this Controller.
     *
     * @param ContainerInterface $container A ContainerInterface instance
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param  Request $request
     * @return type
     */
    public function tokenAction(Request $request)
    {
        $logger = $this->container->get('logger');

        $logger->info(sprintf("OAUTH Client IP: %s",$request->getClientIp()));
        $logger->info(sprintf("OAUTH Query String: %s",$request->getQueryString()));
        $logger->info(sprintf("OAUTH Request: %s",$request->getContent()));

        $response = parent::tokenAction($request);

        $logger->info(sprintf("OAUTH Response: %s",$response->getContent()));

        return $response;
    }

}
