<?php
/**
 * User: sergio
 * Date: 5/22/15
 * Time: 3:32 PM
 */

namespace Youppers\ProductBundle\Security;


use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Youppers\CommonBundle\Security\AbstractOrgVoter;

class ProductVariantVoter extends AbstractOrgVoter implements VoterInterface {

    protected function getObjectOrgs($object) {
        return array($object->getProductCollection()->getBrand()->getCompany()->getOrg());
    }

    public function supportsClass($class) {
        return $class == 'Youppers\ProductBundle\Entity\ProductVariant';
    }

}