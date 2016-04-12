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
			->addArgument('pricelist', InputArgument::IS_ARRAY, 'Codes of the pricelist to update')
		;
	}

    /** @var OutputInterface  */
    private $output;

    /** @var PricelistManager $pricelistManager */
    private $pricelistManager;

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @throws \Exception
     */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$input->validate();
        $this->output = $output;

        $this->pricelistManager = $this->getContainer()->get('youppers.company.manager.pricelist');


        $codes = $input->getArgument("pricelist");
        if (count($codes) == 0) {
            $this->normalizeAll();
        } else {
            foreach ($codes as $code) {
                $this->normalize($code);
            }
        }
	}

    protected function normalizeAll()
    {
        $pricelists = $this->pricelistManager->findBy(array('enabled' => true));
        $codes = array();
        foreach ($pricelists as $pricelist) {
            $this->output->writeln(sprintf("Pricelist '%s'",$pricelist));
            $codes[] = $pricelist->getCode();
        }
        $this->pricelistManager->getObjectManager()->clear();
        foreach ($codes as $code) {
            $this->normalize($code);
        }
    }

	protected function normalize($pricelistcode)
    {
        $pricelist = $this->pricelistManager->findOneBy(array('code' => $pricelistcode));
        /** @var ProductPriceManager $productPriceManager */
        $productPriceManager = $this->getContainer()->get('youppers.company.manager.productprice');
        $prices = $productPriceManager->findBy(array('pricelist' => $pricelist));
        $i = 0;
        $iprec = 1;
        $k = 0;
        $n = count($prices);
        $this->output->writeln(sprintf("Pricelist '%s' normalizing %d prices",$pricelist,$n));
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
                $this->pricelistManager->getObjectManager()->flush();
                $this->output->writeln(sprintf("Updated %d uom of %d/%d prices using %dMB",$i,$k,$n,memory_get_usage()/1048576));
            }
        }
        if ($i > 0) {
            $pricelist->setUpdatedAt(new \DateTime());
            $this->pricelistManager->save($pricelist);
        }
        $this->pricelistManager->getObjectManager()->clear();
	}
	
}