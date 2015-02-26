<?php
namespace Youppers\CompanyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="company__product_price")
 * @ORM\HasLifecycleCallbacks
 */
class ProductPrice
{
	/**
	 * @ORM\Column(type="guid")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="UUID")
	 */
	protected $id;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Product", inversedBy="productPrices")
	 */
	protected $product;
		
	/**
	 * @ORM\ManyToOne(targetEntity="Pricelist")
	 */
	protected $pricelist;
		
	/**
	 * @ORM\Column(type="decimal", scale=4)
	 */
	protected $price;
	
	/**
	 * @ORM\Column(type="string", length=10)
	 */
	protected $uom;	
	
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
	
	public function __toString()
	{
		return $this->price ? $this->getDescription() : "New";
	}
	
	public function getDescription() {
		return ($this->getProduct()?:'?') . ' : ' . ($this->getPricelist()?:'?') . ' = ' . $this->getPrice() . ' / ' . $this->getUom();
	}	
		
	// php app/console doctrine:generate:entities --no-backup YouppersCompanyBundle 


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
     * Set price
     *
     * @param string $price
     * @return ProductPrice
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return string 
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set uom
     *
     * @param string $uom
     * @return ProductPrice
     */
    public function setUom($uom)
    {
        $this->uom = $uom;

        return $this;
    }

    /**
     * Get uom
     *
     * @return string 
     */
    public function getUom()
    {
        return $this->uom;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return ProductPrice
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
     * @return ProductPrice
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
     * Set product
     *
     * @param \Youppers\CompanyBundle\Entity\Product $product
     * @return ProductPrice
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
     * Set pricelist
     *
     * @param \Youppers\CompanyBundle\Entity\Pricelist $pricelist
     * @return ProductPrice
     */
    public function setPricelist(\Youppers\CompanyBundle\Entity\Pricelist $pricelist = null)
    {
        $this->pricelist = $pricelist;

        return $this;
    }

    /**
     * Get pricelist
     *
     * @return \Youppers\CompanyBundle\Entity\Pricelist 
     */
    public function getPricelist()
    {
        return $this->pricelist;
    }
}
