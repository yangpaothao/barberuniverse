<?php
    require_once("./common/page.php");
    $thisutctime = gmdate('H:i');
    $thisdate = date('d M y');
    $thislocaltime = localtime(time(), true);
    $thislocalactualtime = date('H:i');
    $thisarray = Array();
    $thisarray = array('thisutctime' => $thisutctime,
                       'thisdate' => $thisdate,
                       'thislocaltime' => $thislocalactualtime);
    echo json_encode($thisarray);
?>
