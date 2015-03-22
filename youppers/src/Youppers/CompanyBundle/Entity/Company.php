<?php
namespace Youppers\CompanyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity
 * @ORM\Table(name="youppers_company__company")
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity("name")
 * @UniqueEntity("code")
 * @Serializer\ExclusionPolicy("all")  
 */
class Company
{
	/**
	 * @ORM\Column(type="guid")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="UUID")
	 * @Serializer\Groups({"details"})
	 */
	protected $id;
			
	/**
	 * @ORM\Column(type="string", length=60, unique=true)
	 * @Serializer\Expose()
	 * @Serializer\Groups({"details", "json.qr.find"})
	 */
	protected $name;

	/**
	 * @ORM\Column(name="code", type="string", length=20, unique=true)
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
	 * @ORM\ManyToOne(targetEntity="Application\Sonata\MediaBundle\Entity\Media")
	 * @Serializer\Expose()
	 * @Serializer\Groups({"details", "json.qr.find"})
	 */	
	protected $logo;
	
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
	 * @ORM\OneToMany(targetEntity="Brand", mappedBy="company",cascade={"persist"})
	 **/
	private $brands;
	
	/**
	 * @param Brand[] $brands
	 */
	public function setBrands($brands)
	{
		$this->brands->clear();
	
		foreach ($brands as $brand) {
			$this->addBrand($brand);
		}
	}
	
	/**
	 * @return Brand[]
	 */
	public function getBrands()
	{
		return $this->brands;
	}
	
	/**
	 * @param Brand $brand
	 * @return void
	 */
	public function addBrand(Brand $brand)
	{
		$brand->setCompany($this);
		$this->brands->add($brand);
	}
	
	/**
	 * @param Brand $brand
	 * @return void
	 */
	public function removeBrand(Brand $brand)
	{
		$brand->setCompany(null);
		$this->brands->removeElement($brand);
	}	
		
	public function __toString()
	{
		return $this->getName() ? $this->getName() . ' [' . $this->getCode() . ']': 'New';
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
        $this->brands = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Company
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
     * @return Company
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
     * @return Company
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
     * @return Company
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
     * @return Company
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
     * @return Company
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
     * @return Company
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
     * Set logo
     *
     * @param \Application\Sonata\MediaBundle\Entity\Media $logo
     * @return Company
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
}
