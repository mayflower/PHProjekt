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
 * @category   PHProjekt
 * @package    Application
 * @subpackage Core
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <gustavo.solt@mayflower.de>
 */

dojo.provide("phpr.Setting.Form");

dojo.declare("phpr.Setting.Form", phpr.Core.Form, {
    _customActionOnSuccess:function() {
        // Summary:
        //    Display a warning for user sub-module.
        if (phpr.submodule == 'User') {
            var result     = {};
            result.type    = 'warning';
            result.message = phpr.nls.get('You need to log out and log in again in order to let changes have effect');
            new phpr.handleResponse('serverFeedback', result);
        }
    },

    _setBreadCrumbItem:function() {
        // Summary:
        //    Set the Breadcrumb with the current sub-module.
        phpr.BreadCrumb.setItem(phpr.nls.get(phpr.submodule));
    }
});
