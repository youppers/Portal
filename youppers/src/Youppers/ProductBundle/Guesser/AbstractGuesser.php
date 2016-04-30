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
			return $this->parent->addTodo($todo);
		} else if (!in_array($todo,$this->todos)) {
			$this->todos[] = $todo;
		}
		$this->getLogger()->info($todo);
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

	/**
	 * @return LoggerInterface
	 */
	public function getLogger()
	{
		if ($this->parent) {
			return $this->parent->getLogger();
		} else {
			return $this->logger;
		}
	}

	/** @var bool Write guesses */
	private $write = false;

	/**
	 * @param bool $write Write guesses
	 */
	public function setWrite($write) {
		$this->write = $write;
	}

	/**
	 * @return bool Execute data update
	 */
	public function getWrite() {
		if ($this->parent) {
			return $this->parent->getWrite();
		} else {
			return $this->write || $this->force;
		}
	}

	private $force = false;
	
	public function setForce($force) {
		$this->force = $force;
	}

	/**
	 * @return bool Execute data update and change also exinting values
	 */
	public function getForce() {
		if ($this->parent) {
			return $this->parent->getForce();
		} else {
			return $this->force;
		}		
	}

	private $debug = false;

	public function getDebug()
	{
		if ($this->parent) {
			return $this->parent->getDebug();
		} else {
			return $this->debug;
		}
	}

	public function setDebug($debug)
	{
		$this->debug = $debug;
	}

}
