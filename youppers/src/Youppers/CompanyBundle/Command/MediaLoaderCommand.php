<?php
namespace Youppers\CompanyBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MediaLoaderCommand extends ContainerAwareCommand
{	
	protected function configure()
	{
		$this
            ->setName('youppers:media:load')
            ->setDescription('Load media (product images) supplied by the Company')
            ->addArgument('company', InputArgument::REQUIRED, 'Code of the company to load')
            ->addArgument('filename', InputArgument::REQUIRED, 'File name to load from' )
            ->addOption('type', null, InputOption::VALUE_OPTIONAL, 'Media type')
            ->addOption('prefix', null, InputOption::VALUE_OPTIONAL, 'Prefix of the URI / path of the image', './')
            ->addOption('skip', 'k', InputOption::VALUE_OPTIONAL, 'Skip first <n> rows', 0)
		    ->addOption('brand', 'b', InputOption::VALUE_OPTIONAL, 'Brand Code')
		    ->addOption('force', 'f', InputOption::VALUE_NONE, 'Execute data update')
    		->addOption('fieldseparator', 'fs', InputOption::VALUE_OPTIONAL, 'Field separator',";")
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{	
		$input->validate();

		$loader = $this->getContainer()->get('youppers.company.loader.media_loader_factory')->create($input->getArgument("company"));

		$loader->setFs($input->getOption('fieldseparator'));

		$brand = $input->getOption('brand');
		
		if ($brand === null) {
			$output->writeln("Brand code not supplied (multi brand pricelist)");
		} else {				
			$loader->setBrandByCode($brand);
		}
		$loader->setForce($input->getOption('force'));
		$loader->setPrefix($input->getOption('prefix'));
        $loader->setType($input->getOption('type'));
		
        //$loader->setCreateProduct($input->getOption('create-product'));

		if ($output->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG) {
			$loader->setDebug(true);
		}
		
		$loader->load($input->getArgument('filename'),$input->getOption('skip')); 
		
	}
	
}