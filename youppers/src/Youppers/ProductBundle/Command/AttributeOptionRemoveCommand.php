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
use Youppers\ProductBundle\Manager\AttributeOptionManager;
use Youppers\ProductBundle\Manager\VariantPropertyManager;

class AttributeOptionRemoveCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('youppers:product:option:remove')
            ->setDescription('Remove an Attribute Option from all the Variant that have as property')
            ->addArgument('attributeOptionId', InputArgument::REQUIRED, 'Id of the attribute Option to be removed')
			;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var AttributeOptionManager $manager */
        $optionManager = $this->getContainer()->get('youppers.product.manager.attribute_option');
        $attributeOptionId = $input->getArgument('attributeOptionId');
        $option = $optionManager->find($attributeOptionId);
        if ($option == null) {
            throw new \InvalidArgumentException('Option not found');
        }
        $logger = $this->getContainer()->get('logger');
        if ($this->getHelper('dialog')->askConfirmation(
            $output,
            sprintf("Do you want to <error>delete</error> option <question>%s</question> ?", $option),
            false
        )) {
            /** @var VariantPropertyManager $propertyManager */
            $propertyManager = $this->getContainer()->get('youppers.product.manager.variant_property');
            foreach ($propertyManager->findByOption($option) as $property) {
                $logger->debug("Delete property of " . $property->getProductVariant());
                $propertyManager->delete($property);
            }
            $logger->debug("Delete $option");
            $optionManager->delete($option,true);
        }
    }
}