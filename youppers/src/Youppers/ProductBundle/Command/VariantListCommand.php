<?php
namespace Youppers\ProductBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use JMS\Serializer\Serializer;
use Youppers\CommonBundle\Entity\Qr;
use JMS\Serializer\SerializationContext;

class VariantListCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('youppers:product:variant:list')
            ->setDescription('List product variants')
            ->addOption('sessionId', 'i', InputOption::VALUE_OPTIONAL, 'Session id', null)
			->addOption('group', 'g', InputOption::VALUE_OPTIONAL, 'serialization group','json.variant.list')
			->addArgument('query',InputArgument::IS_ARRAY,'Query string')
			;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $productService = $this->getContainer()->get('youppers.product.service.product');
        $products = $productService->listVariants(implode(' ',$input->getArgument('query')));
		
        $serializer = $this->getContainer()->get('serializer');        
        $serializationContext = SerializationContext::create();
        $serializationContext->setGroups(array($input->getOption('group')));        	 
        $serializationContext->enableMaxDepthChecks();
        $output->writeln($serializer->serialize($products,'json',$serializationContext));        
    }
}