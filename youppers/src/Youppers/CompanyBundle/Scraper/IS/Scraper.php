<?php
namespace Youppers\CompanyBundle\Scraper\IS;

use Youppers\CompanyBundle\Scraper\BaseScraper;
use Youppers\ProductBundle\Entity\ProductVariant;

use Symfony\Component\DomCrawler\Link;
use Youppers\CompanyBundle\Scraper\ImgLink;

class Scraper extends BaseScraper
{

    protected function doVariantScrape(ProductVariant $variant)
    {
        $code = $variant->getProduct()->getCode();
        $parameters = array(
            'tx_indexedsearch' => array(
                'sword' => substr($code,0,-2)
            )
        );

        $searchcrawler = $this->client->request('POST', 'http://www.idealstandard.it/search.html?no_cache=1', $parameters);

        $links = $searchcrawler->filter('div.tx-indexedsearch-res > ul > li > div > h3 > a')->links();

        foreach ($links as $link) {
            $uri = $link->getUri();
            $productcrawler = $this->client->request('GET',$uri);

            $product = array();

            $product['uri'] = $uri;

            foreach ($productcrawler->filterXPath('//*[@id="productImg"]/div/div/img') as $imgNode) {
                $product['images'][] = (new ImgLink($imgNode,$uri))->getUri();
            }
            $product['title'] = $productcrawler->filterXPath('//*[@id="productText"]/div/div[1]/div/h1')->html();

            foreach (preg_split("/,/",$productcrawler->filterXPath('//*[@id="productText"]/div/div[1]/div/p[1]/span')->text()) as $productcode) {
                $productcode = trim($productcode);
                if (!empty($productcode)) {
                    $product['codes'][] = $productcode;
                }
            }

            $product['description'] = $productcrawler->filterXPath('//*[@id="productText"]/div/div[1]/div/div[1]')->text();

            foreach ($productcrawler->filterXPath('//*[@id="productImg"]/div/div/div/ul/li/a') as $aNode) {

                $spans = array();
                foreach($aNode->getElementsByTagName('span') as $node) {
                    $spans[] = $node->textContent;
                }
                $product['attachments'][] = array(
                    'uri' => (new Link($aNode,$uri))->getUri(),
                    'title' => $aNode->getAttribute('title'),
                    'description' => $spans[1],
                    'type' => $spans[2],
                );
            }

            $results[] = $uri;
            $products[] = $product;

            if (in_array($code,$product['codes'])) {
                $this->getLogger()->info("Code found");
                break; // found
            }
            // TODO salvare !
        }

    }

	
}
