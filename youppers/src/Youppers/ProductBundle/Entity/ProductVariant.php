<?php
namespace Youppers\ProductBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Assetic\Exception\Exception;
use Doctrine\Common\Collections\Criteria;
use Symfony\Bridge\Doctrine\Validator\Constraints as Validator;
use Youppers\ProductBundle\Validator\Constraints\ConsistentBrand;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity
 * @ORM\Table(name="youppers_product__product_variant",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="collection_name_idx", columns={"product_collection_id", "name"}),
 *   })
 * @ORM\HasLifecycleCallbacks
 * @Validator\UniqueEntity({"name", "productCollection"})
 * @ConsistentBrand()
 * @Serializer\ExclusionPolicy("all") 
 */
class ProductVariant
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
	 * @ORM\ManyToOne(targetEntity="ProductCollection", inversedBy="productVariants")
	 * @ORM\JoinColumn(name="product_collection_id")
	 * @Serializer\Expose()
	 * @Serializer\Groups({"details", "json.qr.find"})
	 */	
	protected $productCollection;
			
	/**
	 * @ORM\Column(type="string")
	 * @deprecated use product name
	 */
	protected $name;

	/**
	 * @ORM\Column(name="code", type="string", length=60)
	 * @deprecated use product name
	 */
	protected $code;
	
	/**
	 * @ORM\Column(type="boolean", options={"default":true})
	 */
	protected $enabled;
		
	/**
	 * @ORM\Column(type="integer")
	 */
	protected $position;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Application\Sonata\MediaBundle\Entity\Media")
	 * @Serializer\Expose()
	 * @Serializer\Groups({"details", "json.qr.find"})
	 */
	protected $image;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Application\Sonata\MediaBundle\Entity\Gallery")
	 * @ORM\JoinColumn(name="pdf_gallery_id")
	 * @Serializer\Expose()
	 * @Serializer\Groups({"details", "json.qr.find"})
	 */
	protected $pdfGallery;
	
	/**
	 * @ORM\OneToMany(targetEntity="VariantProperty", mappedBy="productVariant", cascade={"all"}, orphanRemoval=true)
	 * @ORM\OrderBy({"position" = "ASC"})
	 * @Serializer\Expose()
	 * @Serializer\Groups({"details", "json.qr.find"})
	 **/
	private $variantProperties;
	
	/**
	 * @ORM\OneToOne(targetEntity="\Youppers\CompanyBundle\Entity\Product", inversedBy="variant")
	 **/
	protected $product;
	
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
	
	private $attributesVariant = false;
	
	/**
	 * return collection of attribute types that are variant attributes for this
	 */
	private function getAttributesVariant() {
		if ($this->attributesVariant !== false) {
			return $this->attributesVariant;
		}
		$criteria = Criteria::create()->where(Criteria::expr()->eq("variant", true));
		$this->attributesVariant = $this->getProductCollection()->getProductType()->getProductAttributes()->matching($criteria);
		return $this->attributesVariant;
	}
	
	/**
	 * Update variant named based on attributes that are variant specific
	 */
	private function updateName() {
		$attributesType = array();
		foreach ($this->getAttributesVariant() as $productAttribute) {
			$attributeType = $productAttribute->getAttributeType();
			$attributeType->getName(); // fetch
			$attributesType[]= $attributeType;
		}
		dump($attributesType);
		$criteria = Criteria::create()->where(Criteria::expr()->in("attributeType", $attributesType));
		dump($attributesType);
		dump($this->getVariantProperties());
		$properties = $this->getVariantProperties()->matching($criteria);
		$nameAtoms = array();
		foreach ($properties as $property) {
			$nameAtoms[] = $property->getAttributeOption()->getValue();
		}
		$this->setName(implode(", ",$nameAtoms));
	}
	
	/**
	 * @param VariantProperty $variantProperty
	 * @return void
	 */
	public function addVariantProperty(VariantProperty $variantProperty)
	{
		$variantProperty->setProductVariant($this);
		$this->variantProperties->add($variantProperty);
		//$this->updateName();
	}
	
	/**
	 * @param VariantProperty $variantProperty
	 * @return void
	 */
	public function removeVariantProperty(VariantProperty $variantProperty)
	{
		$variantProperty->setProductVariant(null);
		$this->variantProperties->removeElement($variantProperty);
	}
	
	// php app/console doctrine:generate:entities --no-backup YouppersProductBundle:ProductVariant
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->variantProperties = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return ProductVariant
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
     * @return ProductVariant
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
     * @return ProductVariant
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
     * @return ProductVariant
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
     * @return ProductVariant
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
     * @return ProductVariant
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
     * Set productCollection
     *
     * @param \Youppers\ProductBundle\Entity\ProductCollection $productCollection
     * @return ProductVariant
     */
    public function setProductCollection(\Youppers\ProductBundle\Entity\ProductCollection $productCollection = null)
    {
        $this->productCollection = $productCollection;

        return $this;
    }

    /**
     * Get productCollection
     *
     * @return \Youppers\ProductBundle\Entity\ProductCollection 
     */
    public function getProductCollection()
    {
        return $this->productCollection;
    }

    /**
     * Get variantProperties
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getVariantProperties()
    {
        return $this->variantProperties;
    }

    /**
     * Set product
     *
     * @param \Youppers\CompanyBundle\Entity\Product $product
     * @return ProductVariant
     */
    public function setProduct(\Youppers\CompanyBundle\Entity\Product $product = null)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get product
     *
     * @return \Youppers\CompanyBundle\Entity\Product 
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Set image
     *
     * @param \Application\Sonata\MediaBundle\Entity\Media $image
     * @return ProductVariant
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
     * @return ProductVariant
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
}
