<?php
namespace Youppers\CommonBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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

	private $rootCategory;
	
	protected function configure()
	{
		$this
		->setName('youppers:catalogue:ics')
		->setDescription('Update catalogue with ICS classification')
		->addArgument('base', InputArgument::OPTIONAL, 'ISO Standards catalogue uri',$this->icsBaseUri)
		->addOption('update', null, InputOption::VALUE_NONE, 'Update the database')
		->addOption('root', null, InputOption::VALUE_REQUIRED, 'Root category','ICS')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$root = $input->getOption("root");
		$icsBaseUri = $input->getArgument('base');
		$output->writeln("Root=".$root." Get ICS from: " . $icsBaseUri);

		
		$this->em = $this->getContainer()->get('doctrine')->getManager('default');
		$this->rootCategory == $this->em->getRepository('Application\Sonata\ClassificationBundle\Entity\Category')->findOneBy(array('name' => $root));
		
		if ($this->rootCategory == null) {
			throw new \Exception("Unable to find root category: " . $root);
		}
		
		$this->client = new Client();
		
		$this->crawl($icsBaseUri, $output);
		
		$output->writeln("Done.");
	}

	private function crawl($uri, OutputInterface $output, $parent = "") {
		$crawler = $this->client->request('GET', $uri);
		$numRows = $crawler->filterXPath('//*[@id="ics_list"]/tr')->count();
		for ($i =1; $i <= $numRows; $i++) {
			$numTd = $crawler->filterXPath('//*[@id="ics_list"]/tr[' . $i . ']/td')->count();
			if ($numTd != 2) {
				break;
			}				
			$a = $crawler->filterXPath('//*[@id="ics_list"]/tr[' . $i . ']/td[1]/a');
			if ($a->count() == 1) {
				$ics = explode('.',trim($a->text()));
				$link = $a->link();
			} else {
				$ics = explode('.',trim($crawler->filterXPath('//*[@id="ics_list"]/tr[' . $i . ']/td[1]')->text()));
				$link = null;
			}
			$description = trim($crawler->filterXPath('//*[@id="ics_list"]/tr[' . $i . ']/td[2]')->html());
			$output->writeln($parent . ' Row ' . $i . ' ICS=' . implode('.',$ics) . ' Field=' . $description);
			
			$this->save($ics,$description);
			
			if ($link && count($ics) < 3) {
				$this->crawl($link->getUri(), $output,implode('.',$ics));
			}			
		}
	}
	
	private function save($ics,$description) {
	}	
}