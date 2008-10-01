    At the moment we have role based access control
    that define a set of function rights
    applied to a user on a per project basis.
    Furthermore we have a finer granulated access
    control on a per item.
    To find out if a user is able to read an item we have to combine both.

    The combination of the Roles and item access is do it with the next table:


                    R o l e s  R i g h t s
     -----------------------------------------------------
     |           |  read  |  write  |  create  |  admin  |
     -----------------------------------------------------
 I   |   read    |    X   |    X    |          |    X    |
 t   -----------------------------------------------------
 e   |   write   |        |    X    |    X     |    X    |
 m   -----------------------------------------------------
 s   |   access  |    X   |    X    |    X     |    X    |
     -----------------------------------------------------
 R   |   create  |        |    X    |    X     |    X    |
 i   -----------------------------------------------------
 g   |   copy    |        |    X    |    X     |    X    |
 h   -----------------------------------------------------
 t   |   delete  |        |    X    |          |    X    |
 s   -----------------------------------------------------
     |  download |   X    |    X    |          |    X    |
     -----------------------------------------------------
     |   admin   |        |         |          |    X    |
     -----------------------------------------------------

Each item will have an array with the access.
The array is mached with the user role for the current project node.

All the "X" in the table, are allowed rigths.
The item-role relation must have the two access for be valid.
Example, the delete access is allowed if the role have write or admin right.

    Examples:
		The following role defined:

		Senior Developer:
			Todo: (read, write, create)
			Files: (read, create)

		User 1 is a
			Senior developer on Project 1

		In the Project 1 there are the following todos:
            1.Write a documentation		to be done on 06.05.2006
            There is no user specified access on this todo

            2.Buy a new PC			to be done on 06.06.2006
            No access

            3.Call Mr. Hanson			to be done on 04.05.2006
            User 1 has read access on this todo

		And the following files:
            1.Project_Concept.doc
                User 1 has read, write access on this file

		So the resulting access for an item are:

        	On todo 1 „Write a documentation“ the User 1 can
			do nothing:
			Item: 	none 	 		    => R1 = {}
			Role: 	none      	        => R2 = {read, write, create}
            Combination = {}

			On todo 2 „Buy a new PC“ the User 1 can
			do nothing:
			Item: 	none 	 		    => R1 = {}
			Role: 	read,write,create	=> R2 = {read,write,create}
            Combination = {}

			On todo 3 „Call Mr. Hamson“ the User 1 can
			read:
			Item: 	read	 		    => R1 = {read}
			Role: 	read,write,create	=> R2 = {read,write,create}
            Combination = {read}

			On file „Project_Concept.doc“ the User 1 can
			read:
			Item: 	read,write		=> R1 = {read,write}
			Role: 	read,create		=> R2 = {read,create}
            Combination = {read,write}