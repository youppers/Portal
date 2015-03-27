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

class ProductSearchCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('youppers:product:search')
            ->setDescription('List products')
            ->addOption('sessionId', 'i', InputOption::VALUE_OPTIONAL, 'Session id', null)
			->addOption('group', 'g', InputOption::VALUE_OPTIONAL, 'serialization group','json, json.product.list')
			->addArgument('query',InputArgument::IS_ARRAY,'Query string')
			;
    }

    /**
     * 
     * @param unknown $result
     * @param string $groups comma separated list
     */
    protected function serialize($result,$groups)
    {
    	$serializer = $this->getContainer()->get('serializer');
    	$serializationContext = SerializationContext::create();
    	$serializationContext->setGroups(array_map('trim',explode(',',$groups)));
    	$serializationContext->enableMaxDepthChecks();
    	return $serializer->serialize($result,'json',$serializationContext);    	 
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $productService = $this->getContainer()->get('youppers.product.service.product');
        $products = $productService->searchProducts(implode(' ',$input->getArgument('query')));
		
        $output->writeln($this->serialize($products, $input->getOption('group')));
    }
}