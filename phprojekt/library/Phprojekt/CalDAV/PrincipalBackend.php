<?php
/**
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2012 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 */

/**
 * Calendar2 Caldav Principal Backend
 *
 * This class implements a principal backend for sabredav
 */
class Phprojekt_CalDAV_PrincipalBackend implements Sabre_DAVACL_IPrincipalBackend
{
    public function getPrincipalsByPrefix($prefixPath)
    {
        // TODO: Implement me
        throw new Exception('not implemented. $prefixPath = ' . $prefixPath);
        return array();
    }

    public function getPrincipalByPath($path)
    {
        $user = new Phprojekt_User_User();
        $user = $user->findByUsername(preg_filter('|.*principals/([^/]+)$|', '$1', $path));
        if (is_null($user)) {
            throw new Exception("Principal not found for path $path");
        }

        return array(
            'id'                => $user->id,
            'uri'               => "principals/{$user->username}",
            '{DAV:}displayname' => $user->username,
            '{http://sabredav.org/ns}email-address' => $user->getSetting('email')
        );
    }

    public function getGroupMemberSet($principal)
    {
        throw new Exception('not implemented. $principal = ' . $principal);
    }

    public function getGroupMembership($principal)
    {
        return array();
    }

    public function setGroupMemberSet($principal, array $members)
    {
        throw new Exception('not implemented. $principal = ' . $principal);
    }

    public function updatePrincipal($path, $mutations)
    {
        return false;
    }

    public function searchPrincipals($prefixPath, array $searchProperties)
    {
        return array();
    }
}
