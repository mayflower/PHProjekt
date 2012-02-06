var http = require('http');
var hint = require('./jshint.js').JSHINT;
var util = require('util');
var fs = require('fs');

var filenames = [];

var traverseFileSystem = function (currentPath) {
    var stats = fs.statSync(currentPath);
    if (stats.isFile()) {
        if(currentPath.substring(currentPath.length - 3) == ".js") {
            console.log("adding: " + currentPath);
            filenames.push(currentPath);
        }
    } else {
        var files = fs.readdirSync(currentPath);
        for (var i in files) {
            var currentFile = currentPath + '/' + files[i];
            traverseFileSystem(currentFile);
        }
    }
};

var initFilenames = [];

//get files
process.argv.forEach(function(val, index, array) {
    if (index > 1)
        initFilenames.push(val);
})

var analyseFiles = function() {
    var options = {
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
    }
    var ret = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" '+
        '"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"> '+
        '<html xmlns="http://www.w3.org/1999/xhtml"> '+
        '<head> '+
        '<title>An XHTML 1.0 Strict standard template</title> '+
        '<meta http-equiv="content-type" content="text/html;charset=utf-8" /> '+
        '</head><body>';
    ret += '<script type="text/javascript">' +
        '    if (window.location.hash === "#refresh") {' +
        '        console.log("autorefresh");' +
        '        window.setTimeout(function() {' +
        '            window.location.reload(true)' +
        '        }, 5000);' +
        '    }' +
        '</script>';
    var output = '<table>';
    var errorcount = 0;
    for ( var i in filenames) {
        var file = filenames[i];
        try {
        var content = fs.readFileSync(file).toString().split('\n');
        output+="<tr><td>";
        if (!hint(content, options)) {
            errorcount+=hint.errors.length;
            output+='<div style="background-color:#ff0000;"">'+file+' ' + hint.errors.length + ' Errors</div>';
            for (var e in hint.errors) {
                var error = hint.errors[e];
                if (error!==null && error.evidence) {
                    output+="<div>";
                    output+="<code>Line:"+error.line+
                        " char:"+error.character+
                        ": "+error.reason+"</code></br>";
                    output+="<code>"+error.evidence.replace(/\ /gi, "&nbsp;")+"</code></br>";
                    output+="<code>";
                    for (var c =0;c<error.character-1;c++) {
                        output+="-";
                    }
                    output+="^";
                    output+="</code>";
                    output+="</div>";
                }
            }
        } else {
            output+='<div style="background-color:#00ff00;"">'+file+'</div>';
        }
        output+="</td></tr>";
        } catch (e) {} // in case of fs error
    }
    output = "<div>"+errorcount+" errors found</div>"+output;
    output += '</table>';
    return ret+output+"</body></html>";
}

http.createServer(function (req, res) {
  res.writeHead(200, {'Content-Type': 'text/html; charset=utf-8'});
  filenames = [];

  for (var i in initFilenames)
    traverseFileSystem(initFilenames[i]);

  res.end(analyseFiles());
}).listen(1337, "");
console.log('Server running at http://127.0.0.1:1337/');
