<?php
namespace Youppers\CustomerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Youppers\DealerBundle\Entity\Box;

/**
 * @ORM\Entity
 */
class HistoryQrBox extends History
{
	
	/**
	 * @ORM\ManyToOne(targetEntity="Youppers\DealerBundle\Entity\Box")
	 * @return Box
	 */
	protected $box;
		
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
     * @return HistoryQrBox
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
     * @return HistoryQrBox
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
     * Set box
     *
     * @param \Youppers\DealerBundle\Entity\Box $box
     * @return HistoryQrBox
     */
    public function setBox(\Youppers\DealerBundle\Entity\Box $box = null)
    {
        $this->box = $box;

        return $this;
    }

    /**
     * Get box
     *
     * @return \Youppers\DealerBundle\Entity\Box 
     */
    public function getBox()
    {
        return $this->box;
    }

    /**
     * Set session
     *
     * @param \Youppers\CustomerBundle\Entity\Session $session
     * @return HistoryQrBox
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
