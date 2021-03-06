<?php
namespace Youppers\ProductBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as Validator;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity
 * @ORM\Table(name="youppers_product__attribute_type")
 * @ORM\HasLifecycleCallbacks
 * @Validator\UniqueEntity("name")
 * @Validator\UniqueEntity("code")
 * @JMS\ExclusionPolicy("all") 
 * @JMS\AccessorOrder("custom", custom = {"id","name", "code"})  
 */
class AttributeType
{
	public function __toString()
	{
		return $this->getName() ?: 'New';
	}
	
	/**
	 * @ORM\Column(type="guid")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="UUID")
	 * @JMS\Expose()
	 * @JMS\Groups({"json.collection.read","json.variant.read"})
	 */
	protected $id;
			
	/**
	 * @ORM\Column(type="string", length=60, unique=true)
	 * @JMS\Expose()
	 * @JMS\Groups({"details", "json"})
	 */
	protected $name;

	/**
	 * @ORM\Column(name="code", type="string", length=60, unique=true)
	 * @JMS\Expose()
	 * @JMS\Groups({"details","json"})
	 */
	protected $code;
	
	/**
	 * @ORM\Column(type="boolean", options={"default":true})
	 * @JMS\Expose()
	 * @JMS\Groups({"details"})
	 */
	protected $enabled;

    /**
     * @ORM\Column(type="boolean", options={"default":false})
     */
    protected $hideOptionsImage;

    /**
	 * @ORM\Column(type="text", nullable=true )
	 * @JMS\Expose()
	 * @JMS\Groups({"details"})
	 */
	protected $description;
	
	/**
	 * @ORM\OneToMany(targetEntity="AttributeStandard", mappedBy="attributeType",cascade={"persist"})
	 * @ORM\JoinColumn(name="attribute_standard_id")
	 **/
	private $attributeStandards;
	
	/**
	 * @ORM\OneToMany(targetEntity="ProductAttribute", mappedBy="attributeType",cascade={"persist","remove"})
	 **/
	private $productAttributes;
	
	/**
	 * @ORM\Column(type="datetime", name="updated_at")
	 */
	protected $updatedAt;
	
	/**
	 * @ORM\Column(type="datetime", name="created_at")
	 * @JMS\Expose()
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
     * Constructor
     */
    public function __construct()
    {
        $this->attributeStandards = new \Doctrine\Common\Collections\ArrayCollection();
        $this->productAttributes = new \Doctrine\Common\Collections\ArrayCollection();
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
     *
     * @return AttributeType
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
     * Set code
     *
     * @param string $code
     *
     * @return AttributeType
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     *
     * @return AttributeType
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
     * Set hideOptionsImage
     *
     * @param boolean $hideOptionsImage
     *
     * @return AttributeType
     */
    public function setHideOptionsImage($hideOptionsImage)
    {
        $this->hideOptionsImage = $hideOptionsImage;

        return $this;
    }

    /**
     * Get hideOptionsImage
     *
     * @return boolean
     */
    public function getHideOptionsImage()
    {
        return $this->hideOptionsImage;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return AttributeType
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
     *
     * @return AttributeType
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
     *
     * @return AttributeType
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
     * Add attributeStandard
     *
     * @param \Youppers\ProductBundle\Entity\AttributeStandard $attributeStandard
     *
     * @return AttributeType
     */
    public function addAttributeStandard(\Youppers\ProductBundle\Entity\AttributeStandard $attributeStandard)
    {
        $this->attributeStandards[] = $attributeStandard;

        return $this;
    }

    /**
     * Remove attributeStandard
     *
     * @param \Youppers\ProductBundle\Entity\AttributeStandard $attributeStandard
     */
    public function removeAttributeStandard(\Youppers\ProductBundle\Entity\AttributeStandard $attributeStandard)
    {
        $this->attributeStandards->removeElement($attributeStandard);
    }

    /**
     * Get attributeStandards
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAttributeStandards()
    {
        return $this->attributeStandards;
    }

    /**
     * Add productAttribute
     *
     * @param \Youppers\ProductBundle\Entity\ProductAttribute $productAttribute
     *
     * @return AttributeType
     */
    public function addProductAttribute(\Youppers\ProductBundle\Entity\ProductAttribute $productAttribute)
    {
        $this->productAttributes[] = $productAttribute;

        return $this;
    }

    /**
     * Remove productAttribute
     *
     * @param \Youppers\ProductBundle\Entity\ProductAttribute $productAttribute
     */
    public function removeProductAttribute(\Youppers\ProductBundle\Entity\ProductAttribute $productAttribute)
    {
        $this->productAttributes->removeElement($productAttribute);
    }

    /**
     * Get productAttributes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProductAttributes()
    {
        return $this->productAttributes;
    }
}
