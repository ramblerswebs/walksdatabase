<?php
/**
 * @author  Kirsten H Osa
 * Date created: 28/12/13
 */
############################################
# Group Constants
############################################
define('GROUP_NAME', 'Name of Group Ramblers'); //Used in display
define ('GROUP_DOMAIN', 'gandhramblers.org.uk'); //Must set to your main domain as used for link and email
define ('GROUP_WEBSITE', 'https://'. GROUP_DOMAIN . '/');



############################################
# Send debug information to browser
############################################
define('GH_DEBUG', false);
define('GH_DEVELOPMENT', false);
//define('GH_NO_EMAIL', true); //Set to true on dev server with no email
############################################


############################################
# DATABASE
############################################

define('GH_DBSERVER', 'localhost'); //Database server
//LOCAL Machine
define('GH_DBNAME', 'rwalks');//default name of database - change if different name used
define('GH_DBUSER', 'dbUser'); //change to match your defined database user
define('GH_DBPW', 'dbPassword'); // change to match password for your database user
//LIVE
//define('GH_DBNAME', 'rwalks');//default name of database - change if different name used
//define('GH_DBUSER', 'liveDbUser'); //change to match your defined database user
//define('GH_DBPW', 'LiveDbPassword'); // change to match password for your database user


############################################
# Email Constants
############################################
define ('GH_EMAIL_REPLY_TO', GROUP_NAME . ' <webmaster@'.GROUP_NAME.'>');// MUST set to your email
define ('GH_ADMIN_EMAIL', 'webmaster@your-email'); // Set as appropriate
define ('GH_ERR_EMAIL', 'webmaster@your-email'); // // Set as appropriate
############################################



require_once('lib.php');
