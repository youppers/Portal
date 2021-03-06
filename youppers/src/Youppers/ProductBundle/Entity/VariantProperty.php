<?php
namespace Youppers\ProductBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="youppers_product__variant_property")
 * @ORM\HasLifecycleCallbacks
 * @Serializer\ExclusionPolicy("all") 
 */
class VariantProperty
{
	public function __toString()
	{
		return $this->getAttributeOption() ? $this->getAttributeOption()->__toString(): 'New';
	}
	
	public function getAttributeType()
	{
		return $this->getAttributeOption() ? $this->getAttributeOption()->getAttributeType() : null; 
	}
	
	/**
	 * @ORM\Column(type="guid")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="UUID")
	 * @Serializer\Expose()
	 * @Serializer\Groups({"details"})
	 */
	protected $id;
			
	/**
	 * @ORM\ManyToOne(targetEntity="ProductVariant", inversedBy="variantProperties")
	 * @ORM\JoinColumn(name="product_variant_id")
	 * @Assert\NotNull
	 */	
	protected $productVariant;
	
	/**
	 * @ORM\Column(type="integer")
	 * @Serializer\Expose()
	 * @Serializer\Groups({"details"})
	 */
	protected $position;
	
	/**
	 * @ORM\ManyToOne(targetEntity="AttributeOption")
	 * @ORM\JoinColumn(name="attribute_option_id")
	 * @Serializer\Expose()
	 * @Serializer\Groups({"details", "json.qr.find", "json.variant.list", "json.variant.read", "json.product.list", "json.box.show"})
	 * @Assert\NotNull
	 */
	protected $attributeOption;
	
	/**
	 * @ORM\Column(type="datetime", name="updated_at")
	 */
	protected $updatedAt;
	
	/**
	 * @ORM\Column(type="datetime", name="created_at")
	 * @Serializer\Expose()
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
     * Get id
     *
     * @return guid 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set position
     *
     * @param integer $position
     * @return VariantProperty
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
     * @return VariantProperty
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
     * @return VariantProperty
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
     * Set productVariant
     *
     * @param \Youppers\ProductBundle\Entity\ProductVariant $productVariant
     * @return VariantProperty
     */
    public function setProductVariant(\Youppers\ProductBundle\Entity\ProductVariant $productVariant = null)
    {
        $this->productVariant = $productVariant;

        return $this;
    }

    /**
     * Get productVariant
     *
     * @return \Youppers\ProductBundle\Entity\ProductVariant 
     */
    public function getProductVariant()
    {
        return $this->productVariant;
    }

    /**
     * Set attributeOption
     *
     * @param \Youppers\ProductBundle\Entity\AttributeOption $attributeOption
     * @return VariantProperty
     */
    public function setAttributeOption(\Youppers\ProductBundle\Entity\AttributeOption $attributeOption = null)
    {
        $this->attributeOption = $attributeOption;

        return $this;
    }

    /**
     * Get attributeOption
     *
     * @return \Youppers\ProductBundle\Entity\AttributeOption 
     */
    public function getAttributeOption()
    {
        return $this->attributeOption;
    }
}
