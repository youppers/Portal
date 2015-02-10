<?php
namespace Youppers\ProductBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="youppers_product__attribute_option",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="standard_value_idx", columns={"attribute_standard_id", "value"}),
 *   })
 * @ORM\HasLifecycleCallbacks
 */
class AttributeOption
{
	public function __toString()
	{
		return $this->getValue() ?: 'New';
	}
	
	/**
	 * @ORM\Column(type="guid")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="UUID")
	 */
	protected $id;
			
	/**
	 * @ORM\Column(type="string")
	 */
	protected $value;

	/**
	 * @ORM\Column(type="boolean", options={"default":true})
	 */
	protected $enabled;
	
	/**
	 * @ORM\Column(type="integer")
	 */
	protected $position;
	
	/**
	 * @ORM\ManyToOne(targetEntity="AttributeStandard", inversedBy="attributeOptions")
	 * @ORM\JoinColumn(name="attribute_standard_id")
	 */
	protected $attributeStandard;
	
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
     * Get id
     *
     * @return guid 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set value
     *
     * @param string $value
     * @return AttributeOption
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string 
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     * @return AttributeOption
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
     * Set position
     *
     * @param integer $position
     * @return AttributeOption
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
     * @return AttributeOption
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
     * @return AttributeOption
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
     * Set attributeStandard
     *
     * @param \Youppers\ProductBundle\Entity\AttributeStandard $attributeStandard
     * @return AttributeOption
     */
    public function setAttributeStandard(\Youppers\ProductBundle\Entity\AttributeStandard $attributeStandard = null)
    {
        $this->attributeStandard = $attributeStandard;

        return $this;
    }

    /**
     * Get attributeStandard
     *
     * @return \Youppers\ProductBundle\Entity\AttributeStandard 
     */
    public function getAttributeStandard()
    {
        return $this->attributeStandard;
    }
}
