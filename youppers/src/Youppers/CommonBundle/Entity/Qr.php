<?php
namespace Youppers\CommonBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Youppers\CompanyBundle\Entity\Product;
use Youppers\DealerBundle\Entity\Box;

/**
 * @ORM\Entity
 * @ORM\Table(name="youppers__qr")
 * @ORM\HasLifecycleCallbacks
 * @Serializer\ExclusionPolicy("all") 
 * @Serializer\AccessorOrder("custom", custom = {"id", "target_type"})
 */
class Qr
{
	public function __toString()
	{
		return $this->getText();
	}
	
	public function getText()
	{
		if (!empty($this->url)) {
			return $this->url;
		}
		return 	'http://demo.youppers.com/qr/' . $this->getId();
	}
	
	public function setText($text)
	{
		return $this->setUrl($text);
	}
	
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
	 * @Serializer\Groups({"list", "details", "json.qr.find"})
	 */
	protected $targetType;

	/**
	 * @ORM\Column(type="boolean", options={"default":true})
	 * @Serializer\Expose()
	 * @Serializer\Groups({"list", "details"})
	 */
	protected $enabled;
	
	/**
	 * @ORM\Column(type="string", unique=true, nullable=true)
	 */
	protected $url;
	
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
	
	/**
	 * @ORM\OneToMany(targetEntity="\Youppers\CompanyBundle\Entity\Product", mappedBy="qr", fetch="EAGER")
	 **/
	protected $products;

	/**
	 * @ORM\OneToMany(targetEntity="\Youppers\DealerBundle\Entity\Box", mappedBy="qr", fetch="EAGER")
	 **/
	protected $boxes;

    /**
     * @ORM\OneToMany(targetEntity="\Application\Sonata\MediaBundle\Entity\Media", mappedBy="qr", fetch="EAGER")
     **/
    protected $medias;

    /**
	 * Use this for qr instead of direct relations
	 * 
	 * @Serializer\VirtualProperty
	 * @Serializer\SerializedName("targets")
	 * @Serializer\Groups({"json.qr.find"})
	 */
	public function getTargets()
	{
		switch ($this->getTargetType()) {
			case 'youppers_dealer_box':
				return new ArrayCollection($this->getBoxes()->filter(function (Box $box) { return $box->getEnabled();})->getValues());
			case 'youppers_company_product':
                return new ArrayCollection($this->getProducts()->filter(function (Product $product) { return $product->getEnabled(); })->getValues());
		}
		return new ArrayCollection(array());
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
	
	// php app/console doctrine:generate:entities --no-backup YouppersCommonBundle:Qr
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
     * @return mixed
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
     * @param Product $products
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

    /**
     * Set url
     *
     * @param string $url
     * @return Qr
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Add media
     *
     * @param \Application\Sonata\MediaBundle\Entity\Media $media
     *
     * @return Qr
     */
    public function addMedia(\Application\Sonata\MediaBundle\Entity\Media $media)
    {
        $this->medias[] = $media;

        return $this;
    }

    /**
     * Remove media
     *
     * @param \Application\Sonata\MediaBundle\Entity\Media $media
     */
    public function removeMedia(\Application\Sonata\MediaBundle\Entity\Media $media)
    {
        $this->medias->removeElement($media);
    }

    /**
     * Get medias
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMedias()
    {
        return $this->medias;
    }
}
