<?php
/**
 * @author  Kirsten H Osa
 */
require_once('config_web.php');
if ( !$_SESSION['GH_LoggedIn'] ) {
    //redirect to login page
    header('Location: index.php');
    exit(0);
}
$pageOn="walk";
$title = GROUP_NAME . ' Walks List';
$sErrorString = '';
$sFb = '';
if ( !empty($_POST['Action']) ) {

    $iWlkId = intval($_POST['WlkId']);

    if( !empty($iWlkId)) {
        if( $_POST['Action'] == 'Delete' ) {
            $sQuery = "DELETE FROM walksProg WHERE id=$iWlkId";
            $rResult = runQuery($sQuery, DB_WRITE);
            if ( !$rResult ) {
                $sErrorString = 'Walk could not be deleted ' . $sQuery;
            } else {
                $sFb = 'Walk deleted';
            }
        }
        elseif ( $_POST['Action'] == 'publish' ) {
            $sQuery = "UPDATE walksProg SET status=1 WHERE id=$iWlkId";
            $rResult = runQuery($sQuery, DB_WRITE);
            if ( !$rResult ) {
                $sErrorString = 'Walk could not be set to publish ' . $sQuery;
            } else {
                $sFb = 'Walk included in published list.';
            }
        }
    }
}

# Display / word format
$bWord = false;
$bExcel = false;
if ( !empty($_REQUEST['wd']) ) {
    $bWord = true;
}
elseif ( !empty($_REQUEST['csv']) ) {
    $bExcel = true;
    //$sHeaderRow = 'Date,Brief description (include location),Rough location,Start gridref,Nearest Town,Start time,Grade,Longer description,Distance (miles),Themed Walks,Family walk?,Dogs Welcome?,Car free?,Accessible walk?,Contact name,Contact phone,Contact email, Start Location, Pm start, Pm distance, Pm description, Repeated' . PHP_EOL;

    $sHeaderRow = 'Date*,Title*,Description*,Linear or Circular*,Starting location ,Starting postcode ,Starting gridref ,Starting location details,Show exact starting point?*,Start time*,Meeting location,Meeting postcode,Meeting gridref,Meeting location details,Show exact meeting point?, Meeting time,Finishing location,Finishing postcode,Finishing gridref,Finishing location details,Restriction*,Difficulty*,Local walk grade,Distance km ,Distance miles ,Est finish time,Contact id,Contact first name,Contact surname,Contact display name,Contact email,Contact telephone 1,Contact telephone 2,Is walk leader?,Walk leader name,Festivals,Strands,Additional details,Pace,Ascent metres,Ascent feet,Child friendly?,Dog friendly?,No car needed?,Pushchair friendly?,Wheelchair friendly?,Link route id,Link walk ids,Link event ids,Invite group code' . PHP_EOL;

    $sDataRow = '';
}


$iToday =  mktime(0,0,0,date('m'), date('d'), date('Y'));
$aDates = getNextPeriod();  // need to have predefined for csv & program to match period wanted

$iNextStart = $aDates['start'];
$iNextEnd = $aDates['end'];
$sStartDb = date('Y-m-d', $iNextStart);
$sEndDb = date('Y-m-d', $iNextEnd);
$sStart = date('D j M, Y', $iNextStart);
$sEnd =  date('D j M, Y', $iNextEnd);

$iCurrentEnd = ($iNextStart - $iOneDay);
$sCurrentEnd = date('D j M, Y', $iCurrentEnd);
$sCurrentStartDb = date('Y-m-d', time());
$sCurrentEndDb = date('Y-m-d', $iCurrentEnd);

$iList = intval($_REQUEST['t']); //1=current; 2=next programme - 0 = all
switch ($iList) {
    case 1:
        $sWhere = "WHERE walkDate BETWEEN '$sCurrentStartDb' AND '$sCurrentEndDb' " ;
        $sOrderBy = 'ORDER BY TS';
        $sWalkPeriod = "Current Walks up to $sCurrentEnd)";
        $sExtraInfo = 'Below is a list of current walks.';
        break;
    case 2:
        $sOrderBy = 'ORDER BY TS';
        $sWhere = "WHERE walkDate BETWEEN  '$sStartDb' AND '$sEndDb' ";
        $sWalkPeriod = "Walks for Next Walk Program covering period $sStart - $sEnd";
        $sExtraInfo = 'Below is a list of added walks for the new program.';
        break;
    default:
        $sWhere = '';
        $sOrderBy = 'ORDER BY TS DESC LIMIT 500';
        $sWalkPeriod = 'All Walks';
        //$sExtraInfo = 'Below is a list of all walks entered.';
}


$aDisplayData = array();
$bAllChecked = true;
$aErrorData = array();

$sQuery = "SELECT id, UNIX_TIMESTAMP(walkDate) as TS, startTime, startTimePm, walkTitle, walkGrade, terrain, distance, distancePm, startPointId, startPoint, gridRef, explorer, explorerNew, explorer2, explorer3, landRanger, landRanger2, postCode, keyLocations, keyLocationsPm, leader, leaderLandline, leaderMobile, mobileOpt, leader2, leader2Tel, refreshmentStopType, refreshmentStopDetails, refreshmentStop2Details, repeatType, UNIX_TIMESTAMP(repeatDate) as RTS, status
 FROM walksProg $sWhere $sOrderBy";
$rResult = runQuery($sQuery);
//echo $sQuery;
if($rResult) {
    $iCount = 0;
    while ( $aRow = $rResult->fetch_array(M_DB_BOTH) ) {
        if ( $bExcel ) {
            if( ! $aRow['status'] ) {
                continue; //only publish where status=1  but show all in list
            }

            /* Define variables */
            $Date= ''; $Title=''; $Description=''; $Linear='Circular'; //Mandatory  date as dd/mm/yyyy
            $Starting_location=''; $Starting_postcode=''; $Starting_gridref=''; // Mandatory but only one of these
            $Starting_location_details = ''; //optional
            $Show_exact_starting_point='Yes'; $Start_time=''; //Mandatory - time hh:mm use 24 hour format
            $Meeting_location=''; $Meeting_postcode=''; $Meeting_gridref=''; //Optionla but only one
            $Meeting_location_details=''; $Show_exact_meeting_point=''; $Meeting_time=''; //Optional
            $Finishing_location=''; $Finishing_postcode=''; $Finishing_gridref=''; //Optionla but only one
            $Finishing_location_details=''; //Optional
            $Restriction='Public'; $Difficulty=''; //Mandatory
            $Local_walk_grade=''; //Optional - use terrain
            $Distance_km=''; $Distance_miles=''; // Mandatory but only one of these
            $Est_finish_time=''; //Optional
            //Remainder is optional
            $Contact_id=''; //Only use if contact is in Ramblers database - Not for us
            $Contact_first_name=''; $Contact_surname=''; $Contact_display_name='';
            $Contact_email=''; //Removed by request from Jenny //'walks@godalmingandhaslemereramblers.org.uk';
            $Contact_telephone_1=''; $Contact_telephone_2=''; $Is_walk_leader='Yes'; // No if contact not leader  otherwise leave blank
            $Walk_leader_name='';
            $Festivals=''; $Strands=''; $Additional_details=''; $Pace='';
            $Ascent_metres='';     $Ascent_feet=''; //Optional but only one
            $Child_friendly=''; $Dog_friendly=''; $No_car_needed=''; $Pushchair_friendly=''; $Wheelchair_friendly=''; $Link_route_id='';
            $Link_walk_ids=''; $Link_event_ids=''; $Invite_group_code='';

            // Update variables
            $Date = date('d/m/y', $aRow['TS']);
            $Title = !empty($aRow['walkTitle']) ? $aRow['walkTitle'] : substr($aRow['startPoint'], 0, 150);
            $Description = str_replace(array("\r", "\n"), ' ', ($aRow['keyLocations']));
            $Starting_gridref = $aRow['gridRef']; //formatGridRef($aRow['gridRef']);
            $Starting_location_details =  $aRow['startPoint'];
            $Start_time = substr($aRow['startTime'], 0, 5); // date('H:m',strtotime($aRow['startTime']));
            $Difficulty = $aGradeTitle[$aRow['walkGrade']];
            $Local_walk_grade = $aRow['terrain'];

            $Contact_first_name = $aRow['leader'];
            $Contact_display_name = $aRow['leader'];
            if ( !empty($aRow['leaderLandline']) ) {
                $Contact_telephone_1 = format_telfax2($aRow['leaderLandline']);
            }
            if ( !empty($aRow['leaderMobile']) ) {
                $Contact_telephone_2 = format_telfax2($aRow['leaderMobile']);
            }
            if ( !empty($aRow['leader2']) ) {
                $Contact_display_name .= ' & ' . $aRow['leader2'];
            }

            $sPostCode = formatPostCode($aRow['postCode']);
            if ( !empty($sPostCode) ) {
                $Starting_location_details .= ' Post Code: ' . $sPostCode;
            }


            if ( $aRow['startTimePm'] != null && $aRow['startTimePm'] != '00:00:00' ) {
                $iPmTime = substr($aRow['startTimePm'], 0, 5); //date("g:ia", strtotime($aRow['startTimePm']));
                $sPmKeyPlaces = str_replace(array("\r", "\n"), ' ', ($aRow['keyLocationsPm']));
                $Description .= ' PM: ' .$sPmKeyPlaces;

                $Distance_miles = floor(($aRow['distance'] + $aRow['distancePm']) *2/2);
                $Additional_details .= 'Fig. of 8 PM Start: ' . $iPmTime . ', PM distance: ' .$aRow['distancePm'] . 'miles.' ;
            } else {
                $Distance_miles = floor($aRow['distance'] * 2)/2; //If pm need to add
            }


            $sRefreshments = '';
            if ( !empty($aRow['refreshmentStopType']) ) {
                $sRefreshments = $aRefreshmentOpts[$aRow['refreshmentStopType']] . ' ' . $aRow['refreshmentStopDetails'];
            }

            if( !empty($aRow['refreshmentStop2Details'])) {
                $sRefreshments .= ' PM: ' . $aRow['refreshmentStop2Details'];
            }
            $sRefreshments = str_replace(array("\r", "\n"), ' ', $sRefreshments);

            if ( !empty($sRefreshments) ) {
                $Additional_details .= ' Refreshments: ' . $sRefreshments;
            }

            $sDataRow .= $Date . ',"' .$Title .'","'. $Description.'",'.$Linear.',"'.
                $Starting_location.'","'.$Starting_postcode.'",'. $Starting_gridref .',"'. $Starting_location_details .'",'.
                $Show_exact_starting_point .','. $Start_time .',"'.
                $Meeting_location .'","'. $Meeting_postcode .'",'. $Meeting_gridref .',"'.
                $Meeting_location_details .'",'. $Show_exact_meeting_point .','. $Meeting_time .',"'.
                $Finishing_location .'","'. $Finishing_postcode .'",'. $Finishing_gridref .',"'. $Finishing_location_details .'",'.
                $Restriction .','. $Difficulty .',"' . $Local_walk_grade.'",'. $Distance_km.','. $Distance_miles .','. $Est_finish_time .','.
                $Contact_id .',"'. $Contact_first_name .'","'. $Contact_surname .'","'. $Contact_display_name .'","'.
                $Contact_email.'","'.$Contact_telephone_1 .'","'. $Contact_telephone_2 .'",'. $Is_walk_leader .',"'. $Walk_leader_name .'",'.
                $Festivals .','. $Strands .',"'. $Additional_details .'",'. $Pace .','. $Ascent_metres .','. $Ascent_feet.','.
                $Child_friendly .','. $Dog_friendly .','. $No_car_needed .','. $Pushchair_friendly .','. $Wheelchair_friendly .','.
                $Link_route_id.','. $Link_walk_ids.','.$Link_event_ids.','.$Invite_group_code . PHP_EOL;

            continue;
        }
        $aWlkErrors = array();
        $iHasError = 0;
        if ( $aRow['status'] == 0 ) {
            $bAllChecked = false;
        }
        $iWalkId = $aRow['id'];
        $iCount++;
        //$countCol = '<td style="white-space:nowrap;"><a href="">' . $iCount . '</a></td>' . PHP_EOL; //not now used
        $aDisplayData[$iWalkId]['NumberCol'] = $iCount; //$countCol;
        $aDisplayData[$iWalkId]['Status'] = $aRow['status'];

        if( empty($aRow['walkTitle']) ) {
            $aWlkErrors[] = 'No Walk Title (req. for Walk Finder)';
            $iHasError = 1;
        }

        $sCol1 = ''; //date/time etc = pm start if relevant / terrain
        $sCol2 = '';//fig of 8  / Distance / fog of 8 distance
        $sCol3 = ''; // contact / start info / refreshment etc  / repeat info
        $sCol4 = ''; // grid ref etc


        #Col 1 data
        $iTs = $aRow['TS'];
        $bCanEdit = $iTs >= $iToday ? true : false;
        $sDate = date('D j M Y', $iTs);
        $sTime = date("g:ia", strtotime($aRow['startTime']));

        $iStartTimeNum = (int) date('H',strtotime($aRow['startTime']));
        if ( $iStartTimeNum < 6 ) {
            $aWlkErrors[] =  'Start time is too early.';
            $iCanVerify = 0;
        }

        $sCol1  .= $sDate .'<br />' . $sTime . '<br />';

        $iDistance = (float)$aRow['distance'];  // should not be empty?
        if ( empty($iDistance) ) {
            //$iDistance = (float)$aRow['distancePm']; //Not sure - Qn. should not be used unless fig of 8??
            $aWlkErrors[] = 'No distance';
            $iHasError = 1;
        }

        //Fig of 8  - should be null if no pm
        $sPmKeyPlaces = '';
        $iPmDistance = 0;
        if ( $aRow['startTimePm'] != null && $aRow['startTimePm'] != '00:00:00' ) {
            $sPmTime = date("g:ia", strtotime($aRow['startTimePm']));
            $sCol1 .= $sPmTime;

            $iStartTimeNumPm = (int) date('H',strtotime($aRow['startTimePm']));
            if ( $iStartTimeNumPm < $iStartTimeNum ) {
                $aWlkErrors[] = 'The PM start time is before the AM start time.';
                $iHasError = 1;
            }
            elseif( $iStartTimeNumPm < 12 ) {
                $aWlkErrors[] = 'The PM start time is too early.';
                $iHasError = 1;
            }

            $iPmDistance = (float)$aRow['distancePm'];
            if ( empty($iPmDistance) ) {
                $aWlkErrors[] = 'No pm distance';
            }

            $sPmKeyPlaces = htmlspecialchars($aRow['keyLocationsPm']);
            $sCol2 .= '<b>Fig of 8</b>';
        }

        $sCol1 .= '<br />';
        $sCol2 .= '<br />';

        $sCol2 .= $iDistance . ' miles<br/>';
        if($iPmDistance) {
            $sCol2 .= $iPmDistance . ' miles<br/>';
        }
        //Contact etc
        $sGrade = $aRow['walkGrade']; // $aGradeCode[$aRow['grade']];
        $sTerrain = !empty($aRow['terrain']) ? $aRow['terrain'] : $aGradeTitle[$sGrade];

        $sCol1 .=  $sTerrain; // leave only terrain

        //Leader etc
        $aContactName = $aRow['leader'] . ' ';
        $sContactNo = !empty($aRow['leaderLandline']) ? format_telfax2($aRow['leaderLandline']) . ', ' : '';
        $aMobileOps = $aMobileOpts[$aRow['mobileOpt']];
        $sContactNo .= !empty($aRow['leaderMobile']) ? 'M ' . format_telfax2($aRow['leaderMobile']) . ' ' .$aMobileOps : '';

        if( empty($aRow['leader']) ) {
            $aWlkErrors[] = 'No leader (contact)';
            $iHasError = 1;
        }
        if ( empty($aRow['leaderLandline']) && empty($aRow['leaderMobile']) ) {
            $aWlkErrors[] = 'No contact cumber';
            $iHasError = 1;
        }
        //2nd leader  - need to remove grid ref from column
        if ( !empty($aRow['leader2']) ) {
            $aContactName .= ' & ' . $aRow['leader2']. ' ';
            if ( !empty($aRow['leader2Tel']) ) {
                $sContactNo .= ' or ' . format_telfax2($aRow['leader2Tel']);
            }
        }

        $iStartId = $aRow['startPointId'];
        $sStart = htmlspecialchars($aRow['startPoint']);

        $sCol3 .= '<b>' . $aContactName . '</b>' . $sContactNo . ' <b>'. $sStart . '</b><br />';

        $sKeyPlaces = htmlspecialchars($aRow['keyLocations']); // may have problems with £ sign from db to php looks OK and is supposed to be  utf8
        if ( empty($sKeyPlaces) ) {
            $aWlkErrors[] = 'No key places';
        }
        if( !empty($sPmKeyPlaces)) {
            $sKeyPlaces .= ' - ' .$sPmKeyPlaces;
        }
        $sCol3 .= $sKeyPlaces . '<br />';

        $sRefreshments = '';
        if ( !empty($aRow['refreshmentStopType']) ) {
            $sRefreshments = $aRefreshmentOpts[$aRow['refreshmentStopType']] . ' ' . $aRow['refreshmentStopDetails'];
        }

        if( !empty($aRow['refreshmentStop2Details'])) {
            $sRefreshments .= ' ' . $aRow['refreshmentStop2Details'];
        }
        $sCol3 .= $sRefreshments . '<br />'; //want to add more terrain info here??

        # repeat info  / now at end of col 3
        $iRepeat = $aRow['repeatType'];
        if( !empty($iRepeat) ) {
            if ( $iRepeat > REPEAT_NOT ) {
                $sRepeatTxt = $aRepeatTxt[$iRepeat];
                $iRTs = $aRow['RTS'];
                $sRepeatDate = date('D j M', $iRTs);
                $sCol3 .= '<span style="float:right;">'.$sRepeatTxt .' ' . $sRepeatDate .'</span>';

                if ( $iRepeat % 2 == 0 ) {
                    if ( !isRepeatAdded(date('Y-m-d',$iRTs), $iDistance, $aRow['gridRef']) ) {
                        $aWlkErrors[] = 'Repeat does not appear to be added';
                    } else {
                        if ( $iRTs < $iTs) {
                            $aWlkErrors[] = 'Repeat ON but repeat date is BEFORE';
                            $iHasError = 1;
                        }
                    }
                } else {
                    //check repeat date is before start
                    if ( $iRTs > $iTs) {
                        $aWlkErrors[] = 'Repeat FROM but repeat date is AFTER';
                        $iHasError = 1;
                    }
                }
            }
        }

        // grid refs / maps etc
        $gridRef = str_replace(' ','', $aRow['gridRef']);
        if ( !empty($gridRef) ) {
            $sCode =  strtoupper(substr($gridRef, 0, 2));
            $iNum = substr($gridRef, 2, 6);
            if ( ctype_digit($iNum) ) {
                $gridRef = $sCode . $iNum;
                if(strlen($gridRef) != 8) {
                    $aWlkErrors[] = 'The Grid Ref has too few or too many characters.';
                    $iHasError = 1;
                }
            } else {
                $aWlkErrors[] =  'Invalid Grid Ref format';
                $iHasError = 1;
            }
        } else {
            $aWlkErrors[] =  'Empty Grid Ref';
            $iHasError = 1;
        }

        $sGridRef = formatGridRef($aRow['gridRef']);
        $sCol4 .= $sGridRef . '<br />';
        $mapOld = intval($aRow['explorer']);
        $mapNew = intval($aRow['explorerNew']);

        if ( !empty($mapOld) || !empty($mapNew) ) {
            $sOSMap = !empty($mapNew)? 'OL'.$mapNew : '';
            $sOSMap .= !empty($mapOld) ? (!empty($mapNew) ? ' / ' : '') . 'X' . $mapOld : '';
            if ( !empty($aRow['explorer2']) ) {
                $sOSMap .= '/' . $aRow['explorer2'];
                if (!empty($aRow['explorer3']) ) {
                    $sOSMap .= '/' . $aRow['explorer3'];
                }
            }
            $sCol4 .= $sOSMap . '<br />';
        }

        if ( !empty($aRow['landRanger']) ) {
            $sLRMap = 'LR ' . $aRow['landRanger'];
            if ( !empty($aRow['landRanger2']) ) {
                $sLRMap .= '/' . $aRow['landRanger2'];
            }
            $sCol4 .= $sLRMap . '<br />';
        }
        if ( !checkNumber($aRow['explorer'], 3)) {
            $aWlkErrors[] = 'Explorer map is empty or invalid';
            $iHasError = 1;
        }
        if ( !checkNumber($aRow['landRanger'], 3)) {
            $aWlkErrors[] = 'LandRanger map is empty or invalid';
            $iHasError = 1;
        }

        $sPostCode = '';
        if ( $aRow['postCode'] ) {
            $sPostCode = formatBritishPostcode($aRow['postCode']);
            if ( !$sPostCode ) {
                $aWlkErrors[] = 'Invalid post code';
                $iHasError = 1;
                $sPostCode = $aRow['postCode'];
            }
        } else {
            $aWlkErrors[] = 'Empty post code';
            $iHasError = 1;
        }

        $sCol4 .= $sPostCode;

        $aDisplayData[$iWalkId]['Col1'] = $sCol1;
        $aDisplayData[$iWalkId]['Col2'] = $sCol2;
        $aDisplayData[$iWalkId]['Col3'] = $sCol3;
        $aDisplayData[$iWalkId]['Col4'] = $sCol4;
        $aDisplayData[$iWalkId]['hasError'] = $iHasError;
        $aDisplayData[$iWalkId]['canEdit'] = $bCanEdit;
        $aErrorData['er'.$iWalkId] = $aWlkErrors;
    }
    if ( $bExcel ) {
        header('Content-Type: text/csv; charset=utf-8');
        header( 'Content-Disposition: attachment;filename=WalkFinder_' .date("Y-m-d").'.csv');
        echo $sHeaderRow . $sDataRow;
        exit();
    }

} else {
	$sError =  "Query failed";
}

if ( $_REQUEST['wd'] == 1 ) {

    header("Content-type: application/vnd.ms-word");
    header("Content-Disposition: attachment;Filename=document_name.doc");

    echo '<html>' . PHP_EOL;
    echo '<meta http-equiv="Content-Type" content="text/html; charset=Windows-1252">' . PHP_EOL;
    echo '<body>' . PHP_EOL;
    echo '<p>Walk list for period March 1st to June 30th 2014.</p>' . PHP_EOL;
    echo '<table border="1" cellpadding="5" cellspacing="0" style="font-family:arial;">' . PHP_EOL;
    echo '<tr><th>Date/Time</th><th>Distance</th><th>Leader, contact no, start point, brief description</th><th>OS Grid Ref</th></tr>' . PHP_EOL;
    foreach (  $aDisplayData as $iWkId => $aCol ) {
        $sCol1 = $aCol['Col1'];
        $sCol2 = $aCol['Col2'];
        $sCol3 = $aCol['Col3'];
        $sCol4 = $aCol['Col4'];

$sRow = <<<ROW
<tr>
    <td>$sCol1</td>
    <td>$sCol2</td>
    <td>$sCol3</td>
    <td>$sCol4</td>
</tr>
ROW;

        echo $sRow . PHP_EOL;
    }
    echo '</table>' . PHP_EOL;
    echo '</body></html>';
    exit();
}

?>
<!doctype html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <title><?php echo $title;?></title>
    <link href="common/script/jquery-ui-1.10.3.custom/css/custom-theme/jquery-ui-1.10.3.custom.css" rel="stylesheet">
    <link href="common/css/main.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/jquery.dataTables.css">
    <style type="text/css">

        #dataTable h3 {font-size: 140%; margin-bottom: 5px; margin-top: 5px;}
        #Walks { font-size: 100%;}
        #Walks td {vertical-align: top;}
        #Walks_paginate a.ui-button {margin-right: 0.3em;}
        .warning { font-style: italic; color: red;}
        .er_walk, .del_walk, .ck_walk {cursor: pointer;}

    </style>
</head>
<body id="gh-body">
<?php
    include_once('common/top.php');
?>
    <div id="main-cont">
<?php
    if($sError) {
?>
        <div class="ui-widget">
            <div class="ui-state-error ui-corner-all" style="padding: 0 .7em;">
                <p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span>
                    <strong>Alert:</strong><?php echo $sError; ?></p>
            </div>
        </div>
<?php
    } else {
        //print '<pre>'.print_r($aColumns, true) . "</pre>\n";
?>
        <div id="subMenu">
            <span class="left">User: <?php echo $_SESSION['GH_UserInfo']; ?></span>
            <span class="right">
            <?php
            if ( $isSuperUser && $iList >1 && !empty($aDisplayData) ) {
            ?>
                <a href="walks.php?wd=1&t=<?php echo $iList; ?>"><img src="common/image/icon_word.png" alt="" /> Create Word Doc</a> |
                <a href="walks.php?csv=1&t=<?php echo $iList; ?>"><img src="common/image/icon_excel.gif" alt="" /> Create Walk Finder CSV</a>

            <?php
            }
            ?>
            </span>
        </div>
        <div class="clearbreak"></div>
        <?php
            if ( !empty($sErrorString) ) {
        ?>
                <br />
                <div class="ui-widget">
                    <div class="ui-state-error ui-corner-all" style="padding: 0 .7em;">
                        <p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span>
                            <strong>Alert:</strong><?php echo $sErrorString; ?></p>
                    </div>
                </div>
                <br />
        <?php
            }
        if ( !empty($sFb) ) {
            ?>
            <br />
            <div class="ui-widget">
                <div class="ui-state-highlight ui-corner-all" style="padding: 0 .7em;">
                    <p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
                        <strong>Info:</strong><?php echo $sFb; ?></p>
                </div>
            </div>
            <br />
            <?php
        }
        ?>

        <form action="walks.php" method="post" id="Form">
            <input type="hidden" id="Action" name="Action" value="" />
            <input type="hidden" id="WlkId" name="WlkId" value="0" />
            <input type="hidden" id="ListType" name="t" value="<?php echo $iList; ?>" />
            <div id="dataTable">
            <?php
                echo "<h3>$sWalkPeriod</h3>";
                if ( $iList && !$bAllChecked ) {
                    echo '<span class="warning"><img src="common/image/warning_icon.png" alt="" /> Note: Some Walks will not be published!</span><br />';
                }

                echo '<span>Show: &nbsp;&nbsp; ';
                echo $iList != 2 ? '<a href="walks.php?t=2"><img src="common/image/database_red.png" alt="" /> Next Walk Program</a> &nbsp;&nbsp; ' : '';
                echo $iList != 1 ? '<a href="walks.php?t=1"><img src="common/image/database_green.png" alt="" /> Current Walks</a> &nbsp;&nbsp;' : '';
                echo !empty($iList) ? '<a href="walks.php"><img src="common/image/database_blue.png" alt="" /> All Walks</a>' : '';
                echo '</span>';
            ?>

                <p><span class="left"><?php echo $sExtraInfo; ?></span>
                <span class="right"><a href="editWlk.php"><img src="common/image/add.png" alt="" /> Add a New Walk</a></span></p>
                <table class="display" id="Walks" summary="Walk list">
                    <thead>
                        <tr>
                            <th># </th>
                            <th class="no_sort">Date/Time</th>
                            <th class="no_sort" width="50">Distance</th>
                            <th class="no_sort align_left">Leader, contact no, start point, brief description</th>
                            <th class="no_sort" nowrap>OS Grid Ref</th>
                            <th class="no_sort">Action</th>
                        </tr>
                    </thead>
                    <tbody>
            <?php
            $iCount = 0;
            foreach (  $aDisplayData as $iWkId => $aCol ) {
                $countCol = $aCol['NumberCol'];
                $sCol1 = $aCol['Col1'];
                $sCol2 = $aCol['Col2'];
                $sCol3 = $aCol['Col3'];
                $sCol4 = $aCol['Col4'];

                $aErrorInfo = $aCol['error'];
                $iHasError = $aCol['hasError'];
                $iCanEdit = $aCol['canEdit'];

                echo '<tr id="r'.$iWkId.'">'. PHP_EOL;
                echo '<td class="align_right">'.$countCol.'</td>' . PHP_EOL;
                echo '<td>'.$sCol1.'</td>' . PHP_EOL;
                echo '<td class="align_right">'.$sCol2.'</td>' . PHP_EOL;
                echo '<td>'.$sCol3.'</td>' . PHP_EOL;
                echo '<td class="align_right">'.$sCol4.'</td>' . PHP_EOL;
                echo '<td nowrap>';
                if ( $iCanEdit ) {
                    echo '<a href="editWlk.php?wid='.$iWkId.'" title="Edit walks information"><img src="common/image/pencil_icon.png" alt="" /></a> |
    <a href="editWlk.php?wid='.$iWkId.'&clone=1" title="Create new walk based on this one."><img src="common/image/page_copy.png" alt="" /></a>' . PHP_EOL;
                    //only allow delete if not checked?
                    echo ' |  <img id="Del_'.$iWkId.'" class="del_walk" src="common/image/bin_empty.png" alt="" title="Delete Walk" /> | '. PHP_EOL;

                    if ( $iHasError ) {
                        echo ' <img id="er'.$iWkId.'" class="er_walk" src="common/image/warning_icon.png" alt="" title="Click to see issues with the input for this walk." /> |'. PHP_EOL;;
                    } else {
                        echo ' <img src="common/image/ok_icon.png" alt=""  title="Walk has no errors"/> |';
                    }

                    if ( $aCol['Status'] ) {
                        echo ' <img src="common/image/tick.png" alt=""  title="Walk will be published"/>';
                    } else {
                        if ( $isSuperUser ) {
                            echo ' <img id="Ck_'.$iWkId.'" class="ck_walk" src="common/image/delete.png" alt="" title="Walk will not be published. Click to Publish Walk" />';
                        } else {
                            echo ' <img src="common/image/delete.png" alt=""  title="Walk will NOT be published"/>';
                        }
                    }
                } else {
                    echo '<a href="editWlk.php?wid='.$iWkId.'&clone=1" title="Create new walk based on this one."><img src="common/image/page_copy.png" alt="" /> Copy Walk</a>' . PHP_EOL;
                }
                echo '</td>' . PHP_EOL;
                echo '</tr>'. PHP_EOL;
            }
            ?>
                </tbody>
            </table>

            </div>
        </form>
<?php
    }
?>

    </div>
</div>
<div id="dialogInfo" title="Walk's information problem" style="display:none;">
    <p>The following problems were found:</p>
    <div id="erTxt">&nbsp;</div>
</div>
<div id="dialogPublish" title="Publish Walk" style="display:none;">
    <p>Are you sure you want to enable this walk to be published?</p>
</div>
<div id="dialogDelete" title="Delete" style="display:none;">
    <p>Are you sure you want to delete this Walk ?</p>
</div>
<!-- Script stuff here -->
<script src="common/script/jquery-ui-1.10.3.custom/js/jquery-1.9.1.js"></script>
<script src="common/script/jquery-ui-1.10.3.custom/js/jquery-ui-1.10.3.custom.js"></script>
<script type="text/javascript" charset="utf8" src="http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/jquery.dataTables.min.js"></script>
<script type="text/javascript">
   var errorMsgs = eval(<?php echo json_encode($aErrorData); ?>);

    $(document).ready(function()
    {
    //jQuery here
        var dontSort = [];
        $('#Walks thead th').each( function (){
            if ( $(this).hasClass( 'no_sort' )) {
                dontSort.push( { "bSortable": false } );
            } else {
                dontSort.push( null );
            }
        });

        $('#Walks').dataTable({
            "bJQueryUI": true,
            "aaSorting": [[ 0, "asc" ]],
            "iDisplayLength": 100,
            "sPaginationType": "full_numbers",
            "aoColumns": dontSort
        });

        //$('.del_walk').live('click', function()
        $('#Walks').on('click', '.del_walk', function()
        {
            $('#Action').val(0); //reset to make sure don't delete something else
            var iWalkId = $(this).attr('id');
            iWalkId = iWalkId.replace('Del_', '');
            $('#WlkId').val(iWalkId);

            $('#dialogDelete').dialog('open');

        });

        //$('.ck_walk').live('click', function()
        $('#Walks').on('click', '.ck_walk', function()
        {
            $('#Action').val(0);
            var iWalkId = $(this).attr('id');
            iWalkId = iWalkId.replace('Ck_', '');
            $('#WlkId').val(iWalkId);

            $('#dialogPublish').dialog('open');

        });

        //$('.er_walk').live('click', function()
        $('#Walks').on('click', '.er_walk', function()
        {

            var sWalkId = $(this).attr('id');
            var aErMsg = errorMsgs[sWalkId];
            //console.log(aErMsg);
            var msgCont = '<ul>';
            $.each( aErMsg, function( index, value ){
                msgCont += '<li>' + value + '<\/li>';
                console.log( index + ' : ' + value);
            });
            msgCont += '<\/ul>';

            $('#erTxt').html(msgCont);
            $('#dialogInfo').dialog('open');

        });

        $("#dialogDelete").dialog({
            autoOpen: false,
            bgiframe: true,
            resizable: false,
            /*position: [350,100],*/
            width: 450,
            modal: true,
            overlay:
            {
                backgroundColor: '#000',
                opacity: 0.5
            },
            buttons: {
                'Delete': function()
                {
                    $('#Action').val("Delete");
                    $("#Form").submit();
                    $(this).dialog('close');
                },
                Cancel: function()
                {
                    $('#Action').val(0);
                    $('#WlkId').val(0);
                    $(this).dialog('close');
                }
            }
        });


        $("#dialogPublish").dialog({
            autoOpen: false,
            bgiframe: true,
            resizable: false,
            /*position: [350,100],*/
            width: 450,
            modal: true,
            overlay:
            {
                backgroundColor: '#000',
                opacity: 0.5
            },
            buttons: {
                'Publish': function()
                {
                    $('#Action').val("publish");
                    $("#Form").submit();
                    $(this).dialog('close');
                },
                Cancel: function()
                {
                    $('#Action').val(0);
                    $('#WlkId').val(0);
                    $(this).dialog('close');
                }
            }
        });

        $('#dialogInfo').dialog({
            autoOpen: false,
            modal: true,
            width: 650,
            buttons: {
                "Close": function() {
                    $(this).dialog("close");
                }
            }
        });

    });

</script>

</body>
</html>
