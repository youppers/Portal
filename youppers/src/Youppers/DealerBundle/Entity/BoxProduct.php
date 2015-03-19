<?php
namespace Youppers\DealerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity
 * @ORM\Table(name="youppers_dealer__box_product",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="box_product_idx", columns={"box_id", "product_id"}),
 *     @ORM\UniqueConstraint(name="box_name_idx", columns={"box_id", "name"})
 *   })
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity({"name", "box"})
 * @UniqueEntity({"product", "box"})
 * @Serializer\ExclusionPolicy("all") 
 */
class BoxProduct
{
	/**
	 * @ORM\Column(type="guid")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="UUID")
	 * @Serializer\Groups({"details"})
	 */
	protected $id;
	
	/**
	 * @ORM\Column(type="string", length=60)
	 * @Serializer\Expose()
	 * @Serializer\Groups({"details", "json.qr.find"})
	 */
	protected $name;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Box", inversedBy="boxProducts")
	 * @Serializer\Expose()
	 * @Serializer\Groups({"details"})
	 */
	protected $box;
	
	/**
	 * @ORM\ManyToOne(targetEntity="\Youppers\CompanyBundle\Entity\Product")
	 * @Serializer\Expose()
	 * @Serializer\Groups({"details", "json.qr.find"})
	 **/
	protected $product;

	/**
	 * @ORM\Column(type="integer")
	 * @Serializer\Expose()
	 * @Serializer\Groups({"details", "json.qr.find"})
	 */
	protected $position;
	
	/**
	 * @ORM\Column(type="boolean", options={"default":true})
	 * @Serializer\Expose()
	 * @Serializer\Groups({"details"})
	 */
	protected $enabled;
	
	/**
	 * @ORM\Column(type="datetime", name="updated_at")
	 */
	protected $updatedAt;
	
	/**
	 * @ORM\Column(type="datetime", name="created_at")
	 * @Serializer\Expose()
	 * @Serializer\Groups({"details"})
	 */
	protected $createdAt;
		
	public function __toString()
	{
		return ($this->getBox() ? $this->getBox() : "New") . ($this->getName() ? " - " . $this->getName() : "New") . ($this->getProduct() ? " - " . $this->getProduct() : "");
	}
	
	public function getNameProduct() {
		return ($this->getName() ? $this->getName() : "New") . ($this->getProduct() ? " - " . $this->getProduct() : "");		
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
     * Set name
     *
     * @param string $name
     * @return BoxProduct
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
     * Set position
     *
     * @param integer $position
     * @return BoxProduct
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return integer 
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     * @return BoxProduct
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
     * @return BoxProduct
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
     * @return BoxProduct
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
     * @return BoxProduct
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
     * Set product
     *
     * @param \Youppers\CompanyBundle\Entity\Product $product
     * @return BoxProduct
     */
    public function setProduct(\Youppers\CompanyBundle\Entity\Product $product = null)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get product
     *
     * @return \Youppers\CompanyBundle\Entity\Product 
     */
    public function getProduct()
    {
        return $this->product;
    }
}
