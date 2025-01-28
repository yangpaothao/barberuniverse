<?php
require_once("./common/page.php");
require_once("./common/pdocon.php");
require_once("./common/prompt.php");
$load_headers = new Page_Loader();
$db = new PDOCON();
$pt = new PROMPT();
if(count($_POST) > 0)
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
                var d = new Date();
                var thisday = d.getDate();
                var thismonth = d.getMonth();
                var thisyear = d.getFullYear();
                $('body').data('currentmonth', (thismonth+1)+"/"+thisday+"/"+thisyear); 
                //by default we will set this to current month/year, also added 1 tot thismonth cuz in javascript, month starts at 0, adding one will give us a accurate rep month.
                //alert('0: '+$('body').data('currentmonth'));
                $("#txtdates").multiDatesPicker('show');
            });
            function addFlight(obj){
                $(".div-menu-manageaircraft").each(function(){
                    $(this).css('background-color', '#1079B1');
                    $(this).css('color', 'white');
                })
                $(obj).css("background-color", "white");
                $(obj).css('color', 'black');
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=AddFlight&from=Add', function(result){
                    $("#main_div_body_manageaircraft_right_container").html(result);
                });
            }
            function manageAircraft(obj, lineno, from){
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=ManageAircraft&recno='+$('body').data(lineno)+'&from='+from, function(result){
                    $("#main_div_body_manageaircraft_right_container").html(result);
                });
            }
            function submitAddflight(){
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=SubmitFlight&'+$("#tblmanageaddflight :input").serialize(), function(result){
                    if(result == "Failed"){
                        alert("Failed to add flight.  Please contact your administrator.");
                        return(false);
                    }
                    else
                    {
                        alert("Successfully Added.");
                        $("#tblmanageaddflight").find('input:text').val('');
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
            function deleteThisflight(obj, recno){
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
            function updateFlight(obj, recno){
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=UpdateAircraft&recno='+$('body').data(recno)+'&field='+$(obj).prop('id')+'&value='+$(obj).val(), function(result){
                   if(result == "Failed"){
                        alert('Failed to update.  Please contact your administrator.');
                        return(false);
                    }
                });
            }
            function updateDays(obj, temprecno){
                thisid = $(obj).prop('id');
                if(thisid == "btncleardates"){
                    $("#txtdates").multiDatesPicker('resetDates','picked');
                    return(false);
                }
                else{  
                    //alert('1: '+$('body').data('currentmonth'));
                    $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=UpdateDays&thisid='+thisid+'&curdate='+$('body').data('currentmonth'), function(result){
                        splitresult = result.split(',');
                        temparray = new Array();
                        for(i=0; i<splitresult.length; i++)
                        {
                            //alert(new Date(splitresult[i].trim()));
                            temparray.push(new Date(splitresult[i])); 
                        }
                        $("#txtdates").multiDatesPicker({  
                            addDates: temparray,
                            
                        });
                    });
                    //alert('2: '+$('body').data('currentmonth'));
                }
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
function UpdateDays()
{
    //$_POST['curdate'] = will come in as '10/2022'
    $thisday = date('m/d/Y', strtotime($_POST['curdate']));
    $thismonth = date('m', strtotime($thisday)); 
    
    //file_put_contents("./dodebug/debug.txt", $_POST['curdate'].' into '.$thisday, FILE_APPEND); //passed
    $datestr = "";
    $i = 1;
    switch($_POST['thisid']){
        case "btnmondays":
            file_put_contents("./dodebug/debug.txt", date('m', strtotime($thisday))." == ".$thismonth, FILE_APPEND); //passed
            while(date('m', strtotime($thisday)) == $thismonth)
            {
                $thisweekday = date('N', strtotime($thisday)); //1 = Monday, 7 = Sunday
                file_put_contents("./dodebug/debug.txt", 'this week: '.$thisweekday, FILE_APPEND);
                if($thisweekday == 1)
                {
                    if($datestr == "")
                    {
                        $datestr = $thisday;
                    }
                    else
                    {
                        $datestr .= ", ".$thisday;
                    }
                    //$datestr[] = new Date($thisday);
                    $thisday = date('m/d/Y', strtotime("+7 day", strtotime($thisday)));
                }
                else
                {
                    $thisday = date('m/d/Y', strtotime('next monday', strtotime($thisday)));
                }
            }
            //file_put_contents("./dodebug/debug.txt", $datestr, FILE_APPEND);
            break;
        case "btntuesdays":
            while(date('m', strtotime($thisday)) == $thismonth)
            {
                $thisweekday = date('N', strtotime($thisday)); //1 = Monday, 7 = Sunday
                if($thisweekday == 2)
                {
                    if($datestr == "")
                    {
                        $datestr = $thisday;
                    }
                    else
                    {
                        $datestr .= ", ".$thisday;
                    }
                    //$datestr[] = new Date($thisday);
                    $thisday = date('m/d/Y', strtotime("+7 day", strtotime($thisday)));
                }
                else
                {
                    $thisday = date('m/d/Y', strtotime('next tuesday', strtotime($thisday)));
                }
            }
            break;
        case "btnwednesdays":
            while(date('m', strtotime($thisday)) == $thismonth)
            {
                $thisweekday = date('N', strtotime($thisday)); //1 = Monday, 7 = Sunday
                if($thisweekday == 3)
                {
                    if($datestr == "")
                    {
                        $datestr = $thisday;
                    }
                    else
                    {
                        $datestr .= ", ".$thisday;
                    }
                    //$datestr[] = new Date($thisday);
                    $thisday = date('m/d/Y', strtotime("+7 day", strtotime($thisday)));
                }
                else
                {
                    $thisday = date('m/d/Y', strtotime('next wednesday', strtotime($thisday)));
                }
            }
            break;
        case "btnthursdays":
            while(date('m', strtotime($thisday)) == $thismonth)
            {
                $thisweekday = date('N', strtotime($thisday)); //1 = Monday, 7 = Sunday
                if($thisweekday == 4)
                {
                    if($datestr == "")
                    {
                        $datestr = $thisday;
                    }
                    else
                    {
                        $datestr .= ", ".$thisday;
                    }
                    //$datestr[] = new Date($thisday);
                    $thisday = date('m/d/Y', strtotime("+7 day", strtotime($thisday)));
                }
                else
                {
                    $thisday = date('m/d/Y', strtotime('next thursday', strtotime($thisday)));
                }
            }
            break;
        case "btnfridays":
            while(date('m', strtotime($thisday)) == $thismonth)
            {
                $thisweekday = date('N', strtotime($thisday)); //1 = Monday, 7 = Sunday
                if($thisweekday == 5)
                {
                    if($datestr == "")
                    {
                        $datestr = $thisday;
                    }
                    else
                    {
                        $datestr .= ", ".$thisday;
                    }
                    //$datestr[] = new Date($thisday);
                    $thisday = date('m/d/Y', strtotime("+7 day", strtotime($thisday)));
                }
                else
                {
                    $thisday = date('m/d/Y', strtotime('next friday', strtotime($thisday)));
                }
            }
            break;
        case "btnsaturdays":
            while(date('m', strtotime($thisday)) == $thismonth)
            {
                $thisweekday = date('N', strtotime($thisday)); //1 = Monday, 7 = Sunday
                if($thisweekday == 6)
                {
                    if($datestr == "")
                    {
                        $datestr = $thisday;
                    }
                    else
                    {
                        $datestr .= ", ".$thisday;
                    }
                    //$datestr[] = new Date($thisday);
                    $thisday = date('m/d/Y', strtotime("+7 day", strtotime($thisday)));
                }
                else
                {
                    $thisday = date('m/d/Y', strtotime('next saturday', strtotime($thisday)));
                }
            }
            break;
        case "btnsundays":
            while(date('m', strtotime($thisday)) == $thismonth)
            {
                $thisweekday = date('N', strtotime($thisday)); //1 = Monday, 7 = Sunday
                if($thisweekday == 7)
                {
                    if($datestr == "")
                    {
                        $datestr = $thisday;
                    }
                    else
                    {
                        $datestr .= ", ".$thisday;
                    }
                    //$datestr[] = new Date($thisday);
                    $thisday = date('m/d/Y', strtotime("+7 day", strtotime($thisday)));
                }
                else
                {
                    $thisday = date('m/d/Y', strtotime('next sunday', strtotime($thisday)));
                }
            }
            break;
        case "btnalldays":
            $numofdays = date('t', strtotime($thisday));
            $curday = date('d', strtotime($thisday));
            $curmonth = date('m', strtotime($thisday));
            $curyear = date('Y', strtotime($thisday));
            for($i=$curday; $i<=$numofdays; $i++)
            {
                if($datestr == "")
                {
                    $datestr = $curmonth."/".$i."/".$curyear;
                }
                else
                {
                    $datestr .= ", ".$curmonth."/".$i."/".$curyear;
                }
            }
            break;
        case "btnallweekdays":
            //NO WEEKENDS
            $numofdays = date('t', strtotime($thisday));
            $curday = date('d', strtotime($thisday));
            $curmonth = date('m', strtotime($thisday));
            $curyear = date('Y', strtotime($thisday));
            for($i=$curday; $i<=$numofdays; $i++)
            {
                $thisdate = date('N', strtotime($curmonth."/".$i."/".$curyear));
                if($thisdate != 6 && $thisdate != 7)
                {
                    if($datestr == "")
                    {
                        $datestr = $curmonth."/".$i."/".$curyear;
                    }
                    else
                    {
                        $datestr .= ", ".$curmonth."/".$i."/".$curyear;
                    }
                }
            }
            break;
        default:
             break;
     }
     echo $datestr;
}
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
    <table id="tblaircraft" style="width: 50%; margin: 0 auto;">
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
                        <td class="count-td" onclick="noteFul(<?=$recno?>, 'Modify');"><?=$i?></td>
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
function SubmitFlight()
{
    global $db;
    $thistable = "flight_schedule";
    $thisdata = array('customer' => $_POST['sltcustomer'], 'actype' => $_POST['sltname'], 'flightnumber' => $_POST['txtflightnumber'], 
        'schedulearrival' => $_POST['txtschedulearrival'], 'scheduledeparture' => $_POST['txtscheduledeparture'], 'dates' => $_POST['txtdates']);
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
function AddFlight()
{
    global $db, $pt;
    $thisname = "";
    $thisaircraft = "";
    $thisfunctionchange = "";
    $thisfunctionclick = "";
    $thisflightno = '';
    $thisschedulea = ''; 
    $thisscheduled = '';
    if($_POST['from'] == 'Modify')
    {
        $recno = $_POST['recno'];
        $thisfunctionchange = 'onchange="updateFlight(this, \'temprecno\');"';
        $thistable = 'flight_schedule';
        $thisfields = array('All');
        $thiswheres = array('recno' => $recno);
        $result = $db->PDOQuery($thistable, $thisfields, $thiswheres);   
        foreach($result as $rs)
        {
            $thisname = $rs['name'];
            $thisaircraft = $rs['actype'];
            $thisflightno = $rs['flightnumber'];
            $thisschedulea = $rs['schedulearrival'];
            $thisscheduled = $rs['scheduledeparture'];
            
        }
    }?>
    <table id="tblmanageaddflight" name="tblmanageaddflight" class="tbl-profile">
        <tr>
            <td class="manage-flight-lbl">Customer:</td>
            <td style="text-align: left;" class="manage-flight-lbl"><?php
                $pt->SltCustomer('customer')->GetSelect("sltcustomer", '', true, false);?>
            </td></tr>
        <tr>
            <td class="manage-flight-lbl">ACType:</td>
            <td style="text-align: left;"><?php
                $pt->SltAircraft('actype')->GetSelect("sltname", '', true, false);?>
            </td>
        </tr>
        <tr>
            <td class="manage-flight-lbl">Flight#:</td>
            <td style="text-align: left;"><input class="manage-flight-input" type="text" id="txtflightnumber" name="txtflightnumber" <?=$thisfunctionchange?> value="<?=$thisflightno;?>" placeholder="1111, 1234, 12b5" /></td></tr>
        <tr>
            <td class="manage-flight-lbl">Schedule Arrival:</td>
            <td style="text-align: left;"><input class="manage-flight-input" type="text" id="txtschedulearrival" name="txtschedulearrival" <?=$thisfunctionchange?> value="<?=$thisschedulea;?>" placeholder="12:00"/></td></tr>
        <tr>
            <td class="manage-flight-lbl">Schedule Departure:</td>
            <td style="text-align: left;"><input class="manage-flight-input" type="text" id="txtscheduledeparture" name="txtscheduledeparture" <?=$thisfunctionchange?> value="<?=$thisscheduled;?>" placeholder="23:00" /></td>
        </tr>
        <tr>
            <td class="manage-flight-lbl" style="text-align: left; color: white; height: 40px;" colspan="2">
                <button class="manage-flight-btn" id="btnmondays" name="btnmondays" onclick="updateDays(this, 'temprecno');">All Mondays</button>
                <button class="manage-flight-btn" id="btntuesdays" name="btntuesdays" onclick="updateDays(this, 'temprecno');">All Tuesdays</button>
                <button class="manage-flight-btn" id="btnwednesdays" name="btnwednesdays" onclick="updateDays(this, 'temprecno');">All Wednesdays</button>
                <button class="manage-flight-btn" id="btnthursdays" name="btnthursdays" onclick="updateDays(this, 'temprecno');">All Thursdays</button>
                <button class="manage-flight-btn" id="btnfridays" name="btnfridays" onclick="updateDays(this, 'temprecno');">All Fridays</button>
                <button class="manage-flight-btn" id="btnsaturdays" name="btnsaturdays" onclick="updateDays(this, 'temprecno');">All Saturdays</button>
                <button class="manage-flight-btn" id="btnsundays" name="btnsundays" onclick="updateDays(this, 'temprecno');">All Sundays</button>
                <button class="manage-flight-btn" id="btnalldays" name="btnalldays" onclick="updateDays(this, 'temprecno');">All Days</button>
                <button class="manage-flight-btn" id="btnallweekdays" name="btnallweekdays" onclick="updateDays(this, 'temprecno');">All Days No Weekend</button>
            </td>
        </tr>
        <tr>
            <td class="manage-flight-lbl">Dates:</td>
            <td class="manage-flight-lbl" style="text-align: left;">
                <!--<input class="add-flight-input" type="input" id="txtdates" name="txtdates"  placeholder="One or more dates, ex: 10/01/2022,...,10/10/2022" value=""/>-->
                <div class="add-flight-input" id="txtdates" name="txtdates"></div>
                <button class="manage-flight-btn-clear-dates" id="btncleardates" name="btncleardates" onclick="updateDays(this, 'temprecno');">Clear</button>
            </td>
        </tr><?php
        if($_POST['from'] == 'Add')
        {?>
            <tr><td class="tbl-profile-lbl" colspan="2" style="width: 100%; text-align: center;">
                <button type="button" onclick="submitAddflight();">Submit</button>
            </tr><?php
        }
        if($_POST['from'] == 'Modify')
        {?>
            <script type="text/javascript">
                $('body').data('temprecno', <?=$recno?>);
            </script>
            <tr><td class="tbl-profile-lbl" colspan="2" style="width: 100%; text-align: center;">
                    <button class="button" style="height: 30px; cursor: pointer;" id="btndeleteaircraft" name="btndeleteaircraft" onclick="deleteThisflight(this, 'temprecno');" title="Click to delete this aircraft" >Delete</button>
            </tr><?php
        }?>
    </table>
    <script type="text/javascript">
        
        $('#txtdates').multiDatesPicker({
            //showButtonPanel: true,
            dateFormat: "m/d/y",
            changeMonth: true,
            changeYear: true,
            onChangeMonthYear: function (year, month, inst) {
                //alert('1: '+$('body').data('currentmonth'));
                $('body').data('currentmonth', month+"/01/"+year);
                //alert('2: '+$('body').data('currentmonth'));
            }
        })
        //This code will stop the calendar from going back to current month/year.  Without this block of code, after you selected the dates, it will go back to current month/year
        //even though you are in a diff months and year.
        $.datepicker._selectDateOverload = $.datepicker._selectDate;
        $.datepicker._selectDate = function (id, dateStr) {
          var target = $(id);
          var inst = this._getInst(target[0]);
          inst.inline = true;
          $.datepicker._selectDateOverload(id, dateStr);
          inst.inline = false;
          if (target[0].multiDatesPicker != null) {
            target[0].multiDatesPicker.changed = false;
          } else {
            target.multiDatesPicker.changed = false;
          }
          this._updateDatepicker(inst);
        };
    </script><?php
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
                            <div class="main-div-body-manageaircraft-header">Manage Flight<br><span style="font-size: .4em;">(Add to schedule)</span></div>
                            <div style="float: left;">
                                <div class="div-menu-manageaircraft" onclick="addFlight(this);">Add</div>
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