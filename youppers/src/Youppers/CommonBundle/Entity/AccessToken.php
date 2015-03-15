<?php
namespace Youppers\CommonBundle\Entity;

use FOS\OAuthServerBundle\Entity\AccessToken as BaseAccessToken;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="youppers__oauth2_accesstoken")
 */
class AccessToken extends BaseAccessToken
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