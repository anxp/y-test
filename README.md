#Member registration app
###About
Application allows register peoples to "Club". 
Every record should have unique email.
Data stores in PHP session, and can be wiped any time by clicking "Clear storage" button.
User names are validated - only latin letters, spaces and dots are allowed.
User emails validated for syntax and for actual existing MX record in specified domain.
###Installation
All application consists of just one file - index.php.
To install app just place index.php file to any webhosting.
On first run app will try to create log file. If it can't create log file,
error messge will be displayed. In this case please create log.txt file manually
and check that it has write permission.
###Live Demo
Live demo available here: [http://y-test.sherlock-ua.bid/](http://y-test.sherlock-ua.bid/)