<?php
require_once("./common/page.php");
require_once("./common/pdocon.php");
require_once("./common/prompt.php");
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
            $('body').data('thishost', '<?= $temp_host ?>');
            function noteFul(recno, from){
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=NoteFul&recno='+recno+'&from='+from, function(result){
                    //alert(result);
                    $("#divnote_container").html(result);
                });
            }
            function updateNote(obj){
               if($(obj).prop('id') == "txtexpiredate" || $(obj).prop('id') == "txtnote"){
                    thisvalue = $(obj).val();
                }
                else if($(obj).prop('id') == "chkisactive"){
                    if($(obj).is(":checked")){
                       thisvalue = true; 
                    }
                    else{
                        thisvalue = false;
                    }  
                }
                else{
                    //We handle the Delete button here
                    thisvalue = true;
                }
               $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=UpdateNote&recno='+$('body').data('recno')+'&field='+$(obj).prop('id')+'&value='+thisvalue, function(result){
                    alert(result);
                    if(result == "Failed"){
                        alert("Failed to update.  Contact your administrator");
                        return(false);
                    }
                    if($(obj).prop('id') == "btnisdeleted" || $(obj).prop('id') == "chkisactive"){
                        window.location.href = "fidNote.php?from=Modify";
                    }
                });  
            }
            function submitNote(){
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=SubmitNote&'+$("#tblnew_note :input").serialize(), function(result){
                    if(result == "Success"){
                        alert("Successfully added a note.");
                    }
                    else{
                        alert("failed to add note.  Contact your administrator for assistance.");
                        return(false);
                    }
                    window.location.href = "fidNote.php"; //reload in case user wants to add more notes.
                }); 
            }
            function addNote(){
                window.location.href = "fidNote.php"; //reload in case user wants to add more notes.
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
function SubmitNote()
{
    //file_put_contents("./dodebug/debug.txt", var_dump($_POST), FILE_APPEND);
    global $db, $load_headers;
  
    $thisfields = Array();
    $thistable = "note";
    $sendstatus= "";
    foreach($_POST as $key => $value)
    {
        if($key != "cmd")
        {
            //file_put_contents("./dodebug/debug.txt", "key: ".$key, FILE_APPEND);
            if(substr($key, 3) == "expiredate" && !is_null($value))
            {
                //handles expiredate field
                $thisfields[substr($key, 3)] =  date('Y-m-d', strtotime($value));
            }
            else
            {
                //We assume note field
                $thisfields[substr($key, 3)] =  $value;
            }
        }
    } 
    $thisfields['author'] = $_SESSION['employee_master_recno'];
    $result = $db->PDOInsert($thistable, $thisfields);
    if($result == "Success")
    {
       echo "Success";
    }
    else
    {
        echo "Failed";
    }
}
function UpdateNote()
{
    
    global $db, $load_headers; //PDOUpdate($thistable=null, $thisdata = null, $thiswhere = null)
    $thistable = "note";
    if($_POST['field'] == "txtexpiredate")
    {
        //date
        $thisdata = array(substr($_POST['field'],3) => date('Y-m-d', strtotime($_POST['value'])));
    }
    else if($_POST['field'] == "chkisactive" || $_POST['field'] == "btnisdeleted")
    {
        //true false
        if($_POST['value'] == "true")
        {
            $thisdata = array(substr($_POST['field'],3) => true);
        }
        else
        {
            $thisdata = array(substr($_POST['field'],3) => false);
        }
    }
    else
    {
        //string
        $thisdata = array(substr($_POST['field'],3) => $_POST['value']);
    }
    $thiswhere = array('recno' => $_POST['recno']);
    $rows = $db->PDOUpdate($thistable, $thisdata, $thiswhere, $_POST['recno']);
    if(!isset($rows))
    {
        echo "Failed";
    }
    else
    {
        echo "Success";
    }
}
function Main()
{
    global $db, $load_headers;
    if(array_key_exists('from', $_POST))
    {
        $sql = "SELECT no.*, em.firstname, em.middlename, em.lastname FROM note no INNER JOIN employee_master em ON no.author=em.recno WHERE no.expiredate >= CURDATE() AND no.isactive = true AND no.isdeleted = false ORDER BY no.entrydate";
        $result = $db->PDOMiniquery($sql);
    }?>
    <div class="main-div"><?php
        $load_headers::Load_Header_Logo_Marquee();?>
        <br><br> <?php
        $load_headers::Load_Header_Logo_Main();?>        
        <div class="main-div-body">
            <div id="divnote_container" style="width: 100%; height: 790px;"><?php
                if(array_key_exists('from', $_GET))
                {?>
                    <table id="tblcustomerdata" style="width: 60%; margin: 0 auto;">
                        <thead>
                        <tr style="background-color: #173346; text-align: left;">
                            <th style="width: 40px;">No.</th>
                            <th style="width: 260px; padding-left: 10px;">Author</th>
                            <th style="padding-left: 10px; padding-right: 10px;">
                                Note
                                <button class="button" style="height: 30px; width: 40px; cursor: pointer;" id="btnaddnote" name="btnaddnote" onclick="addNote();" title="Click to add new note" value="Add Note" ><div style="font-size: 2em;">+</div></button>
                            </th>
                            <th style="width: 100px;">Entry Date</th>
                            <th style="width: 100px;">Expire Date</th>
                        </tr>
                        </thead>
                        <tbody id="tblnote_body"><?php
                            
                                $i=1;
                                foreach($result as $rs)
                                {
                                    $realname= $rs['firstname'].' '.($rs['middlename'] != "" ? $rs['middlename'].' ' : '').$rs['lastname'];
                                    $recno = $rs['recno'];
                                    $thisauthor = $rs['author'];
                                    $thisnote = $rs['note'];
                                    $thisexpiredate = $rs['expiredate'];?>
                                    <tr style="background-color: lightgray; text-align: left; height: 20px; cursor: pointer; border: 1px solid;" onclick="noteFul(<?=$recno?>, 'Modify');">
                                        <td><?=$i?></td>
                                        <td style="padding-left: 10px;"><?=$realname?></td>
                                        <td style="padding-left: 10px; padding-right: 10px;"><?=$thisnote?></td>
                                        <td><?= date('d M y', strtotime($rs['entrydate']))?></td>
                                        <td><?= date('d M y', strtotime($rs['expiredate']))?></td>
                                    </tr><?php
                                    $i++;
                                }?>
                        </tbody>
                    </table><?php
                }
                else
                {
                    NoteFul();
                }?>
            </div>
        </div>
        <?php
        $load_headers::Load_Footer();?>
    </div><?php
}
function NoteFul()
{
    
    if(array_key_exists('from', $_POST) && $_POST['from'] == "Modify")
    {
        global $db, $load_headers;
        $sql = "SELECT note.*, em.firstname, em.middlename, em.lastname FROM note INNER JOIN employee_master em ON note.author = em.recno WHERE note.recno = '".$_POST['recno']."' AND note.expiredate >= CURDATE() AND note.isactive = true AND note.isdeleted = false ORDER BY note.entrydate";
        //file_put_contents("./dodebug/debug.txt", $sql, FILE_APPEND);
        $result = $db->PDOMiniquery($sql);
    }
    $recno = "";
    $thisauthor = "";
    $thisnote = "";
    $thisexpiredate = "";
    $isactive = "";
    $isdeleted = "";
    $thisfunction = "";
    $datefrom = "Load";
    $realname = "";
    $tempname = "";
    $isreadonly = "";
    if(array_key_exists('from', $_POST) && $_POST['from'] == "Modify")
    {
        foreach($result as $rs)
        {
            $recno = $rs['recno'];
            $thisauthor = $rs['author'];
            $thisnote = $rs['note'];
            $thisexpiredate = date('m/d/Y', strtotime($rs['expiredate']));
            $isactive = 'checked';
            $isdeleted = $rs['isdeleted'];
            $thisfunction = "onchange='updateNote(this)'";
            $datefrom = "Change";
            $realname= $rs['firstname'].' '.($rs['middlename'] != "" ? $rs['middlename'].' ' : '').$rs['lastname'];
            $isreadonly = "readonly";?>

            <script type="text/javascript">
                $('body').data('recno', <?=$rs['recno']?>);
            </script><?php
        }
    }?>
    <table id="tblnew_note" name="tblnew_note" class="tbl-new-note">
        <tr>
            <td class="tbl-note-header" colspan="2">Note</td>
        </tr><?php
        if(array_key_exists('from', $_POST) && $_POST['from'] == "Modify")
        {?>
            <tr>
                <td class="tbltr-note-lbl">Author: </td>
                <td class="noteinput"><input type="text" class="required" id="txtauthor" name="txtauthor" <?=$thisfunction?> value="<?=$realname?>" <?=$isreadonly?> autofocus/></td>
            </tr><?php
        }?>
        <tr>
            <td class="tbltr-note-lbl">Expire Date: </td><?php
            if(array_key_exists('from', $_POST) && $_POST['from'] == "Modify")
            {?>
                <td class="noteinput"><input type="text" class="required" id="txtexpiredate" name="txtexpiredate" onfocus="getJDate(this);" onchange="updateNote(this);" value="<?=$thisexpiredate?>" placeholder="dd/mm/yyyy ex: 01/22/2022" />dd/mm/yyyy ex: 01/22/2022</td><?php
            }
            else
            {?>
                <td class="noteinput"><input type="text" class="required" id="txtexpiredate" name="txtexpiredate" onfocus="getJDate(this);" value="<?=$thisexpiredate?>" placeholder="dd/mm/yyyy ex: 01/22/2022" />dd/mm/yyyy ex: 01/22/2022</td><?php
            }?>                
        </tr><?php
        if(array_key_exists('from', $_POST) && $_POST['from'] == "Modify")
        {?>
            <tr>
                <td class="tbltr-note-lbl">Active: </td>
                <td class="noteinput"><input type="checkbox" class="required"  style="height: 40px; width: 40px;" id="chkisactive" name="chkisactive" <?=$thisfunction?> <?=$isactive?> /></td>
            </tr>
            <tr>
                <td class="tbltr-note-lbl">Delete: </td>
                <td class="noteinput"><button class="lastname button" style="height: 40px;" id="btnisdeleted" name="btnisdeleted" onclick="updateNote(this);" value="Delete" >Delete</button></td>
            </tr><?php
        }?>
        <tr>
            <td class="tbltr-note-lbl">Note: </td>
            <td class="noteinput"><textarea id="txtnote" name="txtnote"style="width: 98%; resize: none;" row="4" <?=$thisfunction?>><?=$thisnote?></textarea></td>
        </tr><?php
        if(!array_key_exists('from', $_POST))
        {?>
            <tr>
                <td class="noteinput" colspan="2"><button class="button" style="height: 40px; display: block; margin: auto;" id="btnsubmit" name="btnsubmit" value="Submit" onclick="submitNote();">Submit</button></td>
            </tr>
        <?php
        }?>
    </table><?php
}