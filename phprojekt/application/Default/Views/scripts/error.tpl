<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
 <head>
   <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
   <title>Error</title>
   <style type="text/css">
{literal}
    table th,
    table td {
      padding: 2px;
      font-family: Helvetica, sans;
    }

    table tbody th {
      text-align: left;
      color: white;
      background-color: #C3C3C3;
      border-bottom: 1px solid black;
    }

    table tbody td {
      background-color: #EFEFEF;
    }

    table tfoot td {
      text-align: right;
      font-size: 8px;
    }

{/literal}
   </style>
 </head>
 <body>
   <table align="center" width="80%">
    <tbody>
     <tr>
      <th>Sorry an error has occured</th>
     </tr>
     <tr>
      <td><blockquote>{$message}</blockquote></td>
     </tr>
    </tbody>
    <tfoot>
     <td>{$smarty.now|date_format}</td>
    </tfoot>
   </table>
 </body>
</html>