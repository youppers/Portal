<?php
namespace Youppers\CustomerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="youppers_customer__item")
 * @ORM\HasLifecycleCallbacks
 */
class Item
{
	/**
	 * @ORM\Column(type="guid")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="UUID")
	 */
	protected $id;
	
	/**
	 * @ORM\Column(type="boolean")
	 */
	protected $removed;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Session", inversedBy="items")
	 * @var Session
	 */
	protected $session;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Youppers\ProductBundle\Entity\ProductVariant")
	 * @return ProductVariant
	 */
	protected $variant;
	
	/**
	 * @ORM\ManyToMany(targetEntity="Zone")
	 * @ORM\JoinTable(name="youppers_customer__item_zone")
	 */
	protected $zones;	
		
	/**
	 * @ORM\Column(type="datetime", name="updated_at")
	 */
	protected $updatedAt;
	
	/**
	 * @ORM\Column(type="datetime", name="created_at")
	 */
	protected $createdAt;
			
	public function __toString()
	{
		return $this->getVariant() ? $this->getVariant() . '@' . implode($this->getZones()->toArray(),',') : 'New';
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
	
	// php app/console doctrine:generate:entities --no-backup YouppersCustomerBundle:Item
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->zones = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set removed
     *
     * @param boolean $removed
     * @return Item
     */
    public function setRemoved($removed)
    {
        $this->removed = $removed;

        return $this;
    }

    /**
     * Get removed
     *
     * @return boolean 
     */
    public function getRemoved()
    {
        return $this->removed;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return Item
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
     * @return Item
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
     * Set session
     *
     * @param \Youppers\CustomerBundle\Entity\Session $session
     * @return Item
     */
    public function setSession(\Youppers\CustomerBundle\Entity\Session $session = null)
    {
        $this->session = $session;

        return $this;
    }

    /**
     * Get session
     *
     * @return \Youppers\CustomerBundle\Entity\Session 
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * Set variant
     *
     * @param \Youppers\ProductBundle\Entity\ProductVariant $variant
     * @return Item
     */
    public function setVariant(\Youppers\ProductBundle\Entity\ProductVariant $variant = null)
    {
        $this->variant = $variant;

        return $this;
    }

    /**
     * Get variant
     *
     * @return \Youppers\ProductBundle\Entity\ProductVariant 
     */
    public function getVariant()
    {
        return $this->variant;
    }

    /**
     * Add zones
     *
     * @param \Youppers\CustomerBundle\Entity\Zone $zones
     * @return Item
     */
    public function addZone(\Youppers\CustomerBundle\Entity\Zone $zones)
    {
        $this->zones[] = $zones;

        return $this;
    }

    /**
     * Remove zones
     *
     * @param \Youppers\CustomerBundle\Entity\Zone $zones
     */
    public function removeZone(\Youppers\CustomerBundle\Entity\Zone $zones)
    {
        $this->zones->removeElement($zones);
    }

    /**
     * Get zones
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getZones()
    {
        return $this->zones;
    }
}
