<?php
namespace Youppers\DealerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity
 * @ORM\Table(name="youppers_dealer__box",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="store_box_name_idx", columns={"store_id", "name"}),
 *     @ORM\UniqueConstraint(name="store_box_code_idx", columns={"store_id", "code"}),
 *   })
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity({"name", "store"})
 * @UniqueEntity({"code", "store"})
 * @JMS\ExclusionPolicy("all") 
 */
class Box
{
	/**
	 * @ORM\Column(type="guid")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="UUID")
	 * @JMS\Expose()
	 * @JMS\Groups({"details", "json"})
	 */
	protected $id;

	/**
	 * @ORM\ManyToOne(targetEntity="Store", inversedBy="boxes")
	 * @JMS\Expose()
	 * @JMS\Groups({"details", "json.box.list"})
	 */
	protected $store;
	
	/**
	 * @ORM\Column(type="string", length=60)
	 * @JMS\Expose()
	 * @JMS\Groups({"details", "json"})
	 */
	protected $name;

	/**
	 * @ORM\Column(name="code", type="string", length=20)
	 * @JMS\Expose()
	 * @JMS\Groups({"details", "json"})
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
	 * @JMS\Expose()
	 * @JMS\Groups({"details"})
	 */
	protected $createdAt;
		
	/**
	 * @ORM\Column(type="text", nullable=true )
	 * @JMS\Expose()
	 * @JMS\Groups({"details", "json"})
	 */
	protected $description;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Application\Sonata\MediaBundle\Entity\Media")
	 * @JMS\Expose()
	 * @JMS\Groups({"details", "json.qr.find", "json.box.list", "json.box.show", "json.history.list"})
	 */
	protected $image;
	
	/**
	 * @ORM\OneToMany(targetEntity="BoxProduct", mappedBy="box", cascade={"all"}, orphanRemoval=true)
	 * @ORM\OrderBy({"position" = "ASC"})
	 * @JMS\Expose()
	 * @JMS\Groups({"details", "json.qr.find", "json.box.show"})
	 **/
	private $boxProducts;
		
	/**
	 * @param BoxProduct[] $products
	 */
	public function setBoxProducts($boxProducts)
	{
		$this->boxProducts->clear();
	
		foreach ($boxProducts as $boxProduct) {
			$this->addBoxProduct($boxProduct);
		}
	}
	
	/**
	 * @return BoxProduct[]
	 */
	public function getBoxProducts()
	{
		return $this->boxProducts;
	}
	
	/**
	 * @param BoxProduct $boxProduct
	 * @return void
	 */
	public function addBoxProduct(BoxProduct $boxProduct)
	{
		$boxProduct->setBox($this);
		$this->boxProducts->add($boxProduct);
	}
	
	/**
	 * @param BoxProduct $boxProduct
	 * @return void
	 */
	public function removeBoxProduct(BoxProduct $boxProduct)
	{
		$boxProduct->setBox(null);
		$this->boxProducts->removeElement($boxProduct);
	}

    public function getNameCodeStatus()
    {
        return ($this->getName() ? $this->getName() . ' [' . $this->getCode() . ']': 'New') . ($this->getEnabled() ? '' : " DISABLED ");
    }

	public function __toString()
	{
		return ($this->getEnabled() ? '' : "DISABLED ") . ($this->getName() ? $this->getStore() . ' - ' . $this->getName() . ' [' . $this->getCode() . ']': 'New');
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
	
	/**
	 * @ORM\ManyToOne(targetEntity="Youppers\CommonBundle\Entity\Qr", inversedBy="boxes")
	 */
	protected $qr;
		
	// php app/console doctrine:generate:entities --no-backup YouppersDealerBundle
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->boxProducts = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Box
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
     * Set code
     *
     * @param string $code
     * @return Box
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
     * @return Box
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
     * @return Box
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
     * @return Box
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
     * Set description
     *
     * @param string $description
     * @return Box
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set store
     *
     * @param \Youppers\DealerBundle\Entity\Store $store
     * @return Box
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
     * Set qr
     *
     * @param \Youppers\CommonBundle\Entity\Qr $qr
     * @return Box
     */
    public function setQr(\Youppers\CommonBundle\Entity\Qr $qr = null)
    {
        $this->qr = $qr;

        return $this;
    }

    /**
     * Get qr
     *
     * @return \Youppers\CommonBundle\Entity\Qr 
     */
    public function getQr()
    {
        return $this->qr;
    }

    /**
     * Set image
     *
     * @param \Application\Sonata\MediaBundle\Entity\Media $image
     *
     * @return Box
     */
    public function setImage(\Application\Sonata\MediaBundle\Entity\Media $image = null)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return \Application\Sonata\MediaBundle\Entity\Media
     */
    public function getImage()
    {
        return $this->image;
    }
}
