/**
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 2.1 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    $Id$
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

dojo.provide("phpr.Date");

dojo.declare("phpr.Date", null, {
    getIsoDate:function(date) {
        // summary:
        //    Convert a js date into ISO date
        // description:
        //    Convert a js date into ISO date
        var day = date.getDate();
        if (day < 10) {
            day = '0' + day;
        }
        var month = (date.getMonth()+1);
        if (month < 10) {
            month = '0' + month
        }

        return date.getFullYear() + '-' + month + '-' + day;
    },

    getIsoTime:function(time) {
        // summary:
        //    Convert a js time into ISO time
        // description:
        //    Convert a js time into ISO time
       time        = time.replace(/\D/g, "");
       var minutes = time.substr(time.length - 2);
       var hour    = time.substr(0, time.length - 2);

       return hour + ':' + minutes;
    },

    convertTime:function(time) {
        // summary:
        //    Convert a number of minutes into HH:mm
        // description:
        //    Convert a number of minutes into HH:mm
        hoursDiff   = Math.floor(time / 60);
        minutesDiff = time - (hoursDiff * 60);

        if (hoursDiff == 0 || hoursDiff < 10) {
            hoursDiff = '0' + hoursDiff;
        }
        if (minutesDiff == 0 || minutesDiff < 10) {
            minutesDiff = '0' + minutesDiff;
        }

        return hoursDiff + ':' + minutesDiff;
    },

    isoDateTojsDate:function(date) {
        // summary:
        //    Convert a iso string of a date into a js object date
        // description:
        //    Convert a iso string of a date into a js object date
        var day   = date.substr(8, 2);
        var month = date.substr(5, 2);
        var year  = date.substr(0, 4);

        return new Date(year, month - 1, day);
    }
});
