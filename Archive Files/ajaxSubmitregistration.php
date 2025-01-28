<?php
require("./common/page.php");
require("./common/pdocon.php");  
require("./common/sendmail.php"); 


$tempserver = new Page_Loader();
$thisserver = $tempserver -> GET_THIS_SERVER(); //This will be 'localhost' or the webhosting domain, ex:  https://www.somedomain.com

$db = new PDOCON();
$thisfields = Array();
$thistable = "employee_master";
$sendstatus= "";
foreach($_POST as $key => $value)
{
   $thisfields[substr($key, 3)] =  $value;
}
//file_put_contents("./dodebug/debug.txt", var_dump($thisfields), FILE_APPEND);
//We need to add the vericode to add to the row and also add password
$realpassword = $tempserver -> Hash_Me_Password(); //.We want to get a dummy pw.
$realvericode = $tempserver ->Hash_Me_Vericode();

$thisfields['password'] =  $realpassword;
$thisfields['vericode'] = $realvericode;
//At this point $thisfields array should now have an associative array of key and value.
//file_put_contents("./dodebug/debug.txt", $tempstr, FILE_APPEND);  
$result = $db->PDOInsert($thistable, $thisfields);

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
    
    $sendto[] = array($_POST['txtemail'] => $_POST['txtfirstname']." ".$_POST['txtlastname']);
    //file_put_contents('./dodebug/debug.text', $_POST['txtemail']." => ".$_POST['txtfirstname']." ".$_POST['txtlastname'], FILE_APPEND);
    $subject = "Account Creation From ".$_SESSION['companyname'];
    $body = "Please follow the link below to verify your account and change your password.<br><br>";
    $body .= "<a href='$thisserver/verifyme.php?vericode=".$realvericode."'>Click here to verify your email and change your password.</a>";
    $sendstatus = sendmail($sendto, $replyto, $ccto, $bccto, $subject, $body, $attachment);
    //echo $sendstatus;
}
//file_put_contents('./dodebug/debug.text', $result, FILE_APPEND);
echo $result;
exit;
?>
 