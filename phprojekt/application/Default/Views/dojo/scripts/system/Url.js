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
 * @subpackage Default
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <gustavo.solt@mayflower.de>
 */

dojo.provide("phpr.Url");

recentHash = window.location.hash;

dojo.declare("phpr.Url", null, {
    getModule:function() {
        // summary:
        //    Return the module form the hash
        // description:
        //    Check the first value in the hash and return it
        if (!window.location.hash) {
            var module = 'Project';
        } else {
            var data = recentHash.split(',');
            if (data[0]) {
                var module = data[0].replace(/.*#(.*)/, "$1");
            } else {
                var module = 'Project';
            }
        }

        return module;
    },

    addUrl:function(id) {
        // summary:
        //    Change the hash and run the process function
        // description:
        //    Change the hash and run the process function
        if (id) {
            window.location.hash = id;
            this.processUrl();
        }
    },

    processUrl:function() {
        // summary:
        //    Call the module function for process the hash
        // description:
        //    Call the module function for process the hash
        var module = this.getModule();
        if (module) {
            recentHash = window.location.hash;
            dojo.publish(module + '.processUrlHash', [recentHash]);
        }
    },

    getHashForCookie:function() {
        return window.location.hash.toString().substring(1).replace(/,/g,".");
    }
});

function initialiseStateFromUrl() {
    // summary:
    //    Check the changes in the hash
    // description:
    //    Check if the hash was changed and run
    //    the phpr.Url.processUrl function in this case
    // Nothing's changed since last polled.
    if (window.location.hash == recentHash) {
        return;
    } else if (recentHash == '#undefined') {
        return;
    }

    phpr.Url.processUrl();
};
