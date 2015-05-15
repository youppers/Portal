<?php

namespace Youppers\ProductBundle\Controller;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ProductCollectionCRUDController extends CRUDController
{

	public function guessAction()
	{
		return $this->doGuess();
	}

	public function forceGuessAction()
	{
		return $this->doGuess(true);
	}
	
	/**
	 * @throws NotFoundHttpException
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 */
    public function doGuess($force = false)
    {
    	$collection = $this->admin->getSubject();

        $guesser = $this->get('youppers.product.variant.guesser_factory')->create(
        		$collection->getBrand()->getCompany()->getCode(),
        		$collection->getBrand()->getCode(),
        		$collection->getCode());
        
        $guesser->setForce($force);
        
        $guesser->guess();

        $flashBag = $this->admin->getRequest()->getSession()->getFlashBag();

        if (count($guesser->getTodos()) > 0) {
            $flashBag->add('sonata_flash_error',
                $this->renderView('YouppersProductBundle:ProductCollectionCRUD:guess.html.twig', array('todos' => $guesser->getTodos())));
        } else {
            $flashBag->add('sonata_flash_success','All right!');
        }
		
        return $this->showAction();    	         
    }
    	
}