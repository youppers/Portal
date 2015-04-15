<?php
namespace Youppers\CustomerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation as JMS;
use Doctrine\Common\Collections\Criteria;

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
	 * @JMS\Groups({"list", "details","create","json.session.read"})
	 */
	protected $id;

	/**
	 * @ORM\ManyToOne(targetEntity="Profile", inversedBy="sessions")
	 * @JMS\Groups({"list","details","update","create","json.session.read"})
	 */
	protected $profile;
	
	/**
	 * @ORM\ManyToOne(targetEntity="\Youppers\DealerBundle\Entity\Store")
	 * @JMS\Groups({"list","details","update","create","json.session.read"})
	 */
	protected $store;

	/**
	 * @ORM\ManyToOne(targetEntity="\Youppers\DealerBundle\Entity\Consultant")
	 * @JMS\Groups({"list","details","update","create","json.session.read"})
	 */
	protected $consultant;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @JMS\Groups({"list","details","update","create","json.session.read"})
	 */
	protected $name;
	
	/**
	 * @ORM\Column(type="boolean")
	 * @JMS\Groups({"list","details","update","create","json.session.read"})
	 */
	protected $removed;
	
	/**
	 * @ORM\OneToMany(targetEntity="Item", mappedBy="session")
	 * @ORM\OrderBy({"zone" = "ASC"})
	 * @var Item[]
	 * @JMS\Groups({"details"})
	 */
	protected $items;
	
	/**
	 * @ORM\OneToMany(targetEntity="History", mappedBy="session")
	 * @ORM\OrderBy({"createdAt" = "DESC"})
	 * @var History[]
	 * @JMS\Groups({"details"})
	 */
	protected $history;
	
	/**
	 * @ORM\Column(type="datetime", name="updated_at")
	 * @JMS\Groups({"details"})
	 */
	protected $updatedAt;
	
	/**
	 * @ORM\Column(type="datetime", name="created_at")
	 * @JMS\Groups({"list","details","create","json.session.read"})
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
		if (null === $this->getRemoved()) {
			$this->setRemoved(false);
		}
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

    /**
     * Set removed
     *
     * @param boolean $removed
     * @return Session
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
     * Add history
     *
     * @param \Youppers\CustomerBundle\Entity\History $history
     * @return Session
     */
    public function addHistory(\Youppers\CustomerBundle\Entity\History $history)
    {
        $this->history[] = $history;

        return $this;
    }

    /**
     * Remove history
     *
     * @param \Youppers\CustomerBundle\Entity\History $history
     */
    public function removeHistory(\Youppers\CustomerBundle\Entity\History $history)
    {
        $this->history->removeElement($history);
    }

    /**
     * Get history
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getHistory()
    {
        return $this->history;
    }
}
