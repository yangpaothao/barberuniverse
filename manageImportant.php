<?php
require_once("./common/page.php");
require_once("./common/pdocon.php");
require_once("./common/sendmail.php");
require_once("./common/prompt.php");
$pt = new PROMPT();
$load_headers = new Page_Loader();
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
            $(document).ready(function(){   
                <?php
                if(isset($_POST['fromload']) && $_POST['fromload'] == "index")
                {?>
                    showAnnouncementdefault();<?php
                }
                else
                {?>
                    addAnnouncementdefault();<?php
                }?>
            });
            function addAnnouncementdefault(){
                $(".div-menu-service-announcement").each(function(){
                    $(this).css('background-color', '#1079B1');
                    $(this).css('color', 'white');
                })
                $("#div_add").css("background-color", "white");
                $("#div_add").css('color', 'black');
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=addAnnouncement', function(result){
                    //alert(result);
                    $("#main_div_body_announcement_right_container").html(result);
                });
            }
            function addAnnouncement(obj){
                $(".div-menu-service-announcement").each(function(){
                    $(this).css('background-color', '#1079B1');
                    $(this).css('color', 'white');
                })
                $(obj).css("background-color", "white");
                $(obj).css('color', 'black');
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=addAnnouncement', function(result){
                    //alert(result);
                    $("#main_div_body_announcement_right_container").html(result);
                });
            }
            function showAnnouncementdefault(){
                $(".div-menu-service-announcement").each(function(){
                    $(this).css('background-color', '#1079B1');
                    $(this).css('color', 'white');
                })
                $("#div_show").css("background-color", "white");
                $("#div_show").css('color', 'black');
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=ShowAnnouncements', function(result){
                    $("#main_div_body_announcement_right_container").html(result);
                });
            }
            function showAnnouncement(obj){
                $(".div-menu-service-announcement").each(function(){
                    $(this).css('background-color', '#1079B1');
                    $(this).css('color', 'white');
                })
                $(obj).css("background-color", "white");
                $(obj).css('color', 'black');
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=ShowAnnouncements', function(result){
                    $("#main_div_body_announcement_right_container").html(result);
                });
            }
            function searchInterface(obj){
                $(".div-menu-service-announcement").each(function(){
                    $(this).css('background-color', '#1079B1');
                    $(this).css('color', 'white');
                })
                $(obj).css("background-color", "white");
                $(obj).css('color', 'black');
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=SearchInterface', function(result){
                    $("#main_div_body_announcement_right_container").html(result);
                });
            }
            function submitFormannouncement(){
                if($("#txttitle").val() == ""){
                    alert("Please type a title for this important item.");
                    $("#txttitle").focus();
                    return(false);
                }
                if($("#txtexpiredate").val() == ""){
                    alert("Please enter a expire date for this important item.");
                    $("#txtexpiredate").focus();
                    return(false);
                }
                if($("#txtdate").val() == ""){
                    alert("Please type a message or data for this important item.");
                    $("#txtdata").focus();
                    return(false);
                }  
                //We will check to see if the name exists in our database.
                var thisFrmdata = new FormData($('#frmAddannouncement')[0]);
                thisFrmdata.append('from', 'important');
                $.ajax({
                    type: 'POST',
                    url: './common/upload.php',
                    data: thisFrmdata,
                    processData: false,
                    contentType: false,
                    success: function(result) {
                        alert(result);
                        if(result == 'Failed'){
                            alert('Failed to upload.');
                            return(false);
                        }
                        else{
                            alert('Successfully added this important item.');
                            location.reload();
                            return(false);
                        }                            
                    }
                });
            }
            function validateCustomer(obj){
                switch($(obj).prop('id'))
                {
                    case "txtcustomer":
                        if($(obj).val().length == 0 && $(obj).val() == "")
                        {
                            return(false);
                        }
                        break;
                    case "txtairlinecode":
                        if($(obj).val().length != 3)
                        {
                            alert("The airline code has to be 3 characters long.  Please try again.");
                            $(obj).select();
                            return (false);
                        }
                        break;
                    case "txticaocode":
                        if($(obj).val().length != 3)
                        {
                            alert("The ICAO code has to be 3 characters long.  Please try again.");
                            $(obj).select();
                            return (false);
                        }
                        break;
                    case "txtiatacode":
                        if($(obj).val().length != 2)
                        {
                            alert("The ICAO code has to be 2 characters long.  Please try again.");
                            $(obj).select();
                            return (false);
                        }
                        break;
                }
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=ValidateCustomer&field='+$(obj).prop('id')+'&value='+$(obj).val(), function(result){
                    if(result != "Success")
                    {
                        alert(result);
                        $(obj).val('');
                        $(obj).focus();
                        return(false);
                    }
                    if($("#txtcustomer").val != "" && $("#txtairlinecode").val != "" && $("#txticaocode").val != "" && $("#txtiatacode").val != "")
                    {
                        $("#btnfrmaddcustomer").prop('disabled', false);
                    }
                });
            }
            //MGMT
            function showThisannouncement(recno, status='Readonly'){
                //status will come in as Modify or default to Readonly
                //window.location.href = "announcement.php?recno="+recno;
                window.open('important.php?recno='+recno+'&status='+status, '_blank');
            }
            function deleteAnnouncement(obj, recno, lineno){
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=DeleteAnnouncement&recno='+recno, function(result){
                    //alert(result);
                    if(result == "Failed"){
                        alert("Error:  Contact administrator");
                        return(false);
                    }
                    else{
                        //We will remove the TR and renumber.
                        $("#tr"+lineno).remove();
                        i=1;
                        $(".tdnumbered").each(function(){
                            $(this).text(i);
                            i++;
                        });
                    } 
                });
            }
            function searchForannouncement(obj){
                if($(obj).prop('id') == "txtdate"){
                    $("#txtdatefrom").val('');
                    $("#txtdateto").val('');
                }
                if($(obj).prop('id') == "txtdatefrom" || $(obj).prop('id') == "txtdateto"){
                    $("#txtdate").val('');
                }                
                if($(obj).prop('id') == "btnclear"){
                    $("#div_select_body_container").empty();
                    $(':input').val('');
                    $("#slttype").val('Un-read');
                    return(false);
                }
                if($(obj).prop('id') == 'txtdatefrom' || $(obj).prop('id') == 'txtdateto'){
                    if($("#txtdatefrom").val() == "" ||  $("#txtdateto").val() == ""){
                        //We won't go forth if we do not have both date range.
                        return(false);
                    }
                }
                if($(obj).val().length < 2){
                    return(false);
                }
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=SearchForannouncement&'+$("#div_select_header :input").serialize(), function(result){
                    //alert(result);
                    $("#div_select_body_container").html(result);
                });                
            }
            function updateList(thisfilename="") {
                var input = document.getElementById('fileupload');
                var output = document.getElementById('fileList');
                var children = "";
                const dt = new DataTransfer();
                for (var i = 0; i < input.files.length; ++i) {
                    if(thisfilename == ""){
                        children += input.files.item(i).name + '<span style="color: darkred; cursor: pointer;" onclick="updateList(\''+input.files.item(i).name+'\');">&nbsp;X</span><br/>';
                    }
                    else{
                        if(thisfilename != input.files.item(i).name){
                            children += input.files.item(i).name + '<span style="color: darkred; cursor: pointer;" onclick="updateList(\''+input.files.item(i).name+'\');">&nbsp;X</span><br/>';
                            dt.items.add(input.files[i]);
                        }                            
                    }
                }
                if(thisfilename != ""){
                    input.files = dt.files;
                }
                $("#fileList").show();
                output.innerHTML = children;
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
function addAnnouncement()
{?>
    <form name="frmAddannouncement" id="frmAddannouncement" enctype="multipart/form-data" action="<?=$_SERVER['PHP_SELF'];?>" method="post" onsubmit="return false">
        <table id="tbl_atachment" class="tbl-announcement">
            <tr><td id="td1" class="announcement-lbl">Title:</td><td><input class="announcement-input required" style="width: 98%;" type="text" id="txttitle" name="txttitle" value=""/></td></tr>
            <tr><td id="td2" class="announcement-lbl">Expire Date:</td><td><input class="announcement-input required" type="text" style="width: 98%;" id="txtexpiredate" onfocus="getJDate(this);" name="txtexpiredate" value="" placeholder="Enter or select a date in format of 01/22/1997" /></td></tr>
            <tr><td id="td3" class="announcement-lbl">Data:</td><td><textarea class="required" style="width: 600px; height: 400px; resize: none;" id="txtdata" name="txtdata"></textarea></td></tr>
            <tr>
                <td id="td4" class="announcement-lbl">
                    Attachment:              
                </td>
                <td style="height: 120px; text-align: left;" id="tdattachment">
                    <div id="divattachment4"><input class="announcement-input" style="color: white;" type="file" id="fileupload" onchange='updateList();' name="fileupload[]" multiple="multiple" value="" /></div>
                    <div id="fileList" style="display: none; text-align: left; color: white; padding-left: 5px;"></div>
                </td>
            </tr>
        </table>
        <div style="width: 50%;">
            <button style="height: 40px; width: 80px; cursor: pointer;" onclick="submitFormannouncement();">Submit</button>
        </div>
    </form><?php
}
function DeleteAnnouncement()
{
    global $db;
    $thistable = 'announcement';
    $thisdata = array('isdeleted' => true);
    $thiswhere = array('recno' => $_POST['recno']);
    $result = $db->PDOUpdate($thistable, $thisdata, $thiswhere, $_POST['recno']);
    
    if(($result))
    {
        echo 'Success';
    }
    else 
    {
        echo 'Failed';
    }
}
function SearchForannouncement()
{
    global $db;
    $canmod = "display: none;";
    if($_SESSION['profile'] == 'SU' || $_SESSION['profile'] == "SV")
    {
        $canmod = "";
    }
    $thisarec = "";
    //file_put_contents("./dodebug/debug.txt", var_dump($_POST), FILE_APPEND);
    $sql = "SELECT important FROM employee_master WHERE recno=".$_SESSION['employee_master_recno'];
    $result = $db->PDOMiniquery($sql);
    foreach($result as $rsa)
    {
        $thisarec = $rsa['important'];
    }
    $sql = "SELECT an.*, em.firstname, em.middlename, em.lastname FROM important an INNER JOIN employee_master em ";
    $sql .= "ON an.foreignkey_recno = em.recno WHERE 1=1 ";
    if($thisarec != "" && $_POST['slttype'] == 'Unread')
    {
        $sql .= "AND an.recno NOT IN ($thisarec) ";
    }
    else if($thisarec != "" && $_POST['slttype'] == 'Read')
    {
        $sql .= "AND an.recno IN ($thisarec) ";
    }    
    if($_POST['txtauthor'] != "")
    {
        $sql .= "AND (em.firstname LIKE '%".$_POST['txtauthor']."%') OR (em.middlename LIKE '%".$_POST['txtauthor']."%') OR (em.lastname LIKE '%".$_POST['txtauthor']."%') ";
    }
    if($_POST['txttitle'] != "")
    {
        $sql .= "AND an.title LIKE '%".$_POST['txttitle']."%' ";
    }
    if($_POST['txtdate'] != "")
    {
        $sql .= "AND an.expiredate = '".date('Y-m-d', strtotime($_POST['txtdate']))."' ";
    }
    if($_POST['txtdatefrom'] != '' && $_POST['txtdateto'] != '')
    {
        $sql .= "AND an.expiredate BETWEEN '".date('Y-m-d', strtotime($_POST['txtdatefrom']))."' AND '".date('Y-m-d', strtotime($_POST['txtdateto']))."' ";
    }
    $sql .= "ORDER BY an.expiredate";
    file_put_contents("./dodebug/debug.txt", $sql, FILE_APPEND); 
    $result = $db->PDOMiniquery($sql);?>
    <div style="width: 100%; overflow-y: auto; height: 740px;">
        <table id="tbl_order_data" class="tbl-order-data">
            <thead>
                <tr style="background-color: #173346;" style="font-size: .8em;">
                    <th style="width: 20px !important; position: sticky; top: 0px; z-index: 10; padding-right: 10px;"></th>
                    <th style="width: 180px !important; position: sticky; top: 0px; z-index: 10; padding-left: 10px;" >Title</th>
                    <th style="width: 100px !important; position: sticky; top: 0px; z-index: 10; padding-left: 10px;" >Author</th>
                    <th style="width: 40px; position: sticky; top: 0px; z-index: 10; padding-left: 10px;" >Expire Date</th>
                    <th style="width: 200px; position: sticky; top: 0px; z-index: 10; padding-right: 10px;">Data</th>
                    <th style="width: 80px; position: sticky; top: 0px; z-index: 10; padding-right: 10px;" >Attachment</th>
                    <th style="<?= $canmod ?>"></th>
                 </tr>
            </thead>
            <tbody><?php
                $i=1;
                if($result)
                {
                    foreach($result as $rs)
                    {?>
                        <tr id="tr<?=$i?>" style="font-size: .8em;">
                            <td class="tdnumbered" style="text-align: right; height: 40px; width: 20px !important; padding-right: 10px;" onclick="showThisannouncement(this, <?=$rs['recno']?>, <?=$i?>);"><?= $i ?></td>
                            <td style="height: 40px; width: 180px; padding-left: 10px; cursor: pointer;" onclick="showThisannouncement(<?=$rs['recno']?>);"><?= $rs['title']?></td>
                            <td style="height: 40px; width: 100px; padding-left: 10px; cursor: pointer;" onclick="showThisannouncement(<?=$rs['recno']?>);">
                                <?=$rs['firstname']." ".($rs['middlename'] != null ? $rs['middlename']." " : '').$rs['lastname'];?>
                            </td>
                            <td style="height: 40px; width: 40px; padding-left: 20px; cursor: pointer;" onclick="showThisannouncement(<?=$rs['recno']?>);"><?= date('m/d/Y', strtotime($rs['expiredate']));?></td>
                            <td style="height: 40px; width: 200px; padding-right: 20px; text-align: right; cursor: pointer;" onclick="showThisannouncement(<?=$rs['recno']?>);"><textarea rows="2" style="cursor: pointer; border: none; resize: none; width: 99%; height: 90%;" readonly><?= $rs['data']?></textarea></td>
                            <td style="height: 40px; width: 80px; padding-left: 10px; cursor: pointer;"><?php
                                $explodeattachment = explode(';', $rs['attachment']);
                                foreach($explodeattachment as $attachment)
                                {?>
                                    <a href="./uploads/announcement/<?=$attachment?>"><?=$attachment?></a><br/><?php                                        
                                }?>                                                                               
                            </td>
                            <td style="height: 40px; width: 125px; padding-left: 10px; <?=$canmod?>">
                                <button class="serviceorder-button-delete" style="height: 38px; width: 60px;" id="btnmodify_<?=$i?>" value="Modify" onclick="showThisannouncement(<?= $rs['recno'] ?>, 'Modify');">Modify</button>
                                <button class="serviceorder-button-delete" style="height: 38px; width: 60px;" id="btnisdeleted_<?=$i?>" value="Delete" onclick="deleteAnnouncement(this, <?= $rs['recno'] ?>, <?=$i?>);">Delete</button>
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
function SearchInterface()
{?>
    <div class="div-select-header" id="div_select_header">
        <div class="search-service-orders">Author:<br><input type="text" name="txtauthor" id="txtcustomer" value="" onkeyup="searchForannouncement(this);" onfocus="searchForannouncement(this);" placeholder="Type an author"/></div>
        <div class="search-service-orders">Title:<br><input type="text" name="txttitle" id="txtactype" value="" onkeyup="searchForannouncement(this);" onfocus="searchForannouncement(this);" placeholder="txttitle"/></div>
        <div class="search-service-orders">Date:<br><input type="text" name="txtdate" id="txtdate" value="" onfocus="getJDate(this);" onchange="searchForannouncement(this);" placeholder="Enter an expiredate 10/21/2022"/></div>
        <div class="search-service-orders">Type:<br>
            <select id="slttype" name="slttype" style="width: 100%;" onchange="searchForannouncement(this);">
                <option value="All">All</option>
                <option value="Read">Read</option>
                <option value="Un-read" selected>Un-read</option>
            </select>
        </div>
        <div class="search-service-orders">Date Range From:<br><input type="text" name="txtdatefrom" id="txtdatefrom" onfocus="getJDate(this);" onchange="searchForannouncement(this);" value="" placeholder="Enter expiredate 10/21/2022"/></div>
        <div class="search-service-orders">Date Range To:<br><input type="text" name="txtdateto" id="txtdateto" onfocus="getJDate(this);" onchange="searchForannouncement(this);" value="" placeholder="Enter expiredate 10/21/2022"/></div>
        <div style="float: left;">
            <button id="btnclear" value="Search" style="width: 80px; height: 52px;" onclick="searchForannouncement(this);">Clear</button>
        </div>
    </div>
    <div class="main-div-body-announcement-right-container-search" id="div_select_body_container"></div>
<?php
}
function ShowAnnouncements()
{
    global $db;
    $canmod = "display: none;";
    if($_SESSION['profile'] == 'SU' || $_SESSION['profile'] == "SV")
    {
        $canmod = "";
    }
    $thisarec = "";
    //file_put_contents("./dodebug/debug.txt", var_dump($_POST), FILE_APPEND);
    $sql = "SELECT announcement FROM employee_master WHERE recno=".$_SESSION['employee_master_recno'];
    $result = $db->PDOMiniquery($sql);
    foreach($result as $rsa)
    {
        $thisarec = $rsa['announcement'];
    }
    $thisfields = Array();
    $thiswheres = Array();
    //QueryMe($thistype=null, $thistable=null, $thisfields=null, $thiswheres=null, $thisorderby=null, $thisgroupby=null, $ordering=null)
    $sql = "SELECT an.*, em.firstname, em.middlename, em.lastname FROM important an INNER JOIN employee_master em ";
    $sql .= "ON an.foreignkey_recno = em.recno WHERE an.isactive = true AND an.isdeleted = false AND an.recno NOT IN ($thisarec) ORDER BY expiredate ASC";
    $result = $db->PDOMiniquery($sql);
    if($result) //Nott sure if isset() will check if some items is returned or at least something in asso array.
    {?>
        <div>
            <table id="tbl_order_data" class="tbl-order-data">
                <thead>
                    <tr style="background-color: #173346;" style="font-size: .8em;">
                        <th style="width: 20px !important; position: sticky; top: 0px; z-index: 10; padding-right: 10px;"></th>
                        <th style="width: 180px !important; position: sticky; top: 0px; z-index: 10; padding-left: 10px;" >Title</th>
                        <th style="width: 100px !important; position: sticky; top: 0px; z-index: 10; padding-left: 10px;" >Author</th>
                        <th style="width: 40px; position: sticky; top: 0px; z-index: 10; padding-left: 10px;" >Expire Date</th>
                        <th style="width: 200px; position: sticky; top: 0px; z-index: 10; padding-right: 10px;">Data</th>
                        <th style="width: 80px; position: sticky; top: 0px; z-index: 10; padding-right: 10px;" >Attachment</th><?php
                        if(in_array('Modify', $_SESSION['thisauth']) || in_array('Delet', $_SESSION['thisauth']))
                        {?>
                            <th style="<?= $canmod ?>"></th><?php
                        }?>
                     </tr>
                </thead>
                <tbody><?php
                    $i=1;
                    if($result)
                    {
                        foreach($result as $rs)
                        {?>
                            <tr id="tr<?=$i?>" style="font-size: .8em;">
                                <td class="tdnumbered" style="text-align: right; height: 40px; width: 20px !important; padding-right: 10px;" onclick="showThisannouncement(this, <?=$rs['recno']?>, <?=$i?>);"><?= $i ?></td>
                                <td style="height: 40px; width: 180px; padding-left: 10px; cursor: pointer;" onclick="showThisannouncement(<?=$rs['recno']?>);"><?= $rs['title']?></td>
                                <td style="height: 40px; width: 100px; padding-left: 10px; cursor: pointer;" onclick="showThisannouncement(<?=$rs['recno']?>);">
                                    <?=$rs['firstname']." ".($rs['middlename'] != null ? $rs['middlename']." " : '').$rs['lastname'];?>
                                </td>
                                <td style="height: 40px; width: 40px; padding-left: 20px; cursor: pointer;" onclick="showThisannouncement(<?=$rs['recno']?>);"><?= date('m/d/Y', strtotime($rs['expiredate']));?></td>
                                <td style="height: 40px; width: 200px; padding-right: 20px; text-align: right; cursor: pointer;" onclick="showThisannouncement(<?=$rs['recno']?>);"><textarea rows="2" style="cursor: pointer; border: none; resize: none; width: 99%; height: 90%;" readonly><?= $rs['data']?></textarea></td>
                                <td style="height: 40px; width: 80px; padding-left: 10px; cursor: pointer;"><?php
                                    $explodeattachment = explode(';', $rs['attachment']);
                                    foreach($explodeattachment as $attachment)
                                    {?>
                                        <a href="./uploads/announcement/<?=$attachment?>"><?=$attachment?></a><br/><?php                                        
                                    }?>                                                                               
                                </td><?php
                                if(in_array('Modify', $_SESSION['thisauth']) || in_array('Delete', $_SESSION['thisauth']))
                                {?>
                                    <td style="height: 40px; width: 125px; padding-left: 10px; <?=$canmod?>">
                                        <button class="serviceorder-button-delete" style="height: 38px; width: 60px;" id="btnmodify_<?=$i?>" value="Modify" onclick="showThisannouncement(<?= $rs['recno'] ?>, 'Modify');">Modify</button><?php
                                        if(in_array('Delete', $_SESSION['thisauth']))
                                        {?>
                                            <button class="serviceorder-button-delete" style="height: 38px; width: 60px;" id="btnisdeleted_<?=$i?>" value="Delete" onclick="deleteAnnouncement(this, <?= $rs['recno'] ?>, <?=$i?>);">Delete</button><?php
                                        }?>
                                    </td><?php
                                }?>
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
function Main()
{
    global $load_headers;?>
    <div class="main-div">
        <?php
        $load_headers::Load_Header_Logo_Marquee();?>
        <br><br> <?php
        $load_headers::Load_Header_Logo_Main();?>
        <div class="main-div-body-announcement">
            <table>
                <tr>
                    <td>
                        <div class="main-div-body-announcement-left" style="margin-top: -30px;">
                            <div class="main-div-body-announcement-header">Important</div>
                            <div style="float: left;"><?php
                                if(in_array('Write', $_SESSION['thisauth']) || in_array('Modify', $_SESSION['thisauth']) || in_array('Delete', $_SESSION['thisauth']))
                                {?>
                                    <div class="div-menu-service-announcement" id="div_add" onclick="addAnnouncement(this);">Add</div><?php
                                }?>
                                <div class="div-menu-service-announcement" id="div_show" onclick="showAnnouncement(this);">Show</div>
                                <div class="div-menu-service-announcement" id="div_search" onclick="searchInterface(this);">Search</div>
                            </div> 
                        </div>
                    </td>
                    <td>
                        <div id="main_div_body_announcement_right_container" class="main-div-body-order-right-container"></div>
                    </td>
                </tr>
            </table>
        </div>
        <?php
        $load_headers::Load_Footer();?>
    </div><?php
}?>