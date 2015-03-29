<?php
namespace Youppers\CustomerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as Validator;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity
 * @ORM\Table(name="youppers_customer__zone",
 * 		uniqueConstraints={
 * 			@ORM\UniqueConstraint(name="name_profile",columns={"name","profile_id"})
 * 		}
 * )
 * @ORM\HasLifecycleCallbacks
 * @Validator\UniqueEntity(
 * 		fields={"name", "profile"},
 * 		ignoreNull=false
 * )
 */
class Zone
{
	/**
	 * @ORM\Column(type="guid")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="UUID")
	 * @JMS\Groups({"list", "details","create","json.zone.read","json.zone.list", "json.item.list", "json.item.read"})
	 */
	protected $id;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Profile")
	 * @var Profile
	 * @JMS\Groups({"list", "details","create","json.zone.read","json.zone.list", "json.item.list", "json.item.read"})
	 */
	protected $profile;
	
	/**
	 * @ORM\Column(type="string")
	 * @JMS\Groups({"list", "details","create","json"})
	 */
	protected $name;
		
	/**
	 * @ORM\Column(type="datetime", name="updated_at")
	 */
	protected $updatedAt;
	
	/**
	 * @ORM\Column(type="datetime", name="created_at")
	 * @JMS\Groups({"list", "details","create","json.zone.read"})
	 */
	protected $createdAt;
			
	public function __toString()
	{
		return $this->getName() ? : 'New';
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
	
	// php app/console doctrine:generate:entities --no-backup YouppersCustomerBundle:Zone

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
     * @return Zone
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
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return Zone
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
     * @return Zone
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
     * Set profile
     *
     * @param \Youppers\CustomerBundle\Entity\Profile $profile
     * @return Zone
     */
    public function setProfile(\Youppers\CustomerBundle\Entity\Profile $profile = null)
    {
        $this->profile = $profile;

        return $this;
    }

    /**
     * Get profile
     *
     * @return \Youppers\CustomerBundle\Entity\Profile 
     */
    public function getProfile()
    {
        return $this->profile;
    }
}
