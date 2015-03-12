<?php
namespace Youppers\CommonBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Doctrine\Common\Collections\Criteria;

/**
 * @ORM\Entity
 * @ORM\Table(name="youppers__qr")
 * @ORM\HasLifecycleCallbacks
 * @Serializer\ExclusionPolicy("all") 
 */
class Qr
{
	/**
	 * @ORM\Column(type="guid")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="UUID")
	 * @Serializer\Expose()
	 * @Serializer\Groups({"list", "details"})
	 */
	protected $id;
		
	/**
	 * @ORM\Column(type="string", name="target_type")
	 * @Serializer\Expose()
	 * @Serializer\Groups({"list", "details", "json"})
	 */
	protected $targetType;

	/**
	 * @ORM\Column(type="boolean", options={"default":true})
	 * @Serializer\Groups({"list", "details", "json"})
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
	 * @ORM\OneToMany(targetEntity="\Youppers\CompanyBundle\Entity\Product", mappedBy="qr", fetch="EAGER")
	 **/
	protected $products;

	/**
	 * @ORM\OneToMany(targetEntity="\Youppers\DealerBundle\Entity\Box", mappedBy="qr", fetch="EAGER")
	 **/
	protected $boxes;

	/**
	 * @Serializer\VirtualProperty
	 * @Serializer\SerializedName("targets")
	 * @Serializer\Groups({"json"})
	 */
	public function getTargets()
	{
		$criteria = Criteria::create()
			->where(Criteria::expr()->eq("enabled", true));
		$boxes = $this->getBoxes()->matching($criteria);
		if (count($boxes)) {
			return $boxes;
		}
		$products = $this->getProducts()->matching($criteria);
		if (count($products)) {
			return $products;
		}
	}
	
	/**
	 * @ORM\PrePersist()
	 */
	public function prePersist()
	{
		$this->createdAt = new \DateTime();
		$this->updatedAt = new \DateTime();
		if ($this->enabled == null) {
			$this->enabled = false;
		}
	}
	
	/**
	 * @ORM\PreUpdate()
	 */
	public function preUpdate()
	{
		$this->updatedAt = new \DateTime();
	}			
	
	// php app/console doctrine:generate:entities --no-backup YouppersCommonBundle
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->products = new \Doctrine\Common\Collections\ArrayCollection();
        $this->boxes = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set targetType
     *
     * @param string $targetType
     * @return Qr
     */
    public function setTargetType($targetType)
    {
        $this->targetType = $targetType;

        return $this;
    }

    /**
     * Get targetType
     *
     * @return string 
     */
    public function getTargetType()
    {
        return $this->targetType;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     * @return Qr
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
     * @return Qr
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
     * @return Qr
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
     * Add products
     *
     * @param \Youppers\CompanyBundle\Entity\Product $products
     * @return Qr
     */
    public function addProduct(\Youppers\CompanyBundle\Entity\Product $products)
    {
        $this->products[] = $products;

        return $this;
    }

    /**
     * Remove products
     *
     * @param \Youppers\CompanyBundle\Entity\Product $products
     */
    public function removeProduct(\Youppers\CompanyBundle\Entity\Product $products)
    {
        $this->products->removeElement($products);
    }

    /**
     * Get products
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * Add boxes
     *
     * @param \Youppers\DealerBundle\Entity\Box $boxes
     * @return Qr
     */
    public function addBox(\Youppers\DealerBundle\Entity\Box $boxes)
    {
        $this->boxes[] = $boxes;

        return $this;
    }

    /**
     * Remove boxes
     *
     * @param \Youppers\DealerBundle\Entity\Box $boxes
     */
    public function removeBox(\Youppers\DealerBundle\Entity\Box $boxes)
    {
        $this->boxes->removeElement($boxes);
    }

    /**
     * Get boxes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getBoxes()
    {
        return $this->boxes;
    }
}
