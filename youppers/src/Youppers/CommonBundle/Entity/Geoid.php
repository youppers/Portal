<?php
namespace Youppers\CommonBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity
 * @ORM\Table(name="youppers__geoid")
 * @ORM\HasLifecycleCallbacks
 * @Serializer\ExclusionPolicy("all") 
 */
class Geoid
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
	 * @ORM\Column(type="string", name="criteria_id", unique=true)
	 * @Serializer\Expose()
	 * @Serializer\Groups({"list", "details", "json"})
	 */
	protected $criteriaId;
	
	/**
	 * @ORM\Column(type="string", name="name")
	 * @Serializer\Expose()
	 * @Serializer\Groups({"list", "details", "json"})
	 */
	protected $name;

	/**
	 * @ORM\Column(type="string", name="canonical_name")
	 * @Serializer\Expose()
	 * @Serializer\Groups({"list", "details", "json"})
	 */
	protected $canonicalName;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Geoid")
	 * @Serializer\Expose()
	 * @Serializer\Groups({"list", "details", "json"})
	 * @Serializer\MaxDepth(1)
	 **/
	protected $parent;

	/**
	 * @ORM\Column(type="string", length=2, name="country_code")
	 * @Serializer\Expose()
	 * @Serializer\Groups({"list", "details", "json"})
	 */
	protected $countryCode;
	
	/**
	 * @ORM\Column(type="string", name="target_type")
	 * @Serializer\Expose()
	 * @Serializer\Groups({"list", "details", "json"})
	 */
	protected $targetType;
		
	/**
	 * @ORM\Column(type="string")
	 * @Serializer\Expose()
	 * @Serializer\Groups({"list", "details", "json"})
	 */
	protected $status;
	
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
	
	public function __toString()
	{
		return $this->getCanonicalName() ? : 'New';
	}
	
	// php app/console doctrine:generate:entities --no-backup YouppersCommonBundle:Geoid


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
     * Set criteriaId
     *
     * @param string $criteriaId
     * @return Geoid
     */
    public function setCriteriaId($criteriaId)
    {
        $this->criteriaId = $criteriaId;

        return $this;
    }

    /**
     * Get criteriaId
     *
     * @return string 
     */
    public function getCriteriaId()
    {
        return $this->criteriaId;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Geoid
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
     * Set canonicalName
     *
     * @param string $canonicalName
     * @return Geoid
     */
    public function setCanonicalName($canonicalName)
    {
        $this->canonicalName = $canonicalName;

        return $this;
    }

    /**
     * Get canonicalName
     *
     * @return string 
     */
    public function getCanonicalName()
    {
        return $this->canonicalName;
    }

    /**
     * Set countryCode
     *
     * @param string $countryCode
     * @return Geoid
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     * Get countryCode
     *
     * @return string 
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * Set targetType
     *
     * @param string $targetType
     * @return Geoid
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
     * Set status
     *
     * @param string $status
     * @return Geoid
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     * @return Geoid
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
     * @return Geoid
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
     * @return Geoid
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
     * Set parent
     *
     * @param \Youppers\CommonBundle\Entity\Geoid $parent
     * @return Geoid
     */
    public function setParent(\Youppers\CommonBundle\Entity\Geoid $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return \Youppers\CommonBundle\Entity\Geoid 
     */
    public function getParent()
    {
        return $this->parent;
    }
}
