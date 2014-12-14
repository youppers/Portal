<?php
namespace Youppers\DealerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="box_product")
 */
class BoxProduct
{
	/**
	 * @ORM\ManyToOne(targetEntity="Box", inversedBy="boxProducts")
	 * @ORM\Id
	 */
	protected $box;
	
	/**
	 * @ORM\OneToOne(targetEntity="\Youppers\CompanyBundle\Entity\Product")
	 * @ORM\Id
	 **/
	private $product;

	/**
	 * @ORM\Column(type="integer")
	 */
	protected $position;
		
	public function __toString()
	{
		return $this->getProduct() ? $this->getProduct()->getName() : "New";
	}
		
	// php app/console doctrine:generate:entities --no-backup YouppersDealerBundle


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
    public function setBox(\Youppers\DealerBundle\Entity\Box $box)
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
    public function setProduct(\Youppers\CompanyBundle\Entity\Product $product)
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
