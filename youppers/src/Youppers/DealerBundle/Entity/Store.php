<?php
namespace Youppers\DealerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="store",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="dealer_store_name_idx", columns={"dealer_id", "name"}),
 *     @ORM\UniqueConstraint(name="dealer_store_code_idx", columns={"dealer_id", "code"}),
 *   })
 * @ORM\HasLifecycleCallbacks
 */
class Store
{
	/**
	 * @ORM\Column(type="guid")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="UUID")
	 */
	protected $id;

	/**
	 * @ORM\ManyToOne(targetEntity="Dealer", inversedBy="stores")
	 */
	protected $dealer;
	
	/**
	 * @ORM\Column(type="string", length=60)
	 */
	protected $name;

	/**
	 * @ORM\Column(name="code", type="string", length=20)
	 */
	protected $code;

	/**
	 * @ORM\Column(type="boolean", options={"default":true})
	 */
	protected $enabled;
	
	/**
	 * @ORM\Column(type="datetime")
	 */
	protected $createdAt;
	
	/**
	 * @ORM\Column(type="text", nullable=true )
	 */
	protected $description;
	
	/**
	 * @ORM\OneToMany(targetEntity="Box", mappedBy="store", cascade={"all"}, orphanRemoval=true)
	 **/
	private $boxes;
	
	/**
	 * @param Box[] $boxes
	 */
	public function setBoxs($boxes)
	{
		$this->boxes->clear();
	
		foreach ($boxes as $box) {
			$this->addBox($box);
		}
	}
	
	/**
	 * @return Box[]
	 */
	public function getBoxs()
	{
		return $this->boxes;
	}
	
	/**
	 * @param Box $box
	 * @return void
	 */
	public function addBox(Box $box)
	{
		$box->setStore($this);
		$this->boxes->add($box);
	}
	
	/**
	 * @param Box $box
	 * @return void
	 */
	public function removeBox(Box $box)
	{
		$box->setStore(null);
		$this->boxes->removeElement($box);
	}
	
	
	public function __toString()
	{
		return $this->getName() ? $this->getDealer() . ' - ' . $this->getName(): 'New';
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
     * Constructor
     */
    public function __construct()
    {
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
     * Set name
     *
     * @param string $name
     * @return Store
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
     * @return Store
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
     * @return Store
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Store
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
     * @return Store
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
     * Set dealer
     *
     * @param \Youppers\DealerBundle\Entity\Dealer $dealer
     * @return Store
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
     * Get boxes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getBoxes()
    {
        return $this->boxes;
    }
}
