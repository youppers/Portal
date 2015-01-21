<?php
namespace Youppers\CommonBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Application\Sonata\ClassificationBundle\Entity\Category;

use Goutte\Client;
use Symfony\Component\DomCrawler\Link;

class ICSCatalogueCommand extends ContainerAwareCommand
{
	
	protected $icsBaseUri = 'http://www.iso.org/iso/home/store/catalogue_ics/catalogue_ics_browse.htm';
	
	protected $icsRowsXpath = '//*[@id="ics_list"]/tr';
	
	protected $icsAXpath = 'td[1]/a';
	
	protected $icsFieldXpath = 'td[2]';
	
	private $client;
	
	private $em;

	private $update;
	
	private $enable;
	
	private $rootName;
	
	protected function configure()
	{
		$this
		->setName('youppers:catalogue:ics')
		->setDescription('Update catalogue with ICS classification')
		->addArgument('base', InputArgument::OPTIONAL, 'ISO Standards catalogue uri',$this->icsBaseUri)
		->addOption('update', null, InputOption::VALUE_NONE, 'Update the database')
		->addOption('root', null, InputOption::VALUE_REQUIRED, 'Root category name','ICS')
		->addOption('create', null, InputOption::VALUE_NONE, 'Create the root category if not found')
		->addOption('enable', null, InputOption::VALUE_NONE, 'Enable categories')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->rootName = $input->getOption("root");
		$icsBaseUri = $input->getArgument('base');
		$output->writeln("Root='".$this->rootName."' Get ICS from: " . $icsBaseUri);

		$this->update = $input->getOption('update');
		$this->enable = $input->getOption('update');
		
		$this->em = $this->getContainer()->get('doctrine')->getManager();
		$rootCategory = $this->em->getRepository('Application\Sonata\ClassificationBundle\Entity\Category')
			->findOneBy(array('name' => $this->rootName));

		if ($rootCategory == null && $input->getOption('create')) {
			$rootCategory = new Category();
			$rootCategory->setName($this->rootName);
			$rootCategory->setDescription('International Classification for Standards');
			$rootCategory->setEnabled($this->enable);
			$this->em->persist($rootCategory);
			$this->em->flush();
			$output->writeln("Created root category with id='".$rootCategory->getId()."'");
		}
		
		if ($rootCategory && $this->update) {
			$rootCategory->setDescription('International Classification for Standards');
			$this->em->flush();
		}
						
		if ($rootCategory == null) {
			throw new \Exception("Unable to find root category: '" . $this->rootName . "'");
		}
		
		$output->writeln("Using root category with id='".$rootCategory->getId()."'");		

		$this->client = new Client();
		
		$this->crawl($icsBaseUri, $output, $rootCategory);
				
		$output->writeln("Done.");
	}

	private function crawl($uri, OutputInterface $output, Category $parentCategory) {
		$crawler = $this->client->request('GET', $uri);
		$numRows = $crawler->filterXPath('//*[@id="ics_list"]/tr')->count();

		$parentCategoryChildren = $parentCategory->getChildren();
		
		for ($i =1; $i <= $numRows; $i++) {
			$numTd = $crawler->filterXPath('//*[@id="ics_list"]/tr[' . $i . ']/td')->count();
			if ($numTd != 2) {
				break;
			}				
			$a = $crawler->filterXPath('//*[@id="ics_list"]/tr[' . $i . ']/td[1]/a');
			if ($a->count() == 1) {
				$ics = trim($a->text());
				$link = $a->link();
			} else {
				$ics = trim($crawler->filterXPath('//*[@id="ics_list"]/tr[' . $i . ']/td[1]')->text());
				$link = null;
			}
			$description = $parentCategory->getDescription() . '<br>' . trim($crawler->filterXPath('//*[@id="ics_list"]/tr[' . $i . ']/td[2]')->html());
				
			$currentIcs = $this->rootName . '.' . $ics;

			$output->writeln($currentIcs);
				
			$currentCategory = null;
				
			if ($parentCategoryChildren) {
				$filteredParentCategoryChildren = $parentCategoryChildren->filter(
						function($category) use ($currentIcs) {
							return $category->getName() == $currentIcs;
						}
					);
			}
									
			if ($parentCategoryChildren == null || $filteredParentCategoryChildren->count() == 0) {
				$currentCategory = new Category();
				$currentCategory->setName($currentIcs);
				$currentCategory->setDescription($description);
				$currentCategory->setEnabled($this->enable);
				$currentCategory->setPosition($i);
				$this->em->persist($currentCategory);
				$parentCategory->addChild($currentCategory);
			} else if ($filteredParentCategoryChildren->count() == 1) {
				$currentCategory = $filteredParentCategoryChildren->first();
				if ($this->update) {
					if ($currentCategory->getDescription() != $description) {			
						$currentCategory->setDescription($description);
					}
					if ($this->enable && !$currentCategory->getEnabled() ) {
						$currentCategory->setEnabled(true);
					}
					if ($currentCategory->getPosition() != $i) {			
						$currentCategory->setPosition($i);
					}
				} 
			} else {
				throw new \Exception("More than one category with name '"  . $ics . "'");
			}
			
			if ($link && count(explode('.',$ics)) < 3) {
				$this->crawl($link->getUri(), $output, $currentCategory);
			}			
		}
		
		$this->em->flush();
		
	}
	
}