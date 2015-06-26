<?php
namespace Youppers\DealerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="youppers_dealer__dealer")
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity("name")
 * @UniqueEntity("code")
 */
class Dealer
{
	/**
	 * @ORM\Column(type="guid")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="UUID")
	 * @JMS\Groups({"list", "details","create", "json"})
	 */
	protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="\Youppers\CommonBundle\Entity\Org", inversedBy="dealers")
     **/
    protected $org;

    /**
	 * @ORM\Column(type="string", length=60, unique=true)
	 * @JMS\Groups({"list", "details","create","json.store.read","json.session.read", "json.box.list"})
	 */
	protected $name;

	/**
	 * @ORM\Column(name="code", type="string", length=20, unique=true)
	 * @JMS\Groups({"list", "details","create","json.store.read","json.session.read", "json.box.list"})
	 */
	protected $code;
		
	/**
     * @ORM\Column(type="string")
     * @Assert\Email
     * @var string
	 */
	protected $email;

    /**
     * @ORM\ManyToOne(targetEntity="Application\Sonata\MediaBundle\Entity\Media")
     * @JMS\Expose()
     * @JMS\Groups({"details", "json"})
     */
    protected $logo;

    /**
     * @ORM\ManyToMany(targetEntity="\Youppers\CompanyBundle\Entity\Brand", inversedBy="dealers")
     * @ORM\JoinTable(name="youppers_dealer__brands")
     * @ORM\OrderBy({"company" = "ASC", "name" = "ASC"})
     */
    protected $brands;

    /**
	 * @ORM\Column(type="boolean", options={"default":true})
	 */
	protected $enabled;
	
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
	 * @JMS\Groups({"list", "details","create","json.store.read","json.session.read", "json.box.list"})
	 */
	protected $description;
	
	/**
	 * @ORM\OneToMany(targetEntity="Store", mappedBy="dealer")
	 **/
	protected $stores;
	
	/**
	 * @ORM\OneToMany(targetEntity="Consultant", mappedBy="dealer")
	 **/
	protected $consultants;
	
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
	
	// php app/console doctrine:generate:entities --no-backup YouppersDealerBundle

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->brands = new \Doctrine\Common\Collections\ArrayCollection();
        $this->stores = new \Doctrine\Common\Collections\ArrayCollection();
        $this->consultants = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Dealer
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
     * @return Dealer
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
     * Set email
     *
     * @param string $email
     *
     * @return Dealer
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     *
     * @return Dealer
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
     * @return Dealer
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
     * @return Dealer
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
     *
     * @return Dealer
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
     * Set org
     *
     * @param \Youppers\CommonBundle\Entity\Org $org
     *
     * @return Dealer
     */
    public function setOrg(\Youppers\CommonBundle\Entity\Org $org = null)
    {
        $this->org = $org;

        return $this;
    }

    /**
     * Get org
     *
     * @return \Youppers\CommonBundle\Entity\Org
     */
    public function getOrg()
    {
        return $this->org;
    }

    /**
     * Add brand
     *
     * @param \Youppers\CompanyBundle\Entity\Brand $brand
     *
     * @return Dealer
     */
    public function addBrand(\Youppers\CompanyBundle\Entity\Brand $brand)
    {
        $this->brands[] = $brand;

        return $this;
    }

    /**
     * Remove brand
     *
     * @param \Youppers\CompanyBundle\Entity\Brand $brand
     */
    public function removeBrand(\Youppers\CompanyBundle\Entity\Brand $brand)
    {
        $this->brands->removeElement($brand);
    }

    /**
     * Get brands
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBrands()
    {
        return $this->brands;
    }

    /**
     * Add store
     *
     * @param \Youppers\DealerBundle\Entity\Store $store
     *
     * @return Dealer
     */
    public function addStore(\Youppers\DealerBundle\Entity\Store $store)
    {
        $this->stores[] = $store;

        return $this;
    }

    /**
     * Remove store
     *
     * @param \Youppers\DealerBundle\Entity\Store $store
     */
    public function removeStore(\Youppers\DealerBundle\Entity\Store $store)
    {
        $this->stores->removeElement($store);
    }

    /**
     * Get stores
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getStores()
    {
        return $this->stores;
    }

    /**
     * Add consultant
     *
     * @param \Youppers\DealerBundle\Entity\Consultant $consultant
     *
     * @return Dealer
     */
    public function addConsultant(\Youppers\DealerBundle\Entity\Consultant $consultant)
    {
        $this->consultants[] = $consultant;

        return $this;
    }

    /**
     * Remove consultant
     *
     * @param \Youppers\DealerBundle\Entity\Consultant $consultant
     */
    public function removeConsultant(\Youppers\DealerBundle\Entity\Consultant $consultant)
    {
        $this->consultants->removeElement($consultant);
    }

    /**
     * Get consultants
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getConsultants()
    {
        return $this->consultants;
    }

    /**
     * Set logo
     *
     * @param \Application\Sonata\MediaBundle\Entity\Media $logo
     *
     * @return Dealer
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
