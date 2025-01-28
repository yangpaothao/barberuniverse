<?php
require("./common/page.php");
require("./common/pdocon.php");
require("./common/sendmail.php");
require("./common/classes/pageloaderClass.php");

$load_headers = new PageloaderClass();

$db = new PDOCON();

if(count($_POST) > 0 && isset($_POST['hid_cmd']))
{
    $_REQUEST['hid_cmd']();
    exit();
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
            $('body').data('thishost', '<?= $temp_host ?>');
            function submitIndexform(){
                if($("#txtlogin").val() == ""){
                    alert('Please enter user name.');
                    return(false);
                }
                if($("#txtpassword").val() == ""){
                    alert('Please enter a password.');
                    return(false);
                }
                $.post('<?=$_SERVER['PHP_SELF']; ?>', $("#frmlogin").serialize(), function(result){

                    if(result != "Success")
                    {
                        if(result == "Failed")
                        {
                            alert("You have entered a wrong login or password.  Pleaase try again.")
                            $("#txtlogin").focus();
                            $("#txtlogin").select();
                        }
                        else if(result == "Not Verify")
                        {
                            alert("This account hasn't been confirmed yet.  You may not login at this time.");
                            $("#txtlogin").focus();
                            $("#txtlogin").select();
                        }
                        else if(result == "Authenticate"){
                            $("#logincontainer").hide();
                            $("#twofactorcontainer").show();
                            alert("A passcode has been sent to the email in our system.  Please verify your account with code to login.");
                        }
                        else{
                            alert(result);
                        }
                        location.reload();
                    }
                    else
                    {
                        //alert('here');
                        window.location.href = "./index.php";
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
function SubmitLoginForm()
{
    global $db, $load_headers, $temp_host;
    //file_put_contents("./dodebug/debug.txt", 'verify1', FILE_APPEND);
    $thisfields = array();
    $thiswhere = array();
    //file_put_contents("./dodebug/debug.txt", "Here now", FILE_APPEND);  //2137
    $thisfields = array('recno', 'firstname', 'lastname', 'email', 'login', 'media_dir', 'ispasswordchanged', 'isverified', 'isactive', 'isauthenticated', 'isauthenticatedverified', 'profile', 'isAdmin');
    $thistable = "users";
    $getpasssword = $load_headers -> Hash_Me_Password($_POST['txtpassword']); //we hash user's entered pw.      
    $thiswhere = array("login" => $_POST['txtlogin'], "password" => $getpasssword);
    $result = $db->PDOQuery($thistable, $thisfields, $thiswhere);
    if(isset($result))
    {
        foreach($result as $row)
        {
            if($row['ispasswordchanged'] == false && $row['isverified'] == false)
            {
                //file_put_contents("./dodebug/debug.txt", 'verify', FILE_APPEND);
                echo 'Not Verify';
            }
            else if($row['isactive'] == false)
            {
                //file_put_contents("./dodebug/debug.txt", 'verify', FILE_APPEND);
                echo "Account is inactive, contact your administrator.";
            }
            else if($row['isauthenticated'] == true && $row['isauthenticatedverified'] == false)
            {
                //If isauthenticatedverified is false, that means it's not yet verified, if it is true, that means user already verified so it will not come
                //through hgere.
                //If we get here, we know the password and login is good so we will take care of the verification code for 2 factor authentication.
                $sentto = Array();
                $replyto = Array();
                $ccto = Array();
                $bccto = Array();
                $attachment = Array();
                //PDOInsert($thistable=null, $thisdata=null)
                $realfirstname = $row['firstname'];
                $reallastname = $row['lastname'];
                $realemail = $row['email'];  
                $temptime =  substr(time(), -5);
                $realtime = $load_headers -> Hash_Me_Password($temptime);
                
                $thisdata = array('twofactorcode' => $realtime, 'isauthenticatedverified' => false);
                $thiswhere = array('recno' => $row['recno']);
                $db->PDOUpdate($thistable, $thisdata, $thiswhere, $row['recno']);
                
                $sendto[] = array($realemail => $realfirstname." ".$reallastname);
                //file_put_contents('./dodebug/debug.txt', $_POST['txtemail']." => ".$_POST['txtfirstname']." ".$_POST['txtlastname'], FILE_APPEND);
                $subject = "Authentication Required to login.";
                
                $body = "Please use this code to verify your account to login.<br><br>CODE: $temptime";
                $sendstatus = sendmail($sendto, $replyto, $ccto, $bccto, $subject, $body, $attachment);
                $_SESSION['temprecno'] = $row['recno'];
                echo "Authenticate";
            }
            else
            {
                $_SESSION['companyname'] = "Diversity Fade Barbershop"; //Hard coded but will be replace by the individual's company below.
                $_SESSION['companyname_recno'] = 0;
                $sql = "SELECT * FROM company_info WHERE foreign_ur = ".$row['recno'];
                $result = $db ->PDOMiniquery($sql);
                if($db ->PDORowcount($result) > 0)
                {
                    foreach($result as $rs)
                    {
                        $_SESSION['companyname'] = $rs["name"];
                        $_SESSION['companyname_recno'] = $rs["recno"];
                        $_SESSION['companyphonenumber'] = $rs['phone_number']; //In format of 1234567890, 10 number no space or dashes.
                        $_SESSION['main_logo'] = $rs['mainlogo'];
                    }
                }
                
                $_SESSION['user'] = $row['login'];
                $_SESSION['fullname'] = $row['firstname']." ".$row['lastname'];
                $_SESSION['user_recno'] = $row['recno'];
                $_SESSION['media_dir'] = $row['media_dir'];
                $_SESSION['usersearchlist'] = array();
                $_SESSION['customersearchlist'] = array();
                $_SESSION['profile'] = $row['profile'];
                $_SESSION['thiswebsite'] = "$temp_host";
                $_SESSION['isAdmin'] = $row['isAdmin'];
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
}

function Main()
{
    global $load_headers;?>
    <div class="main-div">
        <?php
        $load_headers::Load_Header_Logo();?>
        <br>
        <div class="main-div-body" style="width: 50%; height: 20%; margin: 0px auto;">
            <form name="frmlogin" id="frmlogin" method="post">
                <input type="hidden" name="hid_cmd" id="hid_cmd" value="SubmitLoginForm" />
                <div id="logincontainer">
                    <div class="div-loginname">
                        <div class="div-namelbl">Login: </div>
                        <div class="div-user"><input type="text" class="input-login-user required" id="txtlogin" name="txtlogin" value="" placeholder="Type in your login" required /></div>
                    </div>
                    <div class="div-loginname">
                        <div class="div-passwordlbl">Password: </div>
                        <div class="div-password"><input type="password" class="input-login-password required" id="txtpassword" name="txtpassword" value="" placeholder="Type in password" required /></div>
                    </div>
                    <div class="div-regpasword" style="text-align: center;">
                        <a href="./retrievepassword.php">Retrieve Password</a>
                        <a href="./registration.php">Register</a>
                    </div>
                    <div class="div-buttons">
                        <button style="cursor: pointer;" onclick="submitIndexform();" value="Submit">Submit</button>
                    </div>
                </div>
            </form>
        <?php
        $load_headers::Load_Footer();?>
    </div><?php
}