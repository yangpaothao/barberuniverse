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
                    case "div_poems":
                        window.location.href = "Poems.php";
                        break;
                    default:
                        break;
                        
                }
                window.location.href = "about.php";
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
function LoadAlert()
{
    global $db;
    $thisfields = Array();
    $thiswheres = Array();
    //QueryMe($thistype=null, $thistable=null, $thisfields=null, $thiswheres=null, $thisorderby=null, $thisgroupby=null, $ordering=null)
    $sql = "SELECT so.recno, flow.customer, flow.actype, flow.flightnumber FROM service_orders so INNER JOIN flow ";
    $sql .= "ON flow.recno = so.foreignkey_flow_recno WHERE so.isdeleted = false AND so.completedby IS NULL AND ";
    $sql .= "flow.date <= '".date('Y-m-d')."' AND flow.isdeparted=true ORDER BY flow.date, flow.customer";
    //file_put_contents('./dodebug/debug.txt', $sql, FILE_APPEND);
    $result = $db->PDOMiniquery($sql);
    if(!empty($result)) //Nott sure if isset() will check if some items is returned or at least something in asso array.
    {?> 
        <div style="width: 100%; overflow-y: auto;">
            <table id="tbl_order_data" class="tbl-order-data">
                <thead>
                    <tr style="background-color: #173346; font-size: .8em;">
                        <th style="width: 20px !important; position: sticky; top: 0px; z-index: 10; padding-right: 10px;"></th>
                        <th style="width: 160px !important; position: sticky; top: 0px; z-index: 10; padding-left: 10px;" title="Customer">Customer</th>
                        <th style="width: 60px !important; position: sticky; top: 0px; z-index: 10; padding-left: 10px;" title="Aircraft Type">A/C Type</th>
                        <th style="width: 50px; position: sticky; top: 0px; z-index: 10; padding-left: 10px;" title="Flight Number">Flt No.</th>
                        <th style="width: 40px; position: sticky; top: 0px; z-index: 10; padding-right: 10px;" title="Schedule Arrival">Order No.</th>
                     </tr>
                </thead>
                <tbody><?php
                    $i=1;
                    if($result)
                    {
                        foreach($result as $rs)
                        {?>
                            <tr id="tr<?=$i?>" style="font-size: .8em;">
                                <td class="tdnumbered" style="text-align: right; height: 40px; width: 20px !important; padding-right: 10px;" onclick="showOrders(<?=$rs['recno']?>);"><?= $i ?></td>
                                <td style="height: 40px; width: 160px; padding-left: 10px; cursor: pointer;" onclick="showOrders(<?=$rs['recno']?>);"><?= $rs['customer']?></td>
                                <td style="height: 40px; width: 60px; padding-left: 10px; cursor: pointer;" onclick="showOrders(<?=$rs['recno']?>);"><?= $rs['actype']?></td>
                                <td style="height: 40px; width: 50px; padding-left: 20px; cursor: pointer;" onclick="showOrders(<?=$rs['recno']?>);"><?= $rs['flightnumber']?></td>
                                <td style="height: 40px; width: 40px; padding-right: 20px; text-align: right; cursor: pointer;" onclick="showOrders(<?=$rs['recno']?>);"><?= $rs['recno']?></td>
                            </tr><?php
                            $i++;
                        }
                        if($i==1)
                        {?>
                            <tr id="tr_nodata"><td style="width: 100%; text-align: center;" colspan="5">There is no data</td></tr><?php
                        }
                    }?> 
                </tbody>
            </table>
        </div><?php
    }
}
function LoadMinifid()
{
    global $db, $pt;
    $thisfields = array('All');
    $thistable = "flow";
    $thiswhere = array('isdeparted' => false, 'isdeleted' => false);
    $sql = "SELECT flow.* FROM flow WHERE flow.isdeparted=false AND isdeleted = false AND flow.engineers LIKE '%".$_SESSION['user_recno']."%' ORDER BY flow.date, flow.customer";
    //file_put_contents('./dodebug/debug.txt', $sql, FILE_APPEND);
    $result = $db->PDOMiniquery($sql);?>    
    <table id="tbl_flow_data" class="tbl-fid-data-mini">
        <?php
        $i=1;
        if(isset($result))
        {
            $tempdate = "";
            $curdate = "";
            $thminiheader = false;
            foreach($result as $rs)
            {
                $thisarrival = "";
                if($thminiheader == false)
                {
                    $thminiheader = true;
                    if($rs['schedulearrival'] != "")
                    {
                       $thisarrival = 'SA';
                    }
                    if($rs['estimatearrival'] != "")
                    {
                       $thisarrival = 'EA'; 
                    }
                    if($rs['actualarrival'] != "")
                    {
                       $thisarrival = 'AA';
                    }
                    
                    ?>
                    <tr style="font-size: .8em;">
                        <th style="width: 20px !important; position: sticky; top: 0px; z-index: 10;"></th>
                        <th style="width: 160px !important; position: sticky; top: 0px; z-index: 10;">Cust</th>
                        <th style="width: 60px !important; position: sticky; top: 0px; z-index: 10;">A/C Type</th>
                        <th style="position: sticky; top: 0px; z-index: 10;">Flt No.</th><?php
                        if($thisarrival == 'SA')
                        {?>
                            <th style="position: sticky; top: 0px; z-index: 10;">SArr.</th><?php
                        }
                        else if($thisarrival == 'EA')
                        {?>                            
                            <th style="position: sticky; top: 0px; z-index: 10;">EArr.</th><?php
                        }
                        else
                        {?>
                            <th style="position: sticky; top: 0px; z-index: 10;">AArr.</th><?php
                        }?>
                        <th style="position: sticky; top: 0px; z-index: 10;">Gate</th><?php                                  
                        if($thisarrival == 'SA')
                        {?>
                            <th style="position: sticky; top: 0px; z-index: 10;">SDep.</th><?php
                        }
                        else if($thisarrival == 'EA')
                        {?>
                            <th style="position: sticky; top: 0px; z-index: 10;">EDep.</th><?php
                        }
                        else
                        {?>
                            <th style="position: sticky; top: 0px; z-index: 10;">ADep.</th><?php
                        }?>
                    </tr><?php
                }
                $curdate = $rs['date'];
                //file_put_contents("./dodebug/debug.txt", $tempdate." != ".$curdate."===", FILE_APPEND);
                if($tempdate == "" || strtotime($tempdate) != strtotime($curdate))
                {
                    if($tempdate != "")
                    {
                        //file_put_contents("./dodebug/debug.txt", "INHERE", FILE_APPEND);
                        //First time we come here, this would be empty so we will not get here.  But everytime after that
                        //if we get here, it's cuz it's a new date.  That means we want to show a date divider.?>
                        <tr>
                            <td colspan="16" style="text-align: center; font-weight: bold; font-size: 1.2em; background-color: #181818; color: white; height: 40px;"><?= date('d M y', strtotime($curdate));?></td>
                        </tr><?php
                    }
                    if($tempdate == "")
                    {//We only come in here the first time and only 1 time.?>
                        <tr>
                            <td colspan="16" style="text-align: center; font-weight: bold; font-size: 1.2em; background-color: #181818; color: white; height: 40px;"><?= date('d M y', strtotime($curdate));?></td>
                        </tr><?php
                    }
                }
                $tempdate = $curdate;
                if($rs['schedulearrival'] != "")
                {
                   $thisbgcolor = 'white';
                   $thisfontcolor = 'Black'; 
                }
                if($rs['estimatearrival'] != "")
                {
                   $thisbgcolor = 'yellow';
                   $thisfontcolor = 'black'; 
                }
                if($rs['actualarrival'] != "")
                {
                   $thisbgcolor = 'green';
                   $thisfontcolor = 'black';
                }
                if($rs['status'] != "")
                {
                   $thisbgcolor = 'darkred';
                   $thisfontcolor = 'white';
                }?>
                <tr id="tr<?=$i?>" style="background-color: <?= $thisbgcolor?>; color: <?= $thisfontcolor ?>; font-size: .8em;" onclick="goTofid();">
                    <td style="height: 40px; width: 20px !important; color: <?= $thisfontcolor ?>;"><?= $i ?></td>
                    <td style="height: 40px; color: <?= $thisfontcolor ?>;"><?= $rs['customer']?></td>
                    <td style="height: 40px; color: <?= $thisfontcolor ?>;"><?= $rs['actype']?></td>
                    <td style="height: 40px; color: <?= $thisfontcolor ?>;"><?= $rs['flightnumber']?></td><?php
                    if($thisarrival == 'SA')
                    {?>
                        <td style="height: 40px; color: <?= $thisfontcolor ?>;"><?= $rs['schedulearrival']?></td><?php
                    }
                    else if($thisarrival == 'EA')
                    {?>
                        <td style="height: 40px; color: <?= $thisfontcolor ?>;"><?= $rs['estimatearrival']?></td><?php
                    }
                    else
                    {?>
                        <td style="height: 40px; color: <?= $thisfontcolor ?>;"><?= $rs['actualarrival']?></td><?php
                    }?>
                    <td style="height: 40px; color: <?= $thisfontcolor ?>;"><?= $rs['gate']?></td><?php
                    if($thisarrival == 'SA')
                    {?>
                        <td style="height: 40px; color: <?= $thisfontcolor ?>;"><?= $rs['scheduledeparture']?></td><?php
                    }
                    else if($thisarrival == 'EA')
                    {?>
                        <td style="height: 40px; color: <?= $thisfontcolor ?>;"><?= $rs['estimatedeparture']?></td><?php
                    }
                    else
                    {?>
                        <td style="height: 40px; color: <?= $thisfontcolor ?>;"><?= $rs['actualdeparture']?></td><?php
                    }?>
                </tr><?php
                $i++;
            }
        }
        else
        {?>
        <tr><td>There is no data</td><tr>
        <?php
        }?>  
    </table><?php
}
function LoadAnnouncement()
{
    global $db;
    $canmod = "display: none;";
    if($_SESSION['profile'] == 'SU' || $_SESSION['profile'] == "SV")
    {
        $canmod = "";
    }
    $thisarec = "";
    //file_put_contents("./dodebug/debug.txt", var_dump($_POST), FILE_APPEND);
    $sql = "SELECT announcement FROM user WHERE recno=".$_SESSION['user_recno'];
    $result = $db->PDOMiniquery($sql);
    foreach($result as $rsa)
    {
        $thisarec = $rsa['announcement'];
    }
    $thisfields = Array();
    $thiswheres = Array();
    //QueryMe($thistype=null, $thistable=null, $thisfields=null, $thiswheres=null, $thisorderby=null, $thisgroupby=null, $ordering=null)
    $sql = "SELECT an.*, em.firstname, em.middlename, em.lastname FROM announcement an INNER JOIN user em ";
    $sql .= "ON an.foreignkey_recno = em.recno WHERE an.isactive = true AND an.isdeleted = false ";
    if($thisarec != NULL)
    {
        $sql .= "AND an.recno NOT IN ($thisarec) ";
    }
    $sql .= "ORDER BY expiredate ASC";
    $result = $db->PDOMiniquery($sql);
    if($result) //Nott sure if isset() will check if some items is returned or at least something in asso array.
    {?>
        <div style="height: 798px; overflow-y: auto;">
            <table id="tbl_order_data" class="tbl-order-data" style="font-size: .8em;">
                <thead>
                    <tr style="background-color: #173346;" style="font-size: .8em;">
                        <th style="width: 20px !important; position: sticky; top: 0px; z-index: 10; padding-right: 10px;"></th>
                        <th style="width: 180px !important; position: sticky; top: 0px; z-index: 10; padding-left: 10px;" >Title</th>
                        <th style="width: 100px !important; position: sticky; top: 0px; z-index: 10; padding-left: 10px;" >Author</th>
                        <th style="width: 40px; position: sticky; top: 0px; z-index: 10; padding-left: 10px;" title="Expire Date" >Ex. Date</th>
                        <th style="width: 200px; position: sticky; top: 0px; z-index: 10; padding-right: 10px;">Data</th>
                        <th style="width: 80px; position: sticky; top: 0px; z-index: 10; padding-right: 10px;" >Attach.</th>
                     </tr>
                </thead>
                <tbody><?php
                    $i=1;
                    if($result)
                    {
                        foreach($result as $rs)
                        {?>
                            <tr id="tr<?=$i?>" style="font-size: .8em;" onclick="showThisannouncement();">
                                <td class="tdnumbered" style="text-align: right; height: 40px; width: 20px !important; padding-right: 10px;"><?= $i ?></td>
                                <td style="height: 40px; width: 180px; padding-left: 10px; cursor: pointer;" ><?= $rs['title']?></td>
                                <td style="height: 40px; width: 100px; padding-left: 10px; cursor: pointer;" >
                                    <?=$rs['firstname']." ".($rs['middlename'] != null ? $rs['middlename']." " : '').$rs['lastname'];?>
                                </td>
                                <td style="height: 40px; width: 40px; padding-left: 20px; cursor: pointer;" ><?= date('m/d/Y', strtotime($rs['expiredate']));?></td>
                                <td style="height: 40px; width: 200px; padding-right: 20px; text-align: right; cursor: pointer;" ><textarea rows="2" style="cursor: pointer; border: none; resize: none; width: 99%; height: 90%;" readonly><?= $rs['data']?></textarea></td>
                                <td style="height: 40px; width: 80px; padding-left: 10px; cursor: pointer;"><?php
                                    $explodeattachment = explode(';', $rs['attachment']);
                                    foreach($explodeattachment as $attachment)
                                    {?>
                                        <img title="<?=$attachment?>" src="./images/others/blackattachmentpin.png"/><br/><?php                                        
                                    }?>                                                                               
                                </td>
                            </tr><?php
                            $i++;
                        }
                        if($i==1)
                        {?>
                            <tr id="tr_nodata"><td style="width: 100%; text-align: center;" colspan="5">There is no data</td></tr><?php
                        }
                    }?> 
                </tbody>
            </table>
        </div><?php       
    }
}
function LoadImportant()
{
    global $db;
    $canmod = "display: none;";
    if($_SESSION['profile'] == 'SU' || $_SESSION['profile'] == "SV")
    {
        $canmod = "";
    }
    $thisarec = "";
    //file_put_contents("./dodebug/debug.txt", var_dump($_POST), FILE_APPEND);
    $sql = "SELECT important FROM user WHERE recno=".$_SESSION['user_recno'];
    $result = $db->PDOMiniquery($sql);
    foreach($result as $rsa)
    {
        $thisarec = $rsa['important'];
    }
    $thisfields = Array();
    $thiswheres = Array();
    //QueryMe($thistype=null, $thistable=null, $thisfields=null, $thiswheres=null, $thisorderby=null, $thisgroupby=null, $ordering=null)
    $sql = "SELECT an.*, em.firstname, em.middlename, em.lastname FROM important an INNER JOIN user em ";
    $sql .= "ON an.foreignkey_recno = em.recno WHERE an.isactive = true AND an.isdeleted = false ";
    if($thisarec != NULL)
    {
        $sql .= "AND an.recno NOT IN ($thisarec) ";
    }
    $sql .= "ORDER BY expiredate ASC";
    $result = $db->PDOMiniquery($sql);
    if($result) //Nott sure if isset() will check if some items is returned or at least something in asso array.
    {?>
        <div style="height: 798px; overflow-y: auto;">
            <div>
                <table id="tbl_order_data" class="tbl-order-data" style="font-size: .8em;">
                    <thead>
                        <tr style="background-color: #173346;" style="font-size: .8em;">
                            <th style="width: 20px !important; position: sticky; top: 0px; z-index: 10; padding-right: 10px;"></th>
                            <th style="width: 180px !important; position: sticky; top: 0px; z-index: 10; padding-left: 10px;" >Title</th>
                            <th style="width: 100px !important; position: sticky; top: 0px; z-index: 10; padding-left: 10px;" >Author</th>
                            <th style="width: 40px; position: sticky; top: 0px; padding-left: 10px" title="Expire Date" >Ex. Date</th>
                            <th style="width: 200px; position: sticky; top: 0px; padding-right: 10px;">Data</th>
                            <th style="width: 80px; position: sticky; top: 0px; padding-right: 10px;" >Attach.</th>
                         </tr>
                    </thead>
                    <tbody><?php
                        $i=1;
                        if($result)
                        {
                            foreach($result as $rs)
                            {?>
                                <tr id="tr<?=$i?>" style="font-size: .8em; height: 20px;" onclick="showThisimportant();">
                                    <td class="tdnumbered" style="text-align: right; height: 40px; width: 20px !important; padding-right: 10px;" ><?= $i ?></td>
                                    <td style="height: 40px; width: 180px; padding-left: 10px; cursor: pointer;" ><?= $rs['title']?></td>
                                    <td style="height: 40px; width: 100px; padding-left: 10px; cursor: pointer;" >
                                        <?=$rs['firstname']." ".($rs['middlename'] != null ? $rs['middlename']." " : '').$rs['lastname'];?>
                                    </td>
                                    <td style="height: 40px; width: 40px; padding-left: 20px; cursor: pointer;" ><?= date('m/d/Y', strtotime($rs['expiredate']));?></td>
                                    <td style="height: 40px; width: 200px; padding-right: 20px; text-align: right; cursor: pointer;" ><textarea rows="2" style="cursor: pointer; border: none; resize: none; width: 99%; height: 90%;" readonly><?= $rs['data']?></textarea></td>
                                    <td style="height: 40px; width: 80px; padding-left: 10px; cursor: pointer;"><?php
                                        $explodeattachment = explode(';', $rs['attachment']);
                                        foreach($explodeattachment as $attachment)
                                        {?>
                                            <img title="<?=$attachment?>" src="./images/others/blackattachmentpin.png"/></a><br/><?php                                        
                                        }?>                                                                               
                                    </td>
                                </tr><?php
                                $i++;
                            }
                            if($i==1)
                            {?>
                                <tr id="tr_nodata"><td style="width: 100%; text-align: center;" colspan="5">There is no data</td></tr><?php
                            }
                        }?> 
                    </tbody>
                </table>
            </div>
        </div><?php       
    }
}
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

    $thisfields = array('recno', 'firstname', 'lastname', 'email', 'isverified', 'isactive', 'isauthenticated', 'isauthenticatedverified', 'profile');
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
                $_SESSION['user'] = $_POST['txtlogin'];
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
    global $load_headers;?>
    <div class="main-div" style="">
        <?php
        $load_headers::Load_Header_Logo();?>
        <br>
        <div class="div-body-container">
            <?php
            if(isset($_SESSION['fullname']))
            {?>
                <div class="body-div-menu no-wrap align-left" id="div_alert">
                    <div class="body-div-menu-content no-bubble float-right" id="div_about" onclick="goTomenus(this);">About</div>
                    <div class="body-div-menu-content no-bubble float-right" id="div_introduction" onclick="goTomenus(this);">Introduction</div>
                    <div class="body-div-menu-content no-bubble float-right" id="div_poems" onclick="goTomenus(this);">Poems</div>  
                </div>
                <div class="body-div-content-holder" id="div_minifid">
                    <div class="body-div-content">
                        <img class="center" src="../images/headers/village.png"></a>
                    </div>
                    <div class="body-div-content">
                        <img class="center" src="../images/headers/village.png"></a>
                    </div>
                    <div class="body-div-content">
                        <img class="center" src="../images/headers/village.png"></a>
                    </div>
                </div><?php
            } 
            else
            {?>
                <div class="center" id="div_logincontainter">   
                    <form name="frmlogin" id="frmregistration" method="post">
                        <table id="logincontainer">
                            <tr class="tr-login-lbl">
                                <td class="td-login-lbl">Login: </td>
                                <td class="td-login-input"><input type="text" class="input-login-user required" id="txtlogin" name="txtlogin" value="" placeholder="Type in your login" /></td>
                            </tr>
                            <tr class="tr-login-lbl">
                                <td class="td-login-lbl">Password: </td>
                                <td class="td-login-input"><input type="password" class="input-login-password required" id="txtpassword" name="txtpassword" value="" placeholder="Type in password" /></td>
                            </tr>
                            <tr class="tr-login-lbl">
                                <td class="td-login-btn" colspan="2">
                                    <button class="btn-submit" onclick="submitIndexform(this);" value="Submit">Submit</button>
                                    <button class="btn-retrieve" onclick="submitIndexform(this)" value="Retrieve">Retrieve Password</button>
                                    <button class="btn-register" onclick="submitIndexform(this)" value="Register">Register</button>
                                </td>
                            </tr>
                        </table>  
                    </form>
                </div><?php
            }?>
        </div>
        <?php
        $load_headers::Load_Footer();?>
    </div><?php
}