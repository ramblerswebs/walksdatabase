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
#Default settings
$pageOn="start";
$title = GROUP_NAME . ' - add/edit start point';

$iStartId = intval($_REQUEST['sid']);
if( $_POST['Action'] == 'Save' ) {
    $sErrorMsg = '';

    $startText = addslashes(($_POST['startText']));
    if ( empty($startText) ) {
        $sErrorMsg .= 'You must specify a start point (car park area)..<br />' . PHP_EOL;
    }
    $gridRef = str_replace(' ','', $_POST['gridRef']);
    if ( !empty($gridRef) ) {
        $sCode =  strtoupper(substr($gridRef, 0, 2));
        $iNum = substr($gridRef, 2, 6);
        if ( ctype_digit($iNum) ) {
            $gridRef = $sCode . $iNum;
            if(strlen($gridRef) != 8) {
                $sErrorMsg .= 'The Grid Ref ('.$gridRef.')  has too few or too many characters. It should be 2 letters followed by 6 numbers.<br />' . PHP_EOL;
            }
        } else {
            $sErrorMsg .= 'The Grid Ref ('.$gridRef.')  is not a valid format. It should be 2 letters followed by 6 numbers.<br />' . PHP_EOL;
        }

    } else {
        $sErrorMsg .= 'You must enter a Grid Ref.<br />' . PHP_EOL;
    }
    $bMapOK = true;
    $explorer = intval($_POST['explorer']);
    $explorerNew = !empty($_POST['explorerNew']) ? intval($_POST['explorerNew']) : "NULL"; // converts to number
    $bMapOK = checkNumber($explorer, 3);
    $landRanger = intval($_POST['landRanger']);
    $bMapOK = checkNumber($landRanger, 3);

    if ( !$bMapOK ) {
        $sErrorMsg .= 'Old Map Numbers should contain 3 numbers Only<br />' . PHP_EOL;
    }
    $postCode = formatBritishPostcode($_POST['postCode']);
    if ( !$postCode ) {
        $sErrorMsg .= $_POST['postCode'] . ' is not a valid UK Post Code<br />' . PHP_EOL;
    }

    if(empty($sErrorMsg) ) {
        //update or insert
        if ( !empty($iStartId) ) {
            $sQuery = "UPDATE startPoints SET
            startPoint='$startText', gridRef='$gridRef', explorer=$explorer, explorerNew=$explorerNew, landRanger=$landRanger, postCode='$postCode'
            WHERE startPointId=$iStartId";
            $rResult = runQuery($sQuery, DB_WRITE);
            if($rResult) {
                $sFb = 'Start Point Updated';
            } else {
                $sError =  "Update failed "; // . $sQuery;
            }
        } else {
            $sQuery = "INSERT INTO startPoints (startPointId, startPoint, gridRef, explorer, explorerNew, landRanger, postCode)
            VALUES (0, '$startText', '$gridRef', $explorer,$explorerNew, $landRanger, '$postCode')";
            $rResult = runQuery($sQuery, DB_WRITE);
            $iStartId = $m_SQL_Link->insert_id;
            if ( empty($iStartId) ) {
                $sError =  "Insert failed "; // . $sQuery;
            } else {
                $sFb = 'Start Point Added  &mdash;  <a href="startPoints.php">Start Point List</a>';
            }
        }
    }
}

if ( !empty($iStartId) ) {
    $sQuery = "SELECT startPoint, gridRef, explorer, explorerNew, landRanger, postCode FROM startPoints
     WHERE startPointId = $iStartId";
    $rResult = runQuery($sQuery);
    if ( $rResult && $rResult->num_rows == 1) {
        $aRow = $rResult->fetch_array(M_DB_BOTH);

        $sStartText = $aRow['startPoint'];
        $sGridRef = formatGridRef($aRow['gridRef']);

        $sExplorer = $aRow['explorer'];
        $sExplorerNew = $aRow['explorerNew'];
        $sLandRanger = $aRow['landRanger'];
        $sPostCode = formatPostCode($aRow['postCode']);
    } else {
        $sError =  "Query failed  $sQuery";
    }
}
?>
<!doctype html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <title><?php echo $title;?></title>
    <link rel="stylesheet" href="common/script/jquery-ui-1.10.3.custom/css/custom-theme/jquery-ui-1.10.3.custom.css">
    <link rel="stylesheet" type="text/css" href="common/script/jquery-ui-1.10.3.custom/css/jquery-ui-timepicker-addon.css" />
    <link href="common/css/main.css" rel="stylesheet">
    <style type="text/css">

        h3 {font-size: 140%; margin-bottom: 5px; margin-top: 15px;}
        #FormContent { font-size: 110%;}

        .dateField { border: solid 1px #FFA100; cursor: pointer; padding: 5px 5px 5px 5px; margin-left: 0.5em; margin-right: 0.5em;}
        .dateField img { vertical-align: middle; }
        #datePicker, #datePicker2 { /*background-color: #E9E9E9;*/ border: none !important; width: 130px;}
        #timeStartPicker, #timePmPicker { /*background-color: #E9E9E9;*/ border: none !important; width: 60px;}

        #walkForm { margin-top: 20px; width: 100%; padding-bottom: 10px; }
        #walkForm ul { list-style: none; margin: 0; padding: 0; }
        #walkForm li { padding-bottom: 20px; }
        #walkForm label:first-child { display: block; float: left; margin-right: 0.5em; text-align: right; width: 12em; font-weight: bold;}
        #walkForm input { margin-left: 0.5em; margin-right: 0.5em; }
        #walkForm input.long { width: 600px; }
        #walkForm input.medium { width: 300px; }
        #walkForm input.small { width: 100px; }
        #walkForm input.short { width: 75px; }
        #walkForm input.tel { width: 90px; }
        #walkForm input.map { width: 25px; }
        #walkForm input.mini { width: 20px; }

        #walkForm select { margin-left: 0.5em; margin-right: 0.5em;}

        #walkForm textarea { width: 600px; height: 50px; margin-left: 0.5em; }
        .buttons {text-align: center; margin: 5px;}
        #save {padding: 4px; width: 120px;}
        #walkForm li.button { margin-left: 12em; }
        #walkForm li.submit { margin-left: 12em; }

        fieldset { padding:5px; padding-right: 10px; border: 1px solid #FCB040;}
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

    </style>

</head>
<body id="gh-body">
<?php
include_once('common/top.php');
?>
<div id="main-cont">
<div id="subMenu">
    <span class="left">User: <?php echo $_SESSION['GH_UserInfo']; ?></span>
    <span class="right"><a href="editStartPoint.php">Add a New Start Point</a></span>
</div>
    <h3><?php echo ($iStartId ? 'Edit' : 'Add New'); ?> Start Point</h3>

<?php
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
                        <strong>Info: </strong><?php echo $sFb; ?></p>
                </div>
            </div>
<?php
        }
?>
        <div id="FormContent">
            <form action="<?php  print $_SERVER['PHP_SELF']; ?>" method="post" class="form" id="Form">
                <input type="hidden" name="Action" id="Action" value="" />
                <input type="hidden" name="sid" value="<?php echo $iStartId; ?>" />
                <div class="form" id="walkForm">
                    <fieldset>
                        <legend>Where</legend>
                        <ul>
                            <li><label for="startText">Start Point<span class="required">*</span></label>
                                <input type="text" id="startText" name="startText" value="<?php echo $sStartText; ?>" class="long" /></li>
                            <li><label for="gridRef">Grid Ref.<span class="required">*</span></label>
                                <input type="text" id="gridRef" name="gridRef" value="<?php echo $sGridRef; ?>" class="short" /> </li>
                            <li><label for="explorer">Explorer X<span class="required">*</span></label><input type="text" id="explorer" name="explorer" value="<?php echo $sExplorer; ?>" class="map" maxlength="3" /></li>
                            <li><label for="explorerNew">Explorer OL</label><input type="text" id="explorerNew" name="explorerNew" value="<?php echo $sExplorerNew; ?>" class="map" maxlength="3" /></li>
                            <li><label for="landRanger">Landranger LR<span class="required">*</span></label><input type="text" id="landRanger" name="landRanger" value="<?php echo $sLandRanger; ?>" class="map" maxlength="3" /></li>
                            <li><label for="postCode">Post Code<span class="required">*</span></label><input type="text" id="postCode" name="postCode" value="<?php echo $sPostCode; ?>" class="short" maxlength="8" /></li>
                        </ul>
                    </fieldset>
                    <br />
                    <span class="required">*Required Information</span>
                    <ul>
                        <li><div class="buttons">
                                <button id="save" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" type="button" name="save">
                                    <span class=""ui-button-text">Save</span></button>
                            </div></li>
                    </ul>

            </form>
        </div>

<?php
    }

?>

</div>
<!-- Pop-up Dialog box  -->
<div id="dialog" title=""></div>
<!-- Script stuff here -->
<script type="text/javascript" src="common/script/jquery-ui-1.10.3.custom/js/jquery-1.9.1.js"></script>
<script type="text/javascript" src="common/script/jquery-ui-1.10.3.custom/js/jquery-ui-1.10.3.custom.js"></script>

<script type="text/javascript">

    $(document).ready(function()
    {
    //jQuery here
        $('#save').click(function()
        {
            $('#Action').val("Save");
            if ( validateForm() )
            {
               $("#Form").submit();
               //alert('Validation OK');
            }
        });

        function validateForm()
        {
            var FocusOn = '';
            // Need to check username and password for new users
            var Msg = '';

            var startText = trim($('#startText').val());
            if ( Msg == '' && startText == '' )
            {
                Msg = 'Please enter a start point (car park area).';
                FocusOn = 'startText';
            }

            //Grid Ref
            var GridRef =  stripWs($('#gridRef').val());
            if ( Msg == '' )
            {
                if ( GridRef == '' )
                {
                    Msg = 'Please enter a Grid Ref.';
                }
                if ( Msg == '' && GridRef.length != 8)
                {
                    Msg = 'The Grid reference should be 8 characters long, 2 letters and 2 * 3 numbers';
                }
                if ( Msg == '' && !/^[a-zA-Z]+$/.test(GridRef.substring(0, 2)) )
                {
                    Msg = 'The first 2 characters of the Grid ref. can only be letters';
                }
                if ( Msg == '' &&  !/^\d+$/.test(GridRef.substring(2, 6)) )
                {
                    Msg = 'The last 6 characters of the Grid ref. must be numbers';
                }
                if( Msg != '')
                {
                    FocusOn = 'gridRef';
                }
            }

            //Check map boxes
            var Exp = trim($('#explorer').val());
            var LR = trim($('#landRanger').val());
            if (Msg == '' && Exp == '' )
            {
                Msg = 'Please enter an Explorer Map reference.';
                FocusOn = 'explorer';
            }
            if ( Msg == '' && !isNumeric(Exp))
            {
                Msg = 'The Map reference must be 3 numbers only.';
                FocusOn = 'explorer';
            }

            if (Msg == '' && LR == '' )
            {
                Msg = 'Please enter a Landranger Map reference.';
                FocusOn = 'landRanger';
            }
            if ( Msg == '' && !isNumeric(LR) )
            {
                Msg = 'The LR Map reference must be 3 numbers only.';
                FocusOn = 'landRanger';
            }

            //PostCode
            var postCode = $('#postCode').val().replace(/\s/g, '');
            if (Msg == '' && postCode == '' )
            {
                Msg = 'Please enter a Post Code.';
                FocusOn = 'postCode';
            }
            if ( Msg == '' &&  !isValidPostcode(postCode) )
            {
                Msg = 'Please enter a valid post code for the walk.' + postCode;
                FocusOn = 'postCode';
            }

            // Display any problems
            if ( Msg )
            {
                $('#dialog').dialog('option', 'title', 'Warning');
                $('#dialog').html('<br />' + Msg + '<br /><br />');
                $('#dialog').dialog('open');
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

    //un-escape back characters before displaying in a form field
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

</script>

</body>
</html>
