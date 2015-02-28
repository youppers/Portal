<?php
namespace Youppers\CustomerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

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
	 */
	protected $id;

	/**
	 * @ORM\ManyToOne(targetEntity="Profile", inversedBy="sessions")
	 */
	protected $profile;
	
	/**
	 * @ORM\ManyToOne(targetEntity="\Youppers\DealerBundle\Entity\Store")
	 */
	protected $store;

	/**
	 * @ORM\ManyToOne(targetEntity="\Youppers\DealerBundle\Entity\Consultant")
	 */
	protected $consultant;
	
	/**
	 * @ORM\OneToMany(targetEntity="Item", mappedBy="session")
	 * @var Item[]
	 */
	protected $items;
	
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
	
	// php app/console doctrine:generate:entities --no-backup YouppersCustomerBundle

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
}
