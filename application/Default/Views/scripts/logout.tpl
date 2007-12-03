<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
 <head>
   <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
   <title>Login</title>
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
      text-align: center;
      font-size: 8px;
    }

{/literal}
   </style>
 </head>
 <body>
   <table align="center" width="80%">
    <tbody>
     <tr>
      <th>Login</th>
     </tr>
     <tr>
      <td><blockquote>{$message}</blockquote></td>
     </tr>
    </tbody>
    <tfoot>
     <td>
     <span class="error">{$errors}</span><br />
     <table width="100%" align="center">
         <form method="post" action='{url controller="login" action="login"}'>
    <tr>
        <td>{"Username"|translate}: <input type="text" name="username" value="{$username}" /></td>
    </tr>
    <tr>
        <td>{"Password"|translate}:<input type="password" name="password" value="" /></td>
    <tr>
    </table>
    <br/>
    <input type="submit" value="Send">
     </td>
    </tfoot>
   </table>
   
 </body>
</html>