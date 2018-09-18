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
$pageOn="start";
$title = GROUP_NAME . ' Start Point List';

$sErrorString = '';
$sFb = '';
if ( !empty($_POST['Action']) ) {
    $iStartId = intval($_POST['sid']);

    if( !empty($iStartId)) {
        if( $_POST['Action'] == 'Delete' ) {
            $sQuery = "DELETE FROM startPoints WHERE startPointId=$iStartId";
            $rResult = runQuery($sQuery, DB_WRITE);
            if ( !$rResult ) {
                $sErrorString = 'StartPoint could not be deleted' . $sQuery;
            } else {
                $sFb = 'Start Point deleted';
            }
        }
    }
}


$aStartPointData = array();
$sQuery = "SELECT startPointId, startPoint, gridRef, explorer, explorerNew, landRanger, postCode FROM startPoints ORDER BY startPoint";
$rResult = runQuery($sQuery);
if($rResult) {
    while ( $aRow = $rResult->fetch_array(M_DB_BOTH) ) {
        $iId = $aRow['startPointId'];
        $aStartPointData[$iId]['startPoint'] =  $aRow['startPoint']; //htmlspecialchars($aRow['startPoint']);
        $aStartPointData[$iId]['gridRef'] =  $aRow['gridRef'];
        $aStartPointData[$iId]['explorer'] =  !empty($aRow['explorer']) ? 'X' . $aRow['explorer'] : '';
        $aStartPointData[$iId]['explorerNew'] =  !empty($aRow['explorerNew']) ? 'OL'. $aRow['explorerNew'] : '';
        $aStartPointData[$iId]['landRanger'] =  $aRow['landRanger'];
        $aStartPointData[$iId]['postCode'] =  $aRow['postCode'];
    }
} else {
	$sError =  "Query failed";
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
        #StartPoints { font-size: 100%;}
        #StartPoints td {vertical-align: top;}
        #StartPoints_paginate a.ui-button {margin-right: 0.3em;}
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
        <div id="subMenu">
            <span class="left">User: <?php echo $_SESSION['GH_UserInfo']; ?></span>
            <?php
            if ( $isSuperUser ) {
                echo '<span class="right"><a href="editStartPoint.php">Add a Start Point</a></span>';
            }
            ?>
        </div>
        <div class="clearbreak"></div>
        <form action="startPoints.php" method="post" id="Form">
            <input type="hidden" id="Action" name="Action" value="" />
            <input type="hidden" id="sid" name="sid" value="0" />
            <div id="dataTable">
                <h3>Predefined Walks Start Points</h3>
                <p>Below is a list of predefined start points for walks. </p>
                <table class="display" id="StartPoints" summary="Start Point List list">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Start Point (Car Park Area)</th>
                            <th>Grid Ref</th>
                            <th>Explorer</th>
                            <th>Landranger</th>
                            <th>Post Code</th>
                            <th class="no_sort">Action</th>
                        </tr>
                    </thead>
                    <tbody>
            <?php
            foreach (  $aStartPointData as $iId => $aData ) {
                $sPCode = formatBritishPostcode($aData['postCode']); //$aCol['Col1'];
                $sGridRef = formatGridRef($aData['gridRef']);
                $sEditCol = '&nbsp;';
                if ( $isSuperUser ) {
                    $sEditCol = '<a href="editStartPoint.php?sid='.$iId.'" title="Edit start point information."><img src="common/image/pencil_icon.png" alt="" /></a>';
                    $sEditCol .= ' |  <img id="Del_'.$iId.'" class="del_sp" src="common/image/bin_empty.png" alt="" title="Delete StartPoint" />';
                }

$sRow = <<<ROW
<tr>
    <td class="align_right">$iId</td>
    <td>{$aData['startPoint']}</td>
    <td class="align_right">$sGridRef</td>
    <td class="align_right">{$aData['explorer']} / {$aData['explorerNew']}</td>
    <td class="align_right">{$aData['landRanger']}</td>
    <td  class="align_right">$sPCode</td>
    <td>$sEditCol</td>
</tr>
ROW;


                echo $sRow . PHP_EOL;
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

<div id="dialogDelete" title="Delete" style="display:none;">
    <p>Are you sure you want to delete this Start Point?</p>
</div>
<!-- Script stuff here -->
<script src="common/script/jquery-ui-1.10.3.custom/js/jquery-1.9.1.js"></script>
<script src="common/script/jquery-ui-1.10.3.custom/js/jquery-ui-1.10.3.custom.js"></script>
<script type="text/javascript" charset="utf8" src="http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/jquery.dataTables.min.js"></script>
<script type="text/javascript">

    $(document).ready(function()
    {
    //jQuery here
        var dontSort = [];
        $('#StartPoints thead th').each( function (){
            if ( $(this).hasClass( 'no_sort' )) {
                dontSort.push( { "bSortable": false } );
            } else {
                dontSort.push( null );
            }
        });

        $('#StartPoints').dataTable({
            "bJQueryUI": true,
            "aaSorting": [[ 1, "asc" ]],
            "iDisplayLength": 50,
            "sPaginationType": "full_numbers",
            "aoColumns": dontSort
        });

        $('#StartPoints').on('click', '.del_sp', function()
        {
            $('#Action').val(0); //reset to make sure don't delete something else
            var iSpId = $(this).attr('id');
            iSpId = iSpId.replace('Del_', '');
            $('#sid').val(iSpId);

            $('#dialogDelete').dialog('open');

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
                    $('#sid').val(0);
                    $(this).dialog('close');
                }
            }
        });



    });

</script>

</body>
</html>