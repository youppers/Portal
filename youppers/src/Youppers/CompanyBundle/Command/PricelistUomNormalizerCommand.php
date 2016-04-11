<?php
namespace Youppers\CompanyBundle\Command;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Youppers\CompanyBundle\Component\UomChoiceList;
use Youppers\CompanyBundle\Entity\Pricelist;
use Youppers\CompanyBundle\Manager\PricelistManager;
use Youppers\CompanyBundle\Manager\ProductPriceManager;

class PricelistUomNormalizerCommand extends ContainerAwareCommand
{	
	protected function configure()
	{
		$this
			->setName('youppers:pricelist:uom:normalize')
			->setDescription('Normalize UOM in Pricelist')
			->addArgument('pricelist', InputArgument::OPTIONAL, 'Code of the pricelist to update')
		;
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @throws \Exception
     */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$input->validate();

		/** @var Pricelist $pricelist */
		$this->normalize($input->getArgument("pricelist"),$output);

	}

	protected function normalize($pricelistcode,OutputInterface $output)
    {
        /** @var PricelistManager $pricelistManager */
        $pricelistManager = $this->getContainer()->get('youppers.company.manager.pricelist');

        /** @var ObjectManager $pricelistObjectManager */
        $pricelistObjectManager = $pricelistManager->getObjectManager();

		if (empty($pricelistcode)) {
            $criteria = array('enabled' => true);
            $pricelists = $pricelistManager->findBy($criteria);
            foreach ($pricelists as $pricelist) {
                $output->writeln(sprintf("Pricelist '%s'",$pricelist));
                $this->normalize($pricelist->getCode(),$output);
            }
		} else {
            $criteria['code'] = $pricelistcode;
            $pricelist = $pricelistManager->findOneBy($criteria);
            /** @var ProductPriceManager $productPriceManager */
            $productPriceManager = $this->getContainer()->get('youppers.company.manager.productprice');
			$prices = $productPriceManager->findBy(array('pricelist' => $pricelist));
			$i = 0;
            $iprec = 1;
			$k = 0;
			$n = count($prices);
			$output->writeln(sprintf("Pricelist '%s' normalizing %d prices",$pricelist,$n));
			foreach ($prices as $price) {
				$k++;
				$oldUom = $price->getUom();
				$newUom = UomChoiceList::normalize($oldUom);
				if ($newUom != $oldUom) {
					$price->setUom($newUom);
					$i++;
				}
				if ((($i > $iprec ) && ($i % 500) === 0) || ($k == $n)) {
                    $iprec = $i;
					$pricelistObjectManager->flush();
					$output->writeln(sprintf("Updated %d uom of %d/%d prices using %dMB",$i,$k,$n,memory_get_usage()/1048576));
				}
			}
			if ($i > 0) {
				$pricelist->setUpdatedAt(new \DateTime());
				$pricelistManager->save($pricelist);
			}
			$pricelistObjectManager->clear('ProductPrice');
		}
	}
	
}