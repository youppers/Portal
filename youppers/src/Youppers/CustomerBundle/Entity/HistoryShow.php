<?php
namespace Youppers\CustomerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Youppers\ProductBundle\Entity\ProductVariant;

/**
 * @ORM\Entity
 */
class HistoryShow extends History
{

	/**
	 * @ORM\ManyToOne(targetEntity="Youppers\ProductBundle\Entity\ProductVariant")
	 * @return ProductVariant
	 */
	protected $variant;
	
	public function getDescription()
	{
		return "Show " . $this->getVariant();
	}
	
	public function getType()
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
