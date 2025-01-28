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
            $('body').data('thishost', '<?= $temp_host ?>');
            $(document).ready(function(){
                //NOTE:  THIS READY DOC WORKS WITH SELECT2 BECAUSE IT LOADS, SO THE SELECT2 CAN TEHN CONVERT
                //IF YOU ARE GOING TO USE THIS SELECT2 SOMEWHWERE AFTER IT LOADED BY SOME CLICK OR OTHER EVENT,
                //YOU MUST CALL THIS SELECT2 AFTER IT FINISH PAINTING TO THE DOM.  CAN'T WORK ON DOC READY.  
                //EX ON LOAD IS FLOW.PHP AND EXAMPLE ON AFTER SOME EVENT FINISH LOADING, EX: MANAGEFLIGHT.PHP
                $(".promp-select2").each(function(){
                    $(this).select2({
                        placeholder: "Type to search and or select from list ONLY.",
                        width: 'style',
                    });
                });
                
                //$('#tbl_flow_data').DataTable();
                //$('#tbl_flow_data').tableScroll({height: 300px});
                
                //After loading, we will get the list of flights.
                
            });
            function updateFlow(obj, recno, lineno){
                thisid = $(obj).prop('id');
                if($(obj).prop('id').substr(0, 3) == "slt")
                {
                    thisval = $(obj).find(":selected").val();
                }
                else
                {
                    thisval = $(obj).val();
                }
                if($(obj).hasClass('class-timer')){
                    //We will check to make sure the timer is in good format
                    if(checkTime(obj) ==  false){
                        return(false);
                    }
                    //If we get this far, that means whatever field we are coming from the entered time is correct and well formated.
                    //Now we will check if this time is coming from depature fields
                    if(thisid == "txtscheduledeparture_"+lineno || thisid == "txtestimatedeparture_"+lineno || thisid == "txtactualdeparture_"+lineno){
                        //If we are here, we want to make sure we check if the entered time for these fields are NOT less than the time of arrival or one of the arrivals
                        thisatime = "";
                        if($("#txtactualarrival_"+lineno).val() != ""){
                            $thisatime = $("#txtactualarrival_"+lineno).val();
                        }
                        else if($("#txtestimatearrival_"+lineno).val() != ""){
                            $thisatime = $("#txtestimatearrival_"+lineno).val();
                        }
                        else{
                            $thisatime = $("#txtschedulearrival_"+lineno).val();
                        }
                        $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=checkTimeconflict&time1='+$thisatime+'&time2='+$(obj).val(), function(result){
                            if(result == "Failed"){
                                 isconflict = confirm('The departure time is less than the arrival time.  Is the arrival time for tomorrow?');
                                 if(isconflict == true){
                                     $(obj).val($(obj).val()+"+");
                                 }
                                 else{
                                     alert("Please check your arrival and departure times.  Departure time can not be less than arrival time unless it is for tomorrow.");
                                     $(obj).val('');
                                     return(false);
                                 }
                             }
                         }); 
                    }
                }
                if($(obj).hasClass('flow-button-depart') || $(obj).hasClass('flow-button-delete')){
                    //Regardless of which one we will remove the row from the table before we update the database.
                    $("#tr"+lineno).remove();
                    
                    //Now after we remove the row, we need to renumber the column.
                    i=1;
                    $(".tdnumbered").each(function(){
                        $(this).text(i);
                        i++;
                    });
                    thisval = 'true'; //We will be in here if we clicked Depart or Delete
                }
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=UpdateFlow&recno='+recno+'&field='+$(obj).prop('id')+'&value='+thisval, function(result){
                    //alert(result);
                    if(result == "Failed"){
                        alert("Failed to update.  Please contact your administrator.");
                        return(false);
                    }
                    else
                    {
                        //sltstatus_
                        if($(obj).prop('id').substr(0, 9) == "sltstatus"){
                            //alert($(obj).find(":selected").val());
                            if($(obj).find(":selected").val() == "Delayed"){
                                $("#tr"+lineno).css('background-color', 'darkred');
                            }
                            else if($(obj).find(":selected").val() == "Cancelled"){
                                $("#tr"+lineno).css('background-color', 'MediumOrchid');
                            }
                            else{
                                //Now we find out what color we should go back to by checking for the scheduled, eta, ata
                                if($("#txtschedulearrival_"+lineno).val() != ""){
                                    $("#tr"+lineno).css('background-color', 'white');
                                }
                                if($("#txtestimatearrival_"+lineno).val() != ""){
                                    $("#tr"+lineno).css('background-color', 'yellow');
                                }
                                if($("#txtactualarrival_"+lineno).val() != ""){
                                    $("#tr"+lineno).css('background-color', 'green');
                                }
                            }
                        }
                        if(thisid == 'txtschedulearrival_'+lineno || thisid == 'txtestimatearrival_'+lineno || thisid == 'txtactualarrival_'+lineno){
                            if($("#txtschedulearrival_"+lineno).val() != ""){
                                $("#tr"+lineno).css('background-color', 'white');
                            }
                            if($("#txtestimatearrival_"+lineno).val() != ""){
                                $("#tr"+lineno).css('background-color', 'yellow');
                            }
                            if($("#txtactualarrival_"+lineno).val() != ""){
                                $("#tr"+lineno).css('background-color', 'green');
                            }
                        }
                    }
                });
            }
            function doNotes(){
                window.location.href = "fidNote.php?from=modify";
            }
            function toServiceorder(recno){
                window.location.href = "serviceorder.php?recno="+recno;
            }
        </script>
    </head>
    <body style="overflow: hidden">
        <?php
            Main();
        ?>
    </body>
</html>
<?php
function checkTimeconflict()
{
    global $load_headers;
    $result = $load_headers->Check_Time_Conflict($_POST['time1'], $_POST['time2']);
    echo $result;
}
function UpdateFlow()
{
    global $db;
    $tempfield = substr($_POST['field'], 3); //txtflightnumber_1 -> flightnumber_1, now we have to get rid of the _1
    $realfield = substr($tempfield, 0, strpos($tempfield, "_"));
    $thistable = "flow";
    $thisvalue = $_POST['value'];
    if($_POST['value'] == "true")
    {
        $thisvalue = true;
    }
    if($realfield == "isdeparted")
    {
        $thistime = date('Y-m-d H:i:s'); //Must now be in format of Y-m-d H:i:s
        $thisdata = array($realfield => $thisvalue, 'actualdeparture' => $thistime); 
    }
    if($realfield == "isdeleted")
    {
        $thisdata = array($realfield => $thisvalue);
    }
    else
    {
        $thisdata = array($realfield => ($thisvalue != 'Select' ? $thisvalue : NULL));
    }
    $thiswhere = array("recno" => $_POST['recno']);
    //file_put_contents("./dodebug/debug.txt", "this recno: ".$_POST['recno']." ---this value: ".$thisvalue, FILE_APPEND);
    $result = $db->PDOUpdate($thistable, $thisdata, $thiswhere, $_POST['recno']);
    if(isset($result))
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
    $thisday = date('m/d/Y');
    $thistomorrow = date('m/d/Y', strtotime('+1 day'));
    $tempdatearray = array($thisday, $thistomorrow);
    for($i=0; $i<count($tempdatearray); $i++)
    {
        //First, we want to refresh flow with schedule flight, we want to insert what is in schedule flight that is not yet into flow.
        //$sql = "SELECT fs.* FROM flight_schedule fs LEFT OUTER JOIN flow f ON fs.customer = f.customer AND fs.actype = f.actype AND fs.flightnumber = f.flightnumber ";
        //$sql .= "WHERE fs.dates LIKE '%$tempdatearray[$i]%' AND fs.isdeleted = false AND f.date IS NULL ";
        //$sql .= "AND (fs.dates LIKE '%$tempdatearray[$i]%' AND fs.isdeleted = false AND f.date = '".date('Y-m-d', strtotime($tempdatearray[$i]))."')";
        
        $sql = "SELECT fs.* FROM flight_schedule fs LEFT OUTER JOIN flow f ON fs.customer = f.customer AND fs.actype = f.actype AND fs.flightnumber = f.flightnumber ";
        $sql .= "WHERE fs.dates LIKE '%".date('Y/m/d', strtotime($tempdatearray[$i]))."%' AND fs.isdeleted = false AND f.date IS NULL";
        //file_put_contents("./dodebug/debug.txt", $sql."===", FILE_APPEND);
        $result = $db->PDOMiniquery($sql);
        foreach($result as $rs)
        {
            $thistable = "flow";
            $thisdata = array("customer" => $rs['customer'], "actype" => $rs['actype'], "flightnumber" => $rs['flightnumber'], "date" => date('Y-m-d', strtotime($tempdatearray[$i])),
                            "schedulearrival" => ($rs['schedulearrival'] != NULL ? $rs['schedulearrival'] : NULL), 
                            "scheduledeparture" => ($rs['scheduledeparture'] != NULL ? $rs['scheduledeparture'] : NULL),
                            "fromstation" => ($rs['fromstation'] != NULL ? $rs['fromstation'] : NULL), "tostation" => ($rs['tostation'] != NULL ? $rs['tostation'] : NULL));
            $db->PDOInsert($thistable, $thisdata);
        }
    }
    $sql = "SELECT flow.*, so.recno as so_recno FROM flow INNER JOIN service_orders so ON flow.recno=so.foreignkey_flow_recno WHERE ";
    $sql .= "flow.date <= '".date('Y-m-d', strtotime($thistomorrow))."' AND flow.isdeparted=false AND flow.isdeleted = false ORDER BY date, customer";
    //file_put_contents("./dodebug/debug.txt", $sql."===", FILE_APPEND);
    $result = $db->PDOMiniquery($sql);

    $thistable = "note";
    $thisfields = array('recno', 'note');
    $thiswhere = array('isactive' => true, 'isdeleted' => false);    
    $noterows = $db->PDOQuery($thistable, $thisfields, $thiswhere);?>
    <div class="main-div"><?php
        $load_headers::Load_Header_Logo_Marquee();?>
        <br><br> <?php
        $load_headers::Load_Header_Logo_Main();?>        
        <div class="main-div-body">
            <div id="div_flowdata_containter" class="div-flowdata-containter">
                <div class="div-header-legend">
                    <div class="div-flow-note" id="div_flow_note" style="background-color: black;" onclick="doNotes();">
                        <marquee id="marnote" direction="left" scrollamount="6"><?php                            
                            if(!is_null($noterows))
                            {   
                                foreach($noterows as $nr)
                                {
                                    echo $nr['note'].'&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;';
                                }
                            }?>
                        </marquee>
                    </div>
                    <div class="div-flow-legend">
                        <div class="status-scheduled">Scheduled</div>
                        <div class="status-eta">ETA</div>
                        <div class="status-ata">ATA</div>
                        <div class="status-delayed">Parked/Delayed</div>
                        <div class="status-cancelled">Cancelled</div>
                        
                    </div>
                </div>
                <div style=" width: 100%; overflow-y: auto;">
                   <table id="tbl_flow_data" class="tbl-flow-data">
                        <thead>
                            <tr style="background-color: #173346;">
                                <th style="width: 20px !important; position: sticky; top: 0px; z-index: 10;"></th>
                                <th style="width: 160px !important; position: sticky; top: 0px; z-index: 10;" title="Customer">Cust</th>
                                <th style="width: 60px !important; position: sticky; top: 0px; z-index: 10;" title="Aircraft Type">A/C Type</th>
                                <th style="width: 50px; position: sticky; top: 0px; z-index: 10;" title="Flight Number">Flt No.</th>
                                <th style="width: 40px; position: sticky; top: 0px; z-index: 10;">Dep. Gate</th>
                                <th style="width: 50px; position: sticky; top: 0px; z-index: 10;" title="Flight Number">From Station</th>
                                <th style="width: 40px; position: sticky; top: 0px; z-index: 10;" title="Schedule Depature">SDep.</th>
                                <th style="width: 40px; position: sticky; top: 0px; z-index: 10;" title="Estimate Departure">EDep.</th>
                                <th style="width: 40px; position: sticky; top: 0px; z-index: 10;" title="Actual Depature">ADep.</th>
                                <th style="width: 40px; position: sticky; top: 0px; z-index: 10;">Arr. Gate</th>  
                                <th style="width: 50px; position: sticky; top: 0px; z-index: 10;" title="Flight Number">To Station</th>
                                <th style="width: 40px; position: sticky; top: 0px; z-index: 10;" title="Schedule Arrival">SArr.</th>
                                <th style="width: 40px; position: sticky; top: 0px; z-index: 10;" title="Estimate Arrival">EArr.</th>
                                <th style="width: 40px; position: sticky; top: 0px; z-index: 10;" title="Actual Arrival">AArr.</th>
                                <th style="width: 300px !important; position: sticky; top: 0px; z-index: 10;">Engineer</th>
                                <th style="width: 300px !important; position: sticky; top: 0px; z-index: 10;">Note</th>
                                <th style="width: 60px; position: sticky; top: 0px;">Status</th>
                                <th style="display: none; width: 60px; position: sticky; top: 0px;">Service Order</th>
                                <th style="width: 60px; position: sticky; top: 0px;">Depart</th><?php
                                if(in_array('Delete', $_SESSION['thisauth']))
                                {?>
                                    <th style="width: 60px; position: sticky; top: 0px;">Delete</th><?php
                                }?>
                            </tr>
                        </thead>
                        <tbody><?php
                            $i=1;
                            if(isset($result))
                            {
                                $tempdate = "";
                                $curdate = "";
                                foreach($result as $rs)
                                {
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
                                                <td colspan="19" style="text-align: center; font-weight: bold; font-size: 1.2em; background-color: #181818; color: white; height: 40px;"><?= date('d M y', strtotime($curdate));?></td>
                                            </tr><?php
                                        }
                                        if($tempdate == "")
                                        {//We only come in here the first time and only 1 time.?>
                                            <tr>
                                                <td colspan="19" style="text-align: center; font-weight: bold; font-size: 1.2em; background-color: #181818; color: white; height: 40px;"><?= date('d M y', strtotime($curdate));?></td>
                                            </tr><?php
                                        }
                                    }
                                    $tempdate = $curdate;
                                    
                                    $thisbgcolor = 'white';
                                    $thisfontcolor = 'Black'; 
                                    
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
                                    if($rs['status'] == "Cancelled")
                                    {
                                       $thisbgcolor = 'MediumOrchid';
                                       $thisfontcolor = 'white';
                                    }
                                    if($rs['status'] == "Delayed" || $rs['status'] == "Parked")
                                    {
                                       $thisbgcolor = 'darkred';
                                       $thisfontcolor = 'white';
                                    }
                                    
                                    $tempsa = $rs['schedulearrival']; //2023-01-03 19:34:00
                                    if($rs['schedulearrival'] != "")
                                    {
                                        $explodesa = explode(" ", $rs['schedulearrival']);
                                        $tempsa = date('H:i', strtotime($explodesa[1]));
                                    }  
                                    $tempea = $rs['estimatearrival']; 
                                    if($rs['estimatearrival'] != "")
                                    {
                                        $explodeea = explode(" ", $rs['estimatearrival']);
                                        $tempea = date('H:i', strtotime($explodeea[1]));
                                    }
                                    $tempaa = $rs['actualarrival'];
                                    if($rs['actualarrival'] != "")
                                    {
                                        $explodeaa = explode(" ", $rs['actualarrival']);
                                        $tempaa = date('H:i', strtotime($explodeaa[1]));
                                    }
                                    
                                    $tempsd = $rs['scheduledeparture'];
                                    if($rs['scheduledeparture'] != "")
                                    {
                                        $explodesd = explode(" ", $rs['scheduledeparture']);
                                        $tempsd = date('H:i', strtotime($explodesd[1]));
                                    }
                                    $temped = $rs['estimatedeparture'];
                                    if($rs['estimatedeparture'] != "")
                                    {
                                        $explodeed = explode(" ", $rs['estimatedeparture']);
                                        $temped = date('H:i', strtotime($explodeed[1]));
                                    }
                                    $tempad = $rs['actualdeparture'];
                                    if($rs['actualdeparture'] != "")
                                    {
                                        $explodead = explode(" ", $rs['actualdeparture']);
                                        $tempad = date('H:i', strtotime($explodead[1]));
                                    }?>
                                    <tr id="tr<?=$i?>" style="background-color: <?= $thisbgcolor?>; color: <?= $thisfontcolor ?>;">
                                        <td class="tdnumbered" style="height: 40px; width: 20px !important; color: <?= $thisfontcolor ?>; text-align: right;"><?= $i ?></td>
                                        <td style="height: 40px; width: 160px;"><input class="input-flows" type="text" id="txtcustomer_<?=$i?>" onchange="updateFlow(this, <?= $rs['recno'] ?>, <?=$i?>);" value="<?= $rs['customer']?>" readonly/></td>
                                        <td style="height: 40px; width: 60px;"><input class="input-flows" type="text" id="txtactyp_<?=$i?>e" onchange="updateFlow(this, <?= $rs['recno'] ?>, <?=$i?>);" value="<?= $rs['actype']?>" readonly/></td>
                                        <td style="height: 40px; width: 50px;"><input class="input-flows class-timer" type="text" id="txtflightnumber_<?=$i?>" onfocus="saveThisdata(this);" onchange="updateFlow(this, <?= $rs['recno'] ?>, <?=$i?>);" value="<?= $rs['flightnumber']?>" readonly/></td>
                                        <td style="height: 40px; width: 50px;"><input class="input-flows class-timer" type="text" id="txtfromstation_<?=$i?>" onfocus="saveThisdata(this);" onchange="updateFlow(this, <?= $rs['recno'] ?>, <?=$i?>);" value="<?= $rs['departuregate']?>" readonly/></td>
                                        <td style="height: 40px; width: 50px;"><input class="input-flows class-timer" type="text" id="txtfromstation_<?=$i?>" onfocus="saveThisdata(this);" onchange="updateFlow(this, <?= $rs['recno'] ?>, <?=$i?>);" value="<?= $rs['fromstation']?>" readonly/></td>                                                                           
                                        <td style="height: 40px; width: 40px;"><input class="input-flows class-timer" type="text" id="txtscheduledeparture_<?=$i?>" onfocus="saveThisdata(this);" onchange="updateFlow(this, <?= $rs['recno'] ?>, <?=$i?>);" value="<?=$tempsd?>" readonly/></td>
                                        <td style="height: 40px; width: 40px;"><input class="input-flows class-timer" type="text" id="txtestimatedeparture_<?=$i?>" onfocus="saveThisdata(this);" onchange="updateFlow(this, <?= $rs['recno'] ?>, <?=$i?>);" value="<?=$temped?>" readonly/></td>
                                        <td style="height: 40px; width: 40px;"><input class="input-flows class-timer" type="text" id="txtactualdeparture_<?=$i?>" onfocus="saveThisdata(this);" onchange="updateFlow(this, <?= $rs['recno'] ?>, <?=$i?>);" value="<?=$tempad?>" readonly/></td>
                                        <td style="height: 40px; width: 40px;"><input class="input-flows" type="text" id="txtgate_<?=$i?>" onfocus="saveThisdata(this);" onchange="updateFlow(this, <?= $rs['recno'] ?>, <?=$i?>);" value="<?= $rs['gate']?>"/></td>
                                        <td style="height: 40px; width: 50px;"><input class="input-flows class-timer" type="text" id="txttostation_<?=$i?>" onfocus="saveThisdata(this);" onchange="updateFlow(this, <?= $rs['recno'] ?>, <?=$i?>);" value="<?= $rs['tostation']?>" readonly/></td> 
                                        <td style="height: 40px; width: 40px;"><input class="input-flows class-timer" type="text" id="txtschedulearrival_<?=$i?>" onfocus="saveThisdata(this);" onchange="updateFlow(this, <?= $rs['recno'] ?>, <?=$i?>);" value="<?=$tempsa?>" readonly/></td>
                                        <td style="height: 40px; width: 40px;"><input class="input-flows class-timer" type="text" id="txtestimatearrival_<?=$i?>" onfocus="saveThisdata(this);" onchange="updateFlow(this, <?= $rs['recno'] ?>, <?=$i?>);" value="<?=$tempea?>" readonly/></td>
                                        <td style="height: 40px; width: 40px;"><input class="input-flows class-timer" type="text" id="txtactualarrival_<?=$i?>" onfocus="saveThisdata(this);" onchange="updateFlow(this, <?= $rs['recno'] ?>, <?=$i?>);" value="<?=$tempaa?>" readonly/></td>
                                        <td style="height: 40px; width: 300px !important; color: black;">
                                            <div class="flow-slt-engineer-wrapper"><?php
                                                $thisrecno = $rs['recno'];
                                                $pt->SltEngineer()->GetSelect("txtengineers_$i", $rs['engineers'], true, true, "updateFlow(this, $thisrecno, $i)");?>
                                            </div>
                                        </td>
                                        <td style="height: 40px; width: 300px !important;"><textarea style="border: none;" class="flow-txtarea-note" id="txtnote_<?=$i?>" onchange="updateFlow(this, <?= $rs['recno'] ?>, <?=$i?>);"><?= $rs['note']?></textarea></td>
                                        <td style="height: 40px; width: 60px !important;">
                                            <select style="height: 40px; width: 99%;" class="flow-slt-status" id="sltstatus_<?=$i?>" onchange="updateFlow(this, <?= $rs['recno'] ?>, <?=$i?>);" >
                                                <option value="Select" >Select</option><?php
                                                if($rs['status'] == 'Delayed')
                                                {?>
                                                    <option value="Delayed" selected>Delayed</option><?php
                                                }
                                                else if($rs['status'] == 'Cancelled')
                                                {?>
                                                    <option value="Cancelled" selected>Cancelled</option><?php
                                                }
                                                else
                                                {?>
                                                    <option value="Delayed" >Delayed</option>
                                                    <option value="Cancelled" >Cancelled</option><?php
                                                }?>
                                            </select>
                                        </td>
                                        <td style="display: none; height: 40px; width: 70px;"><button class="flow-button-depart" id="btnisdeparted_<?=$i?>" value="Service Order" onclick="toServiceorder(<?=$rs['so_recno'];?>);"><?=$rs['so_recno'];?></button></td>
                                        <td style="height: 40px; width: 70px;"><button class="flow-button-depart" id="btnisdeparted_<?=$i?>" value="Depart" onclick="updateFlow(this, <?= $rs['recno'] ?>, <?=$i?>);">Depart</button></td><?php
                                        if(in_array('Delete', $_SESSION['thisauth']))
                                        {?>
                                            <td style="height: 40px; width: 70px;"><button class="flow-button-delete" id="btnisdeleted_<?=$i?>" value="Delete" onclick="updateFlow(this, <?= $rs['recno'] ?>, <?=$i?>);">Delete</button></td><?php
                                        }?>
                                    </tr><?php
                                    $i++;
                                }
                            }
                            else
                            {?>
                                <tr><td>There is no data</td><tr><?php
                            }?>  
                        </tbody>
                    </table>
                </div>
            </div>
        </div><?php
        $load_headers::Load_Footer();?>
    </div><?php
}