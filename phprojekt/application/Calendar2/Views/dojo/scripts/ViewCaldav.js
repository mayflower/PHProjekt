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
 * @subpackage Calendar2
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.1
 * @author     Reno Reckling <reno.reckling@mayflower.de>
 */

dojo.provide("phpr.Calendar2.ViewCaldav");

dojo.declare("phpr.Calendar2.ViewCaldav", null, {
    // Summary:
    //    Class for displaying a Calendar2 Month List
    // description:
    //    This Class takes care of displaying the list information we receive from our Server in a HTML table
    _url: "",
    _iosUrl: "",
    _main: null,
    constructor: function() {
        this._setUrls();

        var entry = phpr.fillTemplate(
            "phpr.Calendar2.template.caldavView.html",
            {
                headline: phpr.nls.get("Caldav urls", "Calendar2"),
                normalLabel: phpr.nls.get("CalDav url", "Calendar2"),
                iosLabel: phpr.nls.get("CalDav url for Apple software", "Calendar2"),
                noticeLabel: phpr.nls.get("Notice", "Calendar2"),
                notice: phpr.nls.get("Please pay attention to the trailing slash, it is important", "Calendar2"),
                normalUrl: this._url,
                iosUrl: this._iosUrl
            }
        );

        phpr.viewManager.getView().gridContainer.set('content', entry);
    },

    _getUserName: function() {
    },

    _setUrls: function() {
        var prefix = phpr.getAbsoluteUrl('index.php/Calendar2/caldav/index/');
        this._url = prefix + 'calendars/' + phpr.config.currentUserName + '/default/';
        this._iosUrl = prefix + 'principals/' + phpr.config.currentUserName + '/';
    }
});
