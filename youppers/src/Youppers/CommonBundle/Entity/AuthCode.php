<?php
namespace Youppers\CommonBundle\Entity;

use FOS\OAuthServerBundle\Entity\AuthCode as BaseAuthCode;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="youppers__oauth2_authcode")
 */
class AuthCode extends BaseAuthCode
{
    /**
     * @ORM\Id
	 * @ORM\Column(type="guid")
	 * @ORM\GeneratedValue(strategy="UUID")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Client")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $client;

    /**
	 * @ORM\ManyToOne(targetEntity="\Application\Sonata\UserBundle\Entity\User")
     */
    protected $user;
}
