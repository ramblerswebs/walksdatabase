# RamblersWalksManager

A web based system for a local group to create and manage a list of walks for the Ramblers walks feed or their own programme.  

## Instructions

First create the database using the walksDatabase.sql file. This has all the tables required. Create a user for accessing this via the web.  Update the relevant settings in the config_web.php file.  

1. Create a folder outside the document root for include files. Update your php include_path to point to this. 

2. Add the following files (located in the php_include folder) to this folder:
   config_web.php  (contains constants, database settings and functions)
   lib.php (a library of functions)
   
3. Edit config_web.php with your own settings. It will not work without 

4. Add the remaining files (located in the htdocs folder) to your webserver document root leaving the folder structure as is.

If you are having problems it's most likely to do with the include path. 


### Prerequisites

This has been updated to use php version 7.0.

### Disclaimer

This was initially developed, as a bespoke system for Godalming and Haslemere Ramblers, quite a while ago. This was never intended to be made generally available but as I have had some requests for the code I have updated the files to remove any specific references to our Rambles. It has not been extensively tested.

Although some updates have been made the jQuery libraries used has not been updated since then. These libraries are now old and needs to be replaced. This is likely to require quite a lot of changes to make it look and function properly. Please feel free to have a go.

Due to an update to use php 7.0. I recently updated the code to use mysqli rather than mysql as this was requited for the move to php 7.0. I chose mysqli as this was a quick update. It would be better to use pdo but that's a bigger change. It has not been tested with 7.2.


## License

This project is licensed under [http://opensource.org/licenses/AGPL-3.0] AGPLv3 (Affero GPL, version 3)

## Acknowledgments

* Various snippets of JavaScript have been attributed in the code


