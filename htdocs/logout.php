<?php
require_once('config_web.php');
require_once('lib.php');

    logoutSession($_SESSION['GH_SESSION']);

//session_start();
session_unset();
session_destroy();
    #redirect to index
    header('Location: index.php');
		exit(0);
