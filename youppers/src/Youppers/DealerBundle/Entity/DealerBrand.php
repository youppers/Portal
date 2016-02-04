<?php
namespace Youppers\DealerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="youppers_dealer__dealer_brand",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="dealer_brand_idx", columns={"dealer_id", "brand_id"}),
 *     @ORM\UniqueConstraint(name="dealer_code_idx", columns={"dealer_id", "code"})
 *   })
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity({"brand", "dealer"})
 * @UniqueEntity({"code", "dealer"})
 */
class DealerBrand
{
	/**
	 * @ORM\Column(type="guid")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="UUID")
	 */
	protected $id;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Dealer", inversedBy="dealerBrands")
	 */
	protected $dealer;
	
	/**
	 * @ORM\ManyToOne(targetEntity="\Youppers\CompanyBundle\Entity\Brand")
	 **/
	protected $brand;

	/**
     * @ORM\Column(type="string", length=15)
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
		
	public function __toString()
	{
		return ($this->getDealer() ? $this->getDEaler() : "New") . ($this->getBrand() ? " - " . $this->getBrand() : "New") . " - " . $this->getCode();
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
     * Get id
     *
     * @return guid
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return DealerBrand
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
     *
     * @return DealerBrand
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
     *
     * @return DealerBrand
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
     *
     * @return DealerBrand
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
     * Set dealer
     *
     * @param \Youppers\DealerBundle\Entity\Dealer $dealer
     *
     * @return DealerBrand
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
     * Set brand
     *
     * @param \Youppers\CompanyBundle\Entity\Brand $brand
     *
     * @return DealerBrand
     */
    public function setBrand(\Youppers\CompanyBundle\Entity\Brand $brand = null)
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * Get brand
     *
     * @return \Youppers\CompanyBundle\Entity\Brand
     */
    public function getBrand()
    {
        return $this->brand;
    }
}
