Here is a short description for the fields of the DatabaseManager table

- tableName     = Name of the module and the table of the module.
                  Format: string.

- tablefield    = Name of the field in the table.
                  Format: string.
- formTab       = Number of the tab for show it in various tabs.
                  Format: number (integer).

- formLabel     = Text for display in the form (english text that is translated later).
                  Format: string.

- formTooltip   = Text for display in the title of the field in the form.
                  Format: string.

- formType      = Type of the field, the values allowed are:
                    selectValues        -> Selectbox using values in the formRange field
                    textarea            -> Textarea field
                    textfield           -> Normal input text field
                    checkbox            -> Normal checkbox field
                    date                -> Date field using the dojo datepicker
                    upload              -> Upload file input
                    multipleselect      -> Selectbox for multiple values
                    select_multiple     -> Selectbox for multiple values
                    text                -> Normal input text field
                    display             -> Use for show a value only
                    tree                -> Display a selectbox using the tree database (see formRange)
                  Format: string.

- formPosition  = Position of the field in the form.
                  Format: number (integer).

- formColumns   = Number of columns that use the field.
                  Format: number (integer).

- formRegexp    = Regular Expresion for check the field.
                  Format: string.

- formRange     = Mix value for make the data of the fields, like for select types
                  Format: string.
                  Format for selectvalues: value1#showtitle1|value2#showtitle2.
                  Format for tree: just the name of the class, for example "Project" for make a tree of projects.
                                   the backend will make a tree using the Phprojekt_Tree_Node_Database($activeRecord,1)
                                   where $activeRecord is a "Project" class.

- defaultValue  = Default value.
                  Format: string.

- listPosition  = Position of the field in the list.
                  Format: number (integer).

- listAlign     = Aligment of the field in the list.
                  Format: string (left, center or right).

- listUseFilter = Use the field in the filter list or not.
                  Format: number (0 or 1).

- altPosition   = Position of the field in the alt view.
                  Format: number (integer).

- status        = Active or Inactive field.
                  Format: number (0 or 1).

- isInteger     = Int field or not.
                  Format: number (0 or 1).

- isRequired    = If is a required field or not.
                  Format: number (0 or 1).

- isUnique      = If is a uniq value that can not be repeat or not.
                  Format: number (0 or 1).