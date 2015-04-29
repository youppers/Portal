<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Youppers\CustomerBundle\Security;

use FOS\UserBundle\Security\EmailUserProvider;

class PhoneEmailUserProvider extends EmailUserProvider
{
    /**
     * {@inheritDoc}
     */
    protected function findUser($username)
    {
    	$users = $this->userManager->findUsersBy(array('phone' => $username));
    	if (count($users) == 1) {
    		return $users[0];
    	}
        return $this->userManager->findUserByUsernameOrEmail($username);
    }
}
