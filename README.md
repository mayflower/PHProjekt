PHProjekt 6
-----------

PHProjekt is an application suite that supports communication and management of
teams and companies. It includes a group calendar, project management, a
request tracker and 12 other modules.
See http://www.phprojekt.com for more details

[![Build Status](https://secure.travis-ci.org/Mayflower/PHProjekt.png)](http://travis-ci.org/Mayflower/PHProjekt)

Contributing
-----------

Patches to PHProjekt are always welcome.

Before submitting pull requests sure that your prepared your patch with
the following rules in mind.

For a new feature, please open a pull request.

 1. Use unified diff format. This can be done using the diff -u switch. Version control
    systems like git use unified diffs by default. You can also send us
    pull requests.

 2. Include a proper commit message

 3. Make sure that you used Zend Framework Coding standard.
    http://framework.zend.com/manual/en/coding-standard.html

 4. If you like to add new functionallity, make sure that you write a proper proposal.

 5. If you include third party libraries, make sure that they can be distributed und
    the terms of the Lesser GNU Public License 3 as described in the LICENSE file.

 6. If you change anything under htdocs/dojo, recompile the dojo packages to include new compressed javascript
    ("ant compilejs" in the project root).

Happy hacking.

(c) 2011 Mayflower GmbH and Contributors
