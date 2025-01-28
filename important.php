<?php
require_once("./common/page.php");
require_once("./common/pdocon.php");
require_once("./common/prompt.php");
$load_headers = new Page_Loader();
$db = new PDOCON();
$pt = new PROMPT();
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
                loadAttachment('Loading999');
                $('body').data('thisrecno', <?= $_GET['recno'] ?>);
            });
            $('body').data('thishost', '<?= $temp_host ?>');
            function acknowledgeAnnouncement(recno){
               $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=AcknowledgeAnnouncement&recno='+recno, function(result){
                    //alert(result);
                    if(result == "Failed"){
                        alert("Error:  Contact administrator");
                        return(false);
                    }
                    else{
                        //We will remove the TR and renumber.
                        //alert('here');
                        window.open('','_parent',''); 
                        window.close();
                    } 
                }); 
            }
            function loadAttachment(loading){
                if($('body').data('thisattachments').length > 0){
                    //alert('hi');
                    const dt = new DataTransfer();
                    var input = document.getElementById('fileupload');
                    splitattachments = $('body').data('thisattachments').split(';');
                    for (var i = 0; i < splitattachments.length; ++i) {
                        dt.items.add(new File([], splitattachments[i]));
                    }
                    mynewlist = dt.files;
                    input.files = mynewlist;
                    updateList(loading);
                }
            }
            function updateList(thisfilename="") {
                var input = document.getElementById('fileupload');
                var output = document.getElementById('fileList');
                var children = "";
                const dt = new DataTransfer();
                for (var i = 0; i < input.files.length; ++i) {
                    if(thisfilename == "" && thisfilename == "Loading999"){
                        children += input.files.item(i).name + '<span style="color: darkred; cursor: pointer;" onclick="updateList(\''+input.files.item(i).name+'\');">&nbsp;X</span><br/>';
                    }
                    else{
                        if(thisfilename != input.files.item(i).name){
                            children += input.files.item(i).name + '<span style="color: darkred; cursor: pointer;" onclick="updateList(\''+input.files.item(i).name+'\');">&nbsp;X</span><br/>';
                            dt.items.add(input.files[i]);
                        }                            
                    }
                }
                if(thisfilename != "" && thisfilename != 'Loading999'){
                    input.files = dt.files;
                }
                $("#fileList").show();
                output.innerHTML = children;
                if(thisfilename != "Loading999")
                //Since we are in announcement AND thisfilename is not empty, that means we are attempting
                //to remove an attachment.  We must remove it from the database, therefore we do an update here.
                submitFormannouncement();
            }
            function submitFormannouncement(){
                var thisFrmdata = new FormData($('#frmAddannouncement')[0]);
                thisFrmdata.append('from', 'important');
                thisFrmdata.append('status', 'Modify');
                thisFrmdata.append('recno', $('body').data('thisrecno'));
                $.ajax({
                    type: 'POST',
                    url: './common/upload.php',
                    data: thisFrmdata,
                    processData: false,
                    contentType: false,
                    success: function(result) {
                        if(result == 'Failed'){
                            alert('Failed to upload.');
                            return(false);
                        }
                        else{
                            alert('Successfully updated attachment.');
                            location.reload();
                            return(false);
                        }                            
                    }
                });
            }
            function updateAttachment(obj){
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=UpdateAttachment&recno='+$('body').data('thisrecno')+'&field='+$(obj).prop('id')+'&value='+$(obj).val()+'&from=important', function(result){
                    //alert(result);
                    if(result == 'Failed'){
                        alert('Failed to update.  Contact administrator.');
                        return(false);
                    }
                }); 
            }
        </script>
    </head>
    <body style="overflow-y: hidden;">
        <?php
            Main();
        ?>
    </body>
</html>
<?php
function UpdateAttachment()
{
    global $db; //PDOUpdate($thistable=null, $thisdata = null, $thiswhere = null)
    $thistable = $_POST['from']; //announcement || important
    if($_POST['field'] == 'txtexpiredate')
    {
        $thisdata = array(substr($_POST['field'], 3) => date('Y-m-d', strtotime($_POST['value'])));
    }
    else
    {
        $thisdata = array(substr($_POST['field'], 3) => $_POST['value']);
    }
    $thiswhere = array('recno' => $_POST['recno']);
    $result = $db->PDOUpdate($thistable, $thisdata, $thiswhere, $_POST['recno']);
    if($result)
    {
        echo "Success";
    }
    else
    {
        echo "Failed";
    }
}
function AcknowledgeAnnouncement()
{
    global $db;
    
    $sql = "UPDATE employee_master SET important = CONCAT_WS(',', important, ".$_POST['recno'].") WHERE recno=".$_SESSION['employee_master_recno'];
    //file_put_contents("./dodebug/debug.txt", $sql, FILE_APPEND);
    $result = $db->PDOMiniquery($sql);
    if(($result))
    {
        echo 'Success';
    }
    else 
    {
        echo 'Failed';
    }
}
function Main()
{
    global $db, $load_headers, $pt;
    $curattachment = [];
    $canmod = "readonly";
    $thisonchange = "";
    if(($_SESSION['profile'] == 'SU' || $_SESSION['profile'] == "SV") && $_POST['status'] == "Modify")
    {
        $canmod = "modify";
        $thisonchange = 'onchange="updateAttachment(this, \'Modify\');"';
    }?>
    <div class="main-div"><?php
        $load_headers::Load_Header_Logo_Marquee();?>
        <br><br> <?php
        $load_headers::Load_Header_Logo_Main();
        
        $sql = "SELECT an.*, em.firstname, em.middlename, em.lastname FROM important an INNER JOIN employee_master em ";
        $sql .= "ON an.foreignkey_recno = em.recno WHERE an.recno = ".$_GET['recno'];
        $result = $db->PDOMiniquery($sql);
        foreach($result as $rs)
        {?>        
            <div class="main-div-body">
                <div style="width: 800px;">
                    <div style="width: 100%; height: 40px; font-size: 1.5em; color: black; text-align: center; font-weight: bold;">Announcement</div>
                    <form name="frmAddannouncement" id="frmAddannouncement" enctype="multipart/form-data" action="<?=$_SERVER['PHP_SELF'];?>" method="post" onsubmit="return false">
                        <table id="tbl_atachment" style="width: 100%;" class="tbl-announcement">
                            <tr><td id="td1" class="announcement-lbl">Author:</td><td><input class="required" type="text" style="width: 98.8%;" value="<?=$rs['firstname']." ".($rs['middlename'] != null ? $rs['middlename']." " : '').$rs['lastname'];?>" readonly></td></tr>
                            <tr><td id="td1" class="announcement-lbl">Title:</td><td><input id="txttitle" class="required" type="text" style="width: 98.8%;" value="<?=$rs['title']?>" <?=$thisonchange?> <?=$canmod?>></td></tr><?php
                            if($canmod == "modify")
                            {?>
                                <tr><td id="td2" class="announcement-lbl">Expire Date:</td><td><input id="txtexpiredate" type="text" class="required" style="width: 98.8%;" onfocus="getJDate(this);" <?=$thisonchange?> value="<?=date('m/d/Y', strtotime($rs['expiredate']));?>" <?=$canmod?>></td></tr><?php
                            }
                            else
                            {?>
                                <tr><td id="td2" class="announcement-lbl">Expire Date:</td><td><input id="txtexpiredate" type="text" class="required" style="width: 98.8%;" value="<?=date('m/d/Y', strtotime($rs['expiredate']));?>" readonly></td></tr><?php
                                
                            }?>
                            <tr><td id="td3" class="announcement-lbl">Data:</td><td><textarea class="required" style="width: 99%; height: 300px; resize: none;" id="txtdata" name="txtdata" <?=$thisonchange?> <?=$canmod?>><?=$rs['data']?></textarea></td></tr>
                            <tr>
                                <td id="td4" class="announcement-lbl">
                                    Attachment:              
                                </td>
                                <td style="height: 160px;" id="tdattachment"><?php
                                    $explodeattachment = explode(';', $rs['attachment']);?>
                                    <script type="text/javascript">
                                        $('body').data('thisattachments', '<?=$rs['attachment']?>');
                                    </script><?php
                                    foreach($explodeattachment as $attachment)
                                    {
                                        if($canmod == "readonly")
                                        {?>
                                            <a href="./uploads/announcement/<?=$attachment?>"><?=$attachment?></a><br/><?php 
                                        }
                                    }
                                    if($canmod == "modify")
                                    {?>
                                        <div id="divattachment4"><input class="announcement-input" style="color: white;" type="file" id="fileupload" onchange='updateList();' name="fileupload[]" multiple="multiple" value="" /></div>
                                        <div id="fileList" style="text-align: left; color: white; padding-left: 5px;">
                                        </div><?php
                                    }?> 
                                </td><?php
                                ?>
                            </tr>
                        </table><?php
                        if($canmod == "readonly")
                        {?>
                            <td style="height: 40px; width: 40px; padding-left: 10px;">
                                <button class="announcement-button-acknowledge" id="btnacknowledge" value="Delete" onclick="acknowledgeAnnouncement(<?= $_GET['recno'] ?>);">Click to acknowledge you have read this announcement and close this tab/window.</button>
                            </td><?php
                        }?>
                    </forrm>
                </div>
            </div><?php
        }
        $load_headers::Load_Footer();?>
    </div><?php
}