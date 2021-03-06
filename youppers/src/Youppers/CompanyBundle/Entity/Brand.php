<?php
namespace Youppers\CompanyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as Validator;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity
 * @ORM\Table(name="youppers_company__brand",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="company_brand_name_idx", columns={"company_id", "name"}),
 *     @ORM\UniqueConstraint(name="company_brand_code_idx", columns={"company_id", "code"}),
 *   })
 * @ORM\HasLifecycleCallbacks
 * @Validator\UniqueEntity({"name","company"})
 * @Validator\UniqueEntity({"code","company"})
 * @Serializer\ExclusionPolicy("all") 
 * @Serializer\AccessorOrder("custom", custom = {"id","name", "code"})  
 */
class Brand
{
	/**
	 * @ORM\Column(type="guid")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="UUID")
	 * @Serializer\Expose()
	 * @Serializer\Groups({"details"})
	 */
	protected $id;

	/**
	 * @ORM\ManyToOne(targetEntity="Company", inversedBy="brands")
	 * @Serializer\Expose()
	 * @Serializer\Groups({"details", "json.qr.find", "json.product.list", "json.variant.read", "json.collection.read", "json.box.show"})
	 */
	protected $company;

	/**
	 * @ORM\Column(type="string", length=60)
	 * @Serializer\Expose()
	 * @Serializer\Groups({"details", "json"})
	 */
	protected $name;

	/**
	 * @ORM\Column(name="code", type="string", length=20)
	 * @Serializer\Expose()
	 * @Serializer\Groups({"details", "json"})
	 */
	protected $code;

	/**
	 * @ORM\Column(type="boolean", options={"default":true})
	 */
	protected $enabled;
	
	/**
	 * @ORM\Column(type="text", nullable=true )
	 * @Serializer\Expose()
	 * @Serializer\Groups({"details"})
	 */
	protected $description;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Application\Sonata\MediaBundle\Entity\Media")
	 * @Serializer\Expose()
	 * @Serializer\Groups({"details", "json"})
	 */
	protected $logo;
	
	/**
	 * @ORM\Column(type="string", nullable=true )
	 */
	protected $url;

    /**
     * @ORM\OneToMany(targetEntity="\Youppers\DealerBundle\Entity\DealerBrand", mappedBy="brand")
     */
    protected $dealers;

    /**
	 * @ORM\Column(type="datetime", name="updated_at")
	 */
	protected $updatedAt;
	
	/**
	 * @ORM\Column(type="datetime", name="created_at")
	 * @Serializer\Expose()
	 * @Serializer\Groups({"details"})
	 */
	protected $createdAt;	
	
	/**
	 * @ORM\OneToMany(targetEntity="Product", mappedBy="brand")
	 **/
	private $products;

    public function getCompanyFullCode() {
        return $this->getCompany() ? $this->getCompany()->getCode() : 'null';
    }

    public function getFullCode() {
        return $this->getCode() ? $this->getCompanyFullCode() . '-' . $this->getCode() : 'null';
    }

    public function getNameCodeStatus()
    {
        return ($this->getName() ? $this->getName() . ' [' . $this->getCode() . ']': 'New') . ($this->getEnabled() ? '' : " DISABLED ");
    }

    public function __toString()
	{
		return $this->getName() ? $this->getCompany() . ' - ' . $this->getName() . ' [' . $this->getCode() . ']': 'New';
	}
			
	/**
	 * @ORM\PrePersist()
	 */
	public function prePersist()
	{
		$this->createdAt = new \DateTime();
		$this->updatedAt = new \DateTime();
		if ($this->enabled == null) {
			$this->enabled = false;
		}		
	}

	/**
	 * @ORM\PreUpdate()
	 */
	public function preUpdate()
	{
		$this->updatedAt = new \DateTime();
	}	
		
	// php app/console doctrine:generate:entities --no-backup YouppersCompanyBundle:Brand
	
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->dealers = new \Doctrine\Common\Collections\ArrayCollection();
        $this->products = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Brand
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
     * @return Brand
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
     * @return Brand
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
     *
     * @return Brand
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
     * Set url
     *
     * @param string $url
     *
     * @return Brand
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
     *
     * @return Brand
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
     * @return Brand
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
     * Set company
     *
     * @param \Youppers\CompanyBundle\Entity\Company $company
     *
     * @return Brand
     */
    public function setCompany(\Youppers\CompanyBundle\Entity\Company $company = null)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company
     *
     * @return \Youppers\CompanyBundle\Entity\Company
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set logo
     *
     * @param \Application\Sonata\MediaBundle\Entity\Media $logo
     *
     * @return Brand
     */
    public function setLogo(\Application\Sonata\MediaBundle\Entity\Media $logo = null)
    {
        $this->logo = $logo;

        return $this;
    }

    /**
     * Get logo
     *
     * @return \Application\Sonata\MediaBundle\Entity\Media
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * Add dealer
     *
     * @param \Youppers\DealerBundle\Entity\DealerBrand $dealer
     *
     * @return Brand
     */
    public function addDealer(\Youppers\DealerBundle\Entity\DealerBrand $dealer)
    {
        $this->dealers[] = $dealer;

        return $this;
    }

    /**
     * Remove dealer
     *
     * @param \Youppers\DealerBundle\Entity\DealerBrand $dealer
     */
    public function removeDealer(\Youppers\DealerBundle\Entity\DealerBrand $dealer)
    {
        $this->dealers->removeElement($dealer);
    }

    /**
     * Get dealers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDealers()
    {
        return $this->dealers;
    }

    /**
     * Add product
     *
     * @param \Youppers\CompanyBundle\Entity\Product $product
     *
     * @return Brand
     */
    public function addProduct(\Youppers\CompanyBundle\Entity\Product $product)
    {
        $this->products[] = $product;

        return $this;
    }

    /**
     * Remove product
     *
     * @param \Youppers\CompanyBundle\Entity\Product $product
     */
    public function removeProduct(\Youppers\CompanyBundle\Entity\Product $product)
    {
        $this->products->removeElement($product);
    }

    /**
     * Get products
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProducts()
    {
        return $this->products;
    }
}
