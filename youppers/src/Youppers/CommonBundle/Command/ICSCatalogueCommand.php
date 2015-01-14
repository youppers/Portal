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
	
	protected function configure()
	{
		$this
		->setName('youppers:catalogue:ics')
		->setDescription('Update catalogue with ICS classification')
		//->addArgument('name', InputArgument::OPTIONAL, 'Who do you want to greet?')
		->addOption('update', null, InputOption::VALUE_NONE, 'Update the database')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln("Get ICS from: " . $this->icsBaseUri);
		
		$this->client = new Client();
		
		$this->crawl0($this->icsBaseUri, $output);
		
		/*
		$name = $input->getArgument('name');
		if ($name) {
			$text = 'Hello '.$name;
		} else {
			$text = 'Hello';
		}

		if ($input->getOption('update')) {
			$text = strtoupper($text);
		}

		*/
		
		$output->writeln("Ok");
	}
	
	private function crawl0($uri, OutputInterface $output) {
		$crawler = $this->client->request('GET', $uri);

		//$output->writeln(" [0] crawler:" . $crawler->filterXPath($this->icsRowsXpath)->html());
				
		foreach ($crawler->filterXPath($this->icsRowsXpath) as $trNode) {
			/*
			foreach ($trNode->childNodes as $node) {
				$output->writeln(" [0]  Child:" . trim($node->ownerDocument->saveXML($node)));
			}
			
			$output->writeln(" [0]  Child 0:" . trim($trNode->ownerDocument->saveXML($trNode->childNodes->item(0))));
			var_dump($trNode->ownerDocument->saveXML($trNode->childNodes->item(1)));
			$output->writeln(" [0]  Child 1:" . trim($trNode->ownerDocument->saveXML($trNode->childNodes->item(1))));
			$output->writeln(" [0]  Child 2:" . trim($trNode->ownerDocument->saveXML($trNode->childNodes->item(2))));
				
			*/
			
			if ($trNode->childNodes->length > 3) {
				break;
			}				
			//$output->writeln("row:" . $trNode);
			$TD1 = $trNode->childNodes->item(0);
			if ($TD1->hasChildNodes()) {
				$ANode = $TD1->childNodes->item(1);
				$id1 = trim($ANode->textContent);
			} else {
				$ANode = null;
				$id1 = trim($TD1->textContent);
			}
			$fieldNode = $trNode->childNodes->item(2);
			$name = trim($fieldNode->textContent);				
			$html = trim($fieldNode->ownerDocument->saveXML($fieldNode));
			
			$output->writeln("ID1:" . $id1 . " Name:" . $name . " HTML:".$html);

			if ($ANode != null) {
				$suburi = $ANode->baseURI . $ANode->getAttribute('href');
				$this->crawl1($suburi,$id1,$output);
			}

			//break;
		}				
	}

	private function crawl1($uri, $id1, OutputInterface $output) {
		$crawler = $this->client->request('GET', $uri);
	
		//$output->writeln(" [1] crawler:" . $crawler->filterXPath($this->icsRowsXpath)->html());
	
		foreach ($crawler->filterXPath($this->icsRowsXpath) as $trNode) {
			
			/*
			foreach ($trNode->childNodes as $node) {
				$output->writeln(" [1]  Child:" . trim($node->ownerDocument->saveXML($node)));
			}
			
			$output->writeln(" [1]  Child 0:" . trim($trNode->ownerDocument->saveXML($trNode->childNodes->item(0))));
			var_dump($trNode->ownerDocument->saveXML($trNode->childNodes->item(1)));
			$output->writeln(" [1]  Child 1:" . trim($trNode->ownerDocument->saveXML($trNode->childNodes->item(1))));
			$output->writeln(" [1]  Child 2:" . trim($trNode->ownerDocument->saveXML($trNode->childNodes->item(2))));
			*/
			
			/*
			if ($trNode->childNodes->length > 3) {
				break;
			}
			*/
			
			//$output->writeln("row:" . $trNode);
			$ANode = $trNode->childNodes->item(0)->childNodes->item(1);
			//var_dump($ANode);
			$fieldNode = $trNode->childNodes->item(2);
			//$suburi = $ANode->baseURI . $ANode->getAttribute('href');
			//$this->crawl1($suburi,$output);

			$TD1 = $trNode->childNodes->item(0);
			if ($TD1->childNodes->length == 1) {
				$ANode = null;
				$id2 = trim($TD1->textContent);
			} else {
				$ANode = $TD1->childNodes->item(1);
				$id2 = trim($ANode->textContent);
			}
			$fieldNode = $trNode->childNodes->item(2);
			$name = trim($fieldNode->textContent);
			$html = trim($fieldNode->ownerDocument->saveXML($trNode));				

			$output->writeln("ID1:" . $id1 . " ID2:" . $id2 . " Name:" . $name . " HTML:".$html);
			//$output->writeln("ID1:" . $id1 . " ID2:" . $id2);

			if ($ANode != null) {
				$suburi = $ANode->baseURI . $ANode->getAttribute('href');
				$this->crawl2($suburi,$id1,$id2,$output);
			}
			
			//break;
				
		}
	}

	private function crawl2($uri, $id1, $id2, OutputInterface $output) {
		$crawler = $this->client->request('GET', $uri);
	
		//$output->writeln("crawler:" . $crawler->filterXPath('//*[@id="ics_list"]/tr')->html());
	
		foreach ($crawler->filterXPath($this->icsRowsXpath) as $trNode) {
			
			/*
			foreach ($trNode->childNodes as $node) {
				$output->writeln(" [2]  Child:" . trim($node->ownerDocument->saveXML($node)));
			}
			
			$output->writeln(" [2]  Child 0:" . trim($trNode->ownerDocument->saveXML($trNode->childNodes->item(0))));
			//var_dump($trNode->childNodes->item(1));
			$output->writeln(" [2]  Child 1:" . trim($trNode->ownerDocument->saveXML($trNode->childNodes->item(1))));
			$output->writeln(" [2]  Child 2:" . trim($trNode->ownerDocument->saveXML($trNode->childNodes->item(2))));
			*/
			
			//var_dump($trNode->childNodes);
			
			if (get_class($trNode->childNodes->item(1)) ==  "DOMElement") {
				//$output->writeln("BREAK");
				break;
			}
		
			$TD1 = $trNode->childNodes->item(0);
			if ($TD1->childNodes->length == 1) {
				$ANode = null;
				$id3 = trim($TD1->textContent);
			} else {
				$ANode = $TD1->childNodes->item(1);
				$id3 = trim($ANode->textContent);
			}
			$fieldNode = $trNode->childNodes->item(2);
			$name = trim($fieldNode->textContent);
			$html = trim($fieldNode->ownerDocument->saveXML($fieldNode));
			//$html = trim($fieldNode->ownerDocument->saveXML($trNode));
							
			$output->writeln("ID1:" . $id1 . " ID2:" . $id2 . " ID3:" . $id3 . " Name:" . $name . " HTML:".$html);
			//$output->writeln("ID1:" . $id1 . " ID2:" . $id2 . " ID3:" . $id3);
				
		}
	
	}
	
}