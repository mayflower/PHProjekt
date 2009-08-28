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
        // Summary:
        //    Convert a js date into ISO date
        // Description:
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
        // Summary:
        //    Convert a js time into ISO time
        // Description:
        //    Convert a js time into ISO time
        var value   = time.toString().replace(/\D/g, "");
        value       = value.substr(0, 4);
        var minutes = value.substr(value.length - 2);
        var hour    = value.substr(0, value.length - 2);

        if (isNaN(hour) || hour > 24 || hour < 0) {
            hour = '00';
        }
        if (isNaN(minutes) || minutes > 60 || minutes < 0) {
            minutes = '00';
        }
        return dojo.number.format(hour, {pattern: '00'}) + ':' + dojo.number.format(minutes, {pattern: '00'});
    },

    convertMinutesToTime:function(minutes) {
        // Summary:
        //    Convert a number of minutes into HH:mm
        // Description:
        //    Convert a number of minutes into HH:mm
        hoursDiff   = Math.floor(minutes / 60);
        minutesDiff = minutes - (hoursDiff * 60);

        if (hoursDiff == 0 || hoursDiff < 10) {
            hoursDiff = '0' + hoursDiff;
        }
        if (minutesDiff == 0 || minutesDiff < 10) {
            minutesDiff = '0' + minutesDiff;
        }

        return hoursDiff + ':' + minutesDiff;
    },

    convertTimeToMinutes:function(time) {
        // Summary:
        //    Convert a HH:mm into a number of minutes
        // Description:
        //    Convert a HH:mm into a number of minutes
        var hours   = parseInt(time.substr(0, 2));
        var minutes = parseInt(time.substr(3, 2));

        return (hours * 60) + (minutes);
    },

    isoDateTojsDate:function(date) {
        // Summary:
        //    Convert a iso string of a date into a js object date
        // Description:
        //    Convert a iso string of a date into a js object date
        var day   = date.substr(8, 2);
        var month = date.substr(5, 2);
        var year  = date.substr(0, 4);

        return new Date(year, month - 1, day);
    }
});
