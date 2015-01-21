<?php
namespace Youppers\DealerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="dealer")
 * @ORM\HasLifecycleCallbacks
 */
class Dealer
{
	/**
	 * @ORM\Column(type="guid")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="UUID")
	 */
	protected $id;

	/**
	 * @ORM\Column(type="string", length=60, unique=true)
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
	 * @ORM\OneToMany(targetEntity="Store", mappedBy="dealer")
	 **/
	private $stores;
	
	
	public function __toString()
	{
		return $this->getName();
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
     * Set name
     *
     * @param string $name
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
     * Set enabled
     *
     * @param boolean $enabled
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
     * Add stores
     *
     * @param \Youppers\DealerBundle\Entity\Store $stores
     * @return Dealer
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
}
