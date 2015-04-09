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
use Youppers\CompanyBundle\Loader\ISPricelistLoader;

class PricelistLoaderCommand extends ContainerAwareCommand
{	
	protected function configure()
	{
		$this
		->setName('youppers:pricelist:load')
		->setDescription('Load Company Pricelist')
		->addArgument('pricelist', InputArgument::REQUIRED, 'Code of the pricelist to update')
		->addArgument('filename', InputArgument::REQUIRED, 'File name to load from' )
		->addOption('skip', 'k', InputOption::VALUE_OPTIONAL, 'Skip first <n> rows', 0)
		->addOption('brand', 'b', InputOption::VALUE_OPTIONAL, 'Brand Code')
		->addOption('force', 'f', InputOption::VALUE_NONE, 'Execute data update')
		->addOption('create-product',null, InputOption::VALUE_NONE, 'Create product if dont exists')
		->addOption('create-collection',null, InputOption::VALUE_NONE, 'Create product collection if dont exists')
		->addOption('create-variant',null, InputOption::VALUE_NONE, 'Create product variant if dont exists')
		->addOption('enable', 'y', InputOption::VALUE_NONE, 'Enable created entity')
		->addOption('fieldseparator', 'fs', InputOption::VALUE_OPTIONAL, 'Field separator',",")
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{	
		//new ISPricelistLoader();
		
		$input->validate();

		$loader = $this->getContainer()->get('youppers.company.pricelist.loader_factory')->create($input->getArgument("pricelist"));
		
		$loader->setFs($input->getOption('fieldseparator'));

		$brand = $input->getOption('brand');
		
		if ($brand === null) {
			$output->writeln("Brand code not supplied (multi brand pricelist)");
		} else {				
			$loader->setBrandByCode($brand);
		}
		$loader->setForce($input->getOption('force'));
		$loader->setEnable($input->getOption('enable'));
		
		if (method_exists($loader,'setCreateProduct')) {
			$loader->setCreateProduct($input->getOption('create-product'));
		}
		if (method_exists($loader,'setCreateCollection')) {
			$loader->setCreateCollection($input->getOption('create-collection'));
		}
		if (method_exists($loader,'setCreateVariant')) {		
			$loader->setCreateVariant($input->getOption('create-variant'));
		}
		
		if ($output->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG) {
			$loader->setDebug(true);
		}
		
		$loader->load($input->getArgument('filename'),$input->getOption('skip')); 
		
	}
	
}