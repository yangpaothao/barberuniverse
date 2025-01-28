<?php
require("./common/page.php");
require("./common/classes/pageloaderclass.php");
require("./common/classes/passwordclass.php");
require("./common/pdocon.php");
require("./common/sendmail.php");
require("./common/classes/emailclass.php");

$ne = new Email_Class();
$load_headers = new PageloaderClass();
$db = new PDOCON();
$pc = new Password_Class();

if(count($_POST) > 0 && isset($_POST['cmd']))
{
    $_REQUEST['cmd']();
    exit();
}
if(count($_GET) > 0)
{
    $keys = array_keys($_GET);
    foreach($keys as $value)
    {
        $_POST[$value] = $_GET[$value];
    }
    if(isset($_GET['cmd']))
    {
        $_REQUEST['cmd']();
        exit();
    }
}?>
<!DOCTYPE html>
<html>
    <head>
        <?php
            $temp_host = filter_input(INPUT_SERVER, 'SERVER_NAME'); // will get 'localhost'
            $temp_page = filter_input(INPUT_SERVER, 'PHP_SELF'); // will look like /index.php or /somedir/somepage.php
            $explode_page = explode("/", $temp_page); //This variable will now be an array and the page name is the last element of this array
            $this_page = end($explode_page); //this variable will hold the page name like index.php
            $load_headers::Load_Header(strtok($this_page, ".")); //by using strtok($this_page, "."), we will get just 'index'.
        ?>
        <script type="text/javascript">
            function submitPassowrdreset(){
                if($("#txtpassword").val() == ""){
                     alert("Password can not be empty.");
                     return(false);
                }
                if($("#txtconfirmpw").val() == ""){
                     alert("Confirm password field can not be empty.");
                     return(false);
                }
               $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=SubmitPassowrdreset&from='+$("body").data("from")+'&vericode=<?php echo $_POST['vericode'] ?>&recno='+$("body").data('recno')+'&'+$("#frmpasswordreset").serialize(), function(result){
                   //alert(result);
                   if(result == "First Time" || result == "Forgot Password"){
                       alert("Successfully updated password.");
                       window.location.href = "./login.php";
                    }
                   else{
                       alert(result);
                       return(false);
                   }
               });
           }
        </script>
    </head>
   
        
    <body>
        <?php
            Main();
        ?>
    </body>
</html>
<?php
function SubmitPassowrdreset()
{
    global $db, $pc, $load_headers, $ne;
    //Can!BePharm0KBO
    $pc ->set_password($_POST['txtpassword']);
    if(!$pc ->validate_password())
    {
        echo "Password has not met the requirements. Please try again.";
    }
    else
    {
        //file_put_contents('./dodebug/debug.txt', 'what is datarecno? '.$_POST['datarecno'], FILE_APPEND);
        $getpasssword = $load_headers -> Hash_Me_Password($_POST['txtpassword']); //we hash user's entered pw.
        if($_POST['from'] == "First Time")
        {
            $thisdata = array("password" => $getpasssword, "ispasswordchanged" => true, "isverified" => true, "vericode" => NULL);  
            
        }
        else
        {
            $thisdata = array("password" => $getpasssword, "isrequested" => false, "vericode" => NULL);  //After we updatd the requested pw change, we want to reset the fields.
        }
        $thisfields = Array('email', 'firstname', 'lastname');
        $thistable = "users";
        $thiswhere = array("recno" => $_POST['recno']);
        //file_put_contents("./dodebug/debug.txt", $tempstr, FILE_APPEND);  
        $result = $db->PDOUpdate($thistable, $thisdata, $thiswhere, $_POST['recno']); //$result should be the recno of this insert.
        if(isset($result))
        {
                $thisserver = $load_headers -> GET_THIS_SERVER(); //This will be 'localhost' or the webhosting domain, ex:  https://www.somedomain.com
                //After we updated the system we will need to send an acknowledgement email to the email in the system to let the
                //user know that it's changed.
                $sentto = Array();
                $replyto = Array();
                $ccto = Array();
                $bccto = Array();
                $attachment = Array();
                $subject = "";
                $body = "";

                //If from is coming from Password Requested, we do not have email of the user, so we will need to get it.
                //file_put_contents("./dodebug/debug.txt", "where to 1? ".$_POST['from'], FILE_APPEND); 
                $result = $db->PDOQuery($thistable, $thisfields, $thiswhere);
                foreach($result as $row)
                {
                    $thisemail = $row['email'];
                    $thisfirstname = $row['firstname'];
                    $thislastname = $row['lastname'];
                }
                if($_POST['from'] == "Forgot Password")
                {
                    
                    $subject = $ne->get_verification_passwordreset();
                    $sendto[] = array($thisemail => $thisfirstname." ".$thislastname);
                    $body = $ne->get_passwordreset_body($thisserver);
                    $sendstatus = sendmail($sendto, $replyto, $ccto, $bccto, $subject, $body, $attachment);
                    echo "Forgot Password";
                }
                else
                {
                    
                    //file_put_contents("./dodebug/debug.txt", "where? ".$_POST['from'], FILE_APPEND); 
                    $subject = $ne->get_verification_subject();
                    $sendto[] = array($thisemail => $thisfirstname." ".$thislastname);
                    $body = $ne->get_passwordreset_body($thisserver);
                    $sendstatus = sendmail($sendto, $replyto, $ccto, $bccto, $subject, $body, $attachment);
                    echo "First Time";
                }
        }
        else
        {
            echo "Failed";
        }
    }
}
function LoadForm($from)
{?>
   <form name="frmpasswordreset" id="frmpasswordreset" method="post">
       <script type="text/javascript">
            $("body").data("from", "<?php echo $from ?>");
        </script>
        <table class="tbl-admin-register" name="tbl_passwordreset" id="tbl_passwordreset">
            <tr class="tr-passreset">
                <td class="tbl-register-lbl">New Password:</td>
                <td class="registrationinput"><input class="user-profile-input required" type="password" id="txtpassword" name="txtpassword" onchange="checkPassword(this);" value=""/></td>
            </tr>
            <tr class="tr-passreset">
                <td class="tbl-register-lbl"></td>
                <td>
                    <div class="div-required-param">At least 8 in length</div>
                    <div class="div-required-param">At least 1 special character </div>
                    <div class="div-required-param">At least 1 number</div>
                    <div class="div-required-param">At least 1 UPPER case</div>
                    <div class="div-required-param">At least 1 lower case</div>
                </td>
            </tr>
            <tr class="tr-passreset">
                <td class="tbl-register-lbl">Confirm New Password:</td>
                <td class="registrationinput"><input class="user-profile-input required" type="password" id="txtconfirmpw" name="txtconfirmpw" onchange="checkConfirmpassword(this);" value=""/></td></tr>
            <tr class="tr-passreset">
                <td class="tbl-register-lbl align-center" colspan="2">
                    <button id="btnsubmitpasswordreset" onclick="submitPassowrdreset();" value="Submit">Submit</button>
                </td>
            </tr>
        </table>

</form>
<?php
}
function Main()
{
    global $load_headers;?>
    <div class="main-div">
        <br><br> <?php
        $load_headers::Load_Header_Logo();?>
        <br>
        <div class="div-header-main-container">Password Reset</div>
        <br>
        <div class="div-body-container">
            <?php
            $db = new PDOCON();
            if(array_key_exists('vericode', $_GET))
            {
                $thistable = "users";
                $thisfields = array('recno');
                $thiswhere = array("vericode" => $_GET['vericode'], 'isverified' => 'false');
                $rs = $db -> PDOQuery($thistable, $thisfields, $thiswhere); //($thistable=null, $thisfields=null, $thiswhere=null, $thisorderby=null, $thisgroupby=null, $ordering=null)
                
                if(!is_null($rs))
                {
                    ////If there is a record, we know we need to verify 
                    foreach($rs as $row)
                    {
                        $recno = $row['recno'];
                    }
                    //file_put_contents("./dodebug/debug.txt", "Password Requested recno? ".$recno, FILE_APPEND); 
                    //But we have to make sure the link is still valid and less than 24 hours.
                    $sql = "SELECT recno from $thistable WHERE recno = $recno AND passwordtimer <= now() + INTERVAL 1 DAY";
                    $result = $db->PDOMiniquery($sql);
                    
                    if($db->PDORowcount($result) > 0)
                    {?>
                        <script type="text/javascript">
                            $("body").data("recno", <?php echo $recno ?>);
                        </script>
                        <?php
                        LoadForm('First Time');
                    }
                    else
                    {
                        //Link is more than 24 hours.
                        ?>
                         
                        <div class="div-verifexpiredheader">This link has expired.</div><?php
                    }
                }
                else
                {
                    //We could be here due to user requesting password and the user clicked the link from the email they received in the email
                    //so we have to check for that first.  We are checking for the vericode and the isrequested fields
                    $thiswhere = array("vericode" => $_GET['vericode'], "isrequested" => true);
                    $rs = $db -> PDOQuery($thistable, $thisfields, $thiswhere);
                    if(!is_null($rs))
                    {
                        foreach($rs as $row)
                        {
                            $recno = $row['recno'];
                        }
                        //But we have to make sure the link is still valid and less than 24 hours.
                        $sql = "SELECT recno from $thistable WHERE recno = $recno AND passwordtimer <= now() + INTERVAL 1 DAY";
                        $result = $db->PDOMiniquery($sql);

                        if($db->PDORowcount($result) > 0)
                        {?>
                            <script type="text/javascript">
                                $("body").data("recno", <?php echo $recno ?>);
                            </script>
                            <?php
                            //file_put_contents("./dodebug/debug.txt", "where? Password Requested", FILE_APPEND); 
                            LoadForm('Forgot Password');
                        }
                        else
                        {
                            //Link is more than 24 hours.
                            ?>
                            <div class="div-verifexpiredheader">This link has expired.</div><?php
                        }
                    }
                    else
                    {
                        //If we do not have anything back, that means it's already verified.?> 
                        <div class="div-verifexpiredheader">This link is no longer valid.</div><?php
                    }
                }
            }
            else
            {
                LoadForm();
            }
            ?>
        </div>
    </div><?php
}