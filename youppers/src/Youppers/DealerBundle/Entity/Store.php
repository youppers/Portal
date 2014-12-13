<?php
namespace Youppers\DealerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="store")
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
	 * @ORM\Column(type="string", length=60, unique=true)
	 */
	protected $name;

	/**
	 * @ORM\Column(name="code", type="string", length=20, unique=true)
	 */
	protected $code;

	/**
	 * @ORM\Column(type="boolean", name="is_active", options={"default":true})
	 */
	protected $isActive;
	
	/**
	 * @ORM\Column(type="datetime")
	 */
	protected $createdAt;
	
	/**
	 * @ORM\Column(type="text", nullable=true )
	 */
	protected $description;
	
	/**
	 * @ORM\OneToMany(targetEntity="Box", mappedBy="store")
	 **/
	private $boxes;
	
	public function __toString()
	{
		return $this->getName();
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
     * Set isActive
     *
     * @param boolean $isActive
     * @return Store
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean 
     */
    public function getIsActive()
    {
        return $this->isActive;
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
     * Constructor
     */
    public function __construct()
    {
        $this->boxes = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add boxes
     *
     * @param \Youppers\DealerBundle\Entity\Box $boxes
     * @return Store
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
