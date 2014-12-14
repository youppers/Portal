<?php
namespace Youppers\CompanyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="company")
 */
class Company
{
	/**
	 * @ORM\Column(type="guid")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="UUID")
	 */
	protected $id;
	
	/**
	 * @ORM\OneToMany(targetEntity="Brand", mappedBy="company")
	 **/
	private $brands;
		
	/**
	 * @ORM\Column(type="string", length=60, unique=true)
	 */
	protected $name;
		
	/**
	 * @ORM\Column(type="boolean", name="is_active", options={"default":true})
	 */
	protected $isActive;
	
	/**
	 * @ORM\Column(type="datetime")
	 */
	protected $createdAt;
	
	/**
	 * @ORM\Column(type="text", nullable=true )
	 */
	protected $description;
	
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
		$this->brands->removeElement($brand);
	}	
		
	public function __toString()
	{
		return $this->getName() ?: 'New';
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
     * Set isActive
     *
     * @param boolean $isActive
     * @return Company
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean 
     */
    public function getIsActive()
    {
        return $this->isActive;
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
}
