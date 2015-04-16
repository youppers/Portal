<?php
namespace Youppers\DealerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use JMS\Serializer\Serializer;

class BoxListCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('youppers:dealer:box:list')
            ->setDescription('List Boxes')
            ->addArgument('productId', InputArgument::REQUIRED, 'Product id', null)
			->addOption('storeId', null, InputOption::VALUE_OPTIONAL, 'Store Id')
			->addOption('criteriaId', null, InputOption::VALUE_OPTIONAL, 'Geoid Criteria Id')
			->addOption('group', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'seialization group',array('json','json.box.list'))
			;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $boxService = $this->getContainer()->get('youppers.dealer.service.box');
        $boxes = $boxService->listBoxes(
        		$input->getArgument('productId'), 
        		$input->getOption('storeId'),
        		$input->getOption('criteriaId'));

        $serializer = $this->getContainer()->get('serializer');        
        $serializationContext = \JMS\Serializer\SerializationContext::create();
        if (!empty($input->getOption('group'))) {
        	$serializationContext->setGroups($input->getOption('group'));        	 
        }
        $serializationContext->enableMaxDepthChecks();
        $output->writeln($serializer->serialize($boxes,'json',$serializationContext));       
    }
}