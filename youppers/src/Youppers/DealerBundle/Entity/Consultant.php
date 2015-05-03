<?php
namespace Youppers\DealerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as Validator;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity
 * @ORM\Table(name="youppers_dealer__consultant",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="dealer_consultant_name_idx", columns={"dealer_id", "fullname"}),
 *     @ORM\UniqueConstraint(name="dealer_consultant_code_idx", columns={"dealer_id", "code"}),
 *   })
 * @ORM\HasLifecycleCallbacks
 * @Validator\UniqueEntity(fields={"fullname", "dealer"},ignoreNull=false)
 * @Validator\UniqueEntity(fields={"code", "dealer"},ignoreNull=false)
 * @JMS\ExclusionPolicy("all")
 */
class Consultant
{
	/**
	 * @ORM\Column(type="guid")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="UUID")
	 * @JMS\Expose()
	 * @JMS\Groups({"list","details","update","create","json.consultant.list", "json.session.read"})
	 */
	protected $id;

	/**
	 * @ORM\ManyToOne(targetEntity="\Application\Sonata\UserBundle\Entity\User")
	 * @JMS\Groups({"details","update"})
	 */
	protected $user;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Dealer", inversedBy="consultants")
	 * @JMS\MaxDepth(1)
	 * @JMS\Expose()
	 */
	protected $dealer;

	/**
	 * @ORM\ManyToMany(targetEntity="Store", inversedBy="consultants",  cascade={"all"})
	 * @ORM\JoinTable(name="youppers_dealer__consultants_stores")
	 * @JMS\MaxDepth(2)
	 * @JMS\Expose()
	 */
	protected $stores;
	
	/**
	 * @ORM\Column(type="boolean", options={"default":true})
	 * @JMS\Expose()
	 * @JMS\Groups({"list","details","update","create", "json"})
	 */
	protected $enabled;
	
	/**
	 * @ORM\Column(type="boolean", options={"default":true})
	 * @JMS\Expose()
	 * @JMS\Groups({"list","details","update","create","json"})
	 */
	protected $available;
	
	/**
	 * @ORM\Column(name="code", type="string", length=20)
	 * @JMS\Expose()
	 * @JMS\Groups({"list","details","update","create","json"})
	 */
	protected $code;
	
	/**
	 * @ORM\Column(type="string")
	 * @JMS\Expose()
	 * @JMS\Groups({"list","details","update","create","json"})
	 */
	protected $fullname;

	/**
	 * @ORM\Column(type="text", nullable=true )
	 * @JMS\Expose()
	 * @JMS\Groups({"list","details","update","create","json"})
	 */
	protected $description;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Application\Sonata\MediaBundle\Entity\Media", cascade={"persist"}, fetch="LAZY")
	 * @JMS\Expose()
	 * @JMS\Groups({"list","details","update","create","json"})
	 */
	protected $photo;	
	
	/**
	 * @ORM\Column(type="datetime", name="updated_at")
	 */
	protected $updatedAt;
	
	/**
	 * @ORM\Column(type="datetime", name="created_at")
	 * @JMS\Expose()
	 * @JMS\Groups({"details","update","create"})
	 */
	protected $createdAt;

	public function __toString()
	{
		return $this->getFullname() ? $this->getDealer() . ' - ' . $this->getFullname(): 'New';
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
	
	// php app/console doctrine:generate:entities --no-backup YouppersDealerBundle:Consultant
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->stores = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set enabled
     *
     * @param boolean $enabled
     * @return Consultant
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
     * Set code
     *
     * @param string $code
     * @return Consultant
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
     * Set fullname
     *
     * @param string $fullname
     * @return Consultant
     */
    public function setFullname($fullname)
    {
        $this->fullname = $fullname;

        return $this;
    }

    /**
     * Get fullname
     *
     * @return string 
     */
    public function getFullname()
    {
        return $this->fullname;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Consultant
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
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return Consultant
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
     * @return Consultant
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
     * Set user
     *
     * @param \Application\Sonata\UserBundle\Entity\User $user
     * @return Consultant
     */
    public function setUser(\Application\Sonata\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Application\Sonata\UserBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set dealer
     *
     * @param \Youppers\DealerBundle\Entity\Dealer $dealer
     * @return Consultant
     */
    public function setDealer(\Youppers\DealerBundle\Entity\Dealer $dealer = null)
    {
        $this->dealer = $dealer;

        return $this;
    }

    /**
     * Get dealer
     *
     * @return \Youppers\DealerBundle\Entity\Dealer 
     */
    public function getDealer()
    {
        return $this->dealer;
    }

    /**
     * Add stores
     *
     * @param \Youppers\DealerBundle\Entity\Store $stores
     * @return Consultant
     */
    public function addStore(\Youppers\DealerBundle\Entity\Store $stores)
    {
        $this->stores[] = $stores;

        return $this;
    }

    /**
     * Remove stores
     *
     * @param \Youppers\DealerBundle\Entity\Store $stores
     */
    public function removeStore(\Youppers\DealerBundle\Entity\Store $stores)
    {
        $this->stores->removeElement($stores);
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
     * Set photo
     *
     * @param \Application\Sonata\MediaBundle\Entity\Media $photo
     * @return Consultant
     */
    public function setPhoto(\Application\Sonata\MediaBundle\Entity\Media $photo = null)
    {
        $this->photo = $photo;

        return $this;
    }

    /**
     * Get photo
     *
     * @return \Application\Sonata\MediaBundle\Entity\Media 
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * Set available
     *
     * @param boolean $available
     * @return Consultant
     */
    public function setAvailable($available)
    {
        $this->available = $available;

        return $this;
    }

    /**
     * Get available
     *
     * @return boolean 
     */
    public function getAvailable()
    {
        return $this->available;
    }
}
