<?php
/**
 * User: sergio
 * Date: 5/22/15
 * Time: 2:55 PM
 */

namespace Youppers\CommonBundle\Security;

use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Youppers\CommonBundle\Entity\Org;

abstract class AbstractOrgVoter implements VoterInterface
{
    protected $managerRegistry;
    protected $logger;

    public function __construct(ManagerRegistry $managerRegistry, LoggerInterface $logger)
    {
        $this->managerRegistry = $managerRegistry;
        $this->logger = $logger;
    }

    /**
     * @param $object
     * @return Org
     */
    protected abstract function getObjectOrgs($object);

    public function supportsAttribute($attribute)
    {
        //$this->logger->debug("VOTE attribute ".$attribute);
        return preg_match("/ROLE_YOUPPERS_([A-Z_]*)_ADMIN_([A-Z]*)_(VIEW|EDIT)/",$attribute);
    }

    public function vote(TokenInterface $token, $object, array $attributes)
    {
        //$this->logger->debug("VOTE Class:" . get_class($object) . " Attributes:".print_r($attributes,true));
        // check if class of this object is supported by this voter
        if (!$this->supportsClass(get_class($object))) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        // check if the voter is used correct, only allow one attribute
        // this isn't a requirement, it's just one easy way for you to
        // design your voter
        if (1 !== count($attributes)) {
            throw new \InvalidArgumentException(
                'Only one attribute is allowed for VIEW or EDIT'
            );
        }

        // set the attribute to check against
        $attribute = $attributes[0];

        // check if the given attribute is covered by this voter
        if (!$this->supportsAttribute($attribute)) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        // get current logged in user
        $user = $token->getUser();

        // make sure there is a user object (i.e. that the user is logged in)
        if (!$user instanceof UserInterface) {
            return VoterInterface::ACCESS_DENIED;
        }

        //dump(array('object' => $object, 'attributes' => $attributes, 'user' => $user));

        if (in_array($user->getOrg(),$this->getObjectOrgs($object))) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }
}