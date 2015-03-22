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

use Ddeboer\DataImport\Reader\CsvReader;
use Youppers\CompanyBundle\Entity\Product;
use Youppers\CommonBundle\Entity\Geoid;

class GeoidLoaderCommand extends ContainerAwareCommand
{	
	protected function configure()
	{
		$this
		->setName('youppers:geoid:load')
		->setDescription('Load Geoid Criteria (https://developers.google.com/analytics/devguides/collection/protocol/v1/geoid)')
		->addArgument('filename', InputArgument::REQUIRED, 'File name to load from' )
		->addOption('fieldseparator', 'f', InputOption::VALUE_OPTIONAL, 'Field separator',",")
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{	
		$input->validate();
		
		$fs = $input->getOption('fieldseparator');
		
		$this->em = $this->getContainer()->get('doctrine')->getManager();

		$file = new \SplFileObject($input->getArgument('filename'));
		$reader = new CsvReader($file, $fs);
		
		$numRows = 0;
		$numCreated = 0;
		$numUpdated = 0;
		
		$reader->setHeaderRowNumber(0);
		
		$geoidRepository = $this->em->getRepository('Youppers\CommonBundle\Entity\Geoid');		
		
		// TODO disable all geoid before loading

		foreach ($reader as $row) {
			$numRows++;
			
			$geoid = $geoidRepository->findOneBy(array('criteriaId' => $row['Criteria ID']));
			
			if (null === $geoid) {
				$geoid = new Geoid();
				$this->em->persist($geoid);
				$geoid->setCriteriaId($row['Criteria ID']);
				$numCreated++;
			} else {
				$numUpdated++;
			}			 
			$parent = $geoidRepository->findOneBy(array('criteriaId' => $row['Parent ID']));						
			$geoid
				->setName($row['Name'])
				->setCanonicalName($row['Canonical Name'])
				->setParent($parent)
				->setCountryCode($row['Country Code'])
				->setTargetType($row['Target Type'])
				->setStatus($row['Status'])				
				->setEnabled(true)
			;
							
			if ($numRows % 1000 == 0) {
				$output->write("Rows=" . $numRows . " Updated=" . $numUpdated . " Created=" . $numCreated . "\r");
				$this->em->flush();
				$this->em->clear();
			}
				
		};
				
		$output->writeln("\nDone, " . $numRows . " rows.");

		$this->em->flush();
		
	}
	
}