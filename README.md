This is a fork of https://nasauber.de/opensource/b8/ . The main differences with it are:
 - Support of PHP PDO (the file is still called mysql.php due to limited time/laziness)
 - All tokens are now lowercase so that spammers can't evade the spam rules with different casings
 - Added a list of words to ignore (can be edited under b8/lexer/standard.php line 32, keep everything lowercase)
 - Avoids a database crash in the unlikely scenario that the script would try to insert the same token twice

As per the Mr. Leupold project, it is also dependency-free and simple to setup.

