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
        $variantcode = substr($code, -2, 2);
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

            $product['codes'] = array();

            foreach (preg_split("/,/",$productcrawler->filterXPath('//*[@id="productText"]/div/div[1]/div/p[1]/span')->text()) as $productcode) {
                $productcode = trim($productcode);
                if (!empty($productcode)) {
                    if (!in_array($productcode,$product['codes'])) {
                        $product['codes'][] = $productcode;
                    }
                    if (strlen($productcode) == 7 && substr($productcode, 0, 5) == substr($code, 0, 5)) {
                        $productcode = substr($productcode, 0, -2) . $variantcode;
                    }
                    if (!in_array($productcode,$product['codes'])) {
                        $product['codes'][] = $productcode;
                    }
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

            $this->getLogger()->debug("Codes found: " . implode(' ',$product['codes']));

            if (in_array($code,$product['codes'])) {
                if (count($product['codes']) > 1) {
                    // TODO update two variants;
                }
                break; // found
            } else {
                $this->getLogger()->warning("No results found for " . $variant . " in page " . $uri);
            }
        }

        if (empty($product)) {
            $this->getLogger()->warning("No result for " . $code);
        } else {
            if (!empty($product['images'])) {
                $uri = array_shift($product['images']);
                $this->addVariantImage($variant,$uri);
            }
        }

    }

	
}
