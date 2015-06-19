<?php
namespace Application\Sonata\MediaBundle\Command;

use Doctrine\ORM\PersistentCollection;
use Gaufrette\Exception\FileNotFound;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\Repository\RepositoryFactory;

class MediaMd5Command extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('application:media:md5')
            ->setDescription('Calculate the md5 of all he media and can optimize media usage, changing the media used by entities so to use only unique media')
            ->addOption('generate', null, InputOption::VALUE_NONE, 'Generate md5')
            ->addOption('change', null, InputOption::VALUE_NONE, 'Change media used so that only one is used')
            ->addOption('delete', null, InputOption::VALUE_NONE, 'Delete duplicated media (fail if used!)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getContainer()->get('logger');
        $generate = $input->getOption('generate');
        $change = $input->getOption('change');
        $delete = $input->getOption('delete');

        $mediaManager = $this->getContainer()->get('sonata.media.manager.media');
        $variantManager = $this->getContainer()->get('youppers.product.manager.product_variant');
        $collectionManager = $this->getContainer()->get('youppers.product.manager.product_collection');
        $optionManager = $this->getContainer()->get('youppers.product.manager.attribute_option');

        $boxManager = $this->getContainer()->get('youppers.dealer.manager.box');

        $brandManager = $this->getContainer()->get('youppers.company.manager.brand');

        $generatedHashkeys = 0;
        $hashkeys = array();
    	foreach ($mediaManager->findAll() as $media) {
            $hashkey =  $media->getHashkey();

            if (empty($hashkey) && $generate) {
                $mediaProvider = $this->getContainer()->get($media->getProviderName());
                try {
                    $hashkey = md5($mediaProvider->getReferenceFile($media)->getContent());
                    $media->setHashkey($hashkey);
                    $mediaManager->save($media);
                    $logger->debug(sprintf("Generated hashkey %s for media %s",$hashkey,$media));
                    $generatedHashkeys++;
                    if ($generatedHashkeys % 100 == 0) {
                        //$mediaManager->getObjectManager()->flush();
                    }
                } catch (FileNotFound $e) {
                    $logger->debug($media->getName() . ': file not found');
                    continue;
                }
            }

            if (empty($hashkey)) {
                continue;
            }

            if (!array_key_exists($hashkey,$hashkeys)) {
                $hashkeys[$hashkey] = $media;
                continue;
            }

            $media1 = $hashkeys[$hashkey];
            $logger->info(sprintf("Duplicated media: '%s' <-> '%s'",$media,$media1));
            $mediaProvider0 = $this->getContainer()->get($media->getProviderName());
            $mediaProvider1 = $this->getContainer()->get($media1->getProviderName());
            $filename0 = $mediaProvider0->getReferenceFile($media)->getKey();
            $filename1 = $mediaProvider1->getReferenceFile($media1)->getKey();
            $logger->debug(sprintf("Duplicated media: '%s' <-> '%s'",$filename0,$filename1));

            if (!$change) {
                continue;
            }

            if ($media->getName() !== $media1->getName()) {
                $logger->info("Dont change media whit different names");
            }

            if ($media->getGalleryHasMedias()->count() > 0) {
                // TODO
                $logger->warning("Gallery has media");
                continue;
            }
            foreach ($variantManager->findBy(array('image' => $media)) as $variant) {
                $variant->setImage($media1);
                $variantManager->save($variant);
                $logger->info("Changed image of variant " . $variant . " from " . $media . " to " .$media1);
            }
            foreach ($collectionManager->findBy(array('image' => $media)) as $collection) {
                $collection->setImage($media1);
                $collectionManager->save($collection);
                $logger->info("Changed image of collection " . $collection . " from " . $media . " to " .$media1);
            }
            foreach ($optionManager->findBy(array('image' => $media)) as $option) {
                $option->setImage($media1);
                $optionManager->save($option);
                $logger->info("Changed image of option " . $option . " from " . $media . " to " .$media1);
            }
            foreach ($boxManager->findBy(array('image' => $media)) as $box) {
                $box->setImage($media1);
                $boxManager->save($box);
                $logger->info("Changed image of box " . $box . " from " . $media . " to " .$media1);
            }
            foreach ($brandManager->findBy(array('logo' => $media)) as $brand) {
                $brand->setLogo($media1);
                $brandManager->save($brand);
                $logger->info("Changed image of brand " . $brand . " from " . $media . " to " .$media1);
            }

            if (!$delete) {
                continue;
            }
            $logger->info("Deleting media id=" . $media->getId());
            $mediaManager->delete($media);
            $mediaProvider1->preRemove($media);
    	}
    }
}
