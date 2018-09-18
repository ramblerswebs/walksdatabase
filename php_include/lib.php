<?php
/**
 * @author  Kirsten H Osa
 */
session_start();
/*
 * For Live have protected directory with two logins. SuperUser gets extra options
 * */
//$sUser = $_SERVER['REMOTE_USER'];
$sUser = 'ghWalkAdm'; // Hardcoded for running locally
if($sUser == 'ghWalkAdm')
{
    $isSuperUser = true;
    $sUserType = 'Super Admin';
}
else
{
    $isSuperUser = false;
    $sUserType = 'Walks Admin';
}

############################################
# Constants & Variables
############################################

define('DB_READ',	0);
define('DB_WRITE',	1);

# Error Types for log
define('GH_LOG_ERR',	1);
define('GH_LOG_DEBUG',	2);
define('GH_LOG_INFO',	3);

#SQL CONSTANT
define('M_DB_ASSOC', 1);
define('M_DB_NUM',   2);
define('M_DB_BOTH',  3);


###################
# G&H SPECIF
##################

#Mobile options
define('MOBILE_OPT_NONE',	0);
define('MOBILE_OPT_DAY_ONLY',	1);
define('MOBILE_OPT_ANYTIME',	2);

$aMobileOpts = array(
MOBILE_OPT_NONE		=> 'None',
MOBILE_OPT_DAY_ONLY		=> 'on day',
MOBILE_OPT_ANYTIME		=> 'any time'
);

#Refreshment Stops
define('REFRESHMENT_NONE',	0);
define('REFRESHMENT_PUB',	1);
define('REFRESHMENT_LUNCH',	2);
define('REFRESHMENT_DRINKS',3);
define('REFRESHMENT_CAFE',  4);
define('REFRESHMENT_PICNIC',5);

$aRefreshmentOpts = array(
REFRESHMENT_NONE		=> 'NA',
REFRESHMENT_PUB		    => 'Pub stop',
REFRESHMENT_LUNCH		=> 'Lunch stop',
REFRESHMENT_DRINKS		=> 'Drinks after',
REFRESHMENT_CAFE		=> 'Cafe stop',
REFRESHMENT_PICNIC		=> 'No pub stop, bring picnic'
);

#Repeated walks  even date in future, odd date in past
define('REPEAT_NOT',	1);
define('REPEAT_ON',	2);
define('REPEAT_FROM',	3);
define('REPEAT_REVERSE_ON',	4);
define('REPEAT_REVERSE_FROM',	5);
define('REPEAT_NO_VISIT_ON',	6);
define('REPEAT_NO_VISIT_FROM',	7);
define('REPEAT_VISIT_ON',	8);
define('REPEAT_VISIT_FROM',	9);

$aRepeatTxt = array(
REPEAT_ON => 'Repeated on',
REPEAT_FROM => 'Repeated from',
REPEAT_NOT => 'Not Repeated',
REPEAT_REVERSE_ON => 'Repeated in reverse on',
REPEAT_REVERSE_FROM => 'Repeated in reverse from',
REPEAT_NO_VISIT_ON => 'Repeated without visit on',
REPEAT_NO_VISIT_FROM => 'Repeated without visit from',
REPEAT_VISIT_ON => 'Repeated with visit on',
REPEAT_VISIT_FROM => 'Repeated with visit from',
);


#Walk Grade
define('GRADE_EASY_ACCESS',	'EA');
define('GRADE_EASY',	    'E');
define('GRADE_LEISURELY',	'L');
define('GRADE_MODERATE',	'M');
define('GRADE_STRENUOUS',	'S');
define('GRADE_TECHNICAL',	'T');

$aGradeTitle = array(
GRADE_EASY_ACCESS   => 'Easy Access',
GRADE_EASY          => 'Easy',
GRADE_LEISURELY     => 'Leisurely',
GRADE_MODERATE      => 'Moderate',
GRADE_STRENUOUS     => 'Strenuous',
GRADE_TECHNICAL     => 'Technical',
);

$aGradeDetails = array(
GRADE_EASY_ACCESS   => 'Walks for everyone, including people with conventional wheelchairs and pushchairs, using easy access paths. Comfortable shoes or trainers can be worn. Assistance may be needed to push wheelchairs on some sections: please enquire.',
GRADE_EASY          => 'Walks for anyone who does not have a mobility difficulty or a specific health problem or is seriously unfit. Suitable for pushchairs if they can be lifted over occasional obstructions. Comfortable shoes or trainers can be worn.',
GRADE_LEISURELY     => 'Walks for reasonably fit people with at least a little country walking experience. May include unsurfaced rural paths. Walking boots and warm, waterproof clothing are recommended.',
GRADE_MODERATE      => 'Walks for people with country walking experience and a good level of fitness. May include some steep paths and open country, and may be at a brisk pace. Walking boots and warm, waterproof clothing are essential.',
GRADE_STRENUOUS     => 'Walks for experienced country walkers with an above average fitness level. May include hills and rough country, and may be at a brisk pace. Walking boots and warm, waterproof clothing are essential. People in doubt about their fitness are advised to contact the organiser or leader in advance.',
GRADE_TECHNICAL     => 'Walks for experienced and very fit walkers with additional technical skills. May require scrambling and use of ropes, ice axes and crampons. You must contact the organiser or leader in advance for further details.',
);

$sGradeExtraTxt = "Please ensure that Easy Access and Easy walks are no longer than 8km/5 miles and that the pace of these walks is easy as well as the length and terrain.";


#######################################

# Days of the week
define('DOW_MONDAY',	1);
define('DOW_TUESDAY',	2);
define('DOW_WEDNESDAY',	3);
define('DOW_THURSDAY',	4);
define('DOW_FRIDAY',	5);
define('DOW_SATURDAY',	6);
define('DOW_SUNDAY',	7);

$aDaysOfWeek = array(
DOW_MONDAY		=> 'Monday',
DOW_TUESDAY		=> 'Tuesday',
DOW_WEDNESDAY	=> 'Wednesday',
DOW_THURSDAY	=> 'Thursday',
DOW_FRIDAY		=> 'Friday',
DOW_SATURDAY	=> 'Saturday',
DOW_SUNDAY		=> 'Sunday'
);

$aDaysOfWeekShort = array(
DOW_MONDAY		=> 'Mon',
DOW_TUESDAY		=> 'Tue',
DOW_WEDNESDAY	=> 'Wed',
DOW_THURSDAY	=> 'Thu',
DOW_FRIDAY		=> 'Fri',
DOW_SATURDAY	=> 'Sat',
DOW_SUNDAY		=> 'Sun'
);

# Array for 24 hours from 0 to 23.
$aHourInDay = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23);

# Declare variables values
$iOneMin 	= 60;	// One minute in seconds
$iOneHour = 60 * 60;	// One hour in seconds
$iOneDay 	= 24 * 60 * 60;	// One day in seconds
$iOneWeek	= 24 * 60 * 60 * 7;	// One week in seconds


define('STATUS_DISABLED',			0);
define('STATUS_ENABLED',			1);
define('STATUS_TEMP_DISABLED',   2);
define('STATUS_ARCHIVED',			3);
define('STATUS_DELETED',			4);

$aStatusText = array(
STATUS_DISABLED      => 'Disabled',
STATUS_ENABLED       => 'Active',
STATUS_TEMP_DISABLED => 'Temp. Disabled',
STATUS_ARCHIVED      => 'Archived',
STATUS_DELETED       => 'Deleted'
);



############################################
# Google Constants ???
############################################


#############################
# Database Functions
#############################

/**
 * Connects to MySQL using MySQLi
 * @param $iDbMode string DB_READ or DB_WRITE
 * @return int|mysqli|resource
 */
function mDb_connectToDB($iDbMode=DB_WRITE)
{
    // Bring global database connections into scope.
    global $m_SQL_Link;

    $sDbServer = GH_DBSERVER;
    $sDbName = GH_DBNAME;
    $sDbUser = GH_DBUSER;
    $sDbPassword = GH_DBPW;

    $mysqli = mysqli_init();

    if ( !$mysqli ) {
        sendInfo(GH_LOG_ERR, 0, 'mysqli_init failed', 'mysqli init method failed - probably the MySQLi library is not installed');
        return 0;
    }

    # Set options
    //$mysqli->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, 1); // to not send all results back as strings

    # Connect
    if (!$mysqli->real_connect($sDbServer, $sDbUser, $sDbPassword, $sDbName) ) {
        sendInfo(LOG_ERR, mysqli_connect_errno(), mysqli_connect_error(), 'mysqli connect method failed - probably the connection details are incorrect');
        return 0;
    }

    if ( $iDbMode == DB_READ ) {
        $mysqli->query('SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED');
    }
    //echo "OK SO FAR??";

    // Set global parameter with connection object
    $m_SQL_Link = $mysqli;

    return $mysqli;
}

/**
 * @author KH Osa
 *
 * @desc <p>mySql or mysqli - Connect & query a specified database.</p>
 *
 * @param $sQueryString string - Query to be executed.
 * @param $iDbMode int[optional] - Database mode - defaults to read.
 * @param $bCharset boolean[optional] - default 0 don\t set.
 * @param $aOptions array[optional] - array with connections options, default null.
 *
 * @return resource (handle to query result)
 */
function runQuery($sQueryString, $iDbMode = DB_READ, $bCharset=1, $aOptions=null)
{
    // Connect to MySQL
    $mysql_link = mDb_connectToDB($iDbMode);
    if ( $bCharset == 1 ) {
        $mysql_link->set_charset("utf8");
    }

    # Process options
    switch ( $aOptions['ResultMode'] ) {
        case MYSQLI_USE_RESULT:
            $resultMode = MYSQLI_STORE_RESULT;
            break;
        case MYSQLI_ASYNC:
            $resultMode = MYSQLI_ASYNC;
            break;
        default: // MYSQLI_STORE_RESULT:
            $resultMode = MYSQLI_STORE_RESULT;
            break;
    }

    $rResult = $mysql_link->query($sQueryString, $resultMode );

    if(!$rResult) {
        echo "Results failed :-( ";
        sendInfo(GH_LOG_ERR, $mysql_link->errno, $mysql_link->error, $sQueryString);
        return 0;
    }

    return $rResult;
}

/* Logging */
function addSession() {

    global $m_SQL_Link;
    $iUserId = $_SESSION['GH_UserID'];
    $ip = $_SERVER['REMOTE_ADDR'];
    $userAgentString = $_SERVER['HTTP_USER_AGENT'];
    $date = date("Y-m-d H:i:s");

    $sQueryLog = "INSERT INTO sessions (ip, userAgentString, userId, logInDateTime) VALUES ('$ip','$userAgentString', $iUserId, '$date')";
    $result = runQuery($sQueryLog, DB_WRITE);
    if( $result ) {
       // $_SESSION['GH_SESSION'] = mysqli_insert_id($m_SQL_Link);
        $_SESSION['GH_SESSION'] = $m_SQL_Link->insert_id;
    }
}

function logoutSession($id) {
    if ( !empty($id) ) {
        $date = date("Y-m-d H:i:s");
        $sQueryLog = "UPDATE sessions SET logOutDateTime = '$date' WHERE sessionsId = $id";
        $result = runQuery($sQueryLog, DB_WRITE);
    }
}

function sendInfo($iType, $sErrorDetail, $sSubjectStr = '')
{
    # Needs to be updated to only include relevant info
    if ( defined('GH_NO_EMAIL') &&  GH_NO_EMAIL )
    {
        # if no email server log to file?
        //DO NOT ECHO
        //echo " $iType :- $sErrorDetail";
    }
    else
    {
        if ($iType == GH_LOG_ERR)
        {
            $emailSubj = "Website Error - ".$_SERVER['HTTP_HOST'];
        }
        else if ($iType == GH_LOG_DEBUG)
        {
            $emailSubj = "Website Debug - ".$_SERVER['HTTP_HOST'];
        }
        else if ($iType == GH_LOG_INFO)
        {
            $emailSubj = "Website Info - ".$_SERVER['HTTP_HOST'];
        }

        if ($sSubjectStr)
        {
            $emailSubj = $sSubjectStr;
        }
        // send the email to the administrator
        $emailBody  = "System Version: ".SYS_VERSION."\r\n";
        $emailBody .= "SERVER_NAME: ".$_SERVER["SERVER_NAME"]."\r\n";
        $emailBody .= "REQUEST_TIME: ".date('y-m-d H:i:s', $_SERVER["REQUEST_TIME"])."\r\n";
        $emailBody .= "REQUEST_METHOD: ".$_SERVER["REQUEST_METHOD"]."\r\n";
        $emailBody .= "PHP_SELF: ".$_SERVER["PHP_SELF"]."\r\n";
        $emailBody .= "SCRIPT_NAME: ".$_SERVER["SCRIPT_NAME"]."\r\n";
        //$emailBody .= "SCRIPT_FILENAME: ".$_SERVER["SCRIPT_FILENAME"]."\r\n";
        $emailBody .= "HTTP_REFERER: ".$_SERVER["HTTP_REFERER"]."\r\n";
        $emailBody .= "QUERY_STRING: ".$_SERVER["QUERY_STRING"]."\r\n";
        $emailBody .= "REMOTE_ADDR: ".$_SERVER["REMOTE_ADDR"]."\r\n";
        $emailBody .= "REMOTE_HOST = ".gethostbyaddr($_SERVER['REMOTE_ADDR'])."\r\n";
        $emailBody .= "HTTP_USER_AGENT: ".$_SERVER["HTTP_USER_AGENT"]."\r\n";

        if ( $_REQUEST )
        {
            $emailBody .= "\r\n";
            $emailBody .= "REQUEST:\r\n";
            $emailBody .= "===========\r\n";
            $emailBody .= print_r($_REQUEST, true);
        }

        $emailBody .= "\r\n";
        $emailBody .= "Error Info:\r\n";
        $emailBody .= "===========\r\n";
        $emailBody .= "Error Detail: ".$sErrorDetail."\r\n";
        $emailBody .= "\r\n";

        if ( $_SESSION )
        {
            $emailBody .= "\r\n";
            $emailBody .= "Session Info:\r\n";
            $emailBody .= "=============\r\n";
            $emailBody .= "Session ID: ".session_id()."\r\n";
            $emailBody .= print_r($_SESSION, TRUE);
        }


        mail(GH_ADMIN_EMAIL, $emailSubj, $emailBody,
        "From: ".GH_EMAIL_REPLY_TO."\nReply-To: ".GH_EMAIL_REPLY_TO."\nX-Mailer: PHP/" . phpversion());
    }
}

#Helper functions
function formatPostCode($sPostCode)
{
    if ( !empty($sPostCode) )
    {
        $inCode = substr(trim($sPostCode), -3);
        $outCode = trim(substr(trim($sPostCode), 0, -3));

        return $outCode . ' ' . $inCode;
    }
}

//this is probably better
function formatBritishPostcode($postcode)
{

    //--------------------------------------------------
    // Clean up the user input

    $postcode = strtoupper($postcode);
    $postcode = preg_replace('/[^A-Z0-9]/', '', $postcode);
    $postcode = preg_replace('/([A-Z0-9]{3})$/', ' \1', $postcode);
    $postcode = trim($postcode);

    //--------------------------------------------------
    // Check that the submitted value is a valid
    // British postcode: AN NAA | ANN NAA | AAN NAA | AANN NAA | ANA NAA | AANA NAA

    if (preg_match('/^[a-z](\d[a-z\d]?|[a-z]\d[a-z\d]?) \d[a-z]{2}$/i', $postcode)) {
        return $postcode;
    } else {
        return NULL;
    }
}

//validate postcode
function IsPostcode($postcode)
{
    $postcode = strtoupper(str_replace(' ','',$postcode));
    if(preg_match("/^[A-Z]{1,2}[0-9]{2,3}[A-Z]{2}$/",$postcode) || preg_match("/^[A-Z]{1,2}[0-9]{1}[A-Z]{1}[0-9]{1}[A-Z]{2}$/",$postcode) || preg_match("/^GIR0[A-Z]{2}$/",$postcode))
    {
        return true;
    }
    else
    {
        return false;
    }
}

function formatPhoneNumber($sNumber)
{
    //remove any spaces
    $sNumber = str_replace(' ','', $sNumber);
    if ( !empty($sNumber) )
    {
        //$sFormattedPhone = substr($sNumber,0,5).' '.substr($sNumber,5,3). ' '.substr($sNumber,8);
        $sFormattedPhone = substr($sNumber,0,5).' '.substr($sNumber,5);
        return $sFormattedPhone;
    }
}

#Grid Ref XX 111 111
function formatGridRef($sGridRef)
{
    $sGridRef = str_replace(' ','', $sGridRef);
    if ( !empty($sGridRef) )
    {
        $sCode =  substr($sGridRef, 0, 2);
        $iNum1 = substr($sGridRef, 2, 3);
        $iNum2 = substr($sGridRef, -3);
        return $sCode . ' ' . $iNum1. ' ' .$iNum2;
    }
}
//check specified number of digits in a number
function checkNumber($Number, $Digits)
{
    if( strlen((string)$Number) == $Digits ) {
        return true;
    } else {
        return false;
    }
}

// For repeated walks
function isRepeatAdded( $sWalkDate, $distance, $sGridRef )
{
    //$isAdded=0;
    $iId = 0;
    $sQuery = "SELECT id FROM walksProg WHERE walkDate = '$sWalkDate' AND distance=$distance AND gridRef='$sGridRef'";
    $sResult = runQuery($sQuery);
    if($sResult && $rResult->num_rows > 0)
    {
       $aRow = $rResult->fetch_array(M_DB_NUM);
        $iId = $aRow[0];
        //$isAdded=1;
    }
    return $iId; //$isAdded;
}

function format_telfax2 ($number,$fax=false) {
    // http://james.cridland.net/code/format_uk_phonenumbers.html
    // v2: worked on by Olly Benson to make it look better and work faster!
    // v2.1: removal of a bugette
    // v2.2: fix Cumbria numbers: thank you Roger Miller

    // Change the international number format and remove any non-number character
    $number=preg_replace( '~[^0-9]+~','',str_replace("+", "00", $number));

    // Turn number into array based on Telephone Format
    $numberArray = splitNumber($number,explode(",",getTelephoneFormat($number)));

    // Add brackets around first split of numbers if number starts with 01 or 02
    // if (substr($number,0,2)=="01" || substr($number,0,2)=="02") $numberArray[0]="(".$numberArray[0].")";
    // if (substr($number,0,2)=="01" || substr($number,0,2)=="02") $numberArray[0]="(".$numberArray[0].")";

    // Convert array back into string, split by spaces
    $formattedNumber = implode(" ",$numberArray);

    return $formattedNumber;
}

function getTelephoneFormat($number) {
    // This uses full codes from http://www.area-codes.org.uk/formatting.shtml
    $telephoneFormat = array (
        '02' => "3,4,4",
        '03' => "4,3,4",
        '05' => "3,4,4",
        '0500' => "4,6",
        '07' => "5,6",
        '070' => "3,4,4",
        '076' => "3,4,4",
        '07624' => "5,6",
        '08' => "4,3,4", // some 0800 numbers are 4,6
        '09' => "4,3,4",
        '01' => "5,6", // some 01 numbers are 5,5
        '011' => "4,3,4",
        '0121' => "4,3,4",
        '0131' => "4,3,4",
        '0141' => "4,3,4",
        '0151' => "4,3,4",
        '0161' => "4,3,4",
        '0191' => "4,3,4",
        '013873' => "6,5",
        '015242' => "6,5",
        '015394' => "6,5",
        '015395' => "6,5",
        '015396' => "6,5",
        '016973' => "6,5",
        '016974' => "6,5",
        '016977' => "6,5",
        '0169772' => "6,4",
        '0169773' => "6,4",
        '017683' => "6,5",
        '017684' => "6,5",
        '017687' => "6,5",
        '019467' => "6,5");

    // Sorts into longest key first
    uksort($telephoneFormat, "sortStrLen");

    foreach ($telephoneFormat AS $key=>$value) {
        if (substr($number,0,strlen($key)) == $key) break;
    };
    return $value;
}

function splitNumber($number,$split) {
    $start=0;
    $array = array();
    foreach($split AS $value) {
        $array[] = substr($number,$start,$value);
        $start = $start+$value;
    }
    return $array;
}

function sortStrLen($a, $b) {return strlen($b)-strlen($a);}

function closeSession( $aPrefix )
{
    # will we have separate session name for admin area - if so can unset session here
    if ( !empty($_SESSION) )
    {
        foreach ( $_SESSION as $sKey => $vValue )
        {
            if ( is_array($aPrefix) && !empty($aPrefix) )
            {
                foreach ( $aPrefix as $sPrefix )
                {
                    if ( substr($sKey, 0, strlen($sPrefix)) == $sPrefix )
                    {
                        unset($_SESSION[$sKey]);
                    }
                }
            }
        }
    }
}

function getNextPeriod()
{

    $iToday = time();
    $iMonth = date('m', $iToday);
    $iYear = date('Y', $iToday);
    $iStartYear = $iYear;
    $iEndYear = $iYear;
    switch ($iMonth)
    {
        case 3 :
        case 4:
        case 5:
        case 6:
            $iStartMonth = 7;
            break;
        case 7:
        case 8:
        case 9:
        case 10:
            $iStartMonth = 11;
            break;
        case 11:
        case 12: // need to add 1 to the year
            $iStartYear += 1;
            $iEndYear += 1;
            $iStartMonth = 3;
            break;
        case 1:
        case 2:
            $iStartMonth = 3;
            break;
    }

    $aDates = array();
    $aDates['start'] = mktime(0,0,0,$iStartMonth, 1, $iStartYear);
    $aDates['end'] = mktime(0,0,0,$iStartMonth+4, 1-1, $iEndYear);

    return $aDates;
}