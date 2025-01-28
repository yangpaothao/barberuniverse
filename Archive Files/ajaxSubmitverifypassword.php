<?php
require("./common/page.php");
require("./common/pdocon.php");  
require("./common/sendmail.php"); 
$np = new Page_Loader();
$db = new PDOCON();
$thisfields = Array();
$thistable = "employee_master";
$sendstatus= "";
$realpassword = "";
$getpasssword = $np -> Hash_Me_Password($_POST['txtpassword']); //we hash user's entered pw.
$thisdata = array("password" => $getpasssword, 'ispasswordchanged' => true, 'isverified' => true, 'vericode' => NULL);       
$thiswhere = array("recno" => $_POST['txtrecno']);
//file_put_contents("./dodebug/debug.txt", $tempstr, FILE_APPEND);  
$result = $db->PDOUpdate($thistable, $thisdata, $thiswhere, $_POST['txtrecno']);
if($result == "Success")
{
    //We want to send user an email and allow them to verify the email and change their password once they clicked on 
    //the link in the email.
    $sentto = Array();
    $replyto = Array();
    $ccto = Array();
    $bccto = Array();
    $attachment = Array();
    $subject = "";
    $body = "";
    //Need to get the email for this person
    $thisfields = Array();
    $thiswhere = Array();
    $realfirstname = "";
    $reallastname = "";
    $realemail = "";
    
    $thisfields = ["firstname", "lastname", "email"];
    $thiswhere = array("recno" => $_POST['txtrecno']);
    //file_put_contents('./dodebug/debug.txt', "recno: ".$_POST['divrecno'], FILE_APPEND);
    $rs = $db->PDOQuery($thistable, $thisfields, $thiswhere);
    //file_put_contents('./dodebug/debug.txt', "firstname: ".$rs['firstname'], FILE_APPEND);
    foreach($rs as $row)
    {
        $realfirstname = $row['firstname'];
        $reallastname = $row['lastname'];
        $realemail = $row['email'];  
    }
    $sendto[] = array($realemail => $realfirstname." ".$reallastname);
    //file_put_contents('./dodebug/debug.txt', $_POST['txtemail']." => ".$_POST['txtfirstname']." ".$_POST['txtlastname'], FILE_APPEND);
    $subject = "Account Verification From ".$_SESSION['companyname'];
    $body = "Your account has been verified.";
    $sendstatus = sendmail($sendto, $replyto, $ccto, $bccto, $subject, $body, $attachment);
    //echo $sendstatus;
}
//file_put_contents('./dodebug/debug.text', $result, FILE_APPEND);
echo $result;
exit;
?>
 