<?php
namespace Youppers\CustomerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Youppers\ProductBundle\Entity\ProductVariant;

/**
 * @ORM\Entity
 */
class HistoryQrVariant extends History
{

	/**
	 * @ORM\ManyToOne(targetEntity="Youppers\ProductBundle\Entity\ProductVariant")
	 * @return ProductVariant
	 */
	protected $variant;
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
     * @return HistoryQrVariant
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
     * @return HistoryQrVariant
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
     * Set variant
     *
     * @param \Youppers\ProductBundle\Entity\ProductVariant $variant
     * @return HistoryQrVariant
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
     * Set session
     *
     * @param \Youppers\CustomerBundle\Entity\Session $session
     * @return HistoryQrVariant
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
