<?php

namespace Youppers\ProductBundle\Guesser;

use Doctrine\Common\Collections\Criteria;
use Ddeboer\DataImport\Reader\CsvReader;
use Doctrine\Common\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Monolog\Logger;

abstract class AbstractGuesser extends ContainerAware
{

	private $todos = array();
	
	private $parent = null; // Parent Guesser
	
	protected function setParent(AbstractGuesser $parent)
	{
		$this->parent = $parent;
		$this->setManagerRegistry($parent->getManagerRegistry());
	}
	
	public function getTodos()
	{
		return $this->todos;
	}
	
	protected function addTodo($todo)
	{
		if ($this->parent) {
			$this->parent->addTodo($todo);
		} else if (!in_array($todo,$this->todos)) {
			$this->todos[] = $todo;
		}
	}
			
	private $managerRegistry;
	private $em;

	public function setManagerRegistry(ManagerRegistry $managerRegistry)
	{
		$this->managerRegistry = $managerRegistry;
		$this->em = $managerRegistry->getManager();
	}

	public function getManagerRegistry()
	{
		if ($this->parent) {
			return $this->parent->getManagerRegistry();
		} else {
			return $this->managerRegistry;
		}
	}
	
	private $logger;
	
	public function setLogger(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}
	
	public function getLogger()
	{
		if ($this->parent) {
			return $this->parent->getLogger();
		} else {
			return $this->logger;
		}
	}

	private $force = false;
	
	public function setForce($force) {
		$this->force = $force;
	}
	
	public function getForce() {
		if ($this->parent) {
			return $this->parent->getForce();
		} else {
			return $this->force;
		}		
	}

	protected $debug = false;
	
	public function setDebug($debug)
	{
		$this->debug = $debug;
	}

}
