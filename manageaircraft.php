<?php
require_once("./common/page.php");
require_once("./common/pdocon.php");
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
            function addAircraft(obj){
                $(".div-menu-manageaircraft").each(function(){
                    $(this).css('background-color', '#1079B1');
                    $(this).css('color', 'white');
                })
                $(obj).css("background-color", "white");
                $(obj).css('color', 'black');
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=ManageAircraft&from=Add', function(result){
                    $("#main_div_body_manageaircraft_right_container").html(result);
                });
            }
            function manageAircraft(obj, lineno, from){
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=ManageAircraft&recno='+$('body').data(lineno)+'&from='+from, function(result){
                    $("#main_div_body_manageaircraft_right_container").html(result);
                });
            }
            function submitAircraft(){
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=SubmitAircraft&'+$("#tblmanageaircraft :input").serialize(), function(result){
                    if(result == "Failed"){
                        alert("Failed to add aircraft.  Please contact your administrator.");
                        return(false);
                    }
                    else
                    {
                        alert("Successfully Added.");
                        $("#tblmanageaircraft").find('input:text').val('');
                    }
                });
            }
            function modifyAircraft(obj){
                $(".div-menu-manageaircraft").each(function(){
                    $(this).css('background-color', '#1079B1');
                    $(this).css('color', 'white');
                })
                $(obj).css("background-color", "white");
                $(obj).css('color', 'black');
               $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=ModifyAircraft&from=Modify', function(result){
                    $("#main_div_body_manageaircraft_right_container").html(result);
               }); 
            }
            function deleteAircraft(obj, lineno){
                $("#tr"+lineno).remove();
                i=1;
                $('.count-td').each(function(){
                   $(this).text(i); 
                   i++;
                });
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=DeleteAircraft&recno='+$('body').data(lineno), function(result){
                   if(result == "Failed"){
                        alert('Failed to delete this aircraft.  Please contact your administrator.');
                        return(false);
                    }
                });
            }
            function deleteThisaircraft(obj, recno){
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=DeleteAircraft&recno='+$('body').data(recno), function(result){
                   if(result == "Failed"){
                        alert('Failed to delete this aircraft.  Please contact your administrator.');
                        return(false);
                    }
                    else
                    {
                        window.location.href = "manageaircraft.php";
                    }
                });
            }
            function updateAircraft(obj, recno){
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=UpdateAircraft&recno='+$('body').data(recno)+'&field='+$(obj).prop('id')+'&value='+$(obj).val(), function(result){
                    alert(result);
                   if(result == "Failed"){
                        alert('Failed to update.  Please contact your administrator.');
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
function UpdateAircraft()
{
    global $db;
    $thistable = 'aircraft';
    $thisdata = array(substr($_POST['field'],3) => $_POST['value']);
    $thiswheres = array('recno' => $_POST['recno']);
    $result = $db->PDOUpdate($thistable, $thisdata, $thiswheres, $_POST['recno']);
    if(isset($result))
    {
        echo 'Success';
    }
    else 
    {
        echo 'Failed';
    }
}
function DeleteAircraft()
{
    global $db;
    $thistable = 'aircraft';
    $thisdata = array('isdeleted' => true);
    $thiswheres = array('recno' => $_POST['recno']);
    $result = $db->PDOUpdate($thistable, $thisdata, $thiswheres, $_POST['recno']);
    if(isset($result))
    {
        echo 'Success';
    }
    else 
    {
        echo 'Failed';
    }
}
function ModifyAircraft()
{
    global $db;
    if(array_key_exists('from', $_POST))
    {
        $thistable = 'aircraft';
        $thisfields = array('All');
        $thiswheres = array('isdeleted' => false);
        $thisorderby = array('name');
        $result = $db->PDOQuery($thistable, $thisfields, $thiswheres, $thisorderby);
    }?>
    <table id="tblaircraft" style="width: 50%; text-align: left;">
        <thead>
        <tr style="background-color: #173346; text-align: left;">
            <th style="width: 40px;">No.</th>
            <th style="width: 260px; padding-left: 10px;">Name</th>
            <th style="padding-left: 10px; padding-right: 10px;">
                Aircraft
                <button class="button" style="height: 30px; width: 40px; cursor: pointer;" id="btnaddnote" name="btnaddnote" onclick="addAircraft();" title="Click to add new note" value="Add Note" ><div style="font-size: 2em;">+</div></button>
            </th>
            <th style="width: 50px; padding-left: 10px;"></th>
        </tr>
        </thead>
        <tbody id="tblnote_body"><?php
                $i=1;
                foreach($result as $rs)
                {?>
                    <script type="text/javascript">
                        $('body').data('lineno'+<?=$i?>, <?=$rs['recno']?>);
                    </script> 
                    <tr id="trlineno<?=$i?>" style="background-color: lightgray; text-align: left; height: 20px; cursor: pointer; border: 1px solid;">
                        <td class="count-td" onclick="noteFul(<?=$rs['recno']?>, 'Modify');"><?=$i?></td>
                        <td onclick="manageAircraft(this, 'lineno'+<?=$i?>, 'Modify');" style="padding-left: 10px;"><?=$rs['name']?></td>
                        <td onclick="manageAircraft(this, 'lineno'+<?=$i?>, 'Modify');" style="padding-left: 10px; padding-right: 10px;"><?=$rs['actype']?></td>
                        <td onclick="manageAircraft(this, 'lineno'+<?=$i?>, 'Modify');" style="padding-left: 10px;">
                            <button class="button" style="height: 30px; cursor: pointer;" id="btndeleteaircraft" name="btndeleteaircraft" onclick="deleteAircraft(this, 'lineno'+<?=$i?>);" title="Click to delete this aircraft" >
                                <div>Delete</div>
                            </button></td>
                    </tr><?php
                    $i++;
                }?>
        </tbody>
    </table>
<?php
}
function SubmitAircraft()
{
    global $db;
    $thistable = "aircraft";
    $thisdata = array('name' => $_POST['txtname'], 'actype' => $_POST['txtactype']);
    $result = $db->PDOInsert($thistable, $thisdata);
    if(isset($result))
    {
        echo 'Success';
    }
    else
    {
        echo 'Failed';
    }
}
function ManageAircraft()
{
    global $db;
    $thisname = "";
    $thisaircraft = "";
    $thisfunction = "";
    if($_POST['from'] == 'Modify')
    {
        $recno = $_POST['recno'];
        $thisfunction = 'onchange="updateAircraft(this, \'temprecno\');"';
        
        $thistable = 'aircraft';
        $thisfields = array('name', 'actype');
        $thiswheres = array('recno' => $recno);
        $result = $db->PDOQuery($thistable, $thisfields, $thiswheres);   
        foreach($result as $rs)
        {
            $thisname = $rs['name'];
            $thisaircraft = $rs['actype'];
        }
    }?>
    <table id="tblmanageaircraft" name="tblmanageaircraft" class="tbl-profile">
        <tr><td class="user-profile-lbl tbl-profile-lbl">Name:</td><td><input class="user-profile-input" type="text" id="txtname" name="txtname" <?=$thisfunction?> value="<?=$thisname;?>" placeholder="Airbus A220, Boeing 787-800, Airbus A380" /></td></tr>
        <tr><td class="user-profile-lbl tbl-profile-lbl">Aircraft Type:</td><td><input class="user-profile-input" type="text" id="txtactype" name="txtactype" <?=$thisfunction?> value="<?=$thisaircraft;?>" placeholder="Ex: 787-8, A330, A380"/></td></tr><?php
        if($_POST['from'] == 'Add')
        {?>
            <tr><td class="tbl-profile-lbl" colspan="2" style="width: 100%; text-align: center;">
                <button type="button" onclick="submitAircraft();">Submit</button>
            </tr><?php
        }
        if($_POST['from'] == 'Modify')
        {?>
            <script type="text/javascript">
                $('body').data('temprecno', <?=$recno?>);
            </script>
            <tr><td class="tbl-profile-lbl" colspan="2" style="width: 100%; text-align: center;">
                    <button class="button" style="height: 30px; cursor: pointer;" id="btndeleteaircraft" name="btndeleteaircraft" onclick="deleteThisaircraft(this, 'temprecno');" title="Click to delete this aircraft" >Delete</button>
            </tr><?php
        }?>
    </table><?php
}
function Main()
{
    global $load_headers;?>
    <div class="main-div">
        <?php
        $load_headers::Load_Header_Logo_Marquee();?>
        <br><br> <?php
        $load_headers::Load_Header_Logo_Main();?>
        <div class="main-div-body-manageaircraft">
            <table>
                <tr>
                    <td>
                        <div class="main-div-body-manageaircraft-left" style="margin-top: -30px;">
                            <div class="main-div-body-manageaircraft-header">Manage Aircraft</div>
                            <div style="float: left;">
                                <div class="div-menu-manageaircraft" onclick="addAircraft(this);">Add</div>
                                <div class="div-menu-manageaircraft" onclick="modifyAircraft(this);">Modify</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div id="main_div_body_manageaircraft_right_container" class="main-div-body-manageaircraft-right-container"></div>   
                    </td>
                </tr>
            </table>
        </div>
        <?php
        $load_headers::Load_Footer();?>
    </div><?php
}?>