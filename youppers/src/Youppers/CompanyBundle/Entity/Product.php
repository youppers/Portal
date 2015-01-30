<?php
namespace Youppers\CompanyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="product",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="brand_product_code_idx", columns={"brand_id", "code"}),
 *     @ORM\UniqueConstraint(name="brand_product_code_name_idx", columns={"brand_id", "code", "name"}),
 *   })
 * @ORM\HasLifecycleCallbacks
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
	 */
	protected $brand;
	
	/**
	 * @ORM\OneToMany(targetEntity="ProductModel", cascade={"persist", "remove"}, mappedBy="product")
     * @Assert\Valid
	 **/
	private $productModels;	
	
	/**
	 * @ORM\Column(type="string", length=60)
	 */
	protected $name;
	
	/**
	 * @ORM\Column(type="string", length=20)
	 */
	protected $code;
	
	/**
	 * @ORM\Column(type="boolean", options={"default":true})
	 */
	protected $enabled;
	
	/**
	 * @ORM\Column(type="string")
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
	 */
	protected $description;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Youppers\CommonBundle\Entity\Qr", inversedBy="products")
	 */
	protected $qr;
	
	/**
	 * @param ProductModel[] $models
	 */
	public function setProductModels($models)
	{
		$this->productModels->clear();
	
		foreach ($models as $model) {
			$this->addProductModel($model);
		}
	}
	
	/**
	 * @return ProductModel[]
	 */
	public function getProductModels()
	{
		return $this->productModels;
	}
	
	/**
	 * @param ProductModel $model
	 * @return void
	 */
	public function addProductModel(ProductModel $model)
	{
		$model->setProduct($this);
		$this->productModels->add($model);
	}
	
	/**
	 * @param ProductModel $model
	 * @return void
	 */
	public function removeProductModel(ProductModel $model)
	{
		$this->productModels->removeElement($model);
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
        $this->productModels = new \Doctrine\Common\Collections\ArrayCollection();
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
