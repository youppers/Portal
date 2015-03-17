<?php
namespace Youppers\ProductBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as Validator;

/**
 * @ORM\Entity
 * @ORM\Table(name="youppers_product__attribute_type")
 * @ORM\HasLifecycleCallbacks
 * @Validator\UniqueEntity("name")
 * @Validator\UniqueEntity("code")
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
	 */
	protected $id;
			
	/**
	 * @ORM\Column(type="string", length=60, unique=true)
	 */
	protected $name;

	/**
	 * @ORM\Column(name="code", type="string", length=60, unique=true)
	 */
	protected $code;
	
	/**
	 * @ORM\Column(type="boolean", options={"default":true})
	 */
	protected $enabled;
		
	/**
	 * @ORM\Column(type="text", nullable=true )
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
        $this->productTypes = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set description
     *
     * @param string $description
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
     * Add attributeStandards
     *
     * @param \Youppers\ProductBundle\Entity\AttributeStandard $attributeStandards
     * @return AttributeType
     */
    public function addAttributeStandard(\Youppers\ProductBundle\Entity\AttributeStandard $attributeStandards)
    {
        $this->attributeStandards[] = $attributeStandards;

        return $this;
    }

    /**
     * Remove attributeStandards
     *
     * @param \Youppers\ProductBundle\Entity\AttributeStandard $attributeStandards
     */
    public function removeAttributeStandard(\Youppers\ProductBundle\Entity\AttributeStandard $attributeStandards)
    {
        $this->attributeStandards->removeElement($attributeStandards);
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
     * Add productTypes
     *
     * @param \Youppers\ProductBundle\Entity\AttributeStandard $productTypes
     * @return AttributeType
     */
    public function addProductType(\Youppers\ProductBundle\Entity\AttributeStandard $productTypes)
    {
        $this->productTypes[] = $productTypes;

        return $this;
    }

    /**
     * Remove productTypes
     *
     * @param \Youppers\ProductBundle\Entity\AttributeStandard $productTypes
     */
    public function removeProductType(\Youppers\ProductBundle\Entity\AttributeStandard $productTypes)
    {
        $this->productTypes->removeElement($productTypes);
    }

    /**
     * Get productTypes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProductTypes()
    {
        return $this->productTypes;
    }

    /**
     * Add productAttributes
     *
     * @param \Youppers\ProductBundle\Entity\ProductAttribute $productAttributes
     * @return AttributeType
     */
    public function addProductAttribute(\Youppers\ProductBundle\Entity\ProductAttribute $productAttributes)
    {
        $this->productAttributes[] = $productAttributes;

        return $this;
    }

    /**
     * Remove productAttributes
     *
     * @param \Youppers\ProductBundle\Entity\ProductAttribute $productAttributes
     */
    public function removeProductAttribute(\Youppers\ProductBundle\Entity\ProductAttribute $productAttributes)
    {
        $this->productAttributes->removeElement($productAttributes);
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
