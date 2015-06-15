<?php
namespace Youppers\CompanyBundle\Scraper\MZ;

use Youppers\CompanyBundle\Scraper\BaseScraper;

class Scraper extends BaseScraper
{

	protected function _scrapeCollections()
	{
	
	return
		array (
/*	  558 => 'Altai',
	  564 => 'Arauca',
	  445 => 'Asturias',
	  446 => 'Atlante',
	  3 => 'Autonomy',
	  448 => 'Bits',
	  4 => 'Black&White',
	  592 => 'Blend',
	  591 => 'Block',
	  571 => 'Boise',
	  552 => 'Brooklyn',
	  643 => 'Burlington',
	  5 => 'Caracalla',
	  691 => 'Clays',
	  439 => 'Colorup',
	  580 => 'Colourline',
	  461 => 'Concret',
	  395 => 'Concreta',
	  123 => 'Cotto Antico',
	  540 => 'Covent Garden',
	  675 => 'Cover',
	  269 => 'Cult',
	  563 => 'Denver',
	  689 => 'Diamond',
	  137 => 'Dinastie',
	  10 => 'Dots',
	  559 => 'Dressy',
	  11 => 'Easy',
	  609 => 'Enjoy',
	  324 => 'Etruria',
	  390 => 'Evolutionmarble',
	  696 => 'Evolutionmarble Rivestimento',
	  12 => 'Evolutionstone',
	  565 => 'Fiesta',
	  16 => 'Folk',
	  418 => 'Fontanarosa',
	  471 => 'Forum',
	  472 => 'Fresh',
	  611 => 'Gala',
	  140 => 'Garden',
	  475 => 'Glass',
	  55 => 'Gm',
	  22 => 'Habitat',
	  601 => 'Horizon',
	  677 => 'Imperfetto',
	  26 => 'Iside',
	  480 => 'Kasbah',
	  520 => 'Lander',
	  481 => 'Latina',
	  438 => 'Lite',
	  455 => 'Lithos',
	  484 => 'Loma',
	  631 => 'Lord',
	  572 => 'Mabira',
	  375 => 'Maison',
	  579 => 'Marbleline',
	  165 => 'Match',
	  421 => 'Memories',
	  488 => 'Mercury',
	  638 => 'Midtown',
	  632 => 'Minimal',
	  32 => 'Monolith',
	  566 => 'Montreal',
	  424 => 'Multiquartz',
	  585 => 'Multiquartz20',
	  682 => 'Mystone - Gris Fleury',
	  680 => 'Mystone - Pietra Di Vals',
	  679 => 'Mystone - Pietra Italia',
	  683 => 'Mystone - Silver Stone',
	  35 => 'Naturalstone',
	  686 => 'Nordic Wood',
	  493 => 'Nova',
	  542 => 'Nuance',
	  157 => 'Oceani',
	  551 => 'Oficina7',
	  690 => 'Onix',
	  561 => 'Oregon',
	  567 => 'Oxford',
	  494 => 'Oxistone',
	  569 => 'Perseo',
	  160 => 'Pietra Del Sole',
	  535 => 'Pietra di noto',
	  634 => 'Pietra Occitana',
	  544 => 'Planet',
	  497 => 'Platea',
	  40 => 'Polis',
	  444 => 'Progetto Triennale',
	  166 => 'Progress',
	  130 => 'Ricordi',
	  500 => 'Royale Palace',
	  560 => 'Serpal',
	  44 => 'SistemA',
	  537 => 'SistemB',
	  2 => 'SistemC - Architettura',
	  6 => 'SistemC - Città',
	  161 => 'SistemC - Quarz',
	  587 => 'SistemE',
	  435 => 'SistemL',
	  379 => 'SistemN',
	  588 => 'SistemN20',
	  28 => 'SistemT - Cromie',
	  57 => 'SistemT - Graniti',
	  73 => 'SistemT - Kaleidos',
	  8 => 'SistemV - Crystal Mosaic',
	  555 => 'SistemV - Glass Mosaic',
	  45 => 'Soho',
	  456 => 'Soul',
	  56 => 'Space',
	  272 => 'Spazio',
	  144 => 'Spezie',
	  46 => 'Stardust',
	  47 => 'Stone-collection',
	  48 => 'Stonehenge',
	  385 => 'Stonevision',
	  584 => 'Stonework',
	  507 => 'Storm',
	  509 => 'Style',
	  425 => 'Suite',
	  457 => 'Sunny',
	  573 => 'Tacto',
	  630 => 'Tailor',
	  458 => 'Target',
	  514 => 'Terrano',
	  516 => 'Titan',
*/	  
	  50 => 'Treverk',	  
	  548 => 'Treverkatelier',
	  589 => 'Treverkchic',
	  641 => 'Treverkever',
	  431 => 'Treverkhome',
/*
 	  604 => 'Treverkhome20',
	  590 => 'Treverkmood',
	  441 => 'Treverk Outdoor',
	  432 => 'Treverksign',
	  557 => 'Treverkway',
	  562 => 'Verano',
	  53 => 'Vertical',
	  423 => 'Weekend',
	  694 => 'XLstone',
	  451 => 'Zenith',
*/
	);
	}

	protected function scrapeCollections()
	{
		$collections = array();
	
		$uri = 'http://www.marazzi.it/it/ceramica-e-gres/cerca-i-prodotti/';
		$this->logger->info("Crawling " . $uri); 
		$crawler = $this->client->request('GET', $uri);
	
		$crawler = $crawler->filter('#slccolle');
	
		foreach ($crawler->children() as $optionNode) {
			$value = $optionNode->getAttribute('value');
			$text = $optionNode->firstChild->textContent;
			if (!empty($value)) {
				$collection = $this->getCollection($text);
				if (!empty($collection)) {
					$collections[$value] = $collection;
					//$uri = 'http://www.marazzi.it/it/ceramica-e-gres/collezioni/search-minimal-project/?slccolle=' . $value;
					$uri = 'http://www.marazzi.it/it/ceramica-e-gres/collezioni/search-minimal-project/?slccolori=&slcispirazione=&slcformati=&slctipologia=&slccaratteristica=&slcutilizzo=&slccolle=' . $value;
					$articles = array();
					while ($uri) {
						$this->logger->info("Crawling " . $uri); 
						$crawler = $this->client->request('GET', $uri);
						foreach ($crawler->filterXPath('//*[@id="RicercaItemsList"]/div/div/ul/li')->children() as $articleDOMElement) {
							$article = array();
							$article['img']['src'] = $articleDOMElement->childNodes->item(0)->childNodes->item(1)->getAttribute('src');
							foreach ($articleDOMElement->childNodes as $k => $v) {
								$article['t'][$k][][$v->nodeType] = $v->nodeValue;
							}
							/*
							$x = $articleDOMElement->childNodes->item(1)
														->childNodes->item(2)
														->childNodes->item(0)->textContent;
							*/
							dump($article);
							die;
							//dump($article); die;
							//dump($articleCrawler->filter('div.contenitordata > div:nth-child(1)'));
							
							/*
							//dump($articleElement);
							
							foreach ($articleElement->getElementsByTagName('div') as $divElement) {
								//dump($divElement->textContent);
								//dump($divElement);
								foreach ($divElement->getElementsByTagName('div') as $divElement2) {
									//dump($divElement2);
									foreach ($divElement2->childNodes as $n3) {
										dump($n3);
									}
								}
							}
							
							dump($article);


												die;
							*/
							//$divNodes = $productNode->getElementsByTagName('div');
							
							//$img = $productNodediv.imggrid > img
							//dump($productNode); 
						}		
						$nextPage = $crawler->filter('#RicercaItemsList > div > div > ul > div.endless_container > a');
						if (count($nextPage)) {
							$uri = $nextPage->link()->getUri();
						} else {
							$uri = '';
						}
					}					
				}
			}
		}
	
		//if ($this->debug) var_export($collections);
	
		return $collections;
	}
	
	
}
