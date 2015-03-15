<?php
namespace Youppers\CommonBundle\Entity;

use FOS\OAuthServerBundle\Entity\Client as BaseClient;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="youppers__oauth2_client")
 */
class Client extends BaseClient
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="guid")
	 * @ORM\GeneratedValue(strategy="UUID")
	 */
	protected $id;

	/**
	 * @ORM\Column(type="string", unique=true)
	 */
	protected $name;
	
	public function __construct()
	{
		parent::__construct();
		// your own logic
	}
	// php app/console doctrine:generate:entities --no-backup YouppersCommonBundle:Client

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
     * @return Client
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
}
