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

    constructor: function () {
        dojo.subscribe("updateCacheData", this, "_deleteCache");
    },

    _deleteCache: function() {
        this._forEachCacheItem(function(item) {
            if (item.hasOwnProperty('deferred')) {
                return;
            }
            if (item.hasOwnProperty('data')) {
                delete item.data;
            }
        });
    },

    _forEachCacheItem: function(fun) {
        for (var module in this._cache) {
            if (this._cache.hasOwnProperty(module)) {
                for (var projectId in this._cache[module]) {
                    if (this._cache[module].hasOwnProperty(projectId)) {
                        fun(this._cache[module][projectId]);
                    }
                }
            }
        }
    },

    metadataFor: function(module, projectId) {
        if (projectId === undefined) {
            throw "No projectId provided in phpr.Metadatastore::metadataFor!";
        }

        if (!this._cache.hasOwnProperty(module) ||
                !this._cache[module].hasOwnProperty(projectId) ||
                (!this._cache[module][projectId].hasOwnProperty('data') &&
                 !this._cache[module][projectId].hasOwnProperty('deferred'))) {
            if (!this._cache.hasOwnProperty(module)) {
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
        } else if (this._cache[module][projectId].hasOwnProperty('data')) {
            var def = new dojo.Deferred();
            def.resolve(this._cache[module][projectId].data);
            return def;
        } else {
            return this._cache[module][projectId].deferred;
        }
    }
});

phpr.MetadataStore = new phpr.MetadataStore();
