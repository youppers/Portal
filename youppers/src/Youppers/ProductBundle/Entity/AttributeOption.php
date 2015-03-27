<?php
namespace Youppers\ProductBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as Validator;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="youppers_product__attribute_option",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="standard_value_idx", columns={"attribute_standard_id", "value"}),
 *   })
 * @ORM\HasLifecycleCallbacks
 * @Validator\UniqueEntity({"value", "attributeStandard"})
 * @JMS\ExclusionPolicy("all") 
 */
class AttributeOption
{
	public function __toString()
	{
		return $this->getAttributeStandard() ? $this->getAttributeStandard() . ": " . $this->getValueWithSymbol() : "New";
	}
	
	/**
	 * @JMS\VirtualProperty()
	 * @JMS\Groups({"json"})
	 */
	public function getName()
	{
		return $this->getAttributeStandard()->getAttributeType()->getName();
	}
	
	public function getValueWithSymbol() {
		return trim((null === $this->getValue() ? 'New' : $this->getValue()) . ($this->getAttributeStandard() ? ' '. $this->getAttributeStandard()->getSymbol():'')); 
	}
	
	public function getValueWithEquivalence()
	{
		$equivalent = $this->getEquivalentOption();
		if ($equivalent === null) {
			return $this->getValueWithSymbol();
		} else {
			return $this->getValueWithSymbol() . ' ~ ' . $equivalent->getValueWithSymbol();
		}		 
	}

	public function getValueWithEquivalences()
	{
		$equivalents = $this->getEquivalentOptions();
		if ($equivalents === null) {
			return $this->getValueWithEquivalence();
		} else {
			$res = $this->getValueWithSymbol();
			foreach ($equivalents as $equivalent) {
				$res .= ' ~ ' . $equivalent->getValueWithSymbol();
			}
			return $res;
		}
			
	}
	
	public function getAttributeType()
	{
		return $this->getAttributeStandard() ? $this->getAttributeStandard()->getAttributeType() : null;
	}
	
	/**
	 * @ORM\Column(type="guid")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="UUID")
	 * @JMS\Expose()
	 * @JMS\Groups({"details"})
	 */
	protected $id;
			
	/**
	 * @ORM\Column(type="string")
	 * @JMS\Expose()
	 * @JMS\Groups({"details", "json"})
	 * @JMS\Accessor(getter="getValueWithSymbol")
	 */
	protected $value;
	
	/**
	 * @ORM\Column(type="boolean", options={"default":true})
	 * @JMS\Expose()
	 * @JMS\Groups({"details"})
	 */
	protected $enabled;

	/**
	 * @ORM\ManyToOne(targetEntity="Application\Sonata\MediaBundle\Entity\Media")
	 * @JMS\Expose()
	 * @JMS\Groups({"details"})
	 */
	protected $image;
	
	/**
	 * @ORM\Column(type="integer")
	 * @JMS\Expose()
	 * @JMS\Groups({"details"})
	 */
	protected $position;
	
	/**
	 * @ORM\ManyToOne(targetEntity="AttributeStandard", inversedBy="attributeOptions")
	 * @ORM\JoinColumn(name="attribute_standard_id")
	 * @JMS\Expose()
	 * @JMS\Groups({"details", "json.qr.find"})
	 * @Assert\NotNull
	 */
	protected $attributeStandard;
	
	/**
	 * @ORM\OneToMany(targetEntity="AttributeOption", mappedBy="equivalentOption")
	 **/
	protected $equivalentOptions;
	
	/**
	 * @ORM\ManyToOne(targetEntity="AttributeOption", inversedBy="equivalentOptions")
	 * @ORM\JoinColumn(name="equivalent_option_id", referencedColumnName="id")
	 */
	protected $equivalentOption;
	
	/**
	 * @ORM\Column(type="datetime", name="updated_at")
	 */
	protected $updatedAt;
	
	/**
	 * @ORM\Column(type="datetime", name="created_at")
	 * @JMS\Expose()
	 */
	protected $createdAt;
			
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
		
	// php app/console doctrine:generate:entities --no-backup YouppersProductBundle
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->equivalentOptions = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set value
     *
     * @param string $value
     * @return AttributeOption
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string 
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     * @return AttributeOption
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
     * Set position
     *
     * @param integer $position
     * @return AttributeOption
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
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return AttributeOption
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
     * @return AttributeOption
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
     * Set attributeStandard
     *
     * @param \Youppers\ProductBundle\Entity\AttributeStandard $attributeStandard
     * @return AttributeOption
     */
    public function setAttributeStandard(\Youppers\ProductBundle\Entity\AttributeStandard $attributeStandard = null)
    {
        $this->attributeStandard = $attributeStandard;

        return $this;
    }

    /**
     * Get attributeStandard
     *
     * @return \Youppers\ProductBundle\Entity\AttributeStandard 
     */
    public function getAttributeStandard()
    {
        return $this->attributeStandard;
    }

    /**
     * Add equivalentOptions
     *
     * @param \Youppers\ProductBundle\Entity\AttributeOption $equivalentOptions
     * @return AttributeOption
     */
    public function addEquivalentOption(\Youppers\ProductBundle\Entity\AttributeOption $equivalentOptions)
    {
        $this->equivalentOptions[] = $equivalentOptions;

        return $this;
    }

    /**
     * Remove equivalentOptions
     *
     * @param \Youppers\ProductBundle\Entity\AttributeOption $equivalentOptions
     */
    public function removeEquivalentOption(\Youppers\ProductBundle\Entity\AttributeOption $equivalentOptions)
    {
        $this->equivalentOptions->removeElement($equivalentOptions);
    }

    /**
     * Get equivalentOptions
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEquivalentOptions()
    {
        return $this->equivalentOptions;
    }

    /**
     * Set equivalentOption
     *
     * @param \Youppers\ProductBundle\Entity\AttributeOption $equivalentOption
     * @return AttributeOption
     */
    public function setEquivalentOption(\Youppers\ProductBundle\Entity\AttributeOption $equivalentOption = null)
    {
        $this->equivalentOption = $equivalentOption;

        return $this;
    }

    /**
     * Get equivalentOption
     *
     * @return \Youppers\ProductBundle\Entity\AttributeOption 
     */
    public function getEquivalentOption()
    {
        return $this->equivalentOption;
    }

    /**
     * Set image
     *
     * @param \Application\Sonata\MediaBundle\Entity\Media $image
     * @return AttributeOption
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
