<?php
require("./common/page.php");
require("./common/pdocon.php"); 
$np = new Page_Loader();
$db = new PDOCON();
$thisfields = array();
$thiswhere = array();

$thisfields = array('recno', 'firstname', 'lastname', 'ispasswordchanged', 'isverified');
$thistable = "employee_master";
$getpasssword = $np -> Hash_Me_Password($_POST['txtpassword']); //we hash user's entered pw.      
$thiswhere = array("login" => $_POST['txtlogin'], "password" => $getpasssword);
//file_put_contents("./dodebug/debug.txt", $tempstr, FILE_APPEND);  
//($thistable=null, $thisfields=null, $thiswhere=null, $thisorderby=null, $thisgroupby=null, $ordering=null)
$result = $db->PDOQuery($thistable, $thisfields, $thiswhere);
if(isset($result))
{
    foreach($result as $row)
    {
        if($row['ispasswordchanged'] == false && $row['isverified'] == false)
        {
            //file_put_contents("./dodebug/debug.txt", 'verify', FILE_APPEND);
            echo "Not Verify";
        }
        else
        {
            $_SESSION['user'] = $_POST['txtlogin'];
            $_SESSION['fullname'] = $row['firstname']." ".$row['lastname'];
            $_SESSION['user_recno'] = $row['recno'];
            $_SESSION['companyname'] = "Avion Tracker";
            
            //Since we successfully logged in, we want to make vericode NULL so that it wil negate any new password change request or verification
            
            $thisdata = array('vericode' => NULL);
            $thiswhere = array('recno' => $_SESSION['user_recno']);
            $rows = $db->PDOUpdate($thistable, $thisdata, $thiswhere, $_SESSION['user_recno']);
            if(!isset($rows))
            {
                echo "Failed To Update vericode to NULL";
            }
            else
            {
                echo "Success";
            }
            exit;
        }
    }
}
else
{
    //file_put_contents("./dodebug/debug.txt", 'Failed', FILE_APPEND);
    echo "Failed";
}
//file_put_contents('./dodebug/debug.text', $result, FILE_APPEND);
exit;
?>
 