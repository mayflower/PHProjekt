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
 * @copyright  Copyright (c) 2012 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Simon Kohlmeyer <simon.kohlmeyer@mayflower.de>
 */
dojo.provide("phpr.MetadataStore");

dojo.require("dojo.data.ItemFileReadStore");

dojo.declare("phpr.MetadataStore", null, {
    _cache: {},

    metadataFor: function(module, projectId) {
        if (projectId === undefined) {
            throw "No projectId provided in phpr.Metadatastore::metadataFor!";
        }

        if (typeof this._cache[module] == 'undefined' || typeof this._cache[module][projectId] == "undefined") {
            if (typeof this._cache[module] == 'undefined') {
                this._cache[module] = {};
            }
            this._cache[module][projectId] = {};
            var url = "index.php/" + module + "/index/metadata/csrfToken/" + phpr.csrfToken;
            var def = dojo.xhrGet({
                url: url,
                content: {
                    csrfToken: phpr.csrfToken,
                    projectId: projectId
                },
                handleAs: 'json'
            }).then(
                dojo.hitch(this, function (data) {
                    this._cache[module][projectId].data = data;
                    delete this._cache[module][projectId].deferred;
                    return data;
                }),
                function(err) {
                    phpr.handleError(url, 'php');
                }
            );
            this._cache[module][projectId].deferred = def;
            return def;
        } else if (typeof this._cache[module][projectId].data !== 'undefined') {
            var def = new dojo.Deferred();
            def.resolve(this._cache[module][projectId].data);
            return def;
        } else {
            return this._cache[module][projectId].deferred;
        }
    }
});

phpr.MetadataStore = new phpr.MetadataStore();
