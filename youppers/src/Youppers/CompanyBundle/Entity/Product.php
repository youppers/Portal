<?php
namespace Youppers\CompanyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="product",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="brand_product_code_idx", columns={"brand_id", "code"}),
 *     @ORM\UniqueConstraint(name="brand_product_code_name_idx", columns={"brand_id", "code", "name"}),
 *   })
 */
class Product
{
	/**
	 * @ORM\Column(type="guid")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="UUID")
	 */
	protected $id;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Brand")
	 */
	protected $brand;
	
	/**
	 * @ORM\OneToMany(targetEntity="ProductModel", cascade={"persist", "remove"}, mappedBy="product")
     * @Assert\Valid
	 **/
	private $productModels;	
	
	/**
	 * @ORM\Column(type="string", length=60)
	 */
	protected $name;
	
	/**
	 * @ORM\Column(type="string", length=20)
	 */
	protected $code;
	
	/**
	 * @ORM\Column(type="boolean", name="is_active", options={"default":true})
	 */
	protected $isActive;
	
	/**
	 * @ORM\Column(type="text", nullable=true )
	 */
	protected $description;
	
	/**
	 * @param ProductModel[] $models
	 */
	public function setProductModels($models)
	{
		$this->productModels->clear();
	
		foreach ($models as $model) {
			$this->addProductModel($model);
		}
	}
	
	/**
	 * @return ProductModel[]
	 */
	public function getProductModels()
	{
		return $this->productModels;
	}
	
	/**
	 * @param ProductModel $model
	 * @return void
	 */
	public function addProductModel(ProductModel $model)
	{
		$model->setProduct($this);
		$this->productModels->add($model);
	}
	
	/**
	 * @param ProductModel $model
	 * @return void
	 */
	public function removeProductModel(ProductModel $model)
	{
		$this->productModels->removeElement($model);
	}
	
	
	public function __toString()
	{
		return $this->getName() ? $this->getBrand() . ' - ' . $this->getName() : 'New';
	}
		
	// php app/console doctrine:generate:entities --no-backup YouppersCompanyBundle
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->productModels = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Product
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
     * @return Product
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
     * @return Product
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
     * Set description
     *
     * @param string $description
     * @return Product
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
     * Set brand
     *
     * @param \Youppers\CompanyBundle\Entity\Brand $brand
     * @return Product
     */
    public function setBrand(\Youppers\CompanyBundle\Entity\Brand $brand = null)
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * Get brand
     *
     * @return \Youppers\CompanyBundle\Entity\Brand 
     */
    public function getBrand()
    {
        return $this->brand;
    }
}
