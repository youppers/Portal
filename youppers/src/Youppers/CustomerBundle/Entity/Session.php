<?php
namespace Youppers\CustomerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity
 * @ORM\Table(name="youppers_customer__session")
 * @ORM\HasLifecycleCallbacks
 */
class Session
{
	/**
	 * @ORM\Column(type="guid")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="UUID")
	 * @JMS\Groups({"list", "details","create"})
	 */
	protected $id;

	/**
	 * @ORM\ManyToOne(targetEntity="Profile", inversedBy="sessions")
	 * @JMS\MaxDepth(1)
	 * @JMS\Groups({"list","details","update","create"})
	 */
	protected $profile;
	
	/**
	 * @ORM\ManyToOne(targetEntity="\Youppers\DealerBundle\Entity\Store")
	 * @JMS\MaxDepth(1) // 4
	 * @JMS\Groups({"list","details","update","create"})
	 */
	protected $store;

	/**
	 * @ORM\ManyToOne(targetEntity="\Youppers\DealerBundle\Entity\Consultant")
	 * @JMS\MaxDepth(1) // 4
	 * @JMS\Groups({"list","details","update","create"})
	 */
	protected $consultant;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @JMS\Groups({"list","details","update","create"})
	 */
	protected $name;
	
	/**
	 * @ORM\OneToMany(targetEntity="Item", mappedBy="session")
	 * @var Item[]
	 * @JMS\MaxDepth(1) // 6
	 * @JMS\Groups({"details","json"})
	 */
	protected $items;
	
	/**
	 * @ORM\Column(type="datetime", name="updated_at")
	 * @JMS\Groups({"details","create"})
	 */
	protected $updatedAt;
	
	/**
	 * @ORM\Column(type="datetime", name="created_at")
	 * @JMS\Groups({"list","details","create"})
	 */
	protected $createdAt;
			
	public function __toString()
	{
		return ($this->getProfile() ? $this->getProfile() . ' - ':'') 
			 . ($this->getStore() ? $this->getStore() . ' - ':'')
			 . ($this->getCreatedAt() ? $this->getCreatedAt()->format('c'): 'New');
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
	
	// php app/console doctrine:generate:entities --no-backup YouppersCustomerBundle:Session
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->items = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Session
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
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return Session
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
     * @return Session
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
     * Set profile
     *
     * @param \Youppers\CustomerBundle\Entity\Profile $profile
     * @return Session
     */
    public function setProfile(\Youppers\CustomerBundle\Entity\Profile $profile = null)
    {
        $this->profile = $profile;

        return $this;
    }

    /**
     * Get profile
     *
     * @return \Youppers\CustomerBundle\Entity\Profile 
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * Set store
     *
     * @param \Youppers\DealerBundle\Entity\Store $store
     * @return Session
     */
    public function setStore(\Youppers\DealerBundle\Entity\Store $store = null)
    {
        $this->store = $store;

        return $this;
    }

    /**
     * Get store
     *
     * @return \Youppers\DealerBundle\Entity\Store 
     */
    public function getStore()
    {
        return $this->store;
    }

    /**
     * Set consultant
     *
     * @param \Youppers\DealerBundle\Entity\Consultant $consultant
     * @return Session
     */
    public function setConsultant(\Youppers\DealerBundle\Entity\Consultant $consultant = null)
    {
        $this->consultant = $consultant;

        return $this;
    }

    /**
     * Get consultant
     *
     * @return \Youppers\DealerBundle\Entity\Consultant 
     */
    public function getConsultant()
    {
        return $this->consultant;
    }

    /**
     * Add items
     *
     * @param \Youppers\CustomerBundle\Entity\Item $items
     * @return Session
     */
    public function addItem(\Youppers\CustomerBundle\Entity\Item $items)
    {
        $this->items[] = $items;

        return $this;
    }

    /**
     * Remove items
     *
     * @param \Youppers\CustomerBundle\Entity\Item $items
     */
    public function removeItem(\Youppers\CustomerBundle\Entity\Item $items)
    {
        $this->items->removeElement($items);
    }

    /**
     * Get items
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getItems()
    {
        return $this->items;
    }
}
