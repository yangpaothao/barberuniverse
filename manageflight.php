<?php
require_once("./common/page.php");
require_once("./common/pdocon.php");
require_once("./common/prompt.php");
//make sure that on both add and mod, when after user select a date that when we go back to the month, those date would be highlighted.
//this means we have to start looking at the date array each time we change month and or year.
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
            var pickedDates = [];
            $(document).ready(function(){
                var d = new Date();
                var thisday = d.getDate();
                var thismonth = d.getMonth();
                var thisyear = d.getFullYear();
                $('body').data('currentmonth', (thismonth+1)+"/"+thisday+"/"+thisyear); 
                //by default we will set this to current month/year, also added 1 tot thismonth cuz in javascript, month starts at 0, adding one will give us a accurate rep month.
                //alert('0: '+$('body').data('currentmonth'));
                $("#txtdates").multiDatesPicker('show');
                sltDefault();
                
            });
            function sltDefault(){
                $(".div-menu-manageaircraft").each(function(){
                    $(this).css('background-color', '#1079B1');
                    $(this).css('color', 'white');
                })
                $("#div_add").css("background-color", "white");
                $("#div_add").css('color', 'black');
                
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=ManageFlights&from=Add', function(result){
                    $("#main_div_body_manageaircraft_right_container").html(result);
                });
            }
            function manageFlights(obj, from){
                $(".div-menu-manageaircraft").each(function(){
                    $(this).css('background-color', '#1079B1');
                    $(this).css('color', 'white');
                })
                $(obj).css("background-color", "white");
                $(obj).css('color', 'black');
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=ManageFlights&from='+from, function(result){
                    $("#main_div_body_manageaircraft_right_container").html(result);
                });
            }
            function searchInterface(obj){
                $(".div-menu-manageaircraft").each(function(){
                    $(this).css('background-color', '#1079B1');
                    $(this).css('color', 'white');
                })
                $(obj).css("background-color", "white");
                $(obj).css('color', 'black');
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=SearchInterface', function(result){
                    $("#main_div_body_manageaircraft_right_container").html(result);
                });
            }
           
            function submitAddflight(){
                if($("#sltcustomer").val() == ""){
                    alert('Please select a custoemr from the list.');
                    $("#sltcustomer").select().focus();
                    return(false);
                }
                if($("#sltname").val() == ""){
                    alert('Please select an aircraft type from the list.');
                    $("#sltname").select().focus();
                    return(false);
                }
                if($("#txtflightnumber").val() == ""){
                    alert('Please enter a flight number.');
                    $("#txtflightnumber").select().focus();
                    return(false);
                }
                //Now if we made it to here, we know we have to check if these customer with thius actype and this flight number already exist.
                $.post('<?=$_SERVER['PHP_SELF']; ?>','cmd=ValidateFlight&'+$("#tblmanageaddflight :input").serialize(),function(result){
                    //alert(result);
                    if(result == "Failed"){
                        alert("This flight already exist in the database.  Can't add the same data twice.  Please try again.");
                        return(false);
                    }
                    else
                    {
                        $.post('<?=$_SERVER['PHP_SELF']; ?>','cmd=SubmitFlight&'+$("#tblmanageaddflight :input").serialize()+'&pickedDates='+JSON.stringify(pickedDates),function(result){
                            if(result == "Failed"){
                                alert("Failed to add flight.  Please contact your administrator.");
                                return(false);
                            }
                            else
                            {
                                alert("Successfully Added.");
                                location.reload();
                                return(false);
                                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=ManageFlights&from=Add', function(result){
                                    $("#main_div_body_manageaircraft_right_container").html(result);
                                });
                                
                                //After we successfully added the flight, we want to clear the form for in case user wants to added anther.
                                $("#sltcustomer").val('Select');
                                $("#sltname").val('Select');
                                $("#tblmanageaddflight").find('input:text').val('');
                                pickedDates = []; //reset the array.
                                $('.tbl-manage-flight-calendar-dates').each(function(){
                                    $(this).css('background-color', 'rgba(255, 255, 255)');
                                });
                                var sd = new Date();
                                var dthismonth = sd.getMonth();
                                if(dthismonth < 10)
                                {
                                    dthismonth = "0"+(dthismonth+1);
                                }
                                var dthisyear = sd.getFullYear();
                                $("#sltmonth").val(dthismonth+1);
                                $("#sltyear").val(dthisyear);
                            }
                        });
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
            function manageFlow(){
                window.open("flow.php", '_blank');
            }
            function manageFid(){
                window.open("fid.php", '_blank');
            }
            function addFidnot(obj){
                $(".div-menu-manageaircraft").each(function(){
                    $(this).css('background-color', '#1079B1');
                    $(this).css('color', 'white');
                })
                $(obj).css("background-color", "white");
                $(obj).css('color', 'black');
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=AddFidnote', function(result){
                   $("#main_div_body_manageaircraft_right_container").html(result);
                });
            }
            function addFidnotdefault(){
                $(".div-menu-manageaircraft").each(function(){
                    $(this).css('background-color', '#1079B1');
                    $(this).css('color', 'white');
                })
                $("#div_add").css("background-color", "white");
                $("#div_add").css('color', 'black');
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=AddFidnote', function(result){
                   $("#main_div_body_manageaircraft_right_container").html(result);
                });
            }
            function showFidnote(obj){
                $(".div-menu-manageaircraft").each(function(){
                    $(this).css('background-color', '#1079B1');
                    $(this).css('color', 'white');
                })
                $(obj).css("background-color", "white");
                $(obj).css('color', 'black');
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=AddFidnote&from=Read', function(result){
                   $("#main_div_body_manageaircraft_right_container").html(result);
                });
            }
            function updateFlight(obj){
                thisid = $(obj).prop('id');
                //alert($(obj).val());
                from = 'Update';
                if(thisid == 'sltcustomer' && $(obj).val() != "Select"){
                    //We just want to enable ACType, LATER ON, WE MIGHT DO A POST TO GET JUST THE AIRCRAFTS THAT ARE used by this customer.  For now, we keep it simple.
                    $("#sltname").val('Select');
                    $("#sltname").prop('disabled', false);
                    $("#txtflightnumber").val('');
                    $("#txtflightnumber").prop('disabled', true);
                    $("#txtschedulearrival").val('');
                    $("#txtschedulearrival").prop('disabled', true);
                    $("#txtscheduledeparture").val('');
                    $("#txtscheduledeparture").prop('disabled', true);
                    $("#td_calendar").hide();
                    pickedDates = [];  //Reset this array just in case.
                    return(false);
                }
                else if(thisid == 'sltcustomer' && $(obj).val() == "Select"){
                    //$("#sltname").prop('disabled', true);
                    $("#sltname").val('Select');
                    $("#sltname").prop('disabled', true);
                    $("#txtflightnumber").val('');
                    $("#txtflightnumber").prop('disabled', true);
                    $("#txtschedulearrival").val('');
                    $("#txtschedulearrival").prop('disabled', true);
                    $("#txtscheduledeparture").val('');
                    $("#txtscheduledeparture").prop('disabled', true);                    
                    $("#td_calendar").hide();
                    pickedDates = [];  //Reset this array just in case.
                    return(false);
                }
                //ACType
                if(thisid == 'sltname' && $(obj).val() != "Select"){
                    $("#txtflightnumber").prop('disabled', false);
                    $("#txtflightnumber").val('');
                    $("#txtflightnumber").prop('disabled', false);
                    $("#txtschedulearrival").val('');
                    $("#txtschedulearrival").prop('disabled', true);
                    $("#txtscheduledeparture").val('');
                    $("#txtscheduledeparture").prop('disabled', true);                    
                    $("#td_calendar").hide();
                    return(false);
                }
                else if(thisid == 'sltname' && $(obj).val() == "Select"){
                    $("#txtflightnumber").val('');
                    $("#txtflightnumber").prop('disabled', true);
                    $("#txtflightnumber").val('');
                    $("#txtflightnumber").prop('disabled', true);
                    $("#txtschedulearrival").val('');
                    $("#txtschedulearrival").prop('disabled', true);
                    $("#txtscheduledeparture").val('');
                    $("#txtscheduledeparture").prop('disabled', true);                    
                    $("#td_calendar").hide();
                    return(false);
                }
                if(thisid == 'txtflightnumber' && $(obj).val() != ""){
                    //We just want to enable ACType, LATER ON, WE MIGHT DO A POST TO GET JUST THE AIRCRAFTS THAT ARE used by this customer.  For now, we keep it simple.
                    //$("#txtflightnumber").prop('disabled', false);
                    $("#btndeletethisflight").prop('disabled', false);
                    from = "Query";
                }
                else if(thisid == 'txtflightnumber' && $(obj).val() == ""){
                    $("#txtflightnumber").prop('disabled', true);
                    $("#txtschedulearrival").val('');
                    $("#txtschedulearrival").prop('disabled', true);
                    $("#txtscheduledeparture").val('');
                    $("#txtscheduledeparture").prop('disabled', true);
                    $("#td_calendar").hide();
                    $("#btndeletethisflight").prop('disabled', true);
                    return(false);
                }
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=UpdateFlight&recno='+$('body').data('recno')+'&from='+from+'&thisid='+thisid+'&'+$("#tblmanageaddflight :input").serialize(), function(result){
                    //alert(result);
                    if(result != "Failed"){
                        if(thisid == 'txtflightnumber'){
                            var thisjson = JSON.parse(result);
                            thisjson.forEach(function(obj){
                                //alert(obj.schedulearrival);
                                $("#txtschedulearrival").prop('disabled', false);
                                $("#txtschedulearrival").val(obj.schedulearrival);
                                $("#txtscheduledeparture").prop('disabled', false);
                                $("#td_calendar").show();
                                $("#txtscheduledeparture").val(obj.scheduledeparture);
                                $('body').data('recno', obj.recno);
                                pickedDates = obj.dates.split(',');
                                changeDate(obj.recno, 'Modify');
                                //Now we will need to do another call to a post to build the delete button that will replace the current one.
                                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=DeleteFlightbutton&recno='+$('body').data('recno'), function(result){
                                    $("#tdbuttondelete").html(result);
                                });
                            });
                        }  
                    }
                    else{
                        alert('No schedule for this search.  Please contact your administrator.');
                        return(false);
                    }
                });
            }
            function updateDays(obj, temprecno, from){
                thisid = $(obj).prop('id');
                if(thisid == "btncleardates"){
                    $("#txtdates").multiDatesPicker('resetDates','picked');
                    return(false);
                }
                else{  
                    $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=UpdateDays&from='+from+'&recno='+$('body').data('recno')+'&thisid='+thisid+'&curdate='+$('body').data('currentmonth'), function(result){
                        //alert(result);
                        var jsondata=JSON.parse(result);
                        for(i=0; i<jsondata.length; i++)
                        {
                            //alert(jsondata[i]);
                            splitjdate = jsondata[i].split('/');
                            tempday = splitjdate[1];
                            $('.tbl-manage-flight-calendar-dates').each(function(){
                                thiscaldatetxt = $(this).text();
                                thisbgcolor = $(this).css('background-color');
                                realday = "";
                                if(thiscaldatetxt < 10){
                                    thiscaldatetxt = "0"+thiscaldatetxt;
                                }
                                if(tempday == thiscaldatetxt){
                                    if(thisbgcolor == 'rgb(255, 255, 255)' || thisbgcolor == 'rgba(255, 255, 255, 255)'){
                                        //If thisbgcolor is white (rgb(0, 0, 0), that means we are coming from white into orange so we want to 
                                        //turn this td into orange background and ADD this date into the array.
                                        $(this).css('background-color', 'rgba(255,99,71)');
                                        pickedDates.push(jsondata[i]);
                                    }
                                    else
                                    {
                                        $(this).css('background-color', 'rgba(255, 255, 255)');
                                        var index = $.inArray(jsondata[i], pickedDates); //We check if thisdate is in this array, then we will remove it.
                                        if (index != -1) {  //If it is not -1, that means we found it, if -1, then not found.
                                            pickedDates.splice(index, 1); //WE REMOVE thisdate
                                        }
                                    }
                                }
                            });
                        }
                    });
                }
            }
            function pickDates(obj, bigfrom){
                thisday = parseInt($(obj).text());
                if(thisday < 10){
                    thisday = "0"+thisday;
                }
                thisdate = $("#sltmonth").val()+'/'+thisday+'/'+$("#sltyear").val();
                thisbgcolor = $(obj).css('background-color'); //rgba(0, 0, 0) or rgba(0,0,0) -> white 
                if(thisbgcolor == 'rgb(255, 255, 255)' || thisbgcolor == 'rgba(255, 255, 255, 255)'){
                    //If thisbgcolor is white (rgb(0, 0, 0), that means we are coming from white into orange so we want to 
                    //turn this td into orange background and ADD this date into the array.
                    $(obj).css('background-color', 'rgba(255,99,71)');
                    from = 'Add';
                    pickedDates.push(thisdate);
                }
                else
                {
                    //If thisbgcolor is coming from 'NOT' white, then we assume it is coming from orange and we will go into white.  We
                    //want to remove this date from array and turn this td white.
                    $(obj).css('background-color', 'rgba(255, 255, 255)');
                    from = 'Delete';
                    
                    var index = $.inArray(thisdate, pickedDates); //We check if thisdate is in this array, then we will remove it.
                    if (index != -1) {  //If it is not -1, that means we found it, if -1, then not found.
                        pickedDates.splice(index, 1); //WE REMOVE thisdate
                    }
                }
                if(bigfrom == "Modify"){
                    //We are handling update to single date click
                    $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=UpdateAdate&recno='+$('body').data('recno')+'&from='+bigfrom+'&pickedDates='+JSON.stringify(pickedDates), function(result){
                        //alert(result);
                        if(result == "Failed"){
                            alert('Failed to update this day.  Please contact your administrator.');
                            return(false);
                        }
                    });
                }
            }
            function previousMonth(from='Add'){
                //We want to change the month selectoni to previous, but before that we want to find out what is the current select?
                //If it is already Jan, we want it to go to Dec but up down the year.
                $tempmonth = $("#sltmonth").find(":selected").val();
                if($tempmonth == '01'){
                    $("#sltmonth").val('12');
                    $("#sltyear option:selected").prev().prop('selected', true);
                }
                else
                {
                    $("#sltmonth option:selected").prev().prop('selected', true);
                }
                thismonth = $("#sltmonth").find(":selected").val();
                thisyear = $("#sltyear").find(":selected").val();
                //alert('month: '+thismonth+' && year: '+thisyear);
                $('body').data('currentmonth', thismonth+'/01/'+thisyear);
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=ReselectDate&recno=0&from='+from+'&thismonth='+thismonth+'&thisyear='+thisyear+'&pickedDates='+JSON.stringify(pickedDates), function(result){
                    //alert(result);
                    if(result == "Failed"){
                        alert('Failed to paint calendar.  Please contact your administrator.');
                        return(false);
                    }
                    else
                    {
                        $("#tbodymanageflight").html(result);
                    }
                });
            }
            function nextMonth(from='Add'){
                //We want to change the month selectoni to previous, but before that we want to find out what is the current select?
                //If it is already Jan, we want it to go to Dec but up down the year.
                $tempmonth = $("#sltmonth").find(":selected").val();
                if($tempmonth == '12'){
                    $("#sltmonth").val('01');
                    $("#sltyear option:selected").next().prop('selected', true);
                }
                else
                {
                    $("#sltmonth option:selected").next().prop('selected', true);
                }
                thismonth = $("#sltmonth").find(":selected").val();
                thisyear = $("#sltyear").find(":selected").val();
                //alert('month: '+thismonth+' && year: '+thisyear);
                $('body').data('currentmonth', thismonth+'/01/'+thisyear);
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=ReselectDate&recno=0&from='+from+'&thismonth='+thismonth+'&thisyear='+thisyear+'&pickedDates='+JSON.stringify(pickedDates), function(result){
                   if(result == "Failed"){
                        alert('Failed to paint calendar.  Please contact your administrator.');
                        return(false);
                    }
                    else
                    {
                        $("#tbodymanageflight").html(result);
                    }
                });
            }
            function changeDate(recno=0, from="Add"){
                //from = Add or Modify, we default it to Add
                thismonth = $("#sltmonth").find(":selected").val();
                thisyear = $("#sltyear").find(":selected").val();
                $('body').data('currentmonth', thismonth+'/01/'+thisyear);
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=ReselectDate&recno='+recno+'&from='+from+'&thismonth='+thismonth+'&thisyear='+thisyear+'&pickedDates='+JSON.stringify(pickedDates), function(result){
                   if(result == "Failed"){
                        alert('Failed to paint calendar.  Please contact your administrator.');
                        return(false);
                    }
                    else
                    {
                        $("#tbodymanageflight").html(result);
                    }
                });
            }
            function deleteThisflight(obj, recno){
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=DeleteThisflight&recno='+recno, function(result){
                    if(result == "Success"){
                        alert('Successfully deleted.');
                        $("#sltcustomer").val('Select');
                        $("#sltname").val('Select');
                        $("#sltname").prop('disabled', true);
                        $("#txtflightnumber").val('');
                        $("#txtflightnumber").prop('disabled', true);
                        $("#txtschedulearrival").val('');
                        $("#txtschedulearrival").prop('disabled', true);
                        $("#txtscheduledeparture").val('');
                        $("#txtscheduledeparture").prop('disabled', true);                    
                        $("#td_calendar").hide();
                        $("#btndeletethisflight").prop('disabled', true);
                        pickedDates = [];  //Reset this array just in case.
                        return(false);
                    }
                    else{
                        alert('Failed to Delete this flight.  Please contact your administrator.');
                        return(false);
                    }
                });
            }
            function checkTimeconflict(obj){
                if(checkTime(obj) == false){
                    return(false);
                }
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=checkTimeconflict&time1='+$("#txtschedulearrival").val()+'&time2='+$("#txtscheduledeparture").val(), function(result){
                   if(result == "Failed"){
                        isconflict = confirm('The departure time is less than the arrival time.  Is the arrival time for tomorrow?');
                        if(isconflict == true){
                            $(obj).val($(obj).val()+"+");
                        }
                        else{
                            alert("Please check your arrival and departure times.  Departure time can not be less than arrival time unless it is for tomorrow.");
                            $(obj).select();
                            return(false);
                        }
                    }
                });
            }
            function searchFororders(obj){
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
                    $("#slttype").val('Notdeparted');
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
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=SearchFororders&'+$("#div_select_header :input").serialize(), function(result){
                    //alert(result);
                    $("#div_select_body_container").html(result);
                    $(".promp-select2").each(function(){
                    $(this).select2({
                            placeholder: "Type to search and or select from list ONLY.",
                            width: 'style',
                        });
                    });
                });
                
            }
            function showOrders(recno){
                window.location.href = "serviceorder.php?recno="+recno;
            }
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
                    if(result == "Failed"){
                        alert("Failed to update.  Contact your administrator");
                        return(false);
                    }
                    if($(obj).prop('id') == "btnisdeleted" || $(obj).prop('id') == "chkisactive"){
                        //location.reload();
                        addFidnotdefault();
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
function ModifyFidnot()
{
    global $db, $load_headers;
    if(array_key_exists('from', $_POST))
    {
        $sql = "SELECT no.*, em.firstname, em.middlename, em.lastname FROM note no INNER JOIN employee_master em ON no.author=em.recno WHERE no.expiredate >= CURDATE() AND no.isactive = true AND no.isdeleted = false ORDER BY no.entrydate";
        $result = $db->PDOMiniquery($sql);
    }?>
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
    <div style="width: 600px;">
        <table id="tblnew_note" name="tblnew_note" class="tbl-new-note" style="float: left; width: 100%;">
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
        </table>
    </div><?php
}
function AddFidnote()
{
    global $db, $load_headers;
    if(array_key_exists('from', $_POST))
    {
        $sql = "SELECT no.*, em.firstname, em.middlename, em.lastname FROM note no INNER JOIN employee_master em ON no.author=em.recno WHERE no.expiredate >= CURDATE() AND no.isactive = true AND no.isdeleted = false ORDER BY no.entrydate";
        $result = $db->PDOMiniquery($sql);
        file_put_contents("./dodebug/debug.txt", $sql, FILE_APPEND);
    }?>
    <div id="divnote_container" style="width: 1000px; height: 790px; min-width: 1000px; float: left;"><?php
        if(array_key_exists('from', $_POST))
        {?>
            <table id="tblcustomerdata" style="width: 100%;">
                <thead>
                <tr style="background-color: #173346; text-align: left;">
                    <th style="width: 40px;">No.</th>
                    <th style="width: 260px; padding-left: 10px;">Author</th>
                    <th style="padding-left: 10px; padding-right: 10px;">
                        Note
                        <span title="Click to add new note" onclick="addNote(); style="style="height: 30px; width: 40px; cursor: pointer; font-size: 1em;">+</span>
                    </th>
                    <th style="width: 100px;">Entry Date</th>
                    <th style="width: 100px;">Expire Date</th>
                </tr>
                </thead>
                <tbody id="tblnote_body"><?php

                        $i=1;
                        if($result)
                        {
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
                            }
                            if($i == 1)
                            {?>
                                <tr><td colspan="5">There is no data</td><tr><?php
                            } 
                        }?>
                        
                </tbody>
            </table><?php
        }
        else
        {
            NoteFul();
        }?>
    </div>
<?php
}
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
        $thistime = date('H:i');
        $thisdata = array($realfield => $thisvalue, 'actualdeparture' => $thistime); 
    }
    else
    {
        $thisdata = array($realfield => ($thisvalue != 'Select' ? $thisvalue : NULL)); //First we remove the first 3 char at start then we remove the last character
    }
    $thiswhere = array("recno" => $_POST['recno']);
    //PDOUpdate($thistable=null, $thisdata = null, $thiswhere = null)
    $result = $db->PDOUpdate($thistable, $thisdata, $thiswhere, $_POST['recno']);
    //file_put_contents("./dodebug/debug.txt", var_dump($_POST), FILE_APPEND);
    if(isset($result))
    {
        echo 'Success';
    }
    else
    {
        echo 'Failed';
    }    
}
function SearchFororders()
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
        $sql .= "WHERE fs.dates LIKE '%$tempdatearray[$i]%' AND fs.isdeleted = false AND f.date IS NULL";
        //file_put_contents("./dodebug/debug.txt", $sql."===", FILE_APPEND);
        $result = $db->PDOMiniquery($sql);
        foreach($result as $rs)
        {
            $thistable = "flow";
            $thisdata = array("customer" => $rs['customer'], "actype" => $rs['actype'], "flightnumber" => $rs['flightnumber'], "date" => date('Y-m-d', strtotime($tempdatearray[$i])),
                            "schedulearrival" => ($rs['schedulearrival'] != NULL) ? $rs['schedulearrival'] : NULL, 
                            "scheduledeparture" => ($rs['scheduledeparture'] != NULL) ? $rs['scheduledeparture'] : NULL);
            $db->PDOInsert($thistable, $thisdata);
            
            $thistable = "service_orders";
            $thisdata = array("customer" => $rs['customer'], "actype" => $rs['actype'], "flightnumber" => $rs['flightnumber'], "date" => date('Y-m-d', strtotime($tempdatearray[$i])),
                            "schedulearrival" => ($rs['schedulearrival'] != NULL) ? $rs['schedulearrival'] : NULL, 
                            "scheduledeparture" => ($rs['scheduledeparture'] != NULL) ? $rs['scheduledeparture'] : NULL);
        }
    }
    $sql = "SELECT flow.*, so.recno as so_recno FROM flow INNER JOIN service_orders so ON flow.recno=so.foreignkey_flow_recno WHERE 1=1 ";
    

    if($_POST['txtcustomer'] != "")
    {
        $sql .= "AND flow.customer LIKE '%".$_POST['txtcustomer']."%' ";
    }
    if($_POST['txtactype'] != "")
    {
        $sql .= "AND flow.actype LIKE '%".$_POST['txtactype']."%' ";
    }
    if($_POST['txtflightnumber'] != "")
    {
        $sql .= "AND flow.flightnumber LIKE '%".$_POST['txtflightnumber']."%' ";
    }
    if($_POST['slttype'] != "All")
    {
        if($_POST['slttype'] == "Departed")
        {
            $sql .= "AND flow.isdeparted = true ";
        }
        else
        {
            $sql .= "AND flow.isdeparted = false ";
        }
    }
    if($_POST['txtdate'] != "")
    {
        $sql .= "AND flow.date = '".date('Y-m-d', strtotime($_POST['txtdate']))."' ";
    }
    if($_POST['txtdatefrom'] != '' && $_POST['txtdateto'] != '')
    {
        $sql .= "AND flow.date BETWEEN '".date('Y-m-d', strtotime($_POST['txtdatefrom']))."' AND '".date('Y-m-d', strtotime($_POST['txtdateto']))."' ";
    }
    $sql .= " AND flow.isdeleted = false ORDER BY date, customer";

    //file_put_contents("./dodebug/debug.txt", $sql."===", FILE_APPEND);
    $result = $db->PDOMiniquery($sql);

    $thistable = "note";
    $thisfields = array('recno', 'note');
    $thiswhere = array('isactive' => true, 'isdeleted' => false);    
    $noterows = $db->PDOQuery($thistable, $thisfields, $thiswhere);?>
   
    <div id="div_flowdata_containter" style="margin-left: -10px;" >
        <div class="div-header-legend">
            <div class="div-flow-legend">
                <div class="status-scheduled">Scheduled</div>
                <div class="status-eta">ETA</div>
                <div class="status-ata">ATA</div>
                <div class="status-delayed">Parked/Delayed</div>
            </div>
        </div>
        <div style="height: 740px; width: 1670px; overflow-y: auto;">
           <table id="tbl_flow_data" class="tbl-flow-data" style="width: 99%;">
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
    </div><?php
}
function SearchInterface()
{?>
    <div class="div-select-header" id="div_select_header">
        <div class="search-service-orders">Customer:<br><input type="text" name="txtcustomer" id="txtcustomer" value="" onkeyup="searchFororders(this);" onfocus="searchFororders(this);" placeholder="Type a customer name"/></div>
        <div class="search-service-orders">Aircraft Type:<br><input type="text" name="txtactype" id="txtactype" value="" onkeyup="searchFororders(this);" onfocus="searchFororders(this);" placeholder="Enter Aircraft Type"/></div>
        <div class="search-service-orders">Flight Number:<br><input type="text" name="txtflightnumber" id="txtflightnumber" onkeyup="searchFororders(this);" onfocus="searchFororders(this);" value="" placeholder="Flight Number"/></div>
        <div class="search-service-orders">Date:<br><input type="text" name="txtdate" id="txtdate" value="" onfocus="getJDate(this);" onchange="searchFororders(this);" placeholder="Enter a date 10/21/2022"/></div>
        <div class="search-service-orders">Date Range From:<br><input type="text" name="txtdatefrom" id="txtdatefrom" onfocus="getJDate(this);" onchange="searchFororders(this);" value="" placeholder="Enter a date 10/21/2022"/></div>
        <div class="search-service-orders">Date Range To:<br><input type="text" name="txtdateto" id="txtdateto" onfocus="getJDate(this);" onchange="searchFororders(this);" value="" placeholder="Enter a date 10/21/2022"/></div>
        
        
        <div class="search-service-orders">Type:<br>
            <select id="slttype" name="slttype" style="width: 100%;" onchange="searchFororders(this);">
                <option value="All">All</option>
                <option value="Departed">Departed</option>
                <option value="Notdeparted" selected>Notdeparted</option>
            </select>
        </div>
        
        <div style="float: left;">
            <button id="btnclear" value="Search" style="width: 80px; height: 30px;" onclick="searchFororders(this);">Clear</button>
        </div>
    </div>
    <div class="main-div-body-searchflight-right-container-search" id="div_select_body_container"></div><?php
}
function checkTimeconflict()
{
    global $load_headers;
    $result = $load_headers->Check_Time_Conflict($_POST['time1'], $_POST['time2']);
    echo $result;
}
function UpdateAdate()
{
    global $db;
    $thistable = "flight_schedule";
    $realdates = implode(',', json_decode($_POST['pickedDates']));
    $thisdata = array('dates' => $realdates);  
    $thiswhere = array("recno" => $_POST['recno']);
    $result = $db->PDOUpdate($thistable, $thisdata, $thiswhere, $_POST['recno']);
    //file_put_contents("./dodebug/debug.txt", $result, FILE_APPEND);
    if(isset($result))
    {
        echo 'Success';
    }
    else
    {
        echo 'Failed';
    }
}
function DeleteThisflight()
{
    global $db;
    $thistable = "flight_schedule";
    $thisdata = array('isdeleted' => true);  
    $thiswhere = array("recno" => $_POST['recno']);
    $result = $db->PDOUpdate($thistable, $thisdata, $thiswhere, $_POST['recno']);
    //file_put_contents("./dodebug/debug.txt", $result, FILE_APPEND);
    if(isset($result))
    {
        echo 'Success';
    }
    else
    {
        echo 'Failed';
    }
}
function ValidateFlight()
{
    global $db;
    //file_put_contents("./dodebug/debug.txt", var_dump($_POST), FILE_APPEND);
    //QueryMe($thistype=null, $thistable=null, $thisfields=null, $thiswheres=null, $thisorderby=null, $thisgroupby=null, $ordering=null)
    $thistable = "flight_schedule";
    $thisfields = array('customer');
    $thiswhere = array('customer' => $_POST['sltcustomer'], 'actype' => $_POST['sltname'], 'flightnumber' => $_POST['txtflightnumber']);
    $result = $db->PDOQuery($thistable, $thisfields, $thiswhere);
    if(isset($result)) //Nott sure if isset() will check if some items is returned or at least something in asso array.
    {
       echo "Failed";
    }
    else
    {
        echo "Success";
    }

}
function ReselectDate()
{
    $from = $_POST['from']; //This will be either Add or Modify
    $thismonth = $_POST['thismonth'];
    $thisyear = $_POST['thisyear'];
    $pickeddates = json_decode($_POST['pickedDates']);
    $tempdate = strtotime($thismonth."/01/".$thisyear);
    $thiscurdate = date('m/d/Y', $tempdate);//We do this to include the 'Y' cuz it could go back to December if we are in January, this will make sure we get the right year.
    //file_put_contents("./dodebug/debug.txt", "thiscurdate: ".$thiscurdate, FILE_APPEND);
    PaintCalendar($_POST['recno'], $thiscurdate, $pickeddates, $from);
}
function UpdateDays()
{
    //$_POST['curdate'] = will come in as '10/2022'
    $thisday = date('m/d/Y', strtotime($_POST['curdate']));
    $thismonth = date('m', strtotime($thisday)); 
    $from = $_POST['from'];
    $datearray = [];
    $recno = $_POST['recno'];
    $i = 1;
    switch($_POST['thisid']){
        case "btnmondays":
            //file_put_contents("./dodebug/debug.txt", date('m', strtotime($thisday))." == ".$thismonth, FILE_APPEND); //passed
            while(date('m', strtotime($thisday)) == $thismonth)
            {
                $thisweekday = date('N', strtotime($thisday)); //1 = Monday, 7 = Sunday
                //file_put_contents("./dodebug/debug.txt", 'this week: '.$thisweekday, FILE_APPEND);
                if($thisweekday == 1)
                {
                    $datearray[] = $thisday;
                }
                $thisday = date('m/d/Y', strtotime('next monday', strtotime($thisday)));
            }
            //file_put_contents("./dodebug/debug.txt", $datestr, FILE_APPEND);
            break;
        case "btntuesdays":
            while(date('m', strtotime($thisday)) == $thismonth)
            {
                $thisweekday = date('N', strtotime($thisday)); //1 = Monday, 7 = Sunday
                if($thisweekday == 2)
                {
                    $datearray[] = $thisday;
                }
                $thisday = date('m/d/Y', strtotime('next tuesday', strtotime($thisday)));
            }
            break;
        case "btnwednesdays":
            while(date('m', strtotime($thisday)) == $thismonth)
            {
                $thisweekday = date('N', strtotime($thisday)); //1 = Monday, 7 = Sunday
                if($thisweekday == 3)
                {
                    $datearray[] = $thisday;
                }
                $thisday = date('m/d/Y', strtotime('next wednesday', strtotime($thisday)));
            }
            break;
        case "btnthursdays":
            while(date('m', strtotime($thisday)) == $thismonth)
            {
                $thisweekday = date('N', strtotime($thisday)); //1 = Monday, 7 = Sunday
                if($thisweekday == 4)
                {
                    $datearray[] = $thisday;
                }
                $thisday = date('m/d/Y', strtotime('next thursday', strtotime($thisday)));
            }
            break;
        case "btnfridays":
            while(date('m', strtotime($thisday)) == $thismonth)
            {
                $thisweekday = date('N', strtotime($thisday)); //1 = Monday, 7 = Sunday
                if($thisweekday == 5)
                {
                    $datearray[] = $thisday;
                }
                $thisday = date('m/d/Y', strtotime('next friday', strtotime($thisday)));
            }
            break;
        case "btnsaturdays":
            while(date('m', strtotime($thisday)) == $thismonth)
            {
                $thisweekday = date('N', strtotime($thisday)); //1 = Monday, 7 = Sunday
                if($thisweekday == 6)
                {
                    $datearray[] = $thisday;
                }
                $thisday = date('m/d/Y', strtotime('next saturday', strtotime($thisday)));
            }
            break;
        case "btnsundays":
            while(date('m', strtotime($thisday)) == $thismonth)
            {
                $thisweekday = date('N', strtotime($thisday)); //1 = Monday, 7 = Sunday
                if($thisweekday == 7)
                {
                    $datearray[] = $thisday;
                }
                $thisday = date('m/d/Y', strtotime('next sunday', strtotime($thisday)));
            }
            break;
        case "btnalldays":
            $numofdays = date('t', strtotime($thisday));
            $curday = date('d', strtotime($thisday));
            $curmonth = date('m', strtotime($thisday));
            $curyear = date('Y', strtotime($thisday));
            
            for($i=$curday; $i<=$numofdays; $i++)
            {
                $tempday = $i;
                if($i < 10)
                {
                    $tempday = "0$i";
                }
                $datearray[] = $curmonth."/".$tempday."/".$curyear;
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
                $tempday = $i;
                if($i < 10)
                {
                    $tempday = "0$i";
                }
                $thisdate = date('N', strtotime($curmonth."/".$tempday."/".$curyear));
                if($thisdate != 6 && $thisdate != 7)
                {
                    $datearray[] = $curmonth."/".$tempday."/".$curyear;
                }
            }
            break;
        default:
             break;
     }
     if($from == "Modify")
     {
        global $db;
        $newarray = [];
        //We need to make changes to the dates field with this datges, remove/add
        $thistable = "flight_schedule";
        $thisfields = array('dates');
        $thiswhere = array('recno' => $recno);
        $result = $db->PDOQuery($thistable, $thisfields, $thiswhere);
        if(isset($result))
        {
            //If we got something back, that means the there is some data so we need to find out if these dates are in this data.
            //If the dates are in this data, we need to remove it and update it back to the table.
            foreach($result as $rs)
            {
                $curdatearray = explode(',', $rs['dates']);
            }
            foreach($datearray as $newdate)
            {
                if(in_array($newdate, $curdatearray))
                {
                    //If found in $curdatearray, we remove from it.
                    if(($key = array_search($newdate, $curdatearray)) !== false) unset($curdatearray[$key]);
                }
                else
                {
                    array_push($curdatearray, $newdate);
                }
            }
            file_put_contents("./dodebug/debug.txt", implode(',', $newarray), FILE_APPEND);
            $thisdata = array('dates' => implode(',', $curdatearray));
            $thiswheres = array('recno' => $_POST['recno']);
            $newresult = $db->PDOUpdate($thistable, $thisdata, $thiswheres, $_POST['recno']);
        }
     }
    echo json_encode($datearray);
}
function UpdateFlight()
{
    global $db;
    if($_POST['from'] == "Query")
    {
        $thistable = "flight_schedule";
        $thisfields = array('recno', 'schedulearrival', 'scheduledeparture', 'dates');
        $thiswhere = array('customer' => $_POST['sltcustomer'], 'actype' => $_POST['sltname'], 'flightnumber' => $_POST['txtflightnumber']);
        $result = $db->PDOQuery($thistable, $thisfields, $thiswhere);
        if(isset($result))
        {
            echo json_encode($result);
        }
        else 
        {
            echo 'Failed';
        }
    }
    else
    {
        $thistable = 'flight_schedule';
        $thisid = $_POST['thisid'];
        //file_put_contents("./dodebug/debug.txt", "sessionlist: ".var_dump($_POST), FILE_APPEND);
        $thisdata = array(substr($thisid,3) => $_POST["$thisid"]);
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
}
function DeleteFlightbutton()
{
   echo '<button class="button" style="height: 30px; cursor: pointer;" id="btndeletethisflight" name="btndeletethisflight" onclick="deleteThisflight(this, '.$_POST['recno'].');" title="Click to delete this aircraft" >Delete</button>';
}
function SubmitFlight()
{
    global $db;
   
    $thistable = "flight_schedule";
    $realflightno = $_POST['txtflightnumber'];
    if(is_null($_POST['txtflightnumber']))
    {
        $realflightno = null;
    }
    $realschedulear = $_POST['txtschedulearrival'];
    if(is_null($_POST['txtschedulearrival']))
    {
        $realschedulear = null;
    }
    $realschedulede = $_POST['txtscheduledeparture'];
    if(is_null($_POST['txtscheduledeparture']))
    {
        $realschedulede = null;
    }
    $realdates = implode(',', json_decode($_POST['pickedDates']));
    $thisdata = array('customer' => $_POST['sltcustomer'], 'actype' => $_POST['sltname'], 'flightnumber' => $realflightno, 
        'schedulearrival' => $realschedulear, 'scheduledeparture' => $realschedulede, 'dates' => $realdates);
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
function ManageFlights()
{
    global $db, $pt;
    $recno = 0;
    $thisname = "";
    $thisaircraft = "";
    $thisfunctionchange = "";
    $thisfunctionclick = "";
    $thisflightno = '';
    $thisschedulea = ''; 
    $thisscheduled = '';
    $isdisabled = "";
    $isdisplay = "";
    $issltdisable = false;
    if($_POST['from'] == 'Modify')
    {
        $thisfunctionchange = 'updateFlight(this)';
        $isdisabled = "disabled";
        $isdisplay = 'display: none;';
        $issltdisable = true;
        
    }?>
    <table id="tblmanageaddflight" name="tblmanageaddflight" class="tbl-profile">
        <tr>
            <td class="manage-flight-lbl">Customer:</td>
            <td style="text-align: left;" class="manage-flight-lbl"><?php
                $pt->SltCustomer('customer')->GetSelect("sltcustomer", '', true, false, $thisfunctionchange, false, true);?>
            </td></tr>
        <tr>
            <td class="manage-flight-lbl">ACType:</td>
            <td style="text-align: left;"><?php
                $pt->SltAircraft('actype')->GetSelect("sltname", '', true, false, $thisfunctionchange, $issltdisable, true);?>
            </td>
        </tr>
        <tr>
            <td class="manage-flight-lbl">Flight#:</td>
            <td style="text-align: left;"><input class="manage-flight-input required" type="text" id="txtflightnumber" name="txtflightnumber" onchange="<?=$thisfunctionchange?>;" value="<?=$thisflightno;?>" placeholder="1111, 1234, 12b5" <?=$isdisabled?> /></td></tr>
        <tr>
            <td class="manage-flight-lbl">Schedule Arrival:</td>
            <td style="text-align: left;"><input class="manage-flight-input" type="text" id="txtschedulearrival" name="txtschedulearrival" onchange="checkTime(this);<?=$thisfunctionchange?>;" value="<?=$thisschedulea;?>" placeholder="ex: 12:00" <?=$isdisabled?> /></td></tr>
        <tr>
            <td class="manage-flight-lbl">Schedule Departure:</td>
            <td style="text-align: left;"><input class="manage-flight-input" type="text" id="txtscheduledeparture" name="txtscheduledeparture" onchange="checkTimeconflict(this);<?=$thisfunctionchange?>;" value="<?=$thisscheduled;?>" placeholder="ex: 23:00, 05:00+, '+' is for tomorrow." <?=$isdisabled?> /></td>
        </tr>
        <tr>
            <td class="manage-flight-lbl">Dates:</td>
            <td class="manage-flight-lbl" id="td_calendar" style="text-align: left; <?=$isdisplay?>" >
                <?php doMultidates($_POST['from'], $recno); ?>
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
            <!-- We will have the delete button here -->
            <tr><td class="tbl-profile-lbl" id="tdbuttondelete" colspan="2" style="width: 100%; text-align: center;"></tr><?php
        }?>
    </table><?php
}
function doMultidates($from, $recno)
{ ?>
    <div class="div-calendar-holder">
        <div class="div-calendar-holder-header"><?php
                    $thismonth = date('M');
                    $thisyear = date('Y');
                    $montharray = array('01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'May', '06' => 'Jun', '07' => 'Jul', '08' => 'Aug', 
                        '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dec');?>
            
                <button class="btn-manage-flight-date" id="btnprevious" onclick="previousMonth('<?=$from?>');">Pre</button>
                <select class='slt-manage-flight-month' name='sltmonth' id='sltmonth' onchange="changeDate(<?=$recno?>, '<?=$from?>');"><?php
                    foreach($montharray as $kmonth => $vmonth)
                    {
                        if($vmonth == $thismonth)
                        {?>
                            <option value='<?=$kmonth?>' selected><?=$vmonth?></option><?php
                        }
                        else
                        {?>
                            <option value='<?=$kmonth?>'><?=$vmonth?></option><?php                                
                        }
                    }?>
                </select>
                <select class='slt-manage-flight-month' name='sltyear' id='sltyear' onchange="changeDate(<?=$recno?>, '<?=$from?>');"><?php
                    for($i=($thisyear-6); $i<=($thisyear+6); $i++)
                    {
                        if($i == $thisyear)
                        {?>
                            <option value='<?=$i?>' selected><?=$i?></option><?php
                        }
                        else
                        {?>
                            <option value='<?=$i?>'><?=$i?></option><?php                                
                        }
                    }?>
                </select>
                <button style="margin-left: 10px;" class="btn-manage-flight-date" id="btnprevious" onclick="nextMonth('<?=$from?>');">Next</button>
         
        </div>
        <div class="div-calendar-holder-body">
            <table class="tbl-manage-flight-calendar" border="1" name="tbl_manage_flight_calendar" id="tbl_manage_flight_calendar">
                <thead>
                    <tr>
                        <td class="tbl-manage-flight-calendar-header" id="btnsundays" name="btnsundays" onclick="updateDays(this, <?=$recno?>, '<?=$from?>');">Sun</td>
                        <td class="tbl-manage-flight-calendar-header" id="btnmondays" name="btnmondays" onclick="updateDays(this,  <?=$recno?>, '<?=$from?>');">Mon</td>
                        <td class="tbl-manage-flight-calendar-header" id="btntuesdays" name="btntuesdays" onclick="updateDays(this,  <?=$recno?>, '<?=$from?>');">Tue</td>
                        <td class="tbl-manage-flight-calendar-header" id="btnwednesdays" name="btnwednesdays" onclick="updateDays(this,  <?=$recno?>, '<?=$from?>');">Wed</td>
                        <td class="tbl-manage-flight-calendar-header" id="btnthursdays" name="btnthursdays" onclick="updateDays(this,  <?=$recno?>, '<?=$from?>');">Thu</td>
                        <td class="tbl-manage-flight-calendar-header" id="btnfridays" name="btnfridays" onclick="updateDays(this,  <?=$recno?>, '<?=$from?>');">Fri</td>
                        <td class="tbl-manage-flight-calendar-header" id="btnsaturdays" name="btnsaturdays" onclick="updateDays(this,  <?=$recno?>, '<?=$from?>');">Sat</td>
                        <td style="width: 60px;"></td>
                    </tr>
                </thead>
                <tbody id="tbodymanageflight"><?php
                    $thiscurdate = date('M-Y');
                    paintCalendar($recno, $thiscurdate, array(), $from);?>                    
                </tbody>
            </table>
        </div>
    </div><?php
}
function PaintCalendar($recno, $thiscurdate, $pickeddates=[], $from='Add')
{
    //first day of the month
    $tempmonth = date('m', strtotime($thiscurdate));
    $tempyear = date('Y', strtotime($thiscurdate));
    $fistdayofmonth = date('m/01/Y', strtotime($thiscurdate));
    $lastdayofmonth = date('t', strtotime($thiscurdate));
    $firstdayofweek = date('w', strtotime($fistdayofmonth)); //Now I should get a number between 1 and 7, 1 = Monday and 7 = Sunday
    
    //file_put_contents("./dodebug/debug.txt", "From in paint: ".$from, FILE_APPEND);
    $usethisfunction = "pickDates(this, '$from')";
   
    $k=1; //track all the TDs and use it to name the tds
    $isstarted = false;
    $n = 1; //tracks the actual date inside the TD
    $tempn = "";
    for($i=0; $i<6; $i++) //Tracks the table rows
    {?>
        <tr><?php
            for($j=0; $j<8; $j++) //tracks the columns, the 7th row is for the All buttons
            {
                if($j < 7) //We come in here when we are less than 7
                {
                    $tempn = $n;
                    if($n<10)
                    {
                        $tempn = "0$n";
                    }
                    $tempcurdate = "$tempmonth/$tempn/$tempyear";
                    //file_put_contents("./dodebug/debug.txt", $tempcurdate.'-', FILE_APPEND);
                    $bgcolor = "";
                    if(in_array($tempcurdate, $pickeddates))
                    {
                        $bgcolor = "style='background-color: rgba(255,99,71);'";
                    }
                    if($j == $firstdayofweek && $isstarted == false)
                    {?>
                        <td class="tbl-manage-flight-calendar-dates" name="day$<?=$k?>" id="day$<?=$k?>" onclick="<?=$usethisfunction?>;" <?=$bgcolor?>><?=$n?></td><?php
                        $n++;
                        $isstarted = true;
                    }
                    else
                    {
                        if($isstarted == true && $n <= $lastdayofmonth)
                        {?>
                            <td class="tbl-manage-flight-calendar-dates" name="day$<?=$k?>" id="day$<?=$k?>" onclick="<?=$usethisfunction?>;" <?=$bgcolor?>><?=$n?></td><?php
                            $n++;
                        }
                        else
                        {?>
                            <td></td><?php                                            
                        }
                    }
                }
                else
                {
                    if($i==0)
                    {?>
                        <td name="day$<?=$k?>">
                            <button class="manage-flight-btn" id="btnalldays" name="btnalldays" onclick="updateDays(this,  <?=$recno?>, '<?=$from?>');">All Days</button>
                        </td><?php
                    }
                    else if($i == 1)
                    {?>
                        <td name="day$<?=$k?>">
                            <button class="manage-flight-btn" id="btnallweekdays" name="btnallweekdays" onclick="updateDays(this,  <?=$recno?>, '<?=$from?>');">All Days No Weekend</button>
                        </td><?php 
                    }
                    else
                    {?>
                        <td>&nbsp;</td><?php 
                    }
                }
            }?>                       
        </tr><?php
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
        <div class="main-div-body-manageaircraft">
            <table>
                <tr>
                    <td>
                        <div class="main-div-body-manageaircraft-left" style="margin-top: -30px;">
                            <div class="main-div-body-manageaircraft-header">Manage Flight<br><span style="font-size: .4em;">(Add to schedule)</span></div>
                            <div style="float: left;">
                                <div class="div-menu-manageaircraft" id="div_flow" onclick="manageFid();">Fid</div><?php
                                if($_SESSION['profile'] == "SU" || $_SESSION['profile'] == "SV")
                                {?>
                                    <div class="div-menu-manageaircraft" id="div_flow" onclick="addFidnot(this);">Add Fid Note</div>
                                    <div class="div-menu-manageaircraft" id="div_flow" onclick="showFidnote(this);">Show Fid Note</div>
                                    <div class="div-menu-manageaircraft" id="div_flow" onclick="manageFlow();">Flow</div>
                                    <div class="div-menu-manageaircraft" id="div_add" onclick="manageFlights(this, 'Add');">Add Flight</div>
                                    <div class="div-menu-manageaircraft" id="div_modify" onclick="manageFlights(this, 'Modify');">Modify Flight</div>                                
                                    <div class="div-menu-manageaircraft" id="div_search" onclick="searchInterface(this, 'Modify');">Search Flight</div><?php
                                }?>                                
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