<?php
namespace Youppers\CompanyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="youppers_company__product_price")
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
	 * @ORM\Column(type="decimal", precision=15, scale=4)
     * Price of the product for 1 unit of measure
	 */
	protected $price;

    /**
     * @ORM\Column(type="string", length=6, nullable=true)
     */
    protected $vatcode;

    /**
	 * @ORM\Column(type="string", length=10, nullable=true)
     * Unit Of Measure
	 */
	protected $uom;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     * indicates the quantity of product (expressed in uom) present in the smallest package
     */
    protected $quantity;

    /**
     * @ORM\Column(type="decimal", precision=8, scale=4, nullable=true)
     * surface for the quantity of the product
     */
    protected $surface;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     * E = Eliminated, taken off the price list, no longer available
     * S = End of Stock
     * R = By Request
     * M = Custom made articles
     */
    protected $status;

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
		return ($this->getProduct()?:'?') . ' : ' . $this->getPriceDescription();
	}

    public function getPriceDescription() {
        return ($this->getPricelist()? $this->getPricelist()->getCode() :'?') . ' = ' . $this->getPrice() . ' / ' . $this->getUom()
            . ($this->getQuantity()>0 ? ' Q=' . $this->getQuantity(): '')
            . ($this->getSurface()>0 ? ' S=' . $this->getSurface(): '');
    }

    // php app/console doctrine:generate:entities --no-backup YouppersCompanyBundle:ProductPrice

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
     *
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
     * Set vatcode
     *
     * @param string $vatcode
     *
     * @return ProductPrice
     */
    public function setVatcode($vatcode)
    {
        $this->vatcode = $vatcode;

        return $this;
    }

    /**
     * Get vatcode
     *
     * @return string
     */
    public function getVatcode()
    {
        return $this->vatcode;
    }

    /**
     * Set uom
     *
     * @param string $uom
     *
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
     * Set quantity
     *
     * @param string $quantity
     *
     * @return ProductPrice
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity
     *
     * @return string
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set surface
     *
     * @param string $surface
     *
     * @return ProductPrice
     */
    public function setSurface($surface)
    {
        $this->surface = $surface;

        return $this;
    }

    /**
     * Get surface
     *
     * @return string
     */
    public function getSurface()
    {
        return $this->surface;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return ProductPrice
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
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
     *
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
     *
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
     *
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
