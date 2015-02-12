<?php
namespace Youppers\ProductBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="youppers_product__product_attribute")
 * @ORM\HasLifecycleCallbacks
 */
class ProductAttribute
{
	public function __toString()
	{
		return $this->getProductType() && $this->getAttributeType() ? $this->getProductType() . ' - ' . $this->getAttributeType() : 'New';
	}
	
	public function getDescription() {
		return $this->getAttributeType() . ($this->variant ? " (Variant)": "") . ($this->enabled ? "" : " (Disabled)");
	}
	
	/**
	 * @ORM\ManyToOne(targetEntity="ProductType", inversedBy="productAttributes")
	 * @ORM\Id
	 * @ORM\JoinColumn(name="product_type_id")
	 */
	protected $productType;
	
	/**
	 * @ORM\ManyToOne(targetEntity="AttributeType", inversedBy="productAttributes")
	 * @ORM\Id
	 * @ORM\JoinColumn(name="attribute_type_id")
	 */
	protected $attributeType;
	
	/**
	 * @ORM\Column(type="boolean", options={"default":true})
	 */
	protected $enabled;

	/**
	 * @ORM\Column(type="boolean", options={"default":false})
	 */
	protected $variant;
	
	/**
	 * @ORM\Column(type="integer")
	 */
	protected $position;	
	
	/**
	 * @ORM\Column(type="datetime", name="updated_at")
	 */
	protected $updatedAt;
	
	/**
	 * @ORM\Column(type="datetime", name="created_at")
	 */
	protected $createdAt;
			
	/**
	 * @ORM\PrePersist()
	 */
	public function prePersist()
	{
		$this->createdAt = new \DateTime();
		$this->updatedAt = new \DateTime();
	}

	/**
	 * @ORM\PreUpdate()
	 */
	public function preUpdate()
	{
		$this->updatedAt = new \DateTime();
	}	
	
	// php app/console doctrine:generate:entities --no-backup YouppersProductBundle

    /**
     * Set enabled
     *
     * @param boolean $enabled
     * @return ProductAttribute
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Get enabled
     *
     * @return boolean 
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set variant
     *
     * @param boolean $variant
     * @return ProductAttribute
     */
    public function setVariant($variant)
    {
        $this->variant = $variant;

        return $this;
    }

    /**
     * Get variant
     *
     * @return boolean 
     */
    public function getVariant()
    {
        return $this->variant;
    }

    /**
     * Set position
     *
     * @param integer $position
     * @return ProductAttribute
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return integer 
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return ProductAttribute
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime 
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return ProductAttribute
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set productType
     *
     * @param \Youppers\ProductBundle\Entity\ProductType $productType
     * @return ProductAttribute
     */
    public function setProductType(\Youppers\ProductBundle\Entity\ProductType $productType)
    {
        $this->productType = $productType;

        return $this;
    }

    /**
     * Get productType
     *
     * @return \Youppers\ProductBundle\Entity\ProductType 
     */
    public function getProductType()
    {
        return $this->productType;
    }

    /**
     * Set attributeType
     *
     * @param \Youppers\ProductBundle\Entity\AttributeType $attributeType
     * @return ProductAttribute
     */
    public function setAttributeType(\Youppers\ProductBundle\Entity\AttributeType $attributeType)
    {
        $this->attributeType = $attributeType;

        return $this;
    }

    /**
     * Get attributeType
     *
     * @return \Youppers\ProductBundle\Entity\AttributeType 
     */
    public function getAttributeType()
    {
        return $this->attributeType;
    }
}
