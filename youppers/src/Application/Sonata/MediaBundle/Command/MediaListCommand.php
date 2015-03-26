<?php
namespace Application\Sonata\MediaBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\Repository\RepositoryFactory;

class MediaListCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('application:media:list')
            ->setDescription('List the reference url of all the media that are in Media repository')
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	$repo = $this->getContainer()->get('doctrine')->getRepository('ApplicationSonataMediaBundle:Media');
    	foreach ($repo->findAll() as $media) {
	    	$mediaProvider = $this->getContainer()->get($media->getProviderName());
	    	$url = $mediaProvider->generatePublicUrl($media, 'reference');
	    	$output->writeln($url);
    	}
    }
}