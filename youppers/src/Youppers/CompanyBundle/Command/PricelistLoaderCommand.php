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
		->addOption('fieldseparator', 'f', InputOption::VALUE_OPTIONAL, 'Field separator',",")
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{	
		$input->validate();

		$loader = $this->getContainer()->get('youppers.company.pricelist.loader');
		
		$loader->setFs($input->getOption('fieldseparator'));
		$loader->setPricelistByCode($input->getArgument("pricelist"));

		$brand = $input->getOption('brand');
		
		if ($brand === null) {
			$output->writeln("Brand code not supllied (multi brand pricelist)");
		} else {				
			$loader->setBrand($brand);
		}
		
		$loader->load($input->getArgument('filename'),$input->getOption('skip')); 
		
	}
	
}