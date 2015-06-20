<?php
/**
 * User: sergio
 * Date: 5/22/15
 * Time: 3:32 PM
 */

namespace Youppers\ProductBundle\Security;


use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Youppers\CommonBundle\Security\AbstractOrgVoter;

class ProductCollectionVoter extends AbstractOrgVoter implements VoterInterface {

    protected function getObjectOrgs($object) {
        return array($object->getBrand()->getCompany()->getOrg());
    }

    public function supportsClass($class) {
        return $class == 'Youppers\ProductBundle\Entity\ProductCollection';
    }

}