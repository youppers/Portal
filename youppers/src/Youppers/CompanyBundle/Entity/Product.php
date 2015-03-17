<?php
namespace Youppers\CompanyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as Validator;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity
 * @ORM\Table(name="youppers_company__product",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="brand_product_code_idx", columns={"brand_id", "code"})
 *   })
 * @ORM\HasLifecycleCallbacks
 * @Validator\UniqueEntity({"code", "brand"})
 * @Serializer\ExclusionPolicy("all") 
 */
class Product
{
	/**
	 * @ORM\Column(type="guid")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="UUID")
	 */
	protected $id;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Brand", inversedBy="products")
	 * @Serializer\Expose()
	 * @Serializer\Groups({"details", "json"})
	 * @Serializer\MaxDepth(4)
	 */
	protected $brand;
	
	/**
	 * @ORM\OneToMany(targetEntity="ProductPrice", cascade={"all"}, mappedBy="product")
	 **/
	protected $productPrices;	
	
	/**
	 * @ORM\Column(type="string", length=60)
	 * @Serializer\Expose()
	 * @Serializer\Groups({"list", "details", "json"})
	 */
	protected $name;
	
	/**
	 * @ORM\Column(type="string", length=20, unique=true, nullable=true)
	 * @Serializer\Expose()
	 * @Serializer\Groups({"list", "details", "json"})
	 */
	protected $gtin;

	/**
	 * @ORM\Column(type="string", length=20)
	 * @Serializer\Expose()
	 * @Serializer\Groups({"list", "details", "json"})
	 */
	protected $code;
	
	/**
	 * @ORM\Column(type="boolean", options={"default":true})
	 */
	protected $enabled;
	
	/**
	 * @ORM\Column(type="string", nullable=true )
	 */
	protected $url;
	
	/**
	 * @ORM\Column(type="datetime", name="updated_at")
	 */
	protected $updatedAt;
	
	/**
	 * @ORM\Column(type="datetime", name="created_at")
	 */
	protected $createdAt;
	
	
	/**
	 * @ORM\Column(type="text", nullable=true )
	 * @Serializer\Expose()
	 * @Serializer\Groups({"list", "details", "json"})
	 */
	protected $description;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Youppers\CommonBundle\Entity\Qr", inversedBy="products")
	 */
	protected $qr;
	
	/**
	 * @param ProductPrice[] $prices
	 */
	public function setProductPrices($prices)
	{
		$this->productPrices->clear();
	
		foreach ($prices as $price) {
			$this->addProductPrice($price);
		}
	}
	
	/**
	 * @return ProductPrice[]
	 */
	public function getProductPrices()
	{
		return $this->productPrices;
	}
	
	/**
	 * @param ProductPrice $price
	 * @return void
	 */
	public function addProductPrice(ProductPrice $price)
	{
		$price->setProduct($this);
		$this->productPrices->add($price);
	}
	
	/**
	 * @param ProductPrice $price
	 * @return void
	 */
	public function removeProductPrice(ProductPrice $price)	
	{
		//$price->setProduct(null);
		$this->productPrices->removeElement($price);
	}	
	
	public function __toString()
	{
		return $this->getName() ? $this->getBrand() . ' - ' . $this->getName() : 'New';
	}
	
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
	
	// php app/console doctrine:generate:entities --no-backup YouppersCompanyBundle
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->productPrices = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Product
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
     * Set gtin
     *
     * @param string $gtin
     * @return Product
     */
    public function setGtin($gtin)
    {
        $this->gtin = $gtin;

        return $this;
    }

    /**
     * Get gtin
     *
     * @return string 
     */
    public function getGtin()
    {
        return $this->gtin;
    }

    /**
     * Set code
     *
     * @param string $code
     * @return Product
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
     * @return Product
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
     * Set url
     *
     * @param string $url
     * @return Product
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return Product
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
     * @return Product
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
     * Set description
     *
     * @param string $description
     * @return Product
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
     * Set brand
     *
     * @param \Youppers\CompanyBundle\Entity\Brand $brand
     * @return Product
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
     * Set qr
     *
     * @param \Youppers\CommonBundle\Entity\Qr $qr
     * @return Product
     */
    public function setQr(\Youppers\CommonBundle\Entity\Qr $qr = null)
    {
        $this->qr = $qr;

        return $this;
    }

    /**
     * Get qr
     *
     * @return \Youppers\CommonBundle\Entity\Qr 
     */
    public function getQr()
    {
        return $this->qr;
    }
}
