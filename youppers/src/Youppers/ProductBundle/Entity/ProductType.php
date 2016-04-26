<?php
namespace Youppers\ProductBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Assetic\Exception\Exception;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity
 * @ORM\Table(name="youppers_product__product_type")
 * @ORM\HasLifecycleCallbacks
 * @JMS\ExclusionPolicy("all") 
 * @JMS\AccessorOrder("custom", custom = {"id","name", "code"})  
 */
class ProductType
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
	 * @JMS\Expose()
	 * @JMS\Groups({"details", "json"})
	 */
	protected $name;

	/**
	 * @ORM\Column(name="code", type="string", length=60, unique=true)
	 * @JMS\Expose()
	 * @JMS\Groups({"details", "json"})
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
	 * @ORM\OneToMany(targetEntity="ProductAttribute", mappedBy="productType", cascade={"all"}, orphanRemoval=true)
	 * @ORM\OrderBy({"position" = "ASC"})
	 * @JMS\Expose()
	 * @JMS\Groups({"details", "json.collection.read"})
	 **/
	private $productAttributes;

    /**
     * @ORM\ManyToMany(targetEntity="AttributeStandard")
     * @ORM\JoinTable(name="youppers_product__type_standard")
     */
    protected $standards;

    /**
     * @ORM\ManyToMany(targetEntity="AttributeOption")
     * @ORM\JoinTable(name="youppers_product__type_default")
     */
    protected $defaults;

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
	 * @param ProductAttribute $productAttribute
	 * @return void
	 */
	public function addProductAttribute(ProductAttribute $productAttribute)
	{
		$productAttribute->setProductType($this);
		$this->productAttributes->add($productAttribute);
	}
	
	// php app/console doctrine:generate:entities --no-backup YouppersProductBundle
    /**
     * Constructor
     */
    public function __construct()
    {
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
     * @return ProductType
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
     * @return ProductType
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
     * @return ProductType
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
     * @return ProductType
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
     * @return ProductType
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
     * @return ProductType
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
     * Get productAttributes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProductAttributes()
    {
        return $this->productAttributes;
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
     * Add standard
     *
     * @param \Youppers\ProductBundle\Entity\AttributeStandard $standard
     *
     * @return ProductType
     */
    public function addStandard(\Youppers\ProductBundle\Entity\AttributeStandard $standard)
    {
        $this->standards[] = $standard;

        return $this;
    }

    /**
     * Remove standard
     *
     * @param \Youppers\ProductBundle\Entity\AttributeStandard $standard
     */
    public function removeStandard(\Youppers\ProductBundle\Entity\AttributeStandard $standard)
    {
        $this->standards->removeElement($standard);
    }

    /**
     * Get standards
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getStandards()
    {
        return $this->standards;
    }

    /**
     * Add default
     *
     * @param \Youppers\ProductBundle\Entity\AttributeOption $default
     *
     * @return ProductType
     */
    public function addDefault(\Youppers\ProductBundle\Entity\AttributeOption $default)
    {
        $this->defaults[] = $default;

        return $this;
    }

    /**
     * Remove default
     *
     * @param \Youppers\ProductBundle\Entity\AttributeOption $default
     */
    public function removeDefault(\Youppers\ProductBundle\Entity\AttributeOption $default)
    {
        $this->defaults->removeElement($default);
    }

    /**
     * Get defaults
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDefaults()
    {
        return $this->defaults;
    }
}
