<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
    <title>PHProjekt</title>
    <link rel="shortcut icon" href="../img/favicon.ico" type="image/x-icon" />
    <style type="text/css">
        @import "../css/themes/phprojekt/phprojektCssCompiler.php";
    </style>
    <script type="text/javascript">
        var djConfig = {isDebug: false, parseOnLoad: false, bindEncoding: "utf-8",
                        locale: 'en', useCommentedJson: true};
    </script>
    <script type="text/javascript" src="../dojo/dojo/dojo.js"></script>
    <script type="text/javascript" src="../dojo/dojo/mydojo.js"></script>
    <script type="text/javascript">
        function getMaxHeight() {
            var availHeight = 0;

            if (document.layers) {
                availHeight = window.innerHeight + window.pageYOffset;
            } else if (document.all) {
                availHeight = document.documentElement.clientHeight + document.documentElement.scrollTop;
            } else if (document.getElementById) {
                availHeight = window.innerHeight + window.pageYOffset;
            }

            return availHeight;
        }

        function init() {
            availHeight = getMaxHeight();

            dojo.style(dojo.byId('completeContent'), "height", availHeight + "px");
            dijit.byId('completeContent').resize();
        }

        dojo.addOnLoad(function() {
            dojo.parser.parse();
            init();
        });

        window.onresize = function() {
            init();
        };
    </script>
</head>
<body class="phprojekt">

<div id="completeContent" dojoType="dijit.layout.ContentPane"
    style="width: 100%; height: 1000px; overflow: hidden;">
    <!-- Top Bar menu -->
    <div dojoType="dijit.layout.ContentPane" id="header" region="top" splitter="false"
    style="height:55px;">
        <img class="left" src="../img/logo.png" alt="PHProjekt 6" />
        <div id="mainNavigation" class="right align-right">
        </div>
    </div>
    <!-- Left Content -->
    <div id="navigation-container"
    style="width:15%; height: 100%">
        <div dojoType="dijit.layout.ContentPane">
            <div id="subheader" class="block">
                <div id="navigation-container-titel">
                </div>
            </div>
            <!-- Tree menu -->
            <div id="tree-navigation" dojoType="dijit.layout.ContentPane">
                <div dojoType="dijit.layout.ContentPane" id="treeBox">
                </div>
            </div>
        </div>
    </div>
    <!-- Center Content -->
    <div dojoType="dijit.layout.ContentPane"
    style="position: absolute; top: 55px; left: 16%; width: 100%;">
        <div dojoType="dijit.layout.ContentPane" design="sidebar"
        style="width: 84%; height: 100%;">
            <!-- Second Top Bar -->
            <div dojoType="dijit.layout.ContentPane" region="top" class="block"
            style="height: 7%;" splitter="false">
                <!-- Exception Form -->
                <br />
                <div id="serverFeedback" class="prepend-24">
                    Thanks for using PHProjek6!
                </div>
            </div>
            <!-- Main Content -->
            <div dojoType="dijit.layout.ContentPane" region="center" id="centerMainContent">
                <br /><br /><br /><br /><br /><br /><br />
                <table width="100%" align="center" class="form">
                <col class="col1" />
                    <tr>
                        <td class="label"></td>
                        <td>
                            Installation done!. Please login at <a href="<%SERVER_URL%>index.php"><%SERVER_URL%>index.php</a>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
