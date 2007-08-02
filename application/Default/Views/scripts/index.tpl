<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
 <head>
   <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
   <title>{$phprojekt_version} {$title}</title>
   <style type="text/css">
{literal}
     body {
       margin-top: 0px;
       margin-left: 0px;
       margin-right: 0px;
       font-family: Verdana, Helvetica, sans;
       font-size: 12px;
     }

    #main caption {
      background: SlateGray;
      color: white;
    }

     #main td#treeView,
     #main td#listView,
     #main td#formView {
       background-color: Gainsboro;
       border: 1px outset black;
       vertical-align: top;
     }

     #main td#treeView a:link,
     #main td#treeView a:visited,
     #main td#treeView a:active {
        text-decoration: none;
     }

     #main td#treeView a:hover {
        background: #EFEFEF;
     }


     table.listView th {
       text-align: left;
       background-color: SlateGray;
       color: white;
     }

     table.listView td {
       background-color: white;
     }

     .error {
        color: red;
        margin-left: 2px;
     }
{/literal}
   </style>
 </head>
 <body>

 <table align="center" width="100%" id="main">
 <thead>
  <caption>{$phprojekt_version}</caption>
 </thead>
 <tbody>
     <tr>
        <td rowspan="2" width="20%" id="treeView">{$treeView}</td>
        <td id="listView">{$listView}</td>
     </tr>
     <tr>
        <td id="formView">{$formView}</td>
     </tr>
 </tbody>
 </table>

 </body>
</html>