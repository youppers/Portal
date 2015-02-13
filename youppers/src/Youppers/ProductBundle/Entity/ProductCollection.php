<?php
namespace Youppers\ProductBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Assetic\Exception\Exception;

/**
 * @ORM\Entity
 * @ORM\Table(name="youppers_product__product_collection",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="brand_product_collection_name_idx", columns={"brand_id", "name"}),
 *   })
 * @ORM\HasLifecycleCallbacks
 */
class ProductCollection
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
	 * @ORM\Column(type="string", length=60)
	 */
	protected $name;

	/**
	 * @ORM\ManyToOne(targetEntity="Youppers\CompanyBundle\Entity\Brand")
	 */
	protected $brand;
		
	/**
	 * @ORM\ManyToOne(targetEntity="ProductType")
	 * @ORM\JoinColumn(name="product_type_id")
	 */
	protected $productType;
	
	/**
	 * @ORM\Column(type="boolean", options={"default":true})
	 */
	protected $enabled;
			
	/**
	 * @ORM\OneToMany(targetEntity="ProductVariant", mappedBy="productCollection", cascade={"all"}, orphanRemoval=true)
	 * @ORM\OrderBy({"position" = "ASC"})
	 **/
	private $productVariants;
	
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
	
	// php app/console doctrine:generate:entities --no-backup YouppersProductBundle
    /**
     * Constructor
     */
    public function __construct()
    {
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
     * Set enabled
     *
     * @param boolean $enabled
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
     * Get productVariants
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProductVariants()
    {
        return $this->productVariants;
    }
}
