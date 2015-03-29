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
			->addOption('group', 'g', InputOption::VALUE_OPTIONAL, 'serialization group','json, json.variant.list')
			->addArgument('collectionId',InputArgument::REQUIRED,'Current Collection Id')
			->addArgument('options',InputArgument::IS_ARRAY,'Selected options')
			;
    }

    /**
     *
     * @param unknown $result
     * @param string $groups comma separated list
     * @return json
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
    	$variants = $productService->listVariants($input->getArgument('collectionId'), $input->getArgument('options'), $input->getOption('sessionId'));
    	
    	$output->writeln($this->serialize($variants, $input->getOption('group')));
   	}
}