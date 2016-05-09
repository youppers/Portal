<?php
namespace Youppers\ProductBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Assetic\Exception\Exception;
use Symfony\Bridge\Doctrine\Validator\Constraints as Validator;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity
 * @ORM\Table(name="youppers_product__product_collection",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="brand_product_collection_name_idx", columns={"brand_id", "name"}),
 *     @ORM\UniqueConstraint(name="brand_product_collection_code_idx", columns={"brand_id", "code"}),
 *   })
 * @ORM\HasLifecycleCallbacks
 * @Validator\UniqueEntity({"name","brand"})
 * @Validator\UniqueEntity({"code","brand"})
 * @Serializer\ExclusionPolicy("all") 
 * @Serializer\AccessorOrder("custom", custom = {"id","name", "code"})  
 */
class ProductCollection
{
	public function getNameCode() {
		return $this->getName() ? $this->getName() . ' [' . $this->getCode() . ']' : 'null';
	}

    public function getCodeAndAliases() {
        return empty($this->getAlias()) ? $this->getCode() : $this->getCode() . ' [' . $this->getAlias() . ']';
    }
    
	public function __toString()
	{
		return $this->getName() ? $this->getBrand() . ' - ' . $this->getNameCode() : 'New';
	}
	
	/**
	 * @ORM\Column(type="guid")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="UUID")
	 * @Serializer\Expose()
	 * @Serializer\Groups({"details", "json"})
	 */
	protected $id;
			
	/**
	 * @ORM\Column(type="string", length=60)
	 * @Serializer\Expose()
	 * @Serializer\Groups({"details", "json"})
	 */
	protected $name;
	
	/**
	 * @ORM\Column(name="code", type="string", length=60)
	 * @Serializer\Expose()
	 * @Serializer\Groups({"details", "json"})
	 */
	protected $code;
	
	/**
	 * @ORM\Column(name="alias", type="string", nullable=true)
	 */
	protected $alias;

	/**
	 * @ORM\Column(type="text", nullable=true )
	 * @Serializer\Expose()
	 * @Serializer\Groups({"details", "json.collection.read"})
	 */
	protected $description;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Youppers\CompanyBundle\Entity\Brand")
	 * @Serializer\Expose()
	 * @Serializer\Groups({"json.collection.read"})
	 */
	protected $brand;
		
	/**
	 * @ORM\ManyToOne(targetEntity="ProductType")
	 * @ORM\JoinColumn(name="product_type_id")
	 * @Serializer\Expose()
	 * @Serializer\Groups({"json.collection.read"})
	 */
	protected $productType;

	/**
	 * @ORM\ManyToMany(targetEntity="AttributeStandard")
	 * @ORM\JoinTable(name="youppers_product__collection_standard")
	 */
	protected $standards;

    /**
     * @ORM\ManyToMany(targetEntity="AttributeOption")
     * @ORM\JoinTable(name="youppers_product__collection_default")
     */
    protected $defaults;

    /**
	 * @ORM\Column(type="boolean", options={"default":true})
	 * @Serializer\Expose()
	 * @Serializer\Groups({"json.collection.read"})
	 */
	protected $enabled;

	/**
	 * @ORM\ManyToOne(targetEntity="Application\Sonata\MediaBundle\Entity\Media")
	 * @Serializer\Expose()
	 * @Serializer\Groups({"details", "json"})
	 */	
	protected $image;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Application\Sonata\MediaBundle\Entity\Gallery")
	 * @ORM\JoinColumn(name="pdf_gallery_id")
	 * @Serializer\Expose()
	 * @Serializer\Groups({"details", "json.collection.read"})
	 */
	protected $pdfGallery;
	
	/**
	 * @ORM\OneToMany(targetEntity="ProductVariant", mappedBy="productCollection")
	 * @ORM\OrderBy({"position" = "ASC"})
	 **/
	protected $productVariants;
	
	public function getCountProductVariants()
	{
		return count($this->getProductVariants());
	}
	
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
	 * @param ProductVariant $productVariant
	 * @return void
	 */
	public function addProductVariant(ProductVariant $productVariant)
	{
		$productVariant->setProductCollection($this);
		$this->productVariants->add($productVariant);
	}
	
	/**
	 * @param ProductVariant $productVariant
	 * @return void
	 */
	public function removeProductVariant(ProductVariant $productVariant)
	{
		$productVariant->setProductCollection(null);
		$this->productVariants->removeElement($productVariant);
	}
	
	// php app/console doctrine:generate:entities --no-backup YouppersProductBundle:ProductCollection
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->standards = new \Doctrine\Common\Collections\ArrayCollection();
        $this->defaults = new \Doctrine\Common\Collections\ArrayCollection();
        $this->productVariants = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return ProductCollection
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
     * @return ProductCollection
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
     * Set description
     *
     * @param string $description
     *
     * @return ProductCollection
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
     * Set enabled
     *
     * @param boolean $enabled
     *
     * @return ProductCollection
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
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return ProductCollection
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
     * @return ProductCollection
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
     * Set brand
     *
     * @param \Youppers\CompanyBundle\Entity\Brand $brand
     *
     * @return ProductCollection
     */
    public function setBrand(\Youppers\CompanyBundle\Entity\Brand $brand = null)
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * Get brand
     *
     * @return \Youppers\CompanyBundle\Entity\Brand
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * Set productType
     *
     * @param \Youppers\ProductBundle\Entity\ProductType $productType
     *
     * @return ProductCollection
     */
    public function setProductType(\Youppers\ProductBundle\Entity\ProductType $productType = null)
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
     * Set image
     *
     * @param \Application\Sonata\MediaBundle\Entity\Media $image
     *
     * @return ProductCollection
     */
    public function setImage(\Application\Sonata\MediaBundle\Entity\Media $image = null)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return \Application\Sonata\MediaBundle\Entity\Media
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set pdfGallery
     *
     * @param \Application\Sonata\MediaBundle\Entity\Gallery $pdfGallery
     *
     * @return ProductCollection
     */
    public function setPdfGallery(\Application\Sonata\MediaBundle\Entity\Gallery $pdfGallery = null)
    {
        $this->pdfGallery = $pdfGallery;

        return $this;
    }

    /**
     * Get pdfGallery
     *
     * @return \Application\Sonata\MediaBundle\Entity\Gallery
     */
    public function getPdfGallery()
    {
        return $this->pdfGallery;
    }

    /**
     * Get productVariants
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProductVariants()
    {
        return $this->productVariants;
    }

    /**
     * Set alias
     *
     * @param string $alias
     *
     * @return ProductCollection
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * Get alias
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Add standard
     *
     * @param \Youppers\ProductBundle\Entity\AttributeStandard $standard
     *
     * @return ProductCollection
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
     * @return ProductCollection
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
