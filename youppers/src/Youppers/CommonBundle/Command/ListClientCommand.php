<?php
namespace Youppers\CommonBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;


class ListClientCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('youppers:oauth-server:client:list')
            ->setDescription('List clients')
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $clientClass = $this->getContainer()->get('fos_oauth_server.client_manager')->getClass();
        $clientManager = $this->getContainer()->get('doctrine')->getManagerForClass($clientClass);
        $clientRepository = $clientManager->getRepository($clientClass);
        $clientMetadata = $clientManager->getClassMetadata($clientClass);
        $clientFields = $clientMetadata->getFieldNames();

        $clients = $clientRepository->findAll();
        
        $table = new Table($output);        
        $table->setHeaders(array_merge($clientFields,array('publicId')));
        
        foreach ($clients as $client) {
        	$row = array('publicId' => $client->getPublicId());
        	foreach ($clientFields as $field) {
        		$value = call_user_func_array(array($client, 'get' . $field),array());
        		if (is_string($value)) {
        			$row[$field] = $value;
        		} elseif (is_array($value)) {
        			$row[$field] = implode(', ',$value);
        		} else {
        			$row[$field] = print_r($value,true);
        		}   		
        	}        	
        	$table->addRow($row);
        }
        $table->render();
        
    }
}