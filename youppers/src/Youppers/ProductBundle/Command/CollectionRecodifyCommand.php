<?php
namespace Youppers\ProductBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Youppers\CommonBundle\Service\CodifyService;
use Youppers\CompanyBundle\Entity\Company;
use Youppers\CompanyBundle\Manager\CompanyManager;
use Youppers\ProductBundle\Manager\ProductCollectionManager;

class CollectionRecodifyCommand extends ContainerAwareCommand
{	
	protected function configure()
	{
		$this
			->setName('youppers:collections:recodify')
			->setDescription('Recodify collections code')
			->addArgument('company', InputArgument::REQUIRED, 'Code of the company')
			->addOption('force', 'f', InputOption::VALUE_NONE, 'Execute data update')
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

		/** @var ProductCollectionManager $collectionManager */
		$collectionManager = $this->getContainer()->get('youppers.product.manager.product_collection');

        /** @var CompanyManager $companyManager */
        $companyManager = $this->getContainer()->get('youppers.company.manager.company');

		/** @var Company $company */
		$company = $companyManager->findOneBy(array('code' => $input->getArgument("company")));

		/** @var CodifyService $codifier */
		$codifier = $this->getContainer()->get('youppers.common.service.codify');
		
		foreach ($company->getBrands() as $brand) {
			foreach ($collectionManager->findByBrand($brand) as $collection) {
				$collectionName = $collection->getName();
				$collectionCode = $collection->getCode();
				$newCollectionCode = $codifier->codify($collectionName);
				if ($collectionCode == $collectionName && $newCollectionCode != $collectionCode) {
					$output->writeln(sprintf("Changing code to '%s' of '%s'",$newCollectionCode,$collection));
					$collection->setCode($newCollectionCode);
				}
			}
		}

        if ($input->getOption('force')) {
            $collectionManager->getEntityManager()->flush();
        }

	}
	
}