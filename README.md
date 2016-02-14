Description:
----------------

Information system for doctors and nurses where they can manage appointments, patients, drugs and treatments.
It was a school team project for Information Systems class.
We had to design ERD diagram and Uscase diagram. Based on these diagrams we created UML diagram.
These diagrams were used to create the application.

Account types:
------------------
There are three different types of accounts. You can try them in the demo application.

| Type          |      Username     |  Password |
|----------     |:-------------:    |------:    |
| Doctor        |  0                | 0         |
| Nurse         |  1                | 0         |
| Administrator | admin             | 00        |

Live demo:
---------------
http://iis.mstav.eu/

Technologies used:
------------------
PHP, Nette, Bootstrap, JS, Jquery, HTML, MySQL, Ajax

Authors:
------------------
Filip Jezovica, Marek Marusic

How to add user:
------------------
```
add user = php ..\root\IIS\nette\bin\create-user.php name pwd
admin 00
0 0
1 1
```