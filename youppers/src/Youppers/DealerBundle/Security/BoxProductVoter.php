<?php
/**
 * User: sergio
 * Date: 5/22/15
 * Time: 3:32 PM
 */

namespace Youppers\DealerBundle\Security;


use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Youppers\CommonBundle\Security\AbstractOrgVoter;

class BoxProductVoter extends AbstractOrgVoter implements VoterInterface {

    protected function getObjectOrgs($object) {
        return array($object->getBox()->getStore()->getDealer()->getOrg());
    }

    public function supportsClass($class) {
        return $class == 'Youppers\DealerBundle\Entity\BoxProduct';
    }

}