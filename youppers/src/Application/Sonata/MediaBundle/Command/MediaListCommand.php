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
            ->setHelp(
                <<<EOT
On the server:

1) generate the list with this command
2) copy the list to the clipboard (or pipe output to a file)

On the client:

3) copy the list to a file (media.txt) or scp/ftp from the server
4) in the root of the project, execute:
   
   wget -nv -nc -x -nH -P web -i media.txt

5) update the database from the server (phpmyadmin or zcat xxx.gz | mysql -u -p youppers)
6) update the thumbnails:

   php app/console sonata:media:sync-thumbnails
EOT
            );
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
