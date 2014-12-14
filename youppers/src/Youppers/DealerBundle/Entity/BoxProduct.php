<?php
namespace Youppers\DealerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="box_product",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="box_product_idx", columns={"box_id", "product_id"})
 *   })
 */
class BoxProduct
{
	/**
	 * @ORM\Column(type="guid")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="UUID")
	 */
	protected $id;
	
	/**
	 * @ORM\Column(type="string", length=60)
	 */
	protected $name;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Box", inversedBy="boxProducts")
	 */
	protected $box;
	
	/**
	 * @ORM\ManyToOne(targetEntity="\Youppers\CompanyBundle\Entity\Product")
	 **/
	protected $product;

	/**
	 * @ORM\Column(type="integer")
	 */
	protected $position;
		
	public function __toString()
	{
		return ($this->getBox() ? $this->getBox() : "New"). " - " . ($this->getProduct() ? $this->getProduct() : "New");
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
