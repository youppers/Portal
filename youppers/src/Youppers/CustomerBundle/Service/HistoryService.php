<?php
namespace Youppers\CustomerBundle\Service;

use Symfony\Component\DependencyInjection\ContainerAware;
use Youppers\CustomerBundle\Entity\Session;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Sonata\CoreBundle\Form\FormHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Symfony\Component\Form\Form;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use FOS\UserBundle\Model\User;
use Youppers\CustomerBundle\Entity\Profile;
use Youppers\CustomerBundle\Entity\HistoryQrBox;
use Youppers\CustomerBundle\Entity\HistoryQrVariant;
use Youppers\CustomerBundle\Entity\HistoryShow;
use Youppers\CustomerBundle\Entity\HistoryAdd;
use Youppers\CustomerBundle\Entity\HistoryRemove;
use Youppers\ProductBundle\Entity\ProductVariant;
use Youppers\DealerBundle\Entity\Box;
use Youppers\CustomerBundle\Entity\Item;
use Youppers\CustomerBundle\Entity\History;
use Youppers\CustomerBundle\Manager\HistoryManager;


class HistoryService extends ContainerAware
{
	private $managerRegistry;
	private $logger;
	private $tokenStorage = null;
	private $historyManager;
	
	public function __construct(ManagerRegistry $managerRegistry, LoggerInterface $logger)
	{
		$this->managerRegistry = $managerRegistry;
		$this->logger = $logger;
		$this->historyManager = new HistoryManager($managerRegistry);
	}	
	
	/**
	 * Used to set user as current authenticated user
	 *
	 * @param TokenStorageInterface $tokenStorage
	 */
	public function setTokenStorage(TokenStorageInterface $tokenStorage)
	{
		$this->tokenStorage = $tokenStorage;
	}
	
	
	/**
	 * Save History Event Record
	 * @param History $history
	 * @deprecated
	 */
	protected function save(History $history)
	{
		$em = $this->managerRegistry->getManagerForClass(get_class($history));
		$em->persist($history);
		$em->flush();
	}
	
	/**
	 * Record History Event
	 * @param Box $box
	 * @param Session $session
	 */
	public function newHistoryQrBox(Box $box,Session $session = null)
	{
		$history = new HistoryQrBox();
		$history->setSession($session);
		$history->setBox($box);
		$this->historyManager->save($history);
	}

	/**
	 * Record History Event
	 * @param ProductVariant $variant
	 * @param Session $session
	 */
	public function newHistoryQrVariant(ProductVariant $variant,Session $session = null)
	{
		$history = new HistoryQrVariant();
		$history->setSession($session);
		$history->setVariant($variant);
		$this->historyManager->save($history);
	}

	/**
	 * Record History Event
	 * @param ProductVariant $variant
	 * @param Session $session
	 */
	public function newHistoryShow(ProductVariant $variant,Session $session = null)
	{
		$history = new HistoryShow();
		$history->setSession($session);
		$history->setVariant($variant);
		$this->historyManager->save($history);
	}
		
	/**
	 * Record History Event
	 * @param Item $item
	 * @param Session $session
	 */
	public function newHistoryItemAdd(Item $item)
	{
		$history = new HistoryAdd();
		$history->setSession($item->getSession());
		$history->setItem($item);
		$this->historyManager->save($history);
	}

	/**
	 * Record History Event
	 * @param Item $item
	 * @param Session $session
	 */
	public function newHistoryItemRemove(Item $item)
	{
		$history = new HistoryRemove();
		$history->setSession($item->getSession());
		$history->setItem($item);
		$this->historyManager->save($history);
	}

	public function historyList(Session $session)
	{
		return $this->historyManager->findBy(array('session' => $session),array('createdAt' => 'DESC'));
	}
	
	private function getSession($sessionId)
	{
		if ($sessionId) {
			$session = $this->container->get('youppers.customer.manager.session')->find($sessionId);
			if ($session === null) {
				$this->logger->error(sprintf("Session '%s' not found",$sessionId));
				throw new \Exception("Invalid sessionId ".$sessionId);
			}
			return $session;
		}
		throw new \Exception("Session not specified");
	}
	
	public function listForSession($sessionId)
	{
		return $this->historyList($this->getSession($sessionId));	
	}
	
}
