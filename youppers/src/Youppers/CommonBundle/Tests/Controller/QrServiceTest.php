<?php
namespace Youppers\ProductBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use Youppers\CommonBundle\Tests\JsonTestCase;
use Youppers\CustomerBundle\Entity\Session;

class QrServiceTest extends JsonTestCase
{
	
	private $sessionId = '4633f7ac-f1c1-11e4-b845-0800273000da';

	public function testQrFindNew()
	{
		$text = 'http://www.produttore.it/link/' . mt_rand();
		$response = $this->makeHttpMethodRequest('Qr.find',array('text' => $text, 'sessionId' => $this->sessionId));
		//dump($response); die;
		$this->assertGreaterThan(
	    	0,
	    	count($response['result'])	
		);
	}

    public function testQrMultiple()
    {
        $text = 'http://demo.youppers.com/qr/f036a016-a892-11e4-b4aa-0cc47a127a14';
        $response = $this->makeHttpMethodRequest('Qr.find',array('text' => $text, 'sessionId' => $this->sessionId));
        //dump($response); die;
        $this->assertGreaterThan(
            0,
            count($response['result'])
        );
    }


}