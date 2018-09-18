<?php
/**
 * @author  Kirsten H Osa
 */
include_once('config_web.php');
if ( !$_SESSION['GH_LoggedIn'] ) {
    //redirect to login page
    header('Location: index.php');
    exit(0);
}
$pageOn="walk";
$title = GROUP_NAME . ' - add/edit walk';
$iWalkId = intval($_REQUEST['wid']);
$iClone = intval($_REQUEST['clone']);
#Default settings eg New walk - need to set predefined start date and times
$iToday =  mktime(0,0,0,date('m'), date('d'), date('Y'));
$aDates = getNextPeriod();  // need to have predefined for csv & program to match period wanted
$iNextStart = $aDates['start'];
$iNextEnd = $aDates['end'];
$iCurrentEnd = ($iNextStart - $iOneDay);

$iUserId = intval($_SESSION['GH_UserID']);

if( !empty($iWalkId) && empty($iClone) ) {
    $iDefaultStartDate =  $iToday ; //so can edit current or new
    $sPeriodString = '';
} else {
    $iDefaultStartDate =  $iNextStart ; //so if new walk limit start to new program???
    $sPeriodString = 'New Program Period ' . date('D j M, Y', $iNextStart) . ' to '. date('D j M, Y', $iNextEnd);
}

$iDefaultRepeatStart = strtotime('next Wednesday', $iDefaultStartDate);
$iDefaultRepeatEnd = strtotime('last Wednesday', $iNextEnd);

$sDefaultDateStart = date("D j M Y", $iDefaultStartDate);
$sDefaultDateEnd = date("D j M Y", $iNextEnd);
$sDefaultDateRepeat = date('D j M Y', $iDefaultRepeatStart);
$sDefaultDateRepeatEnd = date('D j M Y', $iDefaultRepeatEnd);

$sTimeStart = '10:00 am';
$bIsFigOf8 = false;
$bIsRepeat = false;

if( $_POST['Action'] == 'Save' ) {
    $sErrorMsg = '';
    $sFbMsg = '';
    $iStatus = intval($_POST['status']);
    //print '<pre>'.print_r($_POST, true) . '</pre>';
    $walkTitle = addslashes($_POST['walkTitle']);
    if ( empty($walkTitle) ) {
        $sFbMsg .= 'The title (brief description) for the walk is empty.<br/>' . PHP_EOL;
    }
    $startTs = strtotime($_POST['startDate']);
    $startDate = date("Y-m-d", $startTs);
    $timeStart = date("H:i:00", strtotime($_POST['timeStart']));
    $distance = floatval($_POST['distance']);
    if ( empty($distance) ) {
        $sFbMsg .= 'The distance (in miles) for the walk is empty.<br/>' . PHP_EOL;
    }
    $isf8 = intval($_POST['isf8']);
    $startTimePm = '00:00:00';
    $distancePm = 0;
    $grade = addslashes($_POST['grade']);
    if ( empty($grade) ) {
        $sFbMsg .= 'No grade has been specified for the walk.<br/>' . PHP_EOL;
    }
    $terrain = addslashes($_POST['terrain']);
    if ( empty($terrain) && !empty($grade) ) {
        $terrain = $aGradeTitle[$grade];
    }
    if( $isf8 ) {
        $timeStartPm = date("H:i:00", strtotime($_POST['timeStartPm']));
        $distancePm = floatval($_POST['distancePm']);
        if ( empty($distancePm) ) {
            $sFbMsg .= 'The distance for the PM part of the walk is missing.<br/>' . PHP_EOL;
        }
        $keyPlacesPm = addslashes($_POST['keyPlacesPm']);
        if ( empty($keyPlacesPm) ) {
            $sFbMsg .= 'Key Places for the PM part of the walk is missing.<br/>' . PHP_EOL;
        }
    }
    $repeatDate = '0000-00-00';
    $repeat = intval($_POST['repeatOpt']);
    if ( $repeat > 1) {
        $bAddRepeat = intval($_POST['addRepeat']);
        $repeatTs =  strtotime($_POST['repeatDate']);
        $repeatDate = date("Y-m-d",$repeatTs);
        if ($repeat % 2 == 0) {
            $repeat2 = $repeat+1;
            //check repeat date is after start
            if ( $repeatTs < $startTs) {
                $sErrorMsg .= 'You have selected Repeat ON but repeat date is BEFORE this walk\'s date<br/>' . PHP_EOL;
            }
        } else {
            $repeat2 = $repeat-1;
            //check repeat date is before start
            if ( $repeatTs > $startTs) {
                $sErrorMsg .= 'You have selected Repeat FROM but repeat date is AFTER this walk\'s date.<br />' . PHP_EOL;
            }
        }
    }
    #Start point data
    $startPointId = filter_var($_POST['startPointId'], FILTER_SANITIZE_NUMBER_INT); // intval($_POST['startPointId']);
    $startText = addslashes(($_POST['startText']));
    if ( empty($startText) ) {
        $sFbMsg .= 'You have not specified a start point. Please choose from predefined list where possible.<br />' . PHP_EOL;
    }
    $gridRef = str_replace(' ','', $_POST['gridRef']);
    if ( !empty($gridRef) ) {
        $sCode =  strtoupper(substr($gridRef, 0, 2));
        $iNum = substr($gridRef, 2, 6);
        if ( ctype_digit($iNum) ) {
            $gridRef = $sCode . $iNum;
            if ( strlen($gridRef) != 8 ) {
                $sErrorMsg .= 'The Grid Ref ('.$gridRef.')  has too few or too many characters. It should be 2 letters followed by 6 numbers.<br />' . PHP_EOL;
            }
        }
        else {
            $sErrorMsg .= 'The Grid Ref ('.$gridRef.')  is not a valid format. It should be 2 letters followed by 6 numbers.<br />' . PHP_EOL;
        }
    } else {
        $sFbMsg .= 'You have not specified a Grid Ref for the start point<br />' . PHP_EOL;
    }
    $bMapOK = true;
    $explorer = intval($_POST['explorer']);
    $explorerNew = !empty($_POST['explorerNew']) ? intval($_POST['explorerNew']) : "NULL";

    if ( empty($explorer) ) {
        $explorer = "NULL";
    } else {
        $bMapOK = checkNumber($explorer, 3); //ctype_alnum($explorer); //
    }

    $explorer2 = intval($_POST['explorer2']);
    if ( empty($explorer2) ) {
        $explorer2 = "NULL";
    } else {
        $bMapOK = checkNumber($explorer2, 3);
    }
    $explorer3 = intval($_POST['explorer3']);
    if ( empty($explorer3) ) {
        $explorer3 = "NULL";
    } else {
        $bMapOK = checkNumber($explorer3, 3);
    }
    $landRanger = intval($_POST['landRanger']);
    if ( empty($landRanger) ) {
        $landRanger = "NULL";
    } else {
        $bMapOK = checkNumber($landRanger, 3);
    }
    $landRanger2 = intval($_POST['landRanger2']);
    if ( empty($landRanger2) ) {
        $landRanger2 = "NULL";
    } else {
        $bMapOK = checkNumber($landRanger2, 3);
    }
    if ( !$bMapOK ) {
        $sErrorMsg .= 'Map Numbers should only contain numbers and letters<br />' . PHP_EOL;
    }
    if ( !empty($_POST['postCode']) ) {
        $postCode = formatBritishPostcode($_POST['postCode']);
        if ( !$postCode ) {
            $sErrorMsg .= $_POST['postCode'] . ' is not a valid UK Post Code<br />' . PHP_EOL;
        }
    } else {
        $sFbMsg .= 'Missing Post Code for the walk.<br/>' . PHP_EOL;
    }

    $keyPlaces  = addslashes(($_POST['keyPlaces']));
    if ( empty($keyPlaces) ) {
        $sFbMsg .= 'Missing Key Places for the walk.<br/>' . PHP_EOL;
    }

    $refreshment = intval($_POST['refreshment']);
    $stopDetails = addslashes($_POST['stopDetails']);
    $stopDetails2 = addslashes($_POST['stopDetails2']);
    if( !empty($stopDetails) &&  $refreshment == 0) {
        $sFbMsg .= 'Refreshment stop added but type of stop not specified<br />' . PHP_EOL;
    }
    $leader = addslashes($_POST{'leader'});
    $leader2 = addslashes($_POST{'leader2'});
    $telH = str_replace(' ','', $_POST['telH']);
    $telM = str_replace(' ','', $_POST['telM']);
    $tel2 = str_replace(' ','', $_POST['tel2']);

    if ( empty($leader) ) {
        $sFbMsg .= 'No contact specified for the walk<br />' . PHP_EOL;
    }
    if( empty($telH) && empty($telM) ) {
        $sFbMsg .= 'No contact number given for the contact<br />' . PHP_EOL;
    }
    if ( !empty($telH) ) {
        if(!ctype_digit($telH)) {
            $sErrorMsg .= 'The phone number can only contain numbers<br />' . PHP_EOL;
        } else {
            //check have enough numbers 11? some may be less.  Should start with 0
        }
    }
    if ( !empty($telM) ) {
        if(!ctype_digit($telM)) {
            $sErrorMsg .= 'The mobile number can only contain numbers<br />' . PHP_EOL;
        } else {
            //check have enough numbers 11? some may be less.  Should start with 0
        }
    }
    if ( !empty($tel2) ) {
        if(!ctype_digit($tel2)) {
            $sErrorMsg .= 'A phone number can only contain numbers<br />' . PHP_EOL;
        } else {
            //check have enough numbers 11? some may be less.  Should start with 0
        }
    }

    $mobOpt = intval($_POST['mobOpt']);
    if ( $mobOpt && empty($telM) ) {
        $sErrorMsg .= 'You have specified a mobile option but no mobile number<br />' . PHP_EOL;
    }

    if ( empty($sErrorMsg) ) {
        //update or insert
        if ( !empty($iWalkId) ) {
            $sQuery = "UPDATE walksProg SET
            walkDate='$startDate', startTime='$timeStart', startTimePm='$timeStartPm', walkTitle='$walkTitle', walkGrade='$grade', terrain='$terrain', distance='$distance', distancePm='$distancePm', startPointId=$startPointId, startPoint='$startText', gridRef='$gridRef', explorer=$explorer, explorerNew=$explorerNew, explorer2=$explorer2, explorer3=$explorer3, landRanger=$landRanger, landRanger2=$landRanger2, postCode='$postCode', keyLocations='$keyPlaces', keyLocationsPm='$keyPlacesPm', leader='$leader', leaderLandline='$telH', leaderMobile='$telM', mobileOpt=$mobOpt, leader2='$leader2', leader2Tel='$tel2', refreshmentStopType=$refreshment, refreshmentStopDetails='$stopDetails', refreshmentStop2Details='$stopDetails2', repeatType=$repeat, repeatDate='$repeatDate', status=$iStatus
            WHERE id=$iWalkId";

            $rResult = runQuery($sQuery, DB_WRITE, 1);
            if($rResult) {
                $sFb = 'Walk Info Updated';
            } else {
                $sError =  "Update failed "; // . $sQuery;
            }
        } else {
            $sQuery = "INSERT INTO walksProg (id, walkDate, startTime, startTimePm, walkTitle, walkGrade, terrain, distance, distancePm, startPointId, startPoint, gridRef, explorer, explorerNew, explorer2, explorer3, landRanger, landRanger2, postCode, keyLocations, keyLocationsPm, leader, leaderLandline, leaderMobile, mobileOpt, leader2, leader2Tel, refreshmentStopType, refreshmentStopDetails, refreshmentStop2Details, repeatType, repeatDate, status, userId)
            VALUES (0, '$startDate','$timeStart', '$timeStartPm', '$walkTitle', '$grade', '$terrain', '$distance', '$distancePm', $startPointId, '$startText', '$gridRef', $explorer, $explorerNew, $explorer2, $explorer3, $landRanger, $landRanger2, '$postCode', '$keyPlaces', '$keyPlacesPm', '$leader', '$telH', '$telM', $mobOpt, '$leader2', '$tel2', $refreshment, '$stopDetails', '$stopDetails2', $repeat, '$repeatDate', $iStatus, $iUserId)";

            $rResult = runQuery($sQuery, DB_WRITE, 1);
            $iWalkId = $m_SQL_Link->insert_id;
            if ( empty($iWalkId) ) {
                $sError =  "Insert failed line 320 ";// . $sQuery;
            } else {
                $sFb = 'Walk Info Added';
            }

        }
        //echo "$sQuery<br/>";
        if ( $bAddRepeat && $iWalkId ) {
            $bHaveRepeat = false;

            // Check no walk with same date and start point?  But only if walk not in the past
            if ( $repeatTs < $iToday ) {
                $bRepeatAdded = isRepeatAdded( $repeatDate, $distance, $gridRef );
            }

            if ( $bRepeatAdded ||  $repeatTs < $iToday ) {
                $sFb .= '<br />Please Note that Repeat has not been added as looks like it has already been added (id:'.$bRepeatAdded.'). If this is not the case please add the repeat manually.' . $bRepeatAdded;
            } else {
                $sQuery2 = "INSERT INTO walksProg (id, walkDate, startTime, startTimePm, walkTitle, walkGrade, terrain, distance, distancePm, startPointId, startPoint, gridRef, explorer, explorerNew, explorer2, explorer3, landRanger, landRanger2, postCode, keyLocations, keyLocationsPm, leader, leaderLandline, leaderMobile, mobileOpt, leader2, leader2Tel, refreshmentStopType, refreshmentStopDetails, refreshmentStop2Details, repeatType, repeatDate, status, userId)
            VALUES (0, '$repeatDate','$timeStart', '$startTimePm', '$walkTitle', '$grade', '$terrain', '$distance', '$distancePm', $startPointId, '$startText', '$gridRef', $explorer, $explorerNew, $explorer2, $explorer3, $landRanger, $landRanger2, '$postCode', '$keyPlaces', '$keyPlacesPm', '$leader', '$telH', '$telM', $mobOpt, '$leader2', '$tel2', $refreshment, '$stopDetails', '$stopDetails2', $repeat2, '$startDate', $iStatus, $iUserId)";
                $rResult2 = runQuery($sQuery2, DB_WRITE);
                $iRepeatId = $m_SQL_Link->insert_id;
                if ( empty($iRepeatId) ) {
                    $sFb .= '<br />Failed to add repeat - line 353'; // . $sQuery;
                } else {
                    $sFb .= '<br />Repeat Walk Added';
                }
            }
        }
        if ( !empty($sFb) && !empty($sFbMsg) ) {
            $sFb .= '<br/>NOTE: <br/><span class=""error">'.$sFbMsg.'</span>' . PHP_EOL;
        }
    }

}

$aStartPointData = array();
$sQuery = "SELECT startPointId, startPoint, gridRef, explorer, explorerNew, landRanger, postCode FROM startPoints ORDER BY startPoint";
$rResult = runQuery($sQuery);
if($rResult) {
    while ($aRow = $rResult->fetch_array(M_DB_BOTH)) {
        $iId = $aRow['startPointId'];
        $aStartPointData['s' . $iId]['id'] =  $iId;
        $aStartPointData['s' . $iId]['startPoint'] =  $aRow['startPoint'];
        $aStartPointData['s' . $iId]['gridRef'] =  formatGridRef($aRow['gridRef']);
        $aStartPointData['s' . $iId]['explorer'] =  $aRow['explorer'];
        $aStartPointData['s' . $iId]['explorerNew'] =  $aRow['explorerNew'];
        $aStartPointData['s' . $iId]['landRanger'] =  $aRow['landRanger'];
        $aStartPointData['s' . $iId]['postCode'] =  $aRow['postCode'];
    }
}

$iToday = time(); // need this now?
if ( !empty($iWalkId) ) {

    $sQuery = "SELECT UNIX_TIMESTAMP(walkDate) as TS, startTime, startTimePm, walkTitle, walkGrade, terrain, distance, distancePm, startPointId, startPoint, gridRef, explorer, explorerNew,  explorer2, explorer3, landRanger, landRanger2, postCode, keyLocations, keyLocationsPm, leader, leaderLandline, leaderMobile, mobileOpt, leader2, leader2Tel, refreshmentStopType, refreshmentStopDetails, refreshmentStop2Details, repeatType, UNIX_TIMESTAMP(repeatDate) as RTS, repeatId, userId, status
     FROM walksProg WHERE id=$iWalkId";
    $rResult = runQuery($sQuery);
    if ( $rResult && $rResult->num_rows == 1) {
        $aRow = $rResult->fetch_array(M_DB_BOTH);
        if( $iClone ) {
            $iWalkId = 0;
        }

        $iStatus = $aRow['status'];
        $iTs = $aRow['TS'];
        $sDateStart = date("D j M Y", $iTs);
        $iDayOfWeek = date('N', $iTs);
        $sTimeStart = date("h:i a", strtotime($aRow['startTime']));
        $iStartTimeNum = (int) date('H',strtotime($aRow['startTime']));
        if ( $iStartTimeNum < 6 ) {
            $sTimeMsg =  'Start time is too early. Please check.';
        }

        $sWalkTitle = htmlspecialchars($aRow['walkTitle']);

        $iDistance = (float)$aRow['distance'];  // should not be empty?
        if ( empty($iDistance)) {
            $sDistMsg = 'You must specify a distance (in miles) for the walk.';
        }

        //Fig of 8  - should be null if no pm
        if ( $aRow['startTimePm'] != null && $aRow['startTimePm'] != '00:00:00' ) {
            $sTimeStartPm = date("h:i a", strtotime($aRow['startTimePm']));
            $iStartTimeNumPm = (int) date('H',strtotime($aRow['startTimePm']));
            if ( $iStartTimeNumPm < $iStartTimeNum ) {
                $sTimeMsgPm =  'The PM start time must be after the AM start time.';
            }
            elseif( $iStartTimeNumPm < 12 ) {
                $sTimeMsgPm =  'The PM start time must be 12 midday or later.';
            }
            $iDistancePm = (float)$aRow['distancePm'];
            $sKeyPlacesPm = $aRow['keyLocationsPm'];
            $bIsFigOf8 = true;
        }

        //Contact etc
        $sGrade = $aRow['walkGrade'];
        $sTerrain = htmlspecialchars($aRow['terrain']);

        if ( empty($sGrade) ) {
            $sGradeMsg = 'You must specify a grade for the walk.';
        }

        //Leader etc
        $sLeader = htmlspecialchars($aRow['leader']);
        $sLeaderLandline = formatPhoneNumber($aRow['leaderLandline']);
        $sLeaderMobile = formatPhoneNumber($aRow['leaderMobile']);

        $iMobileOps = $aRow['mobileOpt']; //$aMobileOpts[$aRow['mobileOpt']];

        //2nd leader  - need to remove grid ref from column
        $sLeader2 = htmlspecialchars($aRow['leader2']);
        $sLeader2Tel = $aRow['leader2Tel'];

        //$sKeyPlaces = htmlspecialchars($aRow['keyLocations']);
        //$sKeyPlaces = htmlentities($aRow['keyLocations'], ENT_IGNORE, UTF-8);
        $sKeyPlaces = $aRow['keyLocations'];

        $iStopType = htmlspecialchars($aRow['refreshmentStopType']);
        $sStopInfo = htmlspecialchars($aRow['refreshmentStopDetails']);
        $sStopInfo2 = htmlspecialchars($aRow['refreshmentStop2Details']);

        # repeat info  / now at end of col 3
        $iRepeat = $aRow['repeatType'];
        $iRTs = $aRow['RTS'];
        $bCanAddRepeat = false;
        $bRepeatAdded = false;
        if ( $iRTs ) {
            $sDateRepeat = date('D j M Y', $iRTs);
            $bIsRepeat = true;
            $iRepeatId = $aRow['repeatId'];
            if ( $iRepeat % 2 == 0 ) {
                $bCanAddRepeat = true;
            }
            $bRepeatAdded = isRepeatAdded(date('Y-m-d',$iRTs), $iDistance, $aRow['gridRef']);
        }

        $iStartId = $aRow['startPointId'];
        $sStartText = htmlspecialchars($aRow['startPoint']);
        $sGridRef = formatGridRef($aRow['gridRef']);

        $sExplorer = $aRow['explorer'];
        $sExplorerNew = $aRow['explorerNew'];
        $sExplorer2 = $aRow['explorer2'];
        $sExplorer3 = $aRow['explorer3'];
        $sLandRanger = $aRow['landRanger'];
        $sLandRanger2 = $aRow['landRanger2'];
        $sPostCode = formatPostCode($aRow['postCode']);
        if ( $iStartId ) {
            $sStartMsg = '';
            if ( $aRow['startPoint'] != $aStartPointData['s' .$iStartId]['startPoint'] ) {
                $sStartMsg .= 'The start point does NOT match that in the predefined start points.<br/>';
            }
            if ( $sGridRef != $aStartPointData['s' .$iStartId]['gridRef']  ) {
                $sStartMsg .= 'The given Grid Reference does NOT match that in the predefined start points.<br/>';
            }
            if ( $sExplorer != $aStartPointData['s' .$iStartId]['explorer'] ) {
                $sStartMsg .= 'The given Explorer Old X map number does NOT match that in the predefined start points.<br/>';
            }
            if ( $sExplorerNew != $aStartPointData['s' .$iStartId]['explorerNew'] ) {
                $sStartMsg .= 'The given Explorer New OL map number does NOT match that in the predefined start points.<br/>';
            }
            if ( $sLandRanger != $aStartPointData['s' .$iStartId]['landRanger'] ) {
                $sStartMsg .= 'The given LandRanger map number does NOT match that in the predefined start points.<br/>';
            }
            if ( $sPostCode != $aStartPointData['s' .$iStartId]['postCode'] ) {
                $sStartMsg .= 'The given Post Code does NOT match that in the predefined start points.<br/>';
            }
        }
    } else {
        $sError =  "Query failed";
    }
}
if ( !$bIsFigOf8 ) {
    $sTimeStartPm = '1:00 pm';
}

?>
<!doctype html>
<html lang="uk">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title><?php echo $title;?></title>
    <link rel="stylesheet" href="common/script/jquery-ui-1.10.3.custom/css/custom-theme/jquery-ui-1.10.3.custom.css">
    <link rel="stylesheet" type="text/css" href="common/script/jquery-ui-1.10.3.custom/css/jquery-ui-timepicker-addon.css" />
    <link href="common/css/main.css" rel="stylesheet">
    <style type="text/css">

        h3 {font-size: 140%; margin-bottom: 5px; margin-top: 15px;}
        #FormContent { font-size: 110%;}

        .dateField { border: solid 1px #C75B12; cursor: pointer; padding: 5px 5px 5px 5px; margin-left: 0.5em; margin-right: 0.5em;}
        .dateField img { vertical-align: middle; }
        #datePicker, #datePicker2 { border: none !important; width: 130px;}
        #timeStartPicker, #timePmPicker { border: none !important; width: 60px;}

        #walkForm { margin-top: 20px; width: 100%; padding-bottom: 10px; }
        #walkForm ul { list-style: none; margin: 0; padding: 0; }
        #walkForm li { padding-bottom: 20px; }
        #walkForm label:first-child { display: block; float: left; margin-right: 0.5em; text-align: right; width: 12em; font-weight: bold;}
        #walkForm input { margin-left: 0.5em; margin-right: 0.5em; font-size: 12px; }
        #walkForm input.long { width: 600px; }
        #walkForm input.medium { width: 400px; }
        #walkForm input.small { width: 100px; }
        #walkForm input.short { width: 75px; }
        #walkForm input.tel { width: 90px; }
        #walkForm input.map { width: 30px; }
        #walkForm input.mini { width: 25px; }

        #walkForm select { margin-left: 0.5em; margin-right: 0.5em;  font-size: 12px;}

        #walkForm textarea { width: 600px; height: 50px; margin-left: 0.5em;  font-size: 12px;}
        .buttons {text-align: center; margin: 5px;}
        #save {padding: 4px; width: 120px;}
        #walkForm li.button { margin-left: 12em; }
        #walkForm li.submit { margin-left: 12em; }

        fieldset { padding:5px; padding-right: 10px; border: 1px solid #C75B12;}
        legend { color: #00539B;  padding:6px; }

        .align_left {text-align: left;}
        .align_center {text-align: center;}
        .align_right {text-align: right;}
        .italic { font-style: italic;}
        .formInfo { margin-left: 13em; }
        .itemInfo { font-style: italic;}
        .itemMsg {  margin-left: 13em; color: #FF0000;}
        .required {color: #FF0000;}
        .error {color: #FF0000;}
        .fix { cursor: pointer; color: #0000EE; font-weight: bold;}
        #save {cursor: pointer; }

       <?php if (!$bIsFigOf8) echo '.fo8 {display:none;}';?>
       <?php if ($iDayOfWeek != DOW_WEDNESDAY) echo '#canRepeat {display:none;}';?>
       <?php if (!$bIsRepeat) echo '#repeat {display:none;}';?>
       <?php if (!$bCanAddRepeat) echo '.AddRepeat {display:none;}';?>

    </style>

</head>
<body id="gh-body">
<?php
include_once('common/top.php');
?>
<div id="main-cont">
<div id="subMenu">
    <span class="left">User: <?php echo $_SESSION['GH_UserInfo']; ?></span>
    <span class="right"><a href="editWlk.php">Add a New Walk</a> | <a href="walks.php#r<?php echo $iWalkId; ?>">Walk List</a></span>
</div>
    <h3><?php echo ($iWalkId ? 'Edit' : 'Add New'); ?> Walk</h3>

<?php
    if ( $sPeriodString ) {
        echo '<h4>' . $sPeriodString . '</h4>';
    }
    if ( $iClone ) {
        ?>
        <div class="ui-widget">
            <div class="ui-state-info ui-corner-all" style="padding: 0 .7em;">
                <p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
                    <strong>Info:</strong>This is a copy of another walk.  Please make sure you change the date before you save to avoid adding a duplicate. </p>
            </div>
        </div>
    <?php
    }

    if($sError) {
?>
        <div class="ui-widget">
            <div class="ui-state-error ui-corner-all" style="padding: 0 .7em;">
                <p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span>
                    <strong>Alert: </strong><?php echo $sError; ?></p>
            </div>
        </div>
<?php
    } else {
        if ( !empty($sErrorMsg) ) {
?>
            <div class="ui-widget">
                <div class="ui-state-error ui-corner-all" style="padding: 0 .7em;">
                    <p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span>
                        <strong>Alert: </strong><?php echo $sErrorMsg; ?></p>
                </div>
            </div>
<?php
        }
        if ( !empty($sFb) ) {
?>
            <div class="ui-widget">
                <div class="ui-state-highlight ui-corner-all" style="padding: 0 .7em;">
                    <p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
                        <strong>Info: </strong><?php echo $sFb; ?> </p>
                </div>
            </div>
<?php
        }
?>
        <div id="FormContent">
            <form action="<?php  print $_SERVER['PHP_SELF']; ?>" method="post" class="form" id="Form">
                <input type="hidden" name="Action" id="Action" value="" />
                <input type="hidden" name="wid" value="<?php echo $iWalkId; ?>" />
                <div class="form" id="walkForm">
                     <fieldset>
                        <legend>When &amp; What</legend>
                        <ul>
                            <li><label for="walkTitle">Walk Title<span class="required">*</span></label><input type="text" id="walkTitle" name="walkTitle" value="<?php echo $sWalkTitle; ?>" class="medium" maxlength="45" /> </li>
                            <li><label>Date and Time<span class="required">*</span></label>
                                <span class="dateField" title="Walk Date"><input type="text" name="startDate" id="datePicker" readonly="readonly" size="20" value="<?php echo $sDateStart; ?>" /></span>
                                &nbsp;<label for="timeStartPicker">Start time</label>&nbsp;
                                <span class="dateField" title="Start Time"><input type="text" name="timeStart" id="timeStartPicker" readonly="readonly" size="20" value="<?php echo $sTimeStart; ?>" /></span><?php if($sTimeMsg) echo '<span class="error" id="timeMsg">'.$sTimeMsg.'</span>'; ?>
                            </li>
                            <li><label for="distance">Distance<span class="required">*</span></label><input id="distance" type="text" name="distance" value="<?php echo $iDistance; ?>" class="mini" /> miles <span class="itemInfo">(total distance or AM distance if fig of 8)</span><?php if($sDistMsg) echo ' <span class="error" id="distMsg">'.$sDistMsg.'</span>'; ?></li>
                            <li><label for="isf8">Is walk Fig. of 8?</label><input type="checkbox" id="isf8" name="isf8" value="1" <?php if($bIsFigOf8) echo 'checked';?>> Tick box if figure of 8 to add details</li>
                            <li class="fo8"><label for="timePmPicker">PM start time (Fig of 8)</label>
                                <span class="dateField" title="Pm Start Time"><input type="text" name="timeStartPm" id="timePmPicker" readonly="readonly" size="20" value="<?php echo $sTimeStartPm; ?>" /></span> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <label for="distancePm">PM distance (Fig of 8)</label> <input id="distancePm" type="text" name="distancePm" value="<?php echo $iDistancePm; ?>" class="mini" /> miles
                            </li>
                            <li>
                                <label for="grade">Grade<span class="required">*</span></label>
                                <select id="grade" name="grade">
                                    <?php
                                    if( empty($sGrade) ) {
                                        echo '<option value="">Please select a Grade</option>';
                                    }
                                    foreach ($aGradeTitle as $sCode => $sVal) {
                                        echo '<option value="'.$sCode.'"';
                                        if($sCode == $sGrade)
                                            echo ' selected';
                                        echo '>'.$sCode .': '.$sVal .'</option>';
                                    }
                                    ?>
                                </select>
                                <label for="terrain">Terrain</label><input id="terrain" type="text" name="terrain" value="<?php echo $sTerrain; ?>"  class="medium" maxlength="45" />
                            </li>
                            <li id="canRepeat">
                                <label for="repeatOpt">Repeated (Wed. Only)</label><select id="repeatOpt" name="repeatOpt">
                                    <option id="0">NA</option>
                                    <?php
                                    foreach ($aRepeatTxt as $index => $sVal) {
                                        echo '<option value="'.$index.'"';
                                        if($index == $iRepeat)
                                            echo ' selected';
                                        echo '>'.$sVal .'</option>';
                                    }
                                    ?>
                                </select>
                                <span id="repeat">
                                <span class="dateField" title="Repeat Date"><input type="text" name="repeatDate" id="datePicker2" readonly="readonly" size="20" value="<?php echo $sDateRepeat; ?>" /></span>

                                <?php
                                if ( !$bRepeatAdded )
                                {
                                    echo '<label for="addRepeat" class="AddRepeat">Add Repeat </label> <input type="checkbox" id="addRepeat" name="addRepeat" value="1" class="AddRepeat" />';
                                }
                                ?>

                            </li>
                        </ul>
                    </fieldset>
                    <br />

                    <fieldset>
                        <legend>Where</legend>
                        <span class="formInfo">Please select from the predefined list below if possible!</span>
                        <ul>
                            <li><label for="startPointId">Predefined Start Points</label>
                                <select id="startPointId" name="startPointId">
                                    <option value="0">Other -Please enter details in box below</option>
                                    <?php
                                    foreach ( $aStartPointData as $sIndex => $aData ) {
                                        echo '<option value="'.$sIndex.'"';
                                        if ( $aData['id'] == $iStartId )
                                            echo ' selected';
                                        echo '>'.$aData['startPoint'] .' ('.$aData['gridRef'] . ')</option>' . PHP_EOL;
                                    }
                                    ?>
                                </select>
                            </li>
                        </ul>
                        <?php
                        if(!empty($sStartMsg) && !empty($iStartId) ) {
                            echo '<p class="itemMsg" id="SPMsg">'.$sStartMsg . ' <span id="f_'.$iStartId.'" class="fix">Click here to Fix It!</span></p>';
                        }
                        ?>
                        <ul>
                            <li><label for="startText">Start Point<span class="required">*</span></label>
                                <input type="text" id="startText" name="startText" value="<?php echo $sStartText; ?>" class="long"  maxlength="100" /></li>
                            <li><label for="gridRef">Grid Ref.<span class="required">*</span></label>
                                <input type="text" id="gridRef" name="gridRef" value="<?php echo $sGridRef; ?>" class="short" />&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;

                                <label for="postCode">Post Code</label><input type="text" id="postCode" name="postCode" value="<?php echo $sPostCode; ?>" class="short" maxlength="8" />
                            </li>
                            <li><label>Maps</label>
                                <label for="explorerNew">Explorer OL</label><input type="text" id="explorerNew" name="explorerNew" value="<?php echo $sExplorerNew; ?>" class="map" maxlength="4" />&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
                                <label for="explorer">Explorer X</label><input type="text" id="explorer" name="explorer" value="<?php echo $sExplorer; ?>" class="map" maxlength="4" />&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
                                <label for="landRanger">Landranger LR</label><input type="text" id="landRanger" name="landRanger" value="<?php echo $sLandRanger; ?>" class="map" maxlength="4" />&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
                            </li>
                            <li><label>Additional Maps</label>
                                <label for="explorer2">Explorer X</label><input type="text" id="explorer2" name="explorer2" value="<?php echo $sExplorer2; ?>" class="map" maxlength="4" /> &nbsp;&nbsp;&nbsp;
                                <label for="explorer3">Explorer X</label><input type="text" id="explorer3" name="explorer3" value="<?php echo $sExplorer3; ?>" class="map" maxlength="4" />  &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
                                <label for="landRanger2">Landranger LR</label><input type="text" id="landRanger2" name="landRanger2" value="<?php echo $sLandRanger2; ?>" class="map" maxlength="4" />
                            </li>
                            <li><label for="keyPlaces">Key Places<span class="required">*</span></label>
                                <textarea id="keyPlaces" name="keyPlaces" maxlength="250"><?php echo $sKeyPlaces; ?></textarea>
                            </li>
                            <li class="fo8"><label for="keyPlacesPm">PM Key places (Fig of 8)</label>
                                <textarea id="keyPlacesPm" name="keyPlacesPm" maxlength="250"><?php echo $sKeyPlacesPm; ?></textarea>
                            </li>
                            <li><label for="refreshment">Main Refreshments</label>
                                <select id="refreshment" name="refreshment">
                                    <option value="0">None</option>
                                    <?php
                                    foreach ( $aRefreshmentOpts as $index => $Val ) {
                                        echo '<option value="'.$index.'"';
                                        if ( $index == $iStopType )
                                            echo ' selected';
                                        echo '>'.$Val.'</option>' . PHP_EOL;
                                    }
                                    ?>
                                </select>
                            </li>
                            <li class="hasStop"><label for="stopDetails">Refreshment Stop 1</label>
                                <input type="text" id="stopDetails" name="stopDetails" value="<?php echo $sStopInfo; ?>" class="medium" maxlength="50" /></li>
                            <li class="hasStop"><label for="stopDetails2">Refreshment Stop 2</label>
                                <input type="text" id="stopDetails2" name="stopDetails2" value="<?php echo $sStopInfo2; ?>" class="medium" maxlength="50" /></li>
                        </ul>
                    </fieldset>
                    <br />
                    <fieldset>
                        <legend>Who</legend>
                        <ul>
                            <li>
                                <label for="leader">Leader (main contact)<span class="required">*</span></label>
                                <input type="text" id="leader" name="leader" value="<?php echo $sLeader; ?>" class="small" />&nbsp;&nbsp;&nbsp;
                                <label for="telH">Landline</label><input type="text" id="telH" name="telH" value="<?php echo $sLeaderLandline; ?>" maxlength="12" class="tel" />&nbsp;&nbsp;&nbsp;
                                <label for="telM">Mobile</label><input type="text" id="telM" name="telM" value="<?php echo $sLeaderMobile; ?>" class="tel" maxlength="12" />&nbsp;&nbsp;&nbsp;
                                <label for="mobOpt">Mobile Options</label>
                                <select id="mobOpt" name="mobOpt">
                                    <option value="0">Please select</option>
                                    <?php
                                    foreach ( $aMobileOpts as $index => $Val ) {
                                        echo '<option value="'.$index.'"';
                                        if ( $index == $iMobileOps )
                                            echo ' selected';
                                        echo '>'.$Val.'</option>' . PHP_EOL;
                                    }
                                    ?>
                                </select>
                            </li>
                            <li>
                                <label for="leader">Leader 2</label>
                                <input type="text" id="leader2" name="leader2" value="<?php echo $sLeader2; ?>" class="small" /> &nbsp;&nbsp;&nbsp;<label for="tel2">Phone</label><input type="text" id="tel2" name="tel2" value="<?php echo $sLeader2Tel; ?>" class="tel" maxlength="12" />
                            </li>
                        </ul>

                    </fieldset>
                    <fieldset>
                        <legend>Publish</legend>
                        <ul>
                            <li>
                                <label>Status</label>
                                <label for="status1"><input type="radio" id="status1" name="status" value="1"<?php if($iStatus) echo 'checked'?> > Include in published walk lists</label>
                                <label for="status0"><input type="radio" id="status0" name="status" value="0"<?php if(!$iStatus) echo 'checked'?> > NOT ready to include in published walk lists</label>
                            </li>
                        </ul>
                    </fieldset>
                    <span class="required">*Required Information</span>
                    <ul>
                        <li>
                            <div class="buttons">
                                <button id="save" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" type="button" name="save">Save</button>
                            </div>
                        </li>
                    </ul>

            </form>
        </div>
<?php
    }
?>

</div>
<!-- Pop-up Dialog box  -->
<div id="dialog" title=""></div>
<div id="dialogSaveAnyway" title=""></div>
<!-- Script stuff here -->
<script type="text/javascript" src="common/script/jquery-ui-1.10.3.custom/js/jquery-1.9.1.js"></script>
<script type="text/javascript" src="common/script/jquery-ui-1.10.3.custom/js/jquery-ui-1.10.3.custom.js"></script>
<script type="text/javascript" src="common/script/jquery-ui-1.10.3.custom/js/jquery-ui-timepicker-addon.js"></script>
<script type="text/javascript">

    var startPoints = eval(<?php echo json_encode($aStartPointData); ?>);

    var today = new Date();
    var defaultStartDate = new Date ('<?php echo $sDefaultDateStart; ?>');
    var defaultStartEnd = new Date ('<?php echo $sDefaultDateEnd; ?>');
    var startDate = new Date ('<?php echo !empty($sDateStar) ? $sDateStart : $sDefaultDateStart; ?>');
    var defaultRepeatDate = new Date ('<?php echo $sDefaultDateRepeat; ?>');
    var defaultRepeatDateEnd = new Date ('<?php echo $sDefaultDateRepeatEnd; ?>');
    var repeatDate = new Date ('<?php echo !empty($sDateRepeat) ? $sDateRepeat : $sDefaultDateRepeat; ?>');
    //var onDate1 = new Date ('{$sDateTo}');
    $(document).ready(function()
    {
    //jQuery here
        $('#save').click(function()
        {
            $('#Action').val("Save");
            if ( validateForm() )
            {
               $("#Form").submit();
            }
        });


        $('#isf8').click(function(){
            $(".fo8").toggle(this.checked);
        });

        $('#repeatOpt').change(function() {
            var repOpt = $(this).val();
            if ( repOpt > 1)
            {
                $('#repeat').show();
                if ( repOpt % 2 == 0 )
                {
                    //addRepeat - enable
                    $('.AddRepeat').show();
                    $('#addRepeat').removeAttr("disabled");
                }
                else
                {
                    $('.AddRepeat').hide();
                    $('#addRepeat').attr("disabled", true);
                }
            }
            else
            {
                $('#repeat').hide();
            }
        });

        //Start point stuff
        $('#startPointId').change(function() {
            var startId = $(this).val();
            if ( startId != 0 )
            {
                $('#startText').val(startPoints[startId].startPoint);
                $('#gridRef').val(startPoints[startId].gridRef);
                $('#explorer').val(startPoints[startId].explorer);
                $('#explorerNew').val(startPoints[startId].explorerNew);
                $('#landRanger').val(startPoints[startId].landRanger);
                $('#postCode').val(startPoints[startId].postCode);

            }
            $('#SPMsg').hide();

        });

        $('.fix').click(function() {
            var startId = $(this).attr('id');
            startId = startId.replace('f_', 's');
            $('#startText').val(startPoints[startId].startPoint);
            $('#gridRef').val(startPoints[startId].gridRef);
            $('#explorer').val(startPoints[startId].explorer);
            $('#explorerNew').val(startPoints[startId].explorerNew);
            $('#landRanger').val(startPoints[startId].landRanger);
            $('#postCode').val(startPoints[startId].postCode);
            $('#SPMsg').hide();
        });

        $('#distance').click(function()
        {
            $('#distMsg').hide();
        });

    // Date picker - individual calendars
        $("#datePicker").datepicker({
            buttonImage: 'common/image/calendar.gif',
            buttonImageOnly: true,
            buttonText: 'Select a date for the walk',
            closeText: 'X',
            defaultDate: startDate,
            dateFormat: 'D d M yy',
            firstDay: 1,
            minDate: +1,
            maxDate: defaultStartEnd,
            showButtonPanel: true,
            showOn: 'both',
            onSelect: function( selectedDate ) {
                $('#datePicker').val(selectedDate);
                var date = $(this).datepicker('getDate');
                var dayOfWeek = date.getDay();
                //console.log(dayOfWeek);
                if ( dayOfWeek==3 )
                {
                    $('#canRepeat').show();
                }
                else
                {
                    $('#canRepeat').hide();
                }

            }
        });

        $("#timeStartPicker").timepicker({
            buttonImage: 'common/image/clock.png',
            buttonImageOnly: true,
            buttonText: 'Set walk start time',
            closeText: 'OK',
            controlType: 'select',
            showOn: 'both',
            alwaysSetTime: false,
            showTime: false,
            hourMin: 6,
            hourMax: 20,
            //timeFormat: 'HH:mm',
            timeFormat: "h:mm tt",
            stepMinute: 5,
            timeOnlyTitle: 'Start Time',
            onSelect: function( selectedTime ) {
                $('#timeStartPicker').val(selectedTime);
                $('#timeMsg').hide();
            }
        });

        $("#timePmPicker").timepicker({
            buttonImage: 'common/image/clock.png',
            buttonImageOnly: true,
            buttonText: 'Set pm start time (if fig. of 8)',
            closeText: 'OK',
            controlType: 'select',
            showOn: 'both',
            showTime: false,
            hourMin: 12,
            hourMax: 20,
            //timeFormat: 'HH:mm',
            timeFormat: "h:mm tt",
            stepMinute: 5,
            timeOnlyTitle: 'Pm Start Time',
            onSelect: function( selectedTime ) { $('#timeEndPicker').val(selectedTime); }
        });

        $("#datePicker2").datepicker({
            buttonImage: 'common/image/calendar.gif',
            buttonImageOnly: true,
            buttonText: 'Select The date for the repeat',
            closeText: 'X',
            defaultDate: repeatDate,
            dateFormat: 'D d M yy',
            firstDay: 1,
            minDate: -360,
            maxDate: defaultRepeatDateEnd,
            showButtonPanel: true,
            showOn: 'both',
            beforeShowDay: enableWednesdays,
            onSelect: function( selectedDate ) { $('#datePicker2').val(selectedDate); }
        });


        function enableWednesdays(date) {
            var day = date.getDay();
            return [(day == 3), ''];
        }


        function validateForm()
        {
            var enableSave = true;
            var FocusOn = '';
            var Msg = '';

            var walkTitle = trim($('#walkTitle').val());
            if ( walkTitle == '' )
            {
                Msg += 'The title (brief description) for your walk should not be empty.<br\/>';
                FocusOn = 'walkTitle';
            }
            //Distance
            if ( $('#distance').val() == "" )
            {
                Msg += 'Please enter a distance (in miles) for the walk.<br\/>';
            }
            else
            {
                if (isNaN($('#distance').val()))
                {
                    Msg += 'The distance must be a number.<br\/>';
                    enableSave = false;
                }
                else if ( !(parseFloat($('#distance').val()) > 0))
                {
                    Msg += 'Please enter a distance (in miles) greater than 0 for the walk.<br\/>';
                }
            }

            if( Msg != '' && FocusOn != '')
            {
                FocusOn = 'distance';
            }

            //grade
            if ( $('#grade').val() == '')
            {
                Msg += 'Please select a Grade for the walk.<br\/>';
                FocusOn = 'grade';
            }
            var figOf8 = $('#isf8').is(':checked'); //ie Fig of 8
            if ( figOf8 )
            {
                //need pm distance
                if ( $('#distancePm').val() == "")
                {
                    Msg += 'Please enter a distance (in miles) for the PM part of the walk.<br\/>';
                }
                if ( isNaN($('#distancePm').val()))
                {
                    Msg += 'The PM distance must be a number.<br\/>';
                }
                if ( !(parseFloat($('#distancePm').val()) > 0))
                {
                    Msg += 'The PM distance must be greater than 0.<br\/>';
                }
                if ( Msg != '' && FocusOn != '' )
                {
                    FocusOn = 'distancePm';
                }
            }

            //Repeat should only be Wednesday but hidden if not so should be OK not to check
            if (  trim($('#startText').val()) == '')
            {
                Msg += 'You must enter a start point.  Please select a predefined start from the drop-down.<br\/>';
                FocusOn = 'startText';
            }

            //Repeat
            var isRepeat = $('#repeatOpt').val();
            if ( isRepeat > 1 )
            {
                //check date
                var walkDate = $("#datePicker").datepicker('getDate');
                var repeatDate = $("#datePicker2").datepicker('getDate');

                if ( walkDate > repeatDate)
                {
                    if ( isRepeat % 2 == 0 )
                    {
                        Msg += "You have selected Repeat ON but repeat date is BEFORE this walk's date.<br\/>" ;
                        FocusOn = 'repeatOpt';
                        enableSave = false;
                    }
                }
                else if(walkDate < repeatDate)
                {
                    if(isRepeat % 2 == 1)
                    {
                        Msg += "You have selected Repeat FROM but repeat date is AFTER this walk's date<br\/>" ;
                        FocusOn = 'repeatOpt';
                        enableSave = false;
                    }
                }
                else
                {
                    Msg += 'The Repeat date cannot be the same as this walk\'s date.<br\/>';
                    FocusOn = 'datePicker2';
                    enableSave = false;
                }
            }

            //Grid Ref
            var GridRef =  stripWs($('#gridRef').val());
            if ( GridRef == '' )
            {
                Msg += 'The starting Grid for the walk is missing, use the predefined locations drop-down to auto complete.<br\/>';
            }
            else
            {
                if ( GridRef.length != 8)
                {
                    Msg += 'The Grid reference should be 8 characters long, 2 letters and 2 * 3 numbers<br\/>';
                    enableSave = false;
                }
                if (  !/^[a-zA-Z]+$/.test(GridRef.substring(0, 2)) )
                {
                    Msg += 'The first 2 characters of the Grid ref. can only be letters<br\/>';
                    enableSave = false;
                }
                if (  !/^\d+$/.test(GridRef.substring(2, 6)) )
                {
                    Msg += 'The last 6 characters of the Grid ref. must be numbers<br\/>';
                    enableSave = false;
                }
            }

            if( Msg != ''  && FocusOn != '' )
            {
                FocusOn = 'gridRef';
            }

            //Check map boxes
            var Exp = trim($('#explorer').val());
            var ExpNew = trim($('#explorerNew').val());
            var Exp2 = trim($('#explorer2').val());
            var Exp3 = trim($('#explorer3').val());
            var LR = trim($('#landRanger').val());
            var LR2 = trim($('landRanger2').val());
            if ( Exp != '' )
            {
                if ( !isNumeric(Exp) )
                {
                    Msg += 'The Map reference must be 3 numbers only.<br\/>';
                    FocusOn = 'explorer';
                    enableSave = false;
                }

            }
            if ( ExpNew != '' )
            {
                if ( !isNumeric(ExpNew) )
                {
                    Msg += 'The Map reference must be numbers only.<br\/>';
                    FocusOn = 'explorerNew';
                    enableSave = false;
                }

            }
            if ( Exp2 != '' )
            {
                if ( !isNumeric(Exp2) )
                {
                    Msg += 'The Map reference must be 3 numbers only.<br\/>';
                    FocusOn = 'explorer2';
                    enableSave = false;
                }

            }
            if ( Exp3 != '' )
            {
               if ( !isNumeric(Exp3) )
               {
                   Msg += 'The Map reference must be 3 numbers only.<br\/>';
                   FocusOn = 'explorer3';
                   enableSave = false;
               }

            }
            if ( LR != '' )
            {
                if ( !isNumeric(LR) )
                {
                    Msg += 'The LR Map reference must be 3 numbers only.<br\/>';
                    FocusOn = 'landRanger';
                    enableSave = false;
                }

            }
            if ( LR2 != '' )
            {
                if ( !isNumeric(LR2) )
                {
                    Msg += 'The LR Map reference must be 3 numbers only.<br\/>';
                    FocusOn = 'landRanger2';
                    enableSave = false;
                }
            }

            //PostCode
            var postCode = $('#postCode').val().replace(/\s/g, '');
            if ( postCode != '' )
            {
                if ( !isValidPostcode(postCode) )
                {
                    Msg += 'Please enter a valid post code for the walk.' + postCode + '<br\/>';
                    FocusOn = 'postCode';
                    enableSave = false;
                }
            }

            //keyPlaces
            if ( trim($('#keyPlaces').val()) == '')
            {
                Msg += 'Please enter key places for the walk.<br\/>';
                FocusOn = 'keyPlaces';
            }
            if ( figOf8 &&  trim($('#keyPlacesPm').val()) == '' )
            {
                Msg += 'Please enter key places for the afternoon part of the walk.<br\/>';
                FocusOn = 'keyPlacesPm';
            }
            //leader
            if ( trim($('#leader').val()) == '')
            {
                Msg += 'Please enter a main contact for the walk.<br\/>';
                FocusOn = 'leader';
            }
            //check if have at least 1 phone number
            var telH = $('#telH').val().replace(/\s/g, '') ;
            var telM = $('#telM').val().replace(/\s/g, '') ;
            if ( telH =='' && telM =='' )
            {
                Msg += 'Please enter a landline or mobile number to contact.<br\/>';
                FocusOn = 'telH';
            }
            if ( telH != '' )
            {
                if ( !isNumeric(telH) )
                {
                    Msg += 'Please enter numbers only for the landline number<br\/>';
                    enableSave = false;
                }
                else if( telH.length < 10 )
                {
                    Msg += 'Not enough numbers. Please enter area code followed by number<br\/>';
                    enableSave = false;
                }
                else if( telH.length > 11)
                {
                    Msg += 'Too many numbers. Please enter area code followed by number only<br\/>';
                    enableSave = false;
                }
                FocusOn = 'telH';
            }
            if ( telM != '' )
            {
                if ( !isNumeric(telM) )
                {
                    Msg += 'Please enter numbers only for the mobile number.<br\/>';
                    enableSave = false;
                }
                else if( telM.length < 10)
                {
                    Msg += 'Not enough numbers. Please enter full number<br\/>';
                    enableSave = false;
                }
                else if( telM.length > 11 )
                {
                    Msg += 'Too many numbers. Please enter full number<br\/>';
                    enableSave = false;
                }
                FocusOn = 'telM';
            } else {
                if($('#mobOpt').val() != 0) {
                    Msg += 'Please enter a mobile number for the mobile option or select none.<br\/>';
                    enableSave = false;
                }
            }

            // Display any problems
            if ( Msg )
            {
                var box = 'dialog';
                if ( enableSave )
                {
                    box = 'dialogSaveAnyway';
                }

                $('#'+box).dialog('option', 'title', 'Warning');
                $('#'+box).html('<br />' + Msg + '<br /><br />');
                $('#'+box).dialog('open');
                $('#' + FocusOn).focus();
                return false;
            }
            else
            {
                return true;
            }
        }

        $('#dialog').dialog({
            autoOpen: false,
            width: 600,
            buttons: {
                "Close": function() {
                    $(this).dialog("close");
                }
            }
        });

        $('#dialogSaveAnyway').dialog({
            autoOpen: false,
            modal: true,
            width: 600,
            buttons: {
                'Save Anyway': function() {
                    $("#Form").submit();
                    $(this).dialog('close');
                },
                Cancel: function() {
                    $(this).dialog('close');
                }
            }
        });


    });

    //END of Document Ready : Helper functions below

    function isNumeric( num ){
        return !isNaN(num)
    }

    //This is supposed to be the best one?
    function isNumber( n ) {
        return !isNaN(parseFloat(n)) && isFinite(n);
    }

    function stripWs( sString )
    {
        return sString.replace(/\s/g, '') ; //seems to work
    }
    //strips leading and trailing whitespace ( > ie8 only
    function trim( sString )
    {
        if ( sString != null && sString != '' )
        {
            return sString.trim();
        }
        else
        {
            return '';
        }
    }

    //unescape back characters before displaying in a form field
    function unEscapeChars( sString )
    {
        /*
         & --> &amp;  //Must be done first!
         < --> &lt;
         > --> &gt;
         " --> &quot;
         ' --> &#x27;  //&apos; is not recommended
         / --> &#x2F;  //forward slash is included as it helps end an HTML entity
         */

        sString = sString.replace(/&amp;/g,"&");
        sString = sString.replace(/&lt;/g,"<");
        sString = sString.replace(/&gt;/g,">");

        sString = sString.replace(/&quot;/g,'"');
        sString = sString.replace(/&#039;/g,"'");

        return sString;
    }

    function EscapeChars( sString )
    {
        sString = sString.replace(/</g,"&lt;");
        sString = sString.replace(/>/g,"&gt;");

        return sString;
    }

    // strip start and end tags from string
    function stripStartEndTag( sString )
    {
        return sString.replace(/[<>]/g, "")
    }

    function stripNonAlphaNum ( sString )
    {
        return sString.replace(/[^A-Za-z0-9.,:\/'_-]/g, " ");
    }

    function isValidPostcode(p) {
        var regPostcode = /^[A-Z]{1,2}[0-9]{1,2}[A-Z]{0,1} ?[0-9][A-Z]{2}$/i;

        return regPostcode.test(p);
    }

    // Function to check letters and numbers.
    function alphanumeric(inputtxt) {
        var letterNumber = /^[0-9a-zA-Z]+$/;
        if (inputtxt.value.match(letterNumber)) {
            return true;
        }
    }

</script>

</body>
</html>
