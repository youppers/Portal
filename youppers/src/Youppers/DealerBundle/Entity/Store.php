<?php
namespace Youppers\DealerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="youppers_dealer__store",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="dealer_store_name_idx", columns={"dealer_id", "name"}),
 *     @ORM\UniqueConstraint(name="dealer_store_code_idx", columns={"dealer_id", "code"}),
 *   })
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity({"name", "dealer"})
 * @UniqueEntity({"code", "dealer"})
 * @Serializer\ExclusionPolicy("all") 
 */
class Store
{
	/**
	 * @ORM\Column(type="guid")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="UUID")
	 * @Serializer\Expose()
	 * @Serializer\Groups({"details", "json.session.read", "json.box.list"})
	 */
	protected $id;

	/**
	 * @ORM\ManyToOne(targetEntity="Dealer", inversedBy="stores")
	 * @Serializer\Expose()
	 * @Serializer\Groups({"details", "json.session.read", "json.box.list"})
	 */
	protected $dealer;
	
	/**
	 * @ORM\Column(type="string", length=60)
	 * @Serializer\Expose()
	 * @Serializer\Groups({"details", "json.session.read", "json.box.list"})
	 */
	protected $name;

	/**
	 * @ORM\ManyToOne(targetEntity="\Youppers\CommonBundle\Entity\Geoid")
	 * @Serializer\Expose()
	 * @Serializer\Groups({"details", "json.box.list"})
	 **/
	protected $geoid;
	
	/**
	 * @ORM\Column(name="code", type="string", length=20)
	 * @Serializer\Expose()
	 * @Serializer\Groups({"details", "json.session.read", "json.box.list"})
	 */
	protected $code;

	/**
	 * @ORM\Column(type="string")
	 * @Assert\Email
	 * @var string
	 */
	protected $email;
	
	/**
	 * @ORM\Column(type="boolean", options={"default":true})
	 * @Serializer\Expose()
	 * @Serializer\Groups({"details", "json.session.read"})
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
	 * @ORM\Column(type="text", nullable=true )
	 * @Serializer\Expose()
	 * @Serializer\Groups({"details", "json"})
	 */
	protected $description;
	
	/**
	 * @ORM\ManyToMany(targetEntity="Consultant", mappedBy="stores",  cascade={"all"})
	 * @ORM\JoinTable(name="youppers_dealer__consultants_stores")
	 **/
	protected $consultants;
		
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
		return $this->getName() ? $this->getDealer() . ' - ' . $this->getName() . ' [' . $this->getCode() . ']': 'New';
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
	
	// php app/console doctrine:generate:entities --no-backup YouppersDealerBundle:Store

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->consultants = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return Store
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
     * Set geoid
     *
     * @param \Youppers\CommonBundle\Entity\Geoid $geoid
     * @return Store
     */
    public function setGeoid(\Youppers\CommonBundle\Entity\Geoid $geoid = null)
    {
        $this->geoid = $geoid;

        return $this;
    }

    /**
     * Get geoid
     *
     * @return \Youppers\CommonBundle\Entity\Geoid 
     */
    public function getGeoid()
    {
        return $this->geoid;
    }

    /**
     * Add consultants
     *
     * @param \Youppers\DealerBundle\Entity\Consultant $consultants
     * @return Store
     */
    public function addConsultant(\Youppers\DealerBundle\Entity\Consultant $consultants)
    {
        $this->consultants[] = $consultants;

        return $this;
    }

    /**
     * Remove consultants
     *
     * @param \Youppers\DealerBundle\Entity\Consultant $consultants
     */
    public function removeConsultant(\Youppers\DealerBundle\Entity\Consultant $consultants)
    {
        $this->consultants->removeElement($consultants);
    }

    /**
     * Get consultants
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getConsultants()
    {
        return $this->consultants;
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

    /**
     * Set email
     *
     * @param string $email
     * @return Store
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }
}
