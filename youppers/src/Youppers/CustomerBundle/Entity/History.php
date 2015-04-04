<?php
namespace Youppers\CustomerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Youppers\DealerBundle\Entity\Box;
use Youppers\ProductBundle\Entity\ProductVariant;
use Doctrine\Common\Util\ClassUtils;

/**
 * @ORM\Entity
 * @ORM\Table(name="youppers_customer__history")
 * @ORM\HasLifecycleCallbacks
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string", length=20)
 * @ORM\DiscriminatorMap({
 * 		"qr_box" = "HistoryQrBox",
 * 		"qr_variant" = "HistoryQrVariant",
 * 		"item_add" = "HistoryAdd",
 * 		"item_remove" = "HistoryRemove",
 * 	})
 *  */
abstract class History
{
	/**
	 * @ORM\Column(type="guid")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="UUID")
     * @var guid
	 */
	protected $id;

	/**
	 * @ORM\ManyToOne(targetEntity="Session", inversedBy="history")
     * @var \Youppers\CustomerBundle\Entity\Session
	 */
	protected $session;
		
	/**
	 * @ORM\Column(type="datetime", name="updated_at")
     * @var \DateTime
	 */
	protected $updatedAt;
	
	/**
	 * @ORM\Column(type="datetime", name="created_at")
     * @var \DateTime
	 */
	protected $createdAt;
	
	public function __toString()
	{
		return $this->getCreatedAt() ? $this->getType() . '@' . $this->getCreatedAt()->format('c') : 'New';
	}
		
	public abstract function getType();
	
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
	
	public abstract function getDescription();
	
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
     * @return History
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
     * @return History
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
     * @return History
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
}
