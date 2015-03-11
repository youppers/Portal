<?php
namespace Youppers\ProductBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="youppers_product__attribute_standard")
 * @ORM\HasLifecycleCallbacks
 */
class AttributeStandard
{
	public function __toString()
	{
		return $this->getName() ?: 'New';
	}
	
	/**
	 * @ORM\Column(type="guid")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="UUID")
	 */
	protected $id;
			
	/**
	 * @ORM\Column(type="string", length=60, unique=true)
	 */
	protected $name;

	/**
	 * @ORM\Column(type="string", length=60, nullable=true )
	 */
	protected $symbol;
	
	/**
	 * @ORM\Column(type="boolean", options={"default":true})
	 */
	protected $enabled;
		
	/**
	 * @ORM\Column(type="text", nullable=true )
	 */
	protected $description;
	
	/**
	 * @ORM\ManyToOne(targetEntity="AttributeType", inversedBy="attributeStandards")
	 * @ORM\JoinColumn(name="attribute_type_id")
	 */
	protected $attributeType;
	
	/**
	 * @ORM\OneToMany(targetEntity="AttributeOption", mappedBy="attributeStandard", cascade={"all"}, orphanRemoval=true)
	 * @ORM\OrderBy({"position" = "ASC"})
	 */
	protected $attributeOptions;
	
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
	
	/**
	 * 
	 * @param AttributeOption $attributeOption
	 */
	public function addAttributeOption(AttributeOption $attributeOption)
	{
		$attributeOption->setAttributeStandard($this);
		$this->attributeOptions->add($attributeOption);
	}
	
	// php app/console doctrine:generate:entities --no-backup YouppersProductBundle
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->attributeOptions = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     * Set name
     *
     * @param string $name
     * @return AttributeStandard
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set symbol
     *
     * @param string $symbol
     * @return AttributeStandard
     */
    public function setSymbol($symbol)
    {
        $this->symbol = $symbol;

        return $this;
    }

    /**
     * Get symbol
     *
     * @return string 
     */
    public function getSymbol()
    {
        return $this->symbol;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     * @return AttributeStandard
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
     * Set description
     *
     * @param string $description
     * @return AttributeStandard
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return AttributeStandard
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
     * @return AttributeStandard
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
     * Set attributeType
     *
     * @param \Youppers\ProductBundle\Entity\AttributeType $attributeType
     * @return AttributeStandard
     */
    public function setAttributeType(\Youppers\ProductBundle\Entity\AttributeType $attributeType = null)
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

    /**
     * Remove attributeOptions
     *
     * @param \Youppers\ProductBundle\Entity\AttributeOption $attributeOptions
     */
    public function removeAttributeOption(\Youppers\ProductBundle\Entity\AttributeOption $attributeOptions)
    {
        $this->attributeOptions->removeElement($attributeOptions);
    }

    /**
     * Get attributeOptions
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAttributeOptions()
    {
        return $this->attributeOptions;
    }
}
