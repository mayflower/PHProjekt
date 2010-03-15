<?php
// System
$lang["Contact"] = "Contacto";

// Fields
 // Basic Data
$lang["Name"] = "Nombre";
$lang["E-Mail"] = "E-Mail";
$lang["Company"] = "Compañía";
$lang["First phone"] = "Teléfono 1";
$lang["Second phone"] = "Teléfono 2";
$lang["Mobile phone"] = "Tel. celular";
$lang["Street"] = "Calle";
$lang["City"] = "Ciudad";
$lang["Zip code"] = "Código postal";
$lang["Country"] = "País";
$lang["Comment"] = "Comentario";
$lang["Private"] = "Privado";

// Tooltip Help
$lang["Tooltip"]["private"] = "No: todos los usuarios pueden verlo - Si: sólo el creador puede verlo.";

// General Help
$lang["Content Help"]["General"] = "DEFAULT";
$lang["Content Help"]["Contact"] = "<br />
    This is the <b>General Help of Contact module</b><br />
    <br />
    This module is just to store people and companies information. It has many fields for writing data.<br />
    <br />
    There is a special field <b>Private</b> that sets whether the selected contact is visible for all users or just
    for the creator.<br />
    <br />
    <br />
    <u><b>Linking contacts from other modules</b></u><br />
    <br />
    <b>Project and Helpdesk</b><br />
    <br />
    Project and Helpdesk modules have a field Contacts to select one to be associated with that Project or Helpdesk
    item.<br />
    <br />
    When you create or modify a Project or Helpdesk item, you can pick a contact from Contact field select box, for you
    to provide a reference to a specific person or company that may be useful for people who work with that Project or
    Helpdesk ticket.<br />
    <br />
    <br />
    <b>Other modules</b><br />
    <br />
    The Module Designer (Administration -> Module), permits adding a Contact field to any module. It has to be a
    'Select' field that has, inside left bottom edit box of the Module Designer for that field, tab Form, field Range,
    the value 'Contact#id#name'.<br />
    <br />
    <br />";

$lang["Content Help"]["Basic data"] = "<br />
    <b>Basic Data tab</b><br />
    <br />
    Fields:<br />
    <br />
    <b>Name</b><br />
    Text: the name of the person (or company / institution), e.g.: John Doe<br />
    * Required field<br />
    <br />
    <b>E-Mail</b><br />
    Text: email address, e.g.: john@doe.com<br />
    <br />
    <b>Company</b><br />
    Text: the company the contact works for, e.g.: Mayflower<br />
    <br />
    <b>First phone</b><br />
    Text: a telephone number. E.g.: 1234-5678<br />
    <br />
    <b>Second phone</b><br />
    Text: another telephone number. E.g.: 2345-6789<br />
    <br />
    <b>Mobile phone</b><br />
    Text: a mobile telephone number. E.g.: (15) 3456-7890<br />
    <br />
    <b>Street</b><br />
    Text: street and number of the address. E.g.: Maanhardtstrasse 100<br />
    <br />
    <b>City</b><br />
    Text: city where the contact live or works. E.g.: Munich<br />
    <br />
    <b>Zip Code</b><br />
    Text: zip code for the contact. E.g.: 12345<br />
    <br />
    <b>Country</b><br />
    Text: country for the contact. E.g.: Argentina<br />
    <br />
    <b>Comment</b><br />
    Textarea: just a comment about the contact. E.g.: call him from 9:00 to 5:00<br />
    <br />
    <b>Private</b><br />
    Select: whether this item is private or not; so that just the owner can see it or everyone can do it,
    respectively.<br />
    <br />
    <br />";

$lang["Content Help"]["History"] = "DEFAULT";
