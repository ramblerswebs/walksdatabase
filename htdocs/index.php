<?php
/**
 * @author  Kirsten H Osa
 */
require_once('config_web.php');
$pageOn="index";
$title = GROUP_NAME . ' Walks program';

$iToday = time();

$bShowLoginForm = true;

if ( $_POST['User'] && $_POST['Password'] ) {
	closeSession(array('GH_')); // delete previous session values in case some left over

	$sUserName = strtolower(trim($_REQUEST['User']));
	$sPassword = trim($_REQUEST['Password']);

	$sQuery = "SELECT userId, userName, firstName, lastName, adminLevel, status	FROM user
		WHERE LOWER(userName) = '".addslashes($sUserName) . "' AND password = '" . addslashes($sPassword)."'";
	$rResult = runQuery($sQuery);

	if ( $rResult ) {
        if ( $rResult->num_rows > 0 ) {
			$aRow = $rResult->fetch_array(M_DB_BOTH);
			$iUserId = $aRow['userId'];
			$sUserName 	= $aRow["userName"];
			$sFirstName = $aRow["firstName"];
			$sLastName 	= $aRow["lastName"];
			$iSecurityLevel = $aRow["adminLevel"]; //10, 9, 8 full access : 5 edit access
			$iStatus = $aRow["status"];

			if ( $iStatus == 1  && $iSecurityLevel >= 5  ) {
				$_SESSION['GH_UserID'] 		= $iUserId;
				$_SESSION['GH_UserName'] 	= $sUserName;
				$_SESSION['GH_FName'] 	= $sFirstName;
				$_SESSION['GH_LName'] 	= $sLastName;
				$_SESSION['GH_SecurityLevel'] = $iSecurityLevel;


                $_SESSION['GH_LoggedIn'] = true;
                $_SESSION['GH_SuperAdmin'] = $iSecurityLevel >= 8 ? true : false;

				//Comment out line below if want to receive email when user log in
                //sendInfo(GH_LOG_INFO, "$sFirstName $sLastName Logged in OK", 'Back Office Log-in OK');
                if( $iSecurityLevel == 10) {
                    $sDbInfo =  ' on ' . GH_DBSERVER;
                }
                $sUserInfo = " ($sUserType$sDbInfo)";
                $_SESSION['GH_UserInfo'] = $sFirstName.' '.$sLastName . $sUserInfo;
			} else {
				$sErrorString = $iSecurityLevel < 5 ? 'You do not have access to the Admin Area.' : 'Your account is disabled';
				$bShowLoginForm = false;
			}
            addSession();
        } else {
    	    $sErrorString = 'The username (email) and password combination did not match.';
        }
	} else {
		$sErrorString = 'Database problem. The webmaster has been notified.'; //\n$sQuery\n";
		$bShowLoginForm = false;
	}
    if ( !empty($sErrorString) ) {
        sendInfo(GH_LOG_ERR, $sErrorString, 'GH Walks Admin Log-in Failure');
    }
}

// check if logged in
if ( $_SESSION['GH_LoggedIn'] ) {
	$bShowLoginForm = false;
    $aDates = getNextPeriod();
    $iNextStart = $aDates['start'];
    $iNextEnd = $aDates['end'];
    $iCurrentEnd = ($iNextStart - $iOneDay);

    $sWalkPeriod = '(Period '.date('D j M, Y', $iNextStart).' - '.date('D j M, Y', $iNextEnd).')';
    $sCurrent = '(up to '.date('D j M, Y', $iCurrentEnd).')';
}

?>

<!doctype html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <title><?php echo $title;?></title>
    <link href="common/script/jquery-ui-1.10.3.custom/css/custom-theme/jquery-ui-1.10.3.custom.css" rel="stylesheet">
    <link href="common/css/main.css" rel="stylesheet">
    <style type="text/css">

        .warning { font-style: italic; color: red;}

        div.error { display: none; }
        input.error { border: 1px dotted red; }
        input:focus { border: 1px dotted black; }
        #Form { color: #333333; width: 750px; }
        #Form label { cursor: hand; display: inline-block; padding-right: 5px; text-align: right; vertical-align: top; width: 200px; margin-top: 0px; }

        #Form label.error { color: red; font-style: italic; margin-left: 188px; text-align: left; width: auto; }
        #Form button#Submit { margin-left: 190px; }

        #Form ul { list-style: none; margin: 0; padding: 0;}
        #Form li { margin-top: 5px;}
        #Form li.submit { margin-left: 200px; padding-top: 10px; padding-bottom: 20px;}
        #Form .input { width: 450px; }

        fieldset
        {
            border: none;
            padding:5px;
            padding-right: 1px;
        }
        legend
        {
            color: #00539B;
            padding:6px;
        }
        .align-left {float:left; min-width: 300px; margin-left: 25px; }

    </style>

</head>
<body id="gh-body">
<?php
include_once('common/top.php');
?>
<div id="main-cont">

<?php
  if ( $_SESSION['GH_LoggedIn'] ) {
?>
<div id="crumbtrail">You are Logged in as <?php echo $_SESSION['GH_UserInfo'] . ' = ' . $_SESSION['GH_SESSION']; ?> &mdash; <a href="logout.php">Logout</a></div>
    <div id="main-cont">
      <h2><?php echo GROUP_NAME; ?> Walks Admin Area</h2>
        <div class="align-left">
            <h3>Walks</h3>
            <ul class="category">
                <li><a href="walks.php?t=1">Current Walks List</a> - <?php echo $sCurrent; ?></li>
                <li><a href="walks.php?t=2">Next Walks Program</a> - <?php echo $sWalkPeriod; ?></li>
                <li><a href="walks.php">All Walks List</a> </li>

            </ul>
        </div>
        <br clear="all" />
        <?php

      if ( $_SESSION['GH_SuperAdmin'] ) {
?>
      <div class="align-left">
          <h3>Start Points</h3>
        <ul class="category">
          <li><a href="startPoints.php">Start Points</a></li>
          <li><a href="editStartPoint.php">Add a New Start Point</a></li>
        </ul>
      </div>

<?php
      }

  } else {
?>
   <div id="main-cont">
     <h2>Godalming &amp; Haslemere Walks Program</h2>
    <h3>Login</h3>
		<p>This is the Walks Program Administration Area.</p>

		<!--<div class="icon_lock">-->
<?php
    if ($sErrorString) {
?>
			<div class="ui-state-error ui-corner-all" style="padding: 0 .7em; width: 700px;">
				<p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em; "></span>
				<strong>Alert:</strong><br /><?php echo $sErrorString; ?>.</p>
			</div><br clear="all" />
<?php
    }
    if ($bShowLoginForm) {
?>
        <fieldset>
				<p>Please login with your username (which is your email address) and password using the form below.</p>
				<form action="" class="form" id="Form" method="post" name="Form">

					<ul>
						<li>
							<label for="Email">Username</label>
							<input class="input" id="Email" name="User" type="text" />
						</li>
						<li>
							<label for="Password">Password</label>
							<input class="input" id="Password" name="Password" type="password" />
						</li>
						<li class="submit">
							<div class="buttons">
								<button id="Login" type="submit">Login</button>
							</div>
							<br clear="all" />
						</li>
					</ul>

				</form>
        </fieldset>
<?php
    }
  }
?>
<br clear="all" />
  </div>
  <br clear="all" />
</div>

<br clear="all" />
<script src="common/script/jquery-ui-1.10.3.custom/js/jquery-1.9.1.js"></script>
<script src="common/script/jquery-ui-1.10.3.custom/js/jquery-ui-1.10.3.custom.js"></script>
<script type="text/javascript">

	$(document).ready(function()
 	{

/* Any extra javaScript code here
*/

// End of Doc ready
  });

</script>
</body>
</html>