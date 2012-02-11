var hint = require('./jshint.js').JSHINT;
var util = require('util');
var fs = require('fs');

if (process.argv.length > 1) {
    fileName = process.argv[2];
}

var analyseFile = function() {
    var options = {
        debug: true,
        curly: true,
        radix: false,
        eqeqeq: false,
        shadow: true,
        switchindent: true,
        evil: true,
        maxerr: 10000,
        noempty: true,
        loopfunc: true,
        white: true
    };
    var ret = "";
    var errorcount = 0;
    var file = fileName;
    try {
        var content = fs.readFileSync(file).toString().split('\n');
        if (!hint(content, options)) {
            errorcount += hint.errors.length;
            for (var e in hint.errors) {
                var error = hint.errors[e];
                if (error !== null && error.evidence) {
                    ret += '"' + fileName + '",' + error.line + "," + error.character + ',"' + error.reason + '"\n';
                }
            }
        }
    } catch (e) {} // in case of fs error
    return ret;
};

process.stdout.write(analyseFile());
