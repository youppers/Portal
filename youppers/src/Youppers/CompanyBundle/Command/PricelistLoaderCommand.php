<?php
namespace Youppers\CompanyBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Application\Sonata\ClassificationBundle\Entity\Category;

use Goutte\Client;
use Symfony\Component\DomCrawler\Link;
use Doctrine\Common\Collections\Criteria;

use Ddeboer\DataImport\Reader\CsvReader;
use Youppers\CompanyBundle\Entity\Product;

class PricelistLoaderCommand extends ContainerAwareCommand
{	
	protected function configure()
	{
		$this
		->setName('youppers:pricelist:load')
		->setDescription('Load Company Pricelist')
		->addArgument('filename', InputArgument::REQUIRED, 'File name to load from' )
		->addOption('company', 'c', InputOption::VALUE_REQUIRED, 'Company Code')
		->addOption('brand', 'b', InputOption::VALUE_REQUIRED, 'Brand Code')
		->addOption('fieldseparator', 'f', InputOption::VALUE_OPTIONAL, 'Field separator',",")
		//->addOption('file', null, InputOption::VALUE_REQUIRED, 'File name to load from')
		->addOption('force', null, InputOption::VALUE_NONE, 'Update the database')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{	
		$input->validate();
		
		$fs = $input->getOption('fieldseparator');
		
		$this->em = $this->getContainer()->get('doctrine')->getManager();
		$company = $this->em->getRepository('Youppers\CompanyBundle\Entity\Company')
			->findOneBy(array('code' => $input->getOption("company")));

		$output->writeln("Company: " . $company);
		
		$criteria = Criteria::create()
			->where(Criteria::expr()->eq("code", $input->getOption("brand")));
		
		$brand = $company->getBrands()->matching($criteria)->first();
		
		$output->writeln("Brand: " . $brand);

		$file = new \SplFileObject($input->getArgument('filename'));
		$reader = new CsvReader($file, $fs);
		
		$numRows = 0;
		
		$reader->setHeaderRowNumber(0);
		
		$elcount = array();
		
		$criteria = Criteria::create();
		
		$products = $brand->getProducts();

		$productRepository = $this->em->getRepository('Youppers\CompanyBundle\Entity\Product');		
		
		foreach ($reader as $row) {
			// do something here.
			// for example, insert $row to database.
			$numRows++;
			$elements = explode('-',$row['CDS Italia']);
			foreach ($elements as $k => $element) {
				$element = trim($element);
				if (array_key_exists($k,$elcount)) {
					if (array_key_exists($element,$elcount[$k])) {
						$elcount[$k][$element]++;
					} else {
						$elcount[$k][$element]=1;
					} 				
				} else {
					$elcount[$k][$element]=1;
				}
			}

			//$output->writeln(" Searching ". $row['Material Code']);

			//$criteria->where(Criteria::expr()->eq("code", $row['Material Code']));
			
			//var_export($criteria);
			
			//$product = $products->matching($criteria)->first();
			//$product = $products->filter('code' => $row['Material Code'])->first();
			
			$product = $productRepository->findOneBy(array('brand' => $brand, 'code' => $row['Material Code']));
			
			//$output->writeln("Product: " . $product);

			if ($product) {
				//var_export($product);
			} else {
				if ($elements[1] == 'IS') {
					$product = (new Product())
						->setBrand($brand)
						->setCode($row['Material Code'])
						->setEnabled(true)
						->setName($row['Description']);
					$this->em->persist($product);
					$output->writeln("Adding product ". $row['Material Code']);
				} else {
					$output->writeln("Not found product ". $row['Material Code']);
				}				
			}
			
		};
				
		$output->writeln("Done, " . $numRows . " rows.");

		var_export($elcount);
		
		$this->em->flush();
		
	}
	
}