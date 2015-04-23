<?php
namespace Youppers\ScraperBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ScraperCommand extends ContainerAwareCommand
{	
	protected function configure()
	{
		$this
		->setName('youppers:scraper')
		->setDescription('Scrape company brand site')
		->addArgument('company', InputArgument::REQUIRED, 'Code of the Company')
		->addArgument('brand', InputArgument::REQUIRED, 'Code of the Brand')
		->addOption('force', 'f', InputOption::VALUE_NONE, 'Execute data update')
		->addOption('create-product',null, InputOption::VALUE_NONE, 'Create product if dont exists')
		->addOption('create-collection',null, InputOption::VALUE_NONE, 'Create product collection if dont exists')
		->addOption('create-variant',null, InputOption::VALUE_NONE, 'Create product variant if dont exists')
		->addOption('enable', 'y', InputOption::VALUE_NONE, 'Enable created entity')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{			
		$input->validate();

		$scraper = $this->getContainer()->get('youppers.scraper.factory')->create($input->getArgument("company"),$input->getArgument("brand"));
		
		$scraper->setForce($input->getOption('force'));
		$scraper->setEnable($input->getOption('enable'));
		if ($output->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG) {
			$scraper->setDebug(true);
		}
		
		if (method_exists($scraper,'setCreateProduct')) {
			$scraper->setCreateProduct($input->getOption('create-product'));
		}
		if (method_exists($scraper,'setCreateCollection')) {
			$scraper->setCreateCollection($input->getOption('create-collection'));
		}
		if (method_exists($scraper,'setCreateVariant')) {		
			$scraper->setCreateVariant($input->getOption('create-variant'));
		}

		$scraper->scrape();
	}
	
}