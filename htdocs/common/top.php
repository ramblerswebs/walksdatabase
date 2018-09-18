<?php
/**
 * @author  Kirsten H Osa
 * Date created: 28/12/13
 */
?>
<div id="ghwf-kho">
    <div id="ramblersheader">
        <div id="logo" class="left">
            <img src="common/image/logo92_1.png" id="ralogo" width="111" height="92" alt="" />
            <h1 id="ramblerstitle"><?php echo GROUP_NAME; ?></h1>
        </div>
        <div id="area" class="right"><h2 id="strap">Walks Program</h2></div>
    </div>
    <div id="main-menu" class="left">
        <ul id="gh-menu">
            <li class="first">
                <a href="<?php echo GROUP_WEBSITE; ?>">
                    <img src="common/image/icon-home.png" alt="" id="gh-icon-home" /> Home
                </a>
            </li>
            <li <?php echo $pageOn=="index" ? ' class="current"' : ''; ?>><a href="index.php" title="Menu/Login"> <?php echo $_SESSION['GH_LoggedIn'] ? 'Admin Menu' : 'Login'; ?> </a></li>
            <?php
            if ( $_SESSION['GH_LoggedIn'] )
            {
            ?>
                <li <?php echo $pageOn=="walk" ? ' class="current"' : ''; ?>><a href="walks.php" title="Walks list"> Walks </a>
                    <ul>
                        <li<?php echo $iList==2 ? ' class="current"' : ''; ?>><a href="walks.php?t=2"><img src="common/image/database_red.png" alt="" /> Next Walk Program</a></li>
                        <li<?php echo $iList==1 ? ' class="current"' : ''; ?>><a href="walks.php?t=1"><img src="common/image/database_green.png" alt="" /> Current Walks</a></li>
                        <li<?php echo empty($iList) ? ' class="current"' : ''; ?>><a href="walks.php"><img src="common/image/database_blue.png" alt="" /> All Walks</a></li>
                    </ul>
                </li>
                <li<?php echo $pageOn=="start" ? ' class="current"' : ''; ?>><a href="startPoints.php" title="List of predefined start points for walks"> Start Points </a></li>
            <?php
            }
            ?>

        </ul>
    </div>
    <div class="clearbreak"></div>