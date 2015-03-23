<?php
namespace Youppers\CommonBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use JMS\Serializer\Serializer;
use Youppers\CommonBundle\Entity\Qr;

class QrFindCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('youppers:common:qr:find')
            ->setDescription('Search QR and dump content')
			->addArgument('text', InputArgument::REQUIRED, 'QR id')
			->addArgument('group', InputArgument::OPTIONAL, 'seialization group')
			;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $qrService = $this->getContainer()->get('youppers.common.qr');
        $qr = $qrService->find($input->getArgument('text'), null);

        //$qr->getTargets()->first()->getBoxProducts()->first()->getProduct()->getVariant()->getPdfGallery()->getName();

/*        
        $qr->getTargets()->first()
        	->getBoxProducts()->first()
        	->getProduct()
        	->getVariant()
        	->getPdfGallery()
        	->getGalleryHasMedias()->first()
        	->getMedia()->getName();
*/
/*        
        dump($qr->getTargets()->first()
        ->getBoxProducts()->first()
        ->getProduct()
        ->getVariant()
        ->getPdfGallery()
        ->getGalleryHasMedias()->first()
        ->getMedia()->getProviderReference()
        ); 
        die;
*/        
        

                
        $serializer = $this->getContainer()->get('serializer');        
        $serializationContext = \JMS\Serializer\SerializationContext::create();
        if (!empty($input->getArgument('group'))) {
        	$serializationContext->setGroups(array($input->getArgument('group')));        	 
        }
        $serializationContext->enableMaxDepthChecks();
        $output->writeln($serializer->serialize($qr,'json',$serializationContext));
        
    }
}