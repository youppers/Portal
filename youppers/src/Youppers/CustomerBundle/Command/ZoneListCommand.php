<?php
namespace Youppers\CustomerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use JMS\Serializer\Serializer;
use Youppers\CommonBundle\Entity\Qr;

class ZoneListCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('youppers:customer:zone:list')
            ->setDescription('List zones')
            ->addArgument('sessionId', InputArgument::REQUIRED, 'Session id', null)
			->addArgument('group', InputArgument::OPTIONAL, 'seialization group','json.zone.list')
			;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $zoneService = $this->getContainer()->get('youppers.customer.zone');
        $zones = $zoneService->listForSession($input->getArgument('sessionId'));

        $serializer = $this->getContainer()->get('serializer');        
        $serializationContext = \JMS\Serializer\SerializationContext::create();
        if (!empty($input->getArgument('group'))) {
        	$serializationContext->setGroups(array($input->getArgument('group')));        	 
        }
        $serializationContext->enableMaxDepthChecks();
        $output->writeln($serializer->serialize($zones,'json',$serializationContext));
        
    }
}