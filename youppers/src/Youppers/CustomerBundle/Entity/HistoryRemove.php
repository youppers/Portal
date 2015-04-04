<?php
namespace Youppers\CustomerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class HistoryRemove extends History
{

	/**
	 * @ORM\ManyToOne(targetEntity="Item")
	 * @return Item
	 */
	protected $item;
	
	public function getDescription()
	{
		return "Remove " . $this->getItem()->getDescription();
	}
	
	public function getType()
	{
		return 'item_remove';
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
     * @return HistoryRemove
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
     * @return HistoryRemove
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
     * Set item
     *
     * @param \Youppers\CustomerBundle\Entity\Item $item
     * @return HistoryRemove
     */
    public function setItem(\Youppers\CustomerBundle\Entity\Item $item = null)
    {
        $this->item = $item;

        return $this;
    }

    /**
     * Get item
     *
     * @return \Youppers\CustomerBundle\Entity\Item 
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Set session
     *
     * @param \Youppers\CustomerBundle\Entity\Session $session
     * @return HistoryRemove
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
