<?php
namespace Youppers\DealerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use JMS\Serializer\Serializer;

class PricelistExportCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('youppers:dealer:pricelist:export')
            ->setDescription('Export pricelist of the dealer')
            ->addArgument('dealer', InputArgument::REQUIRED, 'Dealer Code', null)
            ->addArgument('path', InputArgument::REQUIRED, 'Where to save the pricelist', null)
			->addOption('brand', null, InputOption::VALUE_OPTIONAL, 'Brand Code')
			;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pricelistService = $this->getContainer()->get('youppers.dealer.service.pricelist');
        $pricelistService->export(
            $input->getArgument('dealer'),
            $input->getArgument('path'),
            $input->getOption('brand')
        );
    }
}