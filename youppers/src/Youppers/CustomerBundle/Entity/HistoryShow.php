<?php
namespace Youppers\CustomerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Youppers\ProductBundle\Entity\ProductVariant;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity
 */
class HistoryShow extends History
{

	/**
	 * @ORM\ManyToOne(targetEntity="Youppers\ProductBundle\Entity\ProductVariant")
	 * @return ProductVariant
	 * @JMS\Groups({"json.history.list"})
	 */
	protected $variant;
	
	public function getDescription()
	{
		return "Show " . $this->getVariant();
	}

	/**
	 * @JMS\VirtualProperty
	 * @JMS\Groups({"json.history.list"})
	 */	
	public function getHistoryType()
	{
		return 'variant_show';
	}
	
	// php app/console doctrine:generate:entities --no-backup YouppersCustomerBundle


    /**
     * Set variant
     *
     * @param \Youppers\ProductBundle\Entity\ProductVariant $variant
     *
     * @return HistoryShow
     */
    public function setVariant(\Youppers\ProductBundle\Entity\ProductVariant $variant = null)
    {
        $this->variant = $variant;

        return $this;
    }

    /**
     * Get variant
     *
     * @return \Youppers\ProductBundle\Entity\ProductVariant
     */
    public function getVariant()
    {
        return $this->variant;
    }
}
