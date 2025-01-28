<?php
require("./common/page.php");
require("./common/pdocon.php");
require("./common/sendmail.php");
require("./common/classes/pageloaderclass.php");
require("./common/classes/dateclass.php");
require("./common/classes/emailclass.php");
require("./common/classes/passwordclass.php");
require("./common/classes/loginclass.php");
require("./common/classes/employeenoclass.php");
$load_headers = new PageloaderClass();

$db = new PDOCON();
$nd = new Date_Class();
$ne = new Email_Class();
$pc = new Password_Class();
$nl = new Login_Class();
$en = new Employeeno_Class();

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
             function submitRegisterguest(){
                if($("#txtfirstname").val() == ""){
                     alert("Employee First namne can not be empty.");
                     return(false);
                }
                if($("#txtlastname").val() == ""){
                     alert("Employee last name can not be empty.");
                     return(false);
                }
                if($("#txtemail").val() == ""){
                     alert("Email can not be empty.");
                     return(false);
                }
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=SubmitRegisterguest&'+$("#frmregisterguest").serialize(), function(result){
                    //alert(result);
                    if(result == "Insert OK"){
                        alert("Guest added successfully.");
                        //window.open.href = "localhost";
                        //window.open('','_self').close();
                        window.location.href = "index.php";
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
function SubmitRegisterguest()
{
    global $db, $nd, $en, $ne, $pc, $nl, $load_headers; 
    $isFailed = false;
    
    $thisserver = $load_headers -> GET_THIS_SERVER(); //This will be 'localhost' or the webhosting domain, ex:  https://www.somedomain.com
    //We want to just double check the pw one more time in server side to make sure it is good
    //file_put_contents('./dodebug/debug.txt', $_POST['txtbirthday'].' and '.$_POST['txthiredate'], FILE_APPEND);
    
    if(strlen($_POST['txtlogin']) < 3)
    {
        $result = "Login has to be atleast 3 characters long.";
        $isFailed = true;
    }
    
    //We will also need to validate the employee number.
    $ne->set_email($_POST['txtemployeenumber']); //We set the email first
    if($db->PDORowcount(($db->PDOMiniquery($en->check_login()))) > 0)
    {
        $result = "This employee number already exists.  Please use another.";
        $isFailed = true;
    }
    
    //We only check dates if we have a date
    if(isset($_POST['txtbirthdate']))
    {
        $nd->set_date($_POST['txtbirthdate']);  //Set the date first
        $thisdate = $nd->validate_date(); //We evaluate the date
        if($thisdate)
        {
            $birthdate = $nd->compare_dates($_POST['txtbirthdate'], date('Y-m-d'), 'Greater');
            if($birthdate == false)
            {
                $result = "Birthdate can not be greater than current date.";
                $isFailed = true;
            }
        }
        else
        {
            $result = "BAD birthdate detected.  Please make sure the birthdate is in correct format, mm/dd/yyy.  Ex: 01/22/2023.";
            $isFailed = true;
        }
    }
    
    $nd->set_date($_POST['txthiredate']);  //Set the date first
    $thisdate = $nd->validate_date(); //We evaluate the date
    if($thisdate == false)
    {
        $result = "BAD hiredate detected.  Please make sure the hiredate is in correct format, mm/dd/yyy.  Ex: 01/22/2023.";
        $isFailed = true;
    }

    $ne->set_email($_POST['txtemail']); //We set the email first
    if($ne->validate_email() == false)
    {
        $result = "Bad email format detected.  Please make sure the email is in somename@some.domain.  Ex: info@diversityfade.come.";
        $isFailed = true;
    }
    
    if($db->PDORowcount(($db->PDOMiniquery($ne->check_email()))) > 0)
    {
        $result = "This email already exists.  Please use another.";
        $isFailed = true;
    }
    
    //We will check if login already been used.
    $nl->set_login($_POST['txtlogin']);
    if($db->PDORowcount(($db->PDOMiniquery($nl->check_login()))) > 0)
    {
       $result = "This login already exists.  Please use another.";
       $isFailed = true; 
    }

    if($isFailed == false)
    {
        
        $thisfields = Array();
        $thistable = "guest";
        $thisfields = Array();
        $thiswhere = Array();
        $realfirstname = "";
        $reallastname = "";
        $realemail = "";

        $thisfields = ["firstname", "lastname", "email"];
        $thisdata = array("firstname" => $_POST['txtfirstname'], 
                "lastname" => $_POST['txtlastname'],
                "email" => $_POST['txtemail']);  
        $inresult = $db->PDOInsert($thistable, $thisdata);
        //file_put_contents('./dodebug/debug.txt', "1 what is result?: ".$inresult, FILE_APPEND);
        
        if($inresult == "Insert OK")
        {
            //We want to send verification email to the email above so customer can verify it.
            $sentto = Array();
            $replyto = Array();
            $ccto = Array();
            $bccto = Array();
            $attachment = Array();
            $subject = "";
            $body = "";

            $sendto[] = array($_POST['txtemail'] => $_POST['txtfirstname']." ".$_POST['txtlastname']);
            //file_put_contents('./dodebug/debug.text', $_POST['txtemail']." <=> ".$_POST['txtfirstname']." ".$_POST['txtlastname'], FILE_APPEND);
            $subject = $ne->get_verification_subject();
            $body = $ne->get_registerguest_body($thisserver);
            $sendstatus = sendmail($sendto, $replyto, $ccto, $bccto, $subject, $body, $attachment);
        }
        $result = $inresult;
    }
    //file_put_contents('./dodebug/debug.text', "2 what is result?: ".$result, FILE_APPEND);
    echo $result;
}
function Main()
{
    global $load_headers;?>
    <div class="main-div">
        <?php
        $load_headers::Load_Header_Logo();?>
        <br>
        <div class="div-header-main-container">Guest Registration</div>
        <br>
        <div class="div-body-container">
            <form name="frmregisterguest" id="frmregisterguest" method="post">
                <table class="tbl-admin-register">
                    <tr>
                        <td class="tbl-register-lbl">First Name: <span class="asterisk"> * </span></td>
                        <td class="registrationinput"><input type="text" class="firstname" id="txtfirstname" name="txtfirstname" value="" required /></td>
                    </tr>
                    <tr>
                        <td class="tbl-register-lbl">Middle Name: </td>
                        <td class="registrationinput"><input type="text" class="middlename" id="txtmiddlename" name="txtmiddlename" value="" /></td>
                    </tr>
                    <tr>
                        <td class="tbl-register-lbl">Last Name: <span class="asterisk"> * </span></td>
                        <td class="registrationinput"><input type="text" class="lastname" id="txtlastname" name="txtlastname" value="" /></td>
                    </tr>
                    <tr>
                        <td class="tbl-register-lbl">Email: <span class="asterisk"> * </span></td>
                        <td class="registrationinput"><input type="text" class="email required" id="txtemail" name="txtemail" value="" onchange="validateEmail(this);" size="20" /></td>
                    </tr>
                    <tr>
                        <td class="tbl-register-lbl">Login: <span class="asterisk"> * </span></td>
                        <td class="registrationinput"><input type="text" class="login" id="txtlogin" name="txtlogin" value=""  /></td>
                    </tr>
                    <tr class="tr-register-btn-container">
                        <td class="tbl-register-lbl align-center" colspan="2">
                            <button type="button" value="Submit" id="btnfrmregistration" onclick="submitRegisterguest();">Submit</button>
                        </td>
                    </tr>
                </table>
            </form>
            ?>
        </div>
        <?php $load_headers::Load_Footer();?>
    </div><?php
}