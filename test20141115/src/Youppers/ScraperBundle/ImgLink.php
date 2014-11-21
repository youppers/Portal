<?php
namespace Youppers\ScraperBundle;

use Symfony\Component\DomCrawler\Link;

class ImgLink extends Link 
{
	protected function getRawUri()
	{
		return $this->node->getAttribute('src');
	}

	/**
	 * Sets current \DOMElement instance.
	 *
	 * @param \DOMElement $node A \DOMElement instance
	 *
	 * @throws \LogicException If given node is not an anchor
	 */
	protected function setNode(\DOMElement $node)
	{
		if ('img' !== $node->nodeName) {
			throw new \LogicException(sprintf('Unable to get src on a "%s" tag.', $node->nodeName));
		}
	
		$this->node = $node;
	}
	
}