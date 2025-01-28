<?php
require_once("./common/page.php");
require_once("./common/pdocon.php");
require("./common/sendmail.php");
require("./common/classes/pageloaderClass.php");

$load_headers = new PageloaderClass();

$db = new PDOCON();
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
            
            //Seach by service and specialties on the front page
            //list of specialties of each barbers
        ?>
        <script type="text/javascript">
            function submitIndexform(obj){
                if($(obj).val() == "Submit"){
                    if($("#txtlogin").val() == "")
                    {
                        alert('Please enter a login.');
                        $("#txtlogin").focus();
                        return(false);
                    }
                    if($("#txtpassword").val() == "")
                    {
                        alert('Please enter pasword.');
                        $("#txtlogin").focus();
                        return(false);
                    }
                    $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=SubmitIndexform&txtlogin='+$("#txtlogin").val().toLowerCase()+'&txtpassword='+$("#txtpassword").val(), function(result){
                        if(result != "Success")
                        {
                            if(result == "Failed")
                            {
                                alert("You have entered a wrong login or password.  Pleaase try again.")
                                $("#txtlogin").focus();
                                $("#txtlogin").select();
                                return(false);
                            }
                            else if(result == "Not Verify")
                            {
                                alert("This account hasn't been confirmed yet.  You may not login at this time.  Please go to your email and confirm and change your password first.");
                                $("#txtlogin").focus();
                                $("#txtlogin").select();
                                return(false);
                            }
                            else if(result == "Authenticate"){
                                $("#logincontainer").hide();
                                $("#twofactorcontainer").show();
                                alert("A passcode has been sent to the email in our system.  Please verify your account with code to login.");
                            }
                            else{
                                alert(result);
                                return(false);
                            }
                        }
                        else
                        {
                            window.location.href = "./index.php";
                        }
                    });
                }
                else{
                    if($(obj).val() === "Register"){
                        window.open("registration.php", "_blank");
                    }
                    else{
                        window.open("retrievepassword.php", "_blank");
                    }
                }
            }
            function submitTwofactor(){
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=SubmitTwofactor&txtcode='+$("#txtauthenticatecode").val(), function(result){
                    if(result == "Success"){
                        window.location.href = "./index.php";
                    }
                    else{
                        alert(result);
                        $("#txtauthenticatecode").val('');
                        $("#txtauthenticatecode").focus();
                        return(false);
                    }
                });
                event.preventDefault();
            }
            function resendAuthenticate(){
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=ResendAuthenticate', function(result){
                    if(result != "Success")
                    {
                        alert("A new verify code has been sent to the email in our system.  Please check your email and use the code to verify your account to login.");
                        $("#txtauthenticatecode").val('');
                        $("#txtauthenticatecode").focus();
                        return(false);
                    }
                    else
                    {
                        alert("ERROR: "+result);
                        return(false);
                    }
                });
            }
            function goTomenus(obj){
                switch($(obj).prop('id'))
                {
                    case "div_about":
                        window.location.href = "about.php";
                        break;
                    case "div_introduction":
                        window.location.href = "introduction.php";
                        break;
                    case "div_schedule":
                        window.location.href = "schedule.php";
                        break;
                    default:
                        break;
                        
                }
            }
            function goToschedule(obj, recno){
                window.location.href = "schedule.php?recno="+recno;
            }
            function showOrders(recno){
                window.location.href = "serviceorder.php?recno="+recno;
            }
            function showThisannouncement(){
                //status will come in as Modify or default to Readonly
                //window.location.href = "announcement.php?recno="+recno;
                window.open('manageAnnouncement.php?fromload=index', '_blank');
            }
            function showThisimportant(){
                //status will come in as Modify or default to Readonly
                //window.location.href = "announcement.php?recno="+recno;
                window.open('manageImportant.php?fromload=index', '_blank');
            }
            function goTofid(){
                window.open('fid.php?', '_blank');
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
function ResendAuthenticate()
{
    global $db, $load_headers;
    //By this time, if user is trying to resend a passcode, we should already have $_SESSION['temprecno'] avail.
    
    $thisfields = array();
    $thiswhere = array();
    $thisfields = array('recno', 'firstname', 'lastname', 'email');
    $thistable = "user";
    $thiswhere = array("recno" => $_SESSION['temprecno']);
    $result = $db->PDOQuery($thistable, $thisfields, $thiswhere);
    if(isset($result))
    {
        foreach($result as $row)
        {
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
            echo "Authenticate";
        }
    }
    else
    {
        echo "Failed";
    }
}
function SubmitTwofactor()
{
    global $db, $load_headers;
    $thisfields = array();
    $thiswhere = array();
    $thisfields = array('recno', 'firstname', 'lastname', 'twofactorcode', 'login', 'profile');
    $thistable = "users";
    $realcode = $load_headers -> Hash_Me_Password($_POST['txtcode']); //we hash user's entered pw.      
    $thiswhere = array("recno" => $_SESSION['temprecno'], "twofactorcode" => $realcode);
    //file_put_contents("./dodebug/debug.txt", $tempstr, FILE_APPEND);  
    //($thistable=null, $thisfields=null, $thiswhere=null, $thisorderby=null, $thisgroupby=null, $ordering=null)
    $result = $db->PDOQuery($thistable, $thisfields, $thiswhere);
    if(isset($result))
    {
        foreach($result as $row)
        {
            $_SESSION['user'] = $row['login'];
            $_SESSION['fullname'] = $row['firstname']." ".$row['lastname'];
            $_SESSION['user_recno'] = $row['recno'];
            $_SESSION['companyname'] = "Avion Tracker";
            $_SESSION['usersearchlist'] = array();
            $_SESSION['customersearchlist'] = array();
            $_SESSION['profile'] = $row['profile']; //SV, 2 letter rep
            //Since we successfully logged in, we want to make vericode NULL so that it wil negate any new password change request or verification
            $thisdata = array('vericode' => NULL);
            $thiswhere = array('recno' => $row['recno'], 'isauthenticatedverified' => true);
            $rows = $db->PDOUpdate($thistable, $thisdata, $thiswhere, $row['recno']);
        }
    }
    else
    {  
        echo "Verify vericode is wrong or expired.";
    }
}
function SubmitIndexform()
{
    global $db, $load_headers;
    $thisfields = array();
    $thiswhere = array();

    $thisfields = array('recno', 'firstname', 'lastname', 'email', 'login', 'isverified', 'isactive', 'isauthenticated', 'isauthenticatedverified', 'profile', 'isAdmin');
    $thistable = "users";
    $getpasssword = $load_headers -> Hash_Me_Password($_POST['txtpassword']); //we hash user's entered pw.      
    $thiswhere = array("login" => $_POST['txtlogin'], "password" => $getpasssword);
    //file_put_contents("./dodebug/debug.txt", $tempstr, FILE_APPEND);  
    //($thistable=null, $thisfields=null, $thiswhere=null, $thisorderby=null, $thisgroupby=null, $ordering=null)
    $result = $db->PDOQuery($thistable, $thisfields, $thiswhere);
    if(!is_null($result))
    {
        foreach($result as $row)
        {
            if($row['isverified'] == false)
            {
                //file_put_contents("./dodebug/debug.txt", 'verify', FILE_APPEND);
                echo "Not Verify";
            }
            else if($row['isactive'] == false)
            {
                //file_put_contents("./dodebug/debug.txt", 'verify', FILE_APPEND);
                echo "Account is inactive, contact your administrator.";
            }
            else if($row['isauthenticated'] == true && $row['isauthenticatedverified'] == false)
            {
                //If isauthenticatedverified is false, that means 2 factor has NOT been enable, 
                //if it is true, that means 2 factor is enabled and if they verified that they do want it enable, isauthenticatedverified would be true
                ////and then in this case, we would check against it too.
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
                $_SESSION['user'] = $row['login'];
                $_SESSION['fullname'] = $row['firstname']." ".$row['lastname'];
                $_SESSION['user_recno'] = $row['recno'];
                $_SESSION['usersearchlist'] = array();
                $_SESSION['customersearchlist'] = array();
                $_SESSION['profile'] = $row['profile'];
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
    global $db, $load_headers;?>
    <div class="main-div" style="">
        <?php
        $load_headers::Load_Header_Logo(true);
        $sql = "SELECT recno, media_dir, login FROM users WHERE isactive = true and isverified = true ORDER BY lastname";
        $result = $db -> PDOMiniquery($sql); ?>
        <div class="body-div-menu" id="div_alert">
            <div class="body-div-menu-content" id="div_about" onclick="goTomenus(this);">About Us</div>
            <div class="body-div-menu-content" id="div_introduction" onclick="goTomenus(this);">Introduction</div>
            <div class="body-div-menu-content" style="width: 70% !important">&nbsp</div>
        </div>
        <div class="div-body-container">
            <div class="body-div-content-holder-flex" id="div_minifid">
            <?php
                if($result)
                {
                    foreach($result as $rs)
                    {
                        //file_put_contents("./dodebug/debug.txt", 'Front imag? '.$thisfrontimage, FILE_APPEND);
                        //http://localhost/images/others/TrinaWellis_Trina/defaultimage.png
                        ?>
                        <div onclick="goToschedule(this, <?php echo $rs['recno'] ?>);">
                            <div>
                                <img class="frontimage" src="../images/others/<?php echo $rs['media_dir']?>/avatar/frontimage.png" onerror="this.src='../images/others/<?php echo $rs['media_dir']?>/avatar/defaultimage.png'"></a>
                               
                            </div>
                            <span class="span-front-login"><?php echo $rs['login'] ?></span>
                        </div><?php
                    }
                }?>
                
            </div>
        </div>
        <?php
        $load_headers::Load_Footer();?>
    </div><?php
}