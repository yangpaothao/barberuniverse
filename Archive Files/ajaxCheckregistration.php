<?php
    require("./common/page.php");
    require("./common/pdocon.php");
    $db = new PDOCON();
    $thisfields = Array();
    $thiswheres = Array();
    //QueryMe($thistype=null, $thistable=null, $thisfields=null, $thiswheres=null, $thisorderby=null, $thisgroupby=null, $ordering=null)
    $thistable = "employee_master";
    $thisfields[] = $_POST['field'];
    $realval = $_POST['thisvalue'];
    if($_POST['field'] == "email")
    {
       //$realval = $_POST['thisvalue']."@qantas.com"; 
    }
    $thiswhere = array($_POST['field'] => $realval);
    $result = $db->PDOQuery($thistable, $thisfields, $thiswhere);
    if(isset($result)) //Nott sure if isset() will check if some items is returned or at least something in asso array.
    {
       echo "EXISTS"; 
    }
     
?>
