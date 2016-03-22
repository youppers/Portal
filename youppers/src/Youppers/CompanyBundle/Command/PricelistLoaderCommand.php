<?php
namespace Youppers\CompanyBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Application\Sonata\ClassificationBundle\Entity\Category;

use Goutte\Client;
use Symfony\Component\DomCrawler\Link;
use Doctrine\Common\Collections\Criteria;

use Ddeboer\DataImport\Reader\CsvReader;
use Youppers\CompanyBundle\Entity\Product;
use Youppers\CompanyBundle\Loader\AbstractLoader;
use Youppers\CompanyBundle\Loader\AbstractPricelistLoader;

class PricelistLoaderCommand extends ContainerAwareCommand
{	
	protected function configure()
	{
		$this
		->setName('youppers:pricelist:load')
		->setDescription('Load Company Pricelist')
		->addArgument('pricelist', InputArgument::REQUIRED, 'Code of the pricelist to update')
		->addArgument('filename', InputArgument::REQUIRED, 'File name to load from' )
		->addOption('append', 'a', InputOption::VALUE_NONE, 'Append to existing pricelist (default: delete all prices')
		->addOption('skip', 'k', InputOption::VALUE_OPTIONAL, 'Skip first <n> rows', 0)
		->addOption('brand', 'b', InputOption::VALUE_OPTIONAL, 'Brand Code')
		->addOption('force', 'f', InputOption::VALUE_NONE, 'Execute data update')
		->addOption('change-collection', null, InputOption::VALUE_NONE, 'Change product collection')
		->addOption('enable', 'y', InputOption::VALUE_NONE, 'Enable created entity')
		->addOption('load-product', 'p', InputOption::VALUE_NONE, 'Load Collection and Variant of the Product')
		->addOption('guess', null, InputOption::VALUE_NONE, 'Guess properties of variant')
		->addOption('fieldseparator', 'fs', InputOption::VALUE_OPTIONAL, 'Field separator',";")
		;
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @throws \Exception
     */
	protected function execute(InputInterface $input, OutputInterface $output)
	{	
		$input->validate();

		/** @var AbstractPricelistLoader $loader */
		$loader = $this->getContainer()->get('youppers.company.pricelist.loader_factory')->create($input->getArgument("pricelist"));
		
		$loader->setFs($input->getOption('fieldseparator'));

		$brand = $input->getOption('brand');
		
		if ($brand === null) {
			$output->writeln("Brand code not supplied (multi brand pricelist)");
		} else {				
			$loader->setBrandByCode($brand);
		}
		$loader->setAppend($input->getOption('append'));
		$loader->setForce($input->getOption('force'));
		$loader->setEnable($input->getOption('enable'));
		$loader->setLoadProduct($input->getOption('load-product'));
		$loader->setGuess($input->getOption('guess'));
		$loader->setChangeCollection($input->getOption('change-collection'));


		if ($output->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG) {
			$loader->setDebug(true);
		}
		
		$loader->load($input->getArgument('filename'),$input->getOption('skip')); 
		
	}
	
}