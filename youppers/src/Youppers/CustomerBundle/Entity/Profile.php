<?php
namespace Youppers\CustomerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as Validator;
use Application\Sonata\UserBundle\Entity\User;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity
 * @ORM\Table(name="youppers_customer__profile",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="user_name_idx", columns={"user_id", "name"}),
 *   })
 * @Validator\UniqueEntity({"name", "user"})
 * @ORM\HasLifecycleCallbacks
 */
class Profile
{
	/**
	 * @ORM\Column(type="guid")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="UUID")
	 * @JMS\Groups({"list","details","update","create","json.session.read"})
	 */
	protected $id;

	/**
	 * @ORM\ManyToOne(targetEntity="\Application\Sonata\UserBundle\Entity\User")
	 * @return User
	 * @JMS\Groups({"list","details","update","create","json.session.read"})
	 */
	protected $user;

	/**
	 * @ORM\Column(type="string")
	 * @JMS\Groups({"list","details","update","create","json.session.read"})
	 */
	protected $name;
	
	/**
	 * @ORM\OneToMany(targetEntity="Session", mappedBy="profile")
	 * @return User
	 */
	protected $sessions;
	
	/**
	 * @ORM\Column(type="datetime", name="updated_at")
	 * @JMS\Groups({"details"})
	 */
	protected $updatedAt;
	
	/**
	 * @ORM\Column(type="datetime", name="created_at")
	 * @JMS\Groups({"details","update"})
	 */
	protected $createdAt;
			
	public function __toString()
	{
		return
			($this->getName()?:'New') .
			($this->getUser() ? '@' . $this->getUser() :'@Anonymous');
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
	
	// php app/console doctrine:generate:entities --no-backup YouppersCustomerBundle:Profile
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->sessions = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Profile
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
     * @return Profile
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
     * @return Profile
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
     * Set user
     *
     * @param \Application\Sonata\UserBundle\Entity\User $user
     * @return Profile
     */
    public function setUser(\Application\Sonata\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Application\Sonata\UserBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Add sessions
     *
     * @param \Youppers\CustomerBundle\Entity\Session $sessions
     * @return Profile
     */
    public function addSession(\Youppers\CustomerBundle\Entity\Session $sessions)
    {
        $this->sessions[] = $sessions;

        return $this;
    }

    /**
     * Remove sessions
     *
     * @param \Youppers\CustomerBundle\Entity\Session $sessions
     */
    public function removeSession(\Youppers\CustomerBundle\Entity\Session $sessions)
    {
        $this->sessions->removeElement($sessions);
    }

    /**
     * Get sessions
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSessions()
    {
        return $this->sessions;
    }
}
