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
use Symfony\Component\Console\Question\ChoiceQuestion;

class VariantReadCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('youppers:product:variant:read')
            ->setDescription('Show a variant and search for alternatives')
            ->addOption('sessionId', 'i', InputOption::VALUE_OPTIONAL, 'Session id', null)
			->addOption('group', 'g', InputOption::VALUE_OPTIONAL, 'serialization group','json, json.variant.read')
			->addArgument('variantId',InputArgument::REQUIRED,'Current Variant Id')
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
        
        $variant = $productService->readVariant($input->getArgument('variantId'), $input->getOption('sessionId'));
        
        $output->writeln($this->serialize($variant, $input->getOption('group')));        
    }
}