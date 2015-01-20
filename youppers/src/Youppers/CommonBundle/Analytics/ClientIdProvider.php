<?php

namespace Youppers\CommonBundle\Analytics;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session;
use Happyr\Google\AnalyticsBundle\Service as Happyr;

/**
 * Class ClientIdProvider
 *
 * @author Sergio Strampelli
 *
 */
class ClientIdProvider extends Happyr\ClientIdProvider
{
	const SESSION_CLIENTID_NAME = '_ga_ClientId';
	
    /**
     * @var \Symfony\Component\HttpFoundation\Request request
     */
    private $request;

    /**
     * @param Request $request
     */
    public function setRequest(Request $request = null)
    {
        $this->request = $request;
    }

    /**
     * Get client id from cookie... if we can
     *
     *
     * @return false|string
     */
    public function getClientId()
    {
        if (false === $clientId = $this->getClientIdFormCookie()) {
        	$clientId = $this->getClientIdFormSession();
        }
        return $clientId;
    }

    private function getClientIdFormSession() {
    	$session = $this->request->getSession();
    	if ($clientId = $session->get(self::SESSION_CLIENTID_NAME)) {
    		return $clientId;
    	} else {
    	/*
    	 * We could not find any cookie with a client id. We just have to randomize one
    	 */
    		$clientId = mt_rand(10, 1000) . round(microtime(true));
    		$session->set(self::SESSION_CLIENTID_NAME,$clientId);
    		return $clientId;
    	}
    }
 }