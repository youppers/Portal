<?php
namespace Youppers\ProductBundle\Command;

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

class VariantGuessCommand extends ContainerAwareCommand
{	
	protected function configure()
	{
		$this
		->setName('youppers:product:variant:guess')
		->setDescription('Guess product variant properties')
		->addArgument('company', InputArgument::REQUIRED, 'Code of the Company')
		->addArgument('brand', InputArgument::REQUIRED, 'Code of the Brand' )
		->addArgument('collection', InputArgument::OPTIONAL, 'Code of the Collection' )
		->addOption('skip', 'k', InputOption::VALUE_OPTIONAL, 'Skip first <n> rows', 0)
			->addOption('write', 'w', InputOption::VALUE_NONE, 'Execute data update')
		->addOption('force', 'f', InputOption::VALUE_NONE, 'Execute data update and change also exinting values')
			->addOption('type', 't', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Guess only specified attribute type')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{	
		$input->validate();

		$guesser = $this->getContainer()->get('youppers.product.variant.guesser_factory')->create($input->getArgument("company"),$input->getArgument("brand"),$input->getArgument("collection"));
		
		if ($input->getOption('write') || $input->getOption('force')) {
			if ($this->getHelper('dialog')->askConfirmation(
					$output,
					"<question>Enter 'y' to confirm execution of data update</question>",
					false
			)) {
				$guesser->setWrite($input->getOption('write'));
				$guesser->setForce($input->getOption('force'));
			}
		}

		if ($output->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG) {
			$guesser->setDebug(true);
		}

		$guesser->setTypeCodes($input->getOption('type'));

		$guesser->guess(); 
		
		$output->writeln("Done.");
	}
	
}