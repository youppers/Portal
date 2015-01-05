<?php
namespace Youppers\DealerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="box",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="store_box_name_idx", columns={"store_id", "name"}),
 *     @ORM\UniqueConstraint(name="store_box_code_idx", columns={"store_id", "code"}),
 *   })
 */
class Box
{
	/**
	 * @ORM\Column(type="guid")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="UUID")
	 */
	protected $id;

	/**
	 * @ORM\ManyToOne(targetEntity="Store", inversedBy="boxes")
	 */
	protected $store;
	
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
	 * @ORM\Column(type="datetime", name="updated_at")
	 */
	protected $updatedAt;
	
	/**
	 * @ORM\Column(type="datetime", name="created_at")
	 */
	protected $createdAt;
		
	/**
	 * @ORM\Column(type="text", nullable=true )
	 */
	protected $description;
	
	/**
	 * @ORM\OneToMany(targetEntity="BoxProduct", mappedBy="box", cascade={"all"})
	 **/
	private $boxProducts;
		
	/**
	 * @param BoxProduct[] $products
	 */
	public function setBoxProducts($boxProducts)
	{
		$this->boxProducts->clear();
	
		foreach ($boxProducts as $boxProduct) {
			$this->addBoxProduct($boxProduct);
		}
	}
	
	/**
	 * @return BoxProduct[]
	 */
	public function getBoxProducts()
	{
		return $this->boxProducts;
	}
	
	/**
	 * @param BoxProduct $boxProduct
	 * @return void
	 */
	public function addBoxProduct(BoxProduct $boxProduct)
	{
		$boxProduct->setBox($this);
		$this->boxProducts->add($boxProduct);
	}
	
	/**
	 * @param BoxProduct $boxProduct
	 * @return void
	 */
	public function removeBoxProduct(BoxProduct $boxProduct)
	{
		$this->boxProducts->removeElement($boxProduct);
	}

	public function __toString()
	{
		return $this->getName();
	}
	
	public function prePersist()
	{
		$this->createdAt = new \DateTime();
		$this->updatedAt = new \DateTime();
	}
	
	public function preUpdate()
	{
		$this->updatedAt = new \DateTime();
	}	
		
	// php app/console doctrine:generate:entities --no-backup YouppersDealerBundle

}
