<?php
require("./common/page.php");
require("./common/pdocon.php");
require("./common/sendmail.php");
require("./common/classes/pageloaderclass.php");
require("./common/classes/dateclass.php");
require("./common/classes/emailclass.php");
require("./common/classes/passwordclass.php");
require("./common/classes/loginclass.php");
require("./common/classes/employeenoclass.php");
require("./common/prompt.php");

$load_headers = new PageloaderClass();
$db = new PDOCON();
$nd = new Date_Class();
$ne = new Email_Class();
$pc = new Password_Class();
$nl = new Login_Class();
$en = new Employeeno_Class();
$pr = new PROMPT();

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
            //need to check for when trying to undo the OFF for all and for individual.
            $(document).ready(function(){
                sltDefault();
            });
            
            function sltDefault(){
                //alert($('body').data('recno'));
                var d = new Date();
                var thisday = d.getDate();
                var thismonth = d.getMonth();
                var thisyear = d.getFullYear();
                $('body').data('currentmonth', (thismonth+1)+"/"+thisday+"/"+thisyear); 
                //by default we will set this to current month/year, also added 1 tot thismonth cuz in javascript, month starts at 0, adding one will give us a accurate rep month.
                //alert('0: '+$('body').data('currentmonth'));
                $("#txtdates").multiDatesPicker('show');
                $('body').data('selectview', 'Monthly');
             
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=ManageSchedules&from=Add&recno='+$('body').data('recno')+
                        '&selectview=<?php echo empty($_POST['selectview']) ? 'Monthly' : $_POST['selectview'] ?>&thisdate=<?php echo empty($_POST['thisdate']) ? '' : $_POST['thisdate'] ?>', function(result){
                    $("#main_div_body_schedule_right_container").html(result);
                });
              
            }
            function fillGraph(){
                //$('body').data('curredno');
                //$('body').data('startdate');
                thismonth = $("#sltmonth").find(":selected").val();
                thisyear = $("#sltyear").find(":selected").val();
                thisdate = thisyear+'-'+thismonth;
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=FillGraph&thisrecno='+$('body').data('recno')+'&thisdate='+thisdate, function(result){
                    if(result != "No Record"){
                        myData = JSON.parse(result);

                        $.each(myData, function(thisday, thisdata){
                            //alert(thisday +' -> '+ thisdata);
                            //We get the thisday - day
                            for(i=0; i<thisdata.length; i++){
                                //span_n_v10:00, where n is the day
                                //alert(key +' -> '+ thisdata[i]);
                                alert($("#span_"+thisday+"_v"+thisdata[i]).text());

                                $("#span_"+thisday+"_v"+thisdata[i]).text("-----Bookeded");
                            }                        
                        });
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
                                    else{
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

            function pickDates(obj, thisrecno, thisday, from, isNext=false, thisdate){
                //When isNext is true, it is coming from next month's date so thisday will come in as a date in format of YYYY-mm-dd already
                if(isNext == false){
                    $("body").data('selecteddate', thisday);
                    if(thisrecno == "All"){
                        //We do not want to do anything if this is for All barbers...
                        alert("Please select a barber to book an appointment.");
                        return(false);
                    }
                    if(thisday < 10){
                        thisday = "0"+thisday;
                    }
                    thisdate = $("#sltmonth").val()+'/'+thisday+'/'+$("#sltyear").val();
                }
                //alert(thisdate);
                //We are handling update to single date click
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=PickDates&pickeddate='+thisdate+'&recno='+thisrecno, function(result){
                    //Now that we have the html back, we will want to post this div to the screen inside the big div, main-div.
                    if($("#div_pickedday_container").length > 0){
                        //If we have this div, that means the div is showing so we need to remove before we append to avoid duplicates.
                        $("#div_pickedday_container").remove();
                    }
                    $("#main_div_body_schedule_right_container").append(result);
                    $("#main_div_body_schedule_right_container_holder").show();
                    $("#div_pickedday_container").css("z-index", 200);
                    
                    fillService(thisrecno, thisday, from);
                });

            }
            function fillService(thisrecno, thisday, from){
                var thisdate = $("#sltmonth").val()+'/'+thisday+'/'+$("#sltyear").val();
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=FilledService&thisdate='+thisdate+'&thisrecno='+thisrecno, function(result){
                    //alert(result);
                    if(result != "No Record"){                        
                        //We should get something like {"recno":n, "slot":"nn:nn", "time":n} where n is a number representative.   
                        myData = JSON.parse(result);
                        $.each(myData, function(key, data){
                            //If we are here, we know we have to have at least 1 match appointment so we will
                            //loop through the div-pickedday-container-header-body-slots class and get the text
                            //and if it matches the slot, then we paint the corresponding subdiv.
                            //alert(key + ' -> '+ data['slot'] + ' and ' + data['time']);
                           $(".div-pickedday-container-header-body-slots").each(function(){
                               
                                //alert($(this).text().trim().slice(0, -1)+ ' == '+ data['slot'].trim());
                               
                               //We want to slice the last char to get rid of ':' so we get a string of 0:00
                               //alert(jQuery.type($(this).text().trim().slice(0, -1))+ ' and '+ jQuery.type(data['slot'].trim()));
                               //alert($(this).text().trim()+" === "+data['slot'].trim());
                              
                                if($(this).text().trim() === data['slot'].trim()){
                                    //Since we have a match, we want the i.d and get the counter
                                    tempid = $(this).prop('id');  //div_slot_counter
                                    splittempid = tempid.split('_');
                                    thiscounter = splittempid[2];
                                    //alert(thiscounter);
                                    //Now we get the real id, we want to change the background color and the text
                                    //span_app_text+counter
                                     //So we will need to check on time, if time is greater than 30, then, we have to take up 2 slots and so on...
                                    if(data['time'] != "OFF"){
                                        $("#span_app_text"+thiscounter).text("Booked");
                                        $("#span_app_text"+thiscounter).removeClass("spn-app-cursor");
                                        $("#div_pickedday_container_header_body_app_ava"+thiscounter).css("background-color", "#660000");

                                        caltime = data['time'] - 30;
                                         while(caltime > 0){
                                            //If subtracting 30 from the time is still more than 0, that means we need to also change the next slot as well.
                                             thiscounter++;
                                             caltime = caltime - 30;
                                             //Continous is when the time for the appoint is more than 30 minutes overlowing into the next slot or more...
                                             $("#span_app_text"+thiscounter).text("Continous");
                                             $("#div_pickedday_container_header_body_app_ava"+thiscounter).removeClass("spn-app-cursor");
                                             $("#div_pickedday_container_header_body_app_ava"+thiscounter).css("background-color", "#660000");
                                             if($("body").data("employee_session").length > 0){
                                                $("#img_"+thiscounter).css("visibility", "hidden");
                                            }
                                        }
                                    }
                                    else{
                                        $("#span_app_text"+thiscounter).text("OFF");
                                        $("#span_app_text"+thiscounter).removeClass("spn-app-cursor");
                                        $("#div_pickedday_container_header_body_app_ava"+thiscounter).css("background-color", "#660000");
                                        if($("body").data("employee_session").length > 0){
                                            $("#img_"+thiscounter).prop('class', "img-monthly-off");
                                        }
                                    }
                                    return(false);
                                }
                                
                            });
                        })
                    }
                });
                
            }
            function previousMonth(recno, from='Add'){
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
                //alert($('body').data('selectview'));
                if($('body').data('selectview') != "Daily"){
                    $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=ReselectDate&recno='+recno+'&from='+from+'&thismonth='+thismonth+'&thisyear='+thisyear+'&selectview='+$('body').data('selectview')+'&pickedDates='+JSON.stringify(pickedDates), function(result){
                        //alert(result);
                        if(result == "Failed"){
                            alert('Failed to paint calendar.  Please contact your administrator.');
                            return(false);
                        }
                        else
                        {
                            $("#div_calendar_holder_body").html(result);
                        }
                    });
                }
                else{
                    selectWeekdircaldaily(recno, thisyear, thismonth, "NONE", from);
                }
            }
            function nextMonth(recno, from='Add'){
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
                if($('body').data('selectview') != "Daily"){
                    $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=ReselectDate&recno='+recno+'&from='+from+'&thismonth='+thismonth+'&thisyear='+thisyear+'&selectview='+$('body').data('selectview'), function(result){
                       if(result == "Failed"){
                            alert('Failed to paint calendar.  Please contact your administrator.');
                            return(false);
                        }
                        else
                        {
                            $("#div_calendar_holder_body").html(result);
                        }
                    });
                }
                else{
                    selectWeekdircaldaily(recno, thisyear, thismonth, "NONE", from);
                }
            }
            function changeDate(recno=0, from="Add"){
                //from = Add or Modify, we default it to Add
                thismonth = $("#sltmonth").find(":selected").val();
                thisyear = $("#sltyear").find(":selected").val();
                $('body').data('currentmonth', thismonth+'/01/'+thisyear);
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=ReselectDate&recno='+recno+'&from='+from+'&thismonth='+thismonth+'&thisyear='+thisyear+'&selectview='+$('body').data('selectview')+'&pickedDates='+JSON.stringify(pickedDates), function(result){
                   if(result == "Failed"){
                        alert('Failed to paint calendar.  Please contact your administrator.');
                        return(false);
                    }
                    else
                    {
                        $("#tbodymanageschedule").html(result);
                    }
                });
            }
            function closeDive(){
                $("#div_pickedday_container").remove();
                $("#main_div_body_schedule_right_container_holder").hide();
                //location.reload();
                //window.location.href = "schedule.php?recno="+recno;
                
                //thismonth = $("#sltmonth").find(":selected").val();
                //thisyear = $("#sltyear").find(":selected").val();

                if($('body').data('selectview') == "Monthly"){
                    thisdate = "";
                    thisobj = $("#div_view_monthly")[0];
                }
                else if($('body').data('selectview') == "Weekly"){
                    var dateArray = [];
                    $(".weekly-col-day-lbl").each(function(){
                        dateArray.push($(this).prop('id'));
                    });
                    firstdate = dateArray[0];
                    seconddate = dateArray[dateArray.length - 1];
                    
                    thisdate = firstdate;
                    thisobj = $("#div_view_weekly")[0];
                }
                else{
                    //Daily
                    thisdate = $("#hid_daily_date").val();
                    thisobj = $("#div_view_daily")[0];
                }
                selectView(thisobj);
                //window.location.href = "schedule.php?recno="+$('body').data('recno')+'&selectview='+$('body').data('selectview')+'&thisdate='+thisdate;
            }
            function selectBarber(recno){
                //We get here when user clicked on the barber image.
                //recno should be a number but it can be 'All' as well.
                //User must select a barber to be able to schedule an appointment.  User will not be able to get the appointment interace until 
                //it loads with individual.
                window.location.href = "schedule.php?recno="+recno;
                
            }
            function doAppointment(obj, recno, date, slot, counter, selectview = ""){
                //alert('in do App');
                //$("#div_pickedday_container_header_body_app_ava"+counter).css("background-color", "lightblue");
                //alert("recno: "+recno+" and date: "+date+" slot: "+slot);
                //!@#$%
                //alert($(obj).text());
                if($(obj).text().trim() == "Continous" || $("body").data("employee_session").length == 0 || $("#span_app_text"+counter).text().trim() == "OFF"){
                    //alert('in here');
                    return(false);
                }
                //alert(slot);
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=DoAppointments&recno='+recno+'&counter='+counter+'&thisdate='+date+'&slot='+slot+'&selectview='+selectview, function(result){
                    //alert('hi');
                    if(selectview == "" || selectview == "Weekly" || selectview == "Monthly"){
                        $("#div_pickedday_container").css("z-index", 100);
                        $("#main_div_body_schedule_right_container_holder").show();
                        $("#main_div_body_schedule_right_container_holder").css("z-index", 200);
                        $("#main_div_body_schedule_right_container").append(result);
                        $("#div_sub_app").css("z-index", 300);
                    }
                    else{
                        $("#div_info_container_daily").html(result);
                    }
                });
            }
            function checkApptxtfields(){
                if($("#txt_name").val() == ""){
                    alert("Please enter a name.");
                    $("#txt_name").focus();
                    return(false);
                }
                if(isPhonenumber($("#txt_phoneno").val()) == false){
                    alert("Please enter a phone number.")
                    $("#txt_phoneno").focus();
                    return(false);
                }   
                
                if(validateEmail($("#txt_email").val()) == false){
                    alert("Please enter a valid email.");
                    $("#txt_email").focus();
                    return(false);
                }
            }
            function bookAppointment(obj,thisrecno, thisdate, thisslot, counter){
                //We want to make sure name, phone#, and email is not empty
                if(checkApptxtfields() == false){
                    return(false);
                }
                //alert('bookApp');
                thisservicerecno = "";
                thisservicetime = 0;
                //We have to loop through the div to find the color of the service the guest selected.
                $(".schedule-service-tbl-tr-bg").each(function(){
                    curbg = $(this).css('background-color'); 
                    
                    if(curbg == 'rgb(0, 153, 0)'){
                        //Since it is green, we want to get the recno.
                        //tr_service_recno#
                        splitid = $(this).prop('id').split('_');
                        if(thisservicerecno == ""){
                            
                            thisservicerecno = splitid[2];
                            thisservicetime = parseInt(splitid[3]);
                        }
                        else{
                            thisservicerecno = thisservicerecno+","+splitid[2];
                            thisservicetime = thisservicetime + parseInt(splitid[3]);
                        }
                    }
                });
                //alert(thisservicerecno);
                if(thisservicerecno == ""){
                    alert("Please select at least one service.")
                    return(false);
                }
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=BookAppointment&thisrecno='+thisrecno+'&thisdate='+thisdate+'&thisslot='+thisslot+'&'+$("#frmservice").serialize()+'&thisservicerecno='+thisservicerecno, function(result){
                        if(result == "Booked"){
                            $("#div_sub_app").remove();
                            $("#main_div_body_schedule_right_container_holder").css("z-index", 100);
                            $("#div_pickedday_container").css("z-index", 200);
                            $("#main_div_body_schedule_right_container_holder").hide();
                            //Now we gotta paint the service darkred to mark it as unavailable and make it unclickable.
                            //  div_pickedday_container_header_body_app_ava12345 where 12345 is counter
                            $("#div_pickedday_container_header_body_app_ava"+counter).css("background-color", "#660000");

                            //We want to change the text to 'Booked'
                            $("#span_app_text"+counter).text("Booked");

                            //We also want to remove the class
                            $("#span_app_text"+counter).removeClass("spn-app-cursor");

                            //We want to remove the onclick since we already scheduled and no more can be done for this time.
                            $("#span_app_text"+counter).prop('onclick', null).off('click');

                            //We want to see if we will have a continous service

                            thisservicetime = thisservicetime - 30;
                            //alert(thisservicetime);
                            while(thisservicetime > 0){
                                counter++;
                                $("#div_pickedday_container_header_body_app_ava"+counter).css("background-color", "#660000");
                                $("#span_app_text"+counter).text("Continous");
                                $("#span_app_text"+counter).removeClass("spn-app-cursor");
                                $("#span_app_text"+counter).prop('onclick', null).off('click');
                                thisservicetime = thisservicetime - 30;
                            }
                            alert("Thank you for making an appointment with Divirsity Fade Barbershop.  Please check your email for confirmation.");
                            if($("body").data("selectview") == "Weekly"){
                                selectView($("#div_view_weekly")[0]);
                            }
                            if($("body").data("selectview") == "Monthly"){
                                selectView($("#div_view_monthly")[0]);
                            }
                            else{
                                selectView($("#div_view_daily")[0]);
                            }
                        }
                        else{
                            alert("Booking failed.  Please try again.");
                            return(false);
                        }
                    }
                );
            }
            function cancelAppointment(obj, recno, counter, thisdate, slot){
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=CancelAppointment&recno='+recno+'&counter='+counter+'&thisdate='+thisdate+'&slot='+slot, function(result){
                    $("#div_pickedday_container_header_body_app_ava"+counter).html(result);
                });
            }
            function cancelAppointmentservice(obj, recno, counter, thisdate, slot, selectview){
                //We need to find out which select is selected and refresh upon that only, Daily, Weekly, or Monthly
                $("#div_sub_app").remove();
                $("#main_div_body_schedule_right_container_holder").hide();
                $("#main_div_body_schedule_right_container_holder").css("z-index", 100);
                $("#div_pickedday_container").css("z-index", 200);

                splitthisdate = thisdate.split('/');
                thisday = splitthisdate[1];
                if($('body').data('selectview') == "Monthly"){
                    pickDates(obj, recno, thisday, "from", true, thisdate); //We want to redraw this build so we get the latest change.
                }
                else if($('body').data('selectview') == "Daily"){
                    selectView($("#div_view_daily")[0]);
                }
                else{
                    //We need to reload weekly
                    selectView($("#div_view_weekly")[0]);
                }   

            }
            
            function selectService(obj){
                //darker one is rgb(242, 242, 242), //very light gray
                //The green one is rgb(0, 153, 0),  //green
                //alert('sltService');
                curbg = $(obj).css('background-color'); 
                if(curbg == 'rgb(242, 242, 242)'){
                    //selected
                    $(obj).css('background-color', 'rgb(0, 153, 0)');
                }
                else{
                    //unselect
                    $(obj).css('background-color', 'rgb(242, 242, 242)');
                }
            }
            function updateService(obj, recno, service_recno, field, date, slot){
                //alert(recno);
                thisval = "";
                if(checkApptxtfields() == false){
                    return(false);
                }
                if(field == "sr_recno"){
                    $(".schedule-service-tbl-tr-bg").each(function(){
                       if($(this).css('background-color') == 'rgb(0, 153, 0)'){
                           //alert($(this).prop('id'));
                           //if green now, we want to unselect it
                           splitid = $(this).prop('id').split('_');
                           if(thisval == ""){
                               thisval = splitid[2];
                           }
                           else{
                               thisval = thisval+","+splitid[2];
                           }
                       }
                   });
                }
                else if(field == "iscancelled"){
                    thisval = true;
                }
                else{
                    thisval =  $(obj).val();
                }
                //Now we need to loop through the services and get the service recno so we can update it.
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=UpdateService&thisrecno='+recno+'&thisval='+thisval+'&thisfield='+field+'&thisdate='+date+'&thisslot='+slot, function(result){
                    //alert(result);
                    if(result != "Success"){
                        alert("Failed update.  File schedule on line 492.");
                    }
                    else{
                        if(field == "iscancelled"){
                            alert("Appointment cancelled");
                            $("#div_sub_app").remove();
                            //Now need to call to repaint base on the date and select view.
                            selectView($("#div_view_weekly")[0]);
                        }
                    }
                });
            }
            function dragThis(obj){
                $(obj).draggable();
            }
            function selectView(obj){
                //alert($(obj).prop('id'));
                //circle-me-text-view-options-off
                //alert($(obj).text());
                //Daily, Weekly, Monthly
                //return(false);
                thisview = "";
  
                $(".select-view").each(function(){
                    if($(obj).prop('id') == $(this).prop('id')){
                        $(this).addClass('circle-me-text-view-options-on').removeClass('circle-me-text-view-options-off');
                        thisview = $(obj).text();
                        $('body').data('selectview', thisview);
                    }
                    else{
                        $(this).addClass('circle-me-text-view-options-off').removeClass('circle-me-text-view-options-on');
                    }
                });
                if($(obj).text() == "Monthly"){
                    $("#btn_forward").hide();
                    $("#btn_backward").hide();
                }
                else{
                    $("#btn_forward").show();
                    $("#btn_backward").show();
                    if($(obj).text() == "Daily"){
                        
                    }
                }
                thisday = "";
                if($(obj).text() == "Daily" && $("#hid_daily_date").length > 0 ){
                    thisday = $("#hid_daily_date").val();
                    //alert($("#hid_daily_date").val());
                }
                thismonth = $("#sltmonth").find(":selected").val();
                thisyear = $("#sltyear").find(":selected").val();
                //alert('thismonth: '+thismonth+' and this year: '+thisyear);

                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=SelectView&thisrecno='+$('body').data('recno')+'&thisday='+thisday+'&thismonth='+thismonth+'&thisyear='+thisyear+'&selectview='+thisview, function(result){
                    //alert(result);
                    $("#div_calendar_holder_body").empty();

                    $("#div_calendar_holder_body").html(result);

                });
            }
            function selectWeekdir(obj,thisrecno, thisdir, from){
                //We only get here if we are doing the small next and small previous "<" and ">"
                //
                //weekly-col-day-lbl, the week column has this class in common so we will use it to get the start and end dates
                //We want to change the month selectoni to previous, but before that we want to find out what is the current select?
                //If it is already Jan, we want it to go to Dec but up down the year.
                dateArray = [];
                $(".weekly-col-day-lbl").each(function(){
                    //alert($(this).prop('id'));
                    dateArray.push($(this).prop('id'));
                });
                //dateArray.sort();
               /*
                dateArray.forEach(function(item){
                    alert(item);
                });
                */
                //alert('month: '+thismonth+' && year: '+thisyear);
                $('body').data('currentmonth', thismonth+'/01/'+thisyear);
    
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=SelectWeekdir&recno='+thisrecno+'&from='+from+'&selectview='+$('body').data('selectview')+'&thisdir='+thisdir+'&datearray='+JSON.stringify(dateArray), function(result){    
                    //alert(result);
                    var thisarray = JSON.parse(result);
                    
                    //WE are updating the month and year regardless
                    $("#sltyear").val(thisarray[0]);            
                    $("#sltmonth").val(thisarray[1]);

                    //[$thischange, $newyear, $newmonth, $startofweek, $lastofweek];
                    if($('body').data('selectview') == "Weekly"){
                        //alert('inside weekly');
                                        //thisdir will be either 'Forward' or 'Previous'
                        thisdir = "Previous";
                        if($(obj).prop('id') == "btn_forward"){
                            thisdir = "Forward";
                        }
                         dateArray.push(thisdir);
                        selectWeekdircal(thisrecno, dateArray, $('body').data('selectview'), from);
                    }
                    else{
                        //alert('inside daily');
                        selectWeekdircaldaily(thisrecno, thisarray[0], thisarray[1], thisarray[2], "", from);
                    }
                });
            }
            function selectWeekdircal(thisrecno, thiscurdate, selectview, from){
                //alert(thisyear+' and '+thismonth+' and startweek: '+startweek);
                thismonth = $("#sltmonth").find(":selected").val();
                thisyear = $("#sltyear").find(":selected").val();
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=SelectWeekdircalweek&thisrecno='+thisrecno+'&from='+from+'&thismonth='+thismonth+'&thisyear='+thisyear+
                        '&selectview='+selectview+'&thiscurdate='+JSON.stringify(thiscurdate), function(result){    
                    $("#div_calendar_holder_body").empty();
                    $("#div_calendar_holder_body").html(result);
                    
                });
            }
            function selectWeekdircaldaily(thisrecno, thisyear, thismonth, thiscurdate, from){
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=SelectWeekdircaldaily&thisrecno='+thisrecno+'&from='+from+'&thismonth='+thismonth+'&thisyear='+thisyear+
                        '&selectview='+$('body').data('selectview')+'&thiscurdate='+thiscurdate, function(result){    
                    //alert(result);
                    $("#div_calendar_holder_body").html(result);
                    
                });
            }
            function selectSlot(obj, thisrecno, thiscurdate, selectview, slot, n, from){

                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=SelectSlotDaily&thisrecno='+thisrecno+'&from='+from+'&thisdate='+thiscurdate+'&selectview='+selectview+'&slot='+slot+'&timeframe='+$("#hid_daily_"+n).val(), function(result){    
                    //alert(result);
                    if(result != "NONE"){
                        $("#div_info_container_daily").html(result);
                    }
                    else{
                        doAppointment(obj, thisrecno, thiscurdate, slot, n, 'Daily');
                    }
                    
                });
            }
            function completeCut(){
                alert('Complete this cut');
            }
            function updateDailycomment(obj, thisrecno, thisfield){
                thisval = $(obj).val();
                //alert(thisval);
                txtarray = [thisval];
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=UpdateDailycomment&thisrecno='+thisrecno+'&thisfield='+thisfield+'&thisval='+JSON.stringify(txtarray), function(result){    
                    if(result == "Failed"){
                        alert("Failed to update");
                    }  
                });
            }
            function showServiceselect(obj){
                if($(obj).text() == "+"){
                    $("#sltservice").show();
                    $("#tr_hidden_add_service_alert").show();
                    $(obj).text("-");
                }
                else{
                    $("#sltservice").hide();
                    $("#tr_hidden_add_service_alert").hide();
                    $(obj).text("+");
                }
            }
            function addService(obj, thisrecno){
                //thisrecno is the recno for table schedule_dates recno column
                //$(obj).val() will be the recno for the table service
                //alert(thisrecno+' and '+$(obj).val());
                //alert('here');
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=AddService&thisrecno='+thisrecno+'&thisval='+$(obj).val(), function(result){  
                    //alert(result);
                    if(result != "Failed"){
                        //First we want to remove these TRs inside this table
                        
                        selectView($("#div_view_daily")[0]);
                        selectSlot(obj, $("body").data('recno'), $("#hid_daily_date").val(), "Daily", $("#hid_daily_slot").val(), "", "");
                        //After we reload, we want to display the selected slot.
                        /*
                        //We don't do the below, we chose to reload, easier
                        $(".tr-service").remove();
                        
                        //Now we want to add the rows to the end of this table tbl_service
                        $("#tbl_service").append(result);
                        
                        //Once we finish, we will want to update the total time and total cost.
                        reCalculatetotals(thisrecno);
                         */
                    }  
                });
            }
            function reCalculatetotals(thisrecno){
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=ReCalculatetotals&thisrecno='+thisrecno, function(result){  
                    //alert(result);
                    //First we need to remove the total div
                    $("#div_totals").html(result);
                });
            }
            function editService(obj){
                $(".span-minus").toggle();
            }
            function removeService(obj, thisrecno, thisservicerecno){
                //thisrecno is the recno in schedule_dates
                //thisservicerecno is the recno in service_table
                //status is 'Remove'
                
                //alert(thisrecno+" "+thisservicerecno+" "+status);
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=RemoveService&thisrecno='+thisrecno+'&thisservicerecno='+thisservicerecno, function(result){  
                    //alert(result);
                    //First we need to remove the total div
                    if(result != "Failed"){
                        selectView($("#div_view_daily")[0]);
                        selectSlot(obj, $("body").data('recno'), $("#hid_daily_date").val(), "Daily", $("#hid_daily_slot").val(), "", "");
                    }
                });
            }
            function setthisDate(obj, thisrecno, thisday, thisdate, slot, counter='All'){
                //setthisDate(this, ".$_SESSION['user'].", ".date('M d Y', strtotime($_POST['pickeddate'])).", \'$slotarrayhr[$i]\')
                //thisrecno is the barbar's recno in the users table
                //thisdate is the date in format of M d Y
                //slot is going to be in format of 00:00 or 'All' for all the slots
                //counter will be a number or will default to null for when the user clicked 'All'
                if($("body").data("employee_session").length == 0){
                    return(false);
                }
                if($("#span_app_text"+counter).text().trim() == "Continous"){
                    return(false);
                }
                if(slot == "All"){
                    isconfirm = confirm("Are you taking the whole day off? Press OK or Cancel.");
                }
                else{
                    if($("#span_app_text"+counter).text().trim() != "OFF"){
                        isconfirm = confirm("Are you taking "+slot+" off today? Press OK or Cancel.");
                    }
                    else{
                        //OFF, but we want to turn it back on, don't need confirmation, just do it.
                        isconfirm = true;
                    }
                }
                if(isconfirm == true){
                    //alert(slot);
                    if(slot != "All"){
                        //div_pickedday_container_header_body_app_ava+counter
                        //alert($("#span_app_text"+counter).text());
                        if($("#span_app_text"+counter).text().trim() == "Booked"){
                            isconfirm = confirm("The system will send an email to this customer to inform them of the cancellation.  Press OK to continue with the cancellation or Cancel to keep the appointment.");
                        }
                        if(isconfirm == false){
                            return(false)
                        }
                    }
                    $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=SetthisDate&thisrecno='+thisrecno+'&thisdate='+thisdate+'&thisslot='+slot, function(result){  
                        if(result != "Success"){
                            alert(result);
                        }
                        else{
                            pickDates(obj, thisrecno, thisday, from="", false, thisdate)
                        }
                    });
                }
                else{
                    return(false);
                }
            }
        </script>
    </head>
    <body>
        <?php
            Main();
        ?>
    </body>
</html><?php
function SetthisDate()
{
    //thisrecno is the barbar's recno in the users table
    //thisdate is the date in format of M d Y
    //slot is going to be in format of 00:00 or 'All' for all the slots
    global $db, $ne, $load_headers;
    $thistable = "schedule_dates";
    file_put_contents('./dodebug/debug.txt', "thislot: ".$_POST['thisslot']." \n", FILE_APPEND);
    if($_POST['thisslot'] == "All")
    {
        $sql = "SELECT sr_recno FROM schedule_dates WHERE uf_recno = ".$_POST['thisrecno']." AND date= '".date('Y-m-d', strtotime($_POST['thisdate']))."' AND iscancelled = false AND isdeleted=false";
        //file_put_contents('./dodebug/debug.txt', "SetthisDate sql 1: $sql \n", FILE_APPEND);
        $result = $db ->PDOMiniquery($sql);
        if($db ->PDORowcount($result) > 0)
        {
            echo "There is active appointment for this day.  Please resolve it before action can be taken.";
        }
        else
        {
            //Since we are here, that means there is no appointments but we want to turn off this day.
            $slotarrayhr = ['10:00', '10:30', '11:00', '11:30', '12:00', '12:30', '1:00', '1:30', '2:00', '2:30', '3:00', '3:30', '4:00', '4:30', '5:00', '5:30'];
            
            for($i=0; $i<count($slotarrayhr); $i++)
            {
                $thisdata = [
                    "uf_recno" => $_POST['thisrecno'], 
                    "date" => date('Y-m-d', strtotime($_POST['thisdate'])), 
                    "slot" => $slotarrayhr[$i], 
                    "note" => "Marked OFF by SELF.", 
                    "isOff" => true];
                $inresult = $db->PDOInsert($thistable, $thisdata);
            }
            echo "Success";
            
        }
    }
    else
    {
        //Single record only
        $sql = "SELECT recno, sr_recno, guest, date, email, isOff FROM schedule_dates WHERE uf_recno = ".$_POST['thisrecno']." AND date= '".date('Y-m-d', strtotime($_POST['thisdate']))."' and slot = '".$_POST['thisslot']."' AND iscancelled = false AND isdeleted=false";
        //file_put_contents('./dodebug/debug.txt', "SetthisDate sql 1: $sql \n", FILE_APPEND);
        $result = $db ->PDOMiniquery($sql);
        if($db ->PDORowcount($result) > 0)
        {
            foreach($result as $rs)
            {
                $guestdate = $rs['date'];
                $guestname = $rs['guest'];
                $guestemail = $rs['email'];  
                $isOff = $rs['isOff'];
                $recno = $rs['recno']; //This is the recno for this table schedule_dates
            }
            if($isOff == false)
            {
                //We will need to email the customer about the cancellation.
                //file_put_contents('./dodebug/debug.txt', "SetthisDate sql 1: failing here? \n", FILE_APPEND);
                $thisdata = ['note' => "Marked OFF by SELF.", "iscancelled" => true, 'isOff' => true];
                $thiswhere = ["recno" => $rs['recno']];
                $result = $db->PDOUpdate($thistable, $thisdata, $thiswhere);
                if($result == "Success")
                {

                    $sentto = Array();
                    $replyto = Array();
                    $ccto = Array();
                    $bccto = Array();
                    $attachment = Array();


                    $guestdate = $rs['date'];
                    $guestname = $rs['guest'];
                    $guestemail = $rs['email'];


                    //file_put_contents("./dodebug/debug.txt", "updateservice email is: $thisemail\n", FILE_APPEND);
                    $subject = "Hair Cut Appointment Cancellation at ".$_SESSION['companyname'];
                    $sendto[] = array($guestemail => $guestname);
                    $body = $ne -> confirm_cancellation($guestdate, 'Barber');
                    $sendstatus = sendmail($sendto, $replyto, $ccto, $bccto, $subject, $body, $attachment);
                    echo "Success";
                }
                else
                {
                    echo "Failed to update";
                }
            }
            else
            {
                //file_put_contents("./dodebug/debug.txt", "SetthisDate: We should be here\n", FILE_APPEND);
                //We are here when the slot is OFF and the barber wants to un-Off it for appointments
                //so we just update the field
                $thisdata = ['isOff' => false, 'isdeleted' => true];
                $thiswhere = ['recno' => $recno];
                $result = $db->PDOUpdate($thistable, $thisdata, $thiswhere);
                if($result == "Success")
                {
                    echo "Success";
                }
                else
                {
                    echo "Failed";
                }
            }
        }
        else
        {
            //Since we are here, that means there is no record so we can just insert this new record into the table
            //No emailing is needed since this slot is emptied.  We just want to turn it off so customers can't request it.
            $thisdata = [
                "uf_recno" => $_POST['thisrecno'], 
                "date" => date('Y-m-d', strtotime($_POST['thisdate'])), 
                "slot" => $_POST['thisslot'], 
                "note" => "Marked OFF by SELF",
                "isOff" => true];
            
            $inresult = $db->PDOInsert($thistable, $thisdata);
            if($inresult != "Failed Insert")
            {
                echo "Success";
            }
            else
            {
                echo "Failed to insert.";
            }
        }
    }
}
function RemoveService()
{
    global $db, $pr;

    $caltimertotal = 0;
    $calcosttotal = 0;
    $sql = "SELECT sr_recno FROM schedule_dates WHERE recno = ".$_POST['thisrecno'];
    $result = $db ->PDOMiniquery($sql);
    //file_put_contents('./dodebug/debug.txt', "removeService: ".$_POST['thisservicerecno']." \n", FILE_APPEND);
    if($db ->PDORowcount($result) > 0)
    {
        foreach($result as $rs)
        {
            $explodesr_recno = explode(",", $rs['sr_recno']);
        }
        $searcharray = array_search($_POST['thisservicerecno'], $explodesr_recno);
        
        if ($searcharray !== false) {
            unset($explodesr_recno[$searcharray]);
        }
        
    }
    $newarray = implode(',', $explodesr_recno);
    var_dump($newarray);
    $thistable = "schedule_dates";
    $thisdata = ['sr_recno' => $newarray];
    $thiswhere = ['recno' => $_POST['thisrecno']];
    
    $result = $db->PDOUpdate($thistable, $thisdata, $thiswhere);
    if($result == "Success")
    {
        echo "Success";
    }
    else
    {
        echo "Failed";
    }
}
function ReCalculatetotals(){
    global $db, $pr;

    $caltimertotal = 0;
    $calcosttotal = 0;
    $sql = "SELECT sr_recno FROM schedule_dates WHERE recno = ".$_POST['thisrecno'];
    $result = $db ->PDOMiniquery($sql);
    if($db ->PDORowcount($result) > 0)
    {
        foreach($result as $rs)
        {
            $thisservices = $rs['sr_recno'];    
        }
        $sql = "SELECT time, price FROM service WHERE recno IN($thisservices)";
        $result = $db ->PDOMiniquery($sql);
        foreach($result as $rs)
        {
            $caltimertotal += $rs['time'];
            $calcosttotal += $rs['price'];
        }

    }?>
    <div class="daily-div-total">
        <div class="daily-div-total-label float-left">Total time:</div><div class="daily-div-total-val align-left"><?php echo $pr -> ConvertMinToHour($caltimertotal) ?></div>
    </div>
    <div class="daily-div-total">
        <div class="daily-div-total-label float-left">Total cost:</div><div class="daily-div-total-val align-left">$<?php echo number_format($calcosttotal,2) ?></div>
    </div><?php  

}
function AddService()
{
    global $db;
    
    $thistable = "schedule_dates";
    $thisdata = ["sr_recno" => $_POST['thisval']];
    $thiswhere = ["recno" => $_POST['thisrecno']]; //thisrecno is the recno in schedule_dates table
    $result = $db->PDOUpdate($thistable, $thisdata, $thiswhere, null, 'Yes');
    $thisservices = "";
    if($result == "Success")
    {
        $sql = "SELECT sr_recno FROM schedule_dates WHERE recno = ".$_POST['thisrecno'];
        //file_put_contents('./dodebug/debug.txt', "addservice sql 1: $sql \n", FILE_APPEND);
        $result = $db ->PDOMiniquery($sql);
        if($db ->PDORowcount($result) > 0)
        {
            foreach($result as $rs)
            {
                $thisservices = $rs['sr_recno'];    
            }
            $sql = "SELECT recno, title, time, price FROM service WHERE recno IN($thisservices)";
            //file_put_contents('./dodebug/debug.txt', "addservice sql 2: $sql \n", FILE_APPEND);
            $lineno = 1;
            $result = $db ->PDOMiniquery($sql);
            foreach($result as $rs)
            {?>
                <tr class="tr-service">
                    <td class="daily-span-appointment-lbl-no align-right td-srecno-no" style="padding-left: 100px;" id="td_srecno_no_<?php echo $lineno ?>"><?php echo $lineno ?>.&nbsp;&nbsp;</td><td class="daily-span-appointment-disc-no align-left td-srecno-disc" id="td_srecno_disc_<?php echo $rs['recno']?>"><?php echo $rs['title'] ?> (<?php echo $rs['time']?>)mins/$<?php echo number_format($rs['price'], 2) ?></td>
                </tr><?php
                $lineno++;
            }
            
        }
            
    }
    else
    {
        echo "Failed";
    }
}
function UpdateDailycomment()
{
    global $db;
    $txtarray = json_decode($_POST['thisval']);
    $thisdata = [];
    $thiswhere = [];
    
    $thistable = "schedule_dates";
    $thisdata = [$_POST['thisfield'] => $txtarray[0]];
    //$thisdata = ['"'.$_POST['thisfield']."'" => $txtarray[0]];
    $thiswhere = ['recno' => $_POST['thisrecno']];
    
    $result = $db->PDOUpdate($thistable, $thisdata, $thiswhere);
    if($result == "Success")
    {
        echo "Success";
    }
    else
    {
        echo "Failed";
    }
}
function SelectSlotDaily()
{
    global $db, $pr;
    $thisservices = "";
    $sql = "SELECT sr_recno FROM schedule_dates WHERE uf_recno = ".$_POST['thisrecno']." AND date= '".date('Y-m-d', strtotime($_POST['thisdate']))."' AND slot = '".$_POST['slot']."' AND uf_recno = ".$_POST['thisrecno']." AND iscancelled = false AND isdeleted=false";
    //file_put_contents('./dodebug/debug.txt', "selectslotdaily sql 1: $sql \n", FILE_APPEND);
    $result = $db ->PDOMiniquery($sql);
    if($db ->PDORowcount($result) > 0)
    {
        foreach($result as $rs)
        {
            $thisservices = $rs['sr_recno'];    
        }
        $sql = "SELECT sd.*, s.recno as service_recno, s.title, s.time, s.price FROM schedule_dates sd INNER JOIN service s on s.recno IN($thisservices) WHERE sd.date= '".date('Y-m-d', strtotime($_POST['thisdate']))."' AND sd.slot = '".$_POST['slot']."' AND sd.uf_recno = ".$_POST['thisrecno']." AND sd.iscancelled = false AND sd.isdeleted=false AND s.isActive=true AND s.isdeleted=false";
        //file_put_contents('./dodebug/debug.txt', "selectslotdaily sql 2: $sql \n", FILE_APPEND);
        $result = $db ->PDOMiniquery($sql);?>
        <div class="div-daily-rightpan-container"> <!-- width = 660px -->
            <input type="hidden" id="hid_daily_slot" value="<?php echo $_POST['slot'] ?>"/>
            <div class="div-daily-rightpan-header-container">
                <div class="div-daily-rightpan-header div-daily-rightpan-header-selected">Appointment</div>
                <div class="div-daily-rightpan-header div-daily-rightpan-header-selected-notselected cursor-pointer">Profile</div>
                <div class="div-daily-rightpan-header div-daily-rightpan-header-selected-notselected cursor-pointer">History</div>
            </div>
            <div class="daily-right-container-data float-left">
                <table id="tbl_service" style="overflow-y: auto; height: 550px; display: block;"><?php
                    $doonce = false;
                    $norow = 1;
                    $caltimertotal = 0;
                    $calcosttotal = 0;
                    foreach($result as $rs)
                    {
                        if($doonce == false)
                        {
                            $doonce = true;?>
                            <tr>
                                <td class="daily-span-appointment-lbl align-right">Name:&nbsp;&nbsp;</td><td class="daily-span-appointment-disc align-left"><?php echo $rs['guest'] ?></td>
                            </tr>
                            <tr>
                                <td class="daily-span-appointment-lbl align-right">Email:&nbsp;&nbsp;</td><td class="daily-span-appointment-disc align-left"><?php echo $rs['email'] ?></td>
                            </tr>
                            <tr>
                                <td class="daily-span-appointment-lbl align-right">Phone#:&nbsp;&nbsp;</td><td class="daily-span-appointment-disc align-left"><?php echo $rs['phone_number'] ?></td>
                            </tr>
                            <tr>
                                <td colspan="2" class="daily-span-appointment-disc-no align-center daily-span-appointment-disc-service">Service is from <?php echo $_POST['timeframe']?></td>
                            </tr>
                            <tr>
                                <td colspan="2" class="daily-span-appointment-lbl-no align-left" style="padding-left: 150px;">
                                    Comment:
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <div class="float-left" style="background-color: white; margin-left: 150px;"><textarea id="txtarea_daily_comment" onchange="updateDailycomment(this, <?php echo $rs['recno'];?>, 'comment');" rows="10" cols="50"><?php echo $rs['comment'] ?></textarea></div>
                                </td>
                            </tr>
                            <tr style="display: none;" id="tr_hidden_add_service_alert">
                                <td>&nbsp;</td>
                                <td class="align-left" style="color: darkred; padding-left: 20px;">If you add service to this time slot, it may recalculate your slots and could push your next appointments back.</td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td class="daily-span-appointment-lbl-no align-right">
                                    <button class="daily-td-edit-service cursor-pointer float-left" id="btn_editservice" onclick="editService(this);"><image  title="Click to edit service, may recalculate time slots." src="./images/others/penedit.png"></button>
                                    <button class="daily-td-add-service cursor-pointer float-left" id="btn_addservice" onclick="showServiceselect(this);"  title="Click to add additional service, may recalculate time slots.">+</button>
                                </td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td class="daily-span-appointment-disc-no align-left"><?php
                                    $thisfunctionchange = 'addService(this, '.$rs['recno'].');';
                                    $pr->SltService()->GetSelect("sltservice", '', true, false, $thisfunctionchange, true, false, true);?>
                                </td>
                            </tr><?php
                        }?>
                        <tr class="tr-service">
                            <td class="daily-span-appointment-lbl-no align-right td-srecno-no" style="padding-left: 100px;" id="td_srecno_no_<?php echo $norow?>"><?php echo $norow?>.&nbsp;&nbsp;</td><td class="daily-span-appointment-disc-no align-left td-srecno-disc" id="td_srecno_disc_<?php echo $rs['recno']?>"><span id="span_service_edit" class="circle-me-text-remove-service float-left cursor-pointer  span-minus" style="display: none;" title="Remove this service." onclick="removeService(this, <?php echo $rs['recno'] ?>, <?php echo $rs['service_recno'] ?>);">-</span>&nbsp;&nbsp;<?php echo $rs['title'] ?> (<?php echo $rs['time']?>)mins/$<?php echo number_format($rs['price'], 2) ?></td>
                        </tr><?php
                        $norow++;
                        $caltimertotal += $rs['time'];
                        $calcosttotal += $rs['price'];
                    }?>
                    
                </table>
                <br>
                <div id="div_totals">
                    <div class="daily-div-total">
                        <div class="daily-div-total-label float-left">Total time:</div><div class="daily-div-total-val align-left"><?php echo $pr -> ConvertMinToHour($caltimertotal) ?></div>
                    </div>
                    <div class="daily-div-total">
                        <div class="daily-div-total-label float-left">Total cost:</div><div class="daily-div-total-val align-left">$<?php echo number_format($calcosttotal,2) ?></div>
                    </div>
                </div>
                <br>
                <div class="daily-div-btn-complete align-center"><button class="cursor-pointer" name="btn_complete" id="btn_complete" onclick="completeCut();" style="width: 120px;">Complete Cut</button><div>

            </div>
        </div><?php
    }
    else
    {
        echo "NONE";
    }
}
function UpdateService(){
    global $db, $ne, $load_headers;
    //cmd=UpdateService&thisrecno='+recno+'&thisdate='+date+'&thisslot='+slot
    $thistable = "schedule_dates";
    if($_POST['thisfield'] == "iscancelled")
    {
        if($_POST['thisval'] == true)
        {
            $thisdata = ['iscancelled' => true];
        }
        else
        {
            $thisdata = ['iscancelled' => false];
        }
    }
    else 
    {
        //I have spent many hours trying to use this for the if above, it just don't work for some reason, it just can't get the boolean correctly
        //without explicitely doing what I am doing above.
        $thisdata = [$_POST['thisfield'] => $_POST['thisval']];
    }
    $thiswhere = ['recno' => $_POST['thisrecno']];
    //file_put_contents("./dodebug/debug.txt", "updateservice: ".$_POST['thisfield']." => ".$_POST['thisval']."\n", FILE_APPEND);
   
    
    $result = $db->PDOUpdate($thistable, $thisdata, $thiswhere);
    
    if($result == "Success")
    {
        //file_put_contents("./dodebug/debug.txt", "updateservice: in success.\n", FILE_APPEND);
        if($_POST['thisfield'] == "iscancelled")
        {
            //If we are doing cancellation, we need to email the guest that the app has been cancelled.
            $sql = "SELECT * FROM schedule_dates WHERE recno = ".$_POST['thisrecno'];
            $result = $db ->PDOMiniquery($sql);
            if($db ->PDORowcount($result) > 0)
            {
                $sentto = Array();
                $replyto = Array();
                $ccto = Array();
                $bccto = Array();
                $attachment = Array();
                
                foreach($result as $rs)
                {
                    $guestdate = $rs['date'];
                    $guestname = $rs['guest'];
                    $guestemail = $rs['email'];
                }
            }
            //file_put_contents("./dodebug/debug.txt", "updateservice email is: $thisemail\n", FILE_APPEND);
            $subject = "Hair Cut Appointment Cancellation at ".$_SESSION['companyname'];
            $sendto[] = array($guestemail => $guestname);
            $body = $ne -> confirm_cancellation($guestdate);
            $sendstatus = sendmail($sendto, $replyto, $ccto, $bccto, $subject, $body, $attachment);?><?php
        }
        echo "Success";
    }
    else
    {
        echo "Failed";
    }
}
function SelectWeekdircaldaily()
{
 
    
    //PaintCalendarWeekly($thisrecno, $thiscurdate, $start_date, $end_date, $selectview, $from);
    //file_put_contents("./dodebug/debug.txt", "firstofweek: ".$_POST['firstofweek']."\n", FILE_APPEND);
    //PaintCalendarDaily($thisrecno, $thiscurdate, $selectview, $from)
    $thiscurday = date('Y/m/d', strtotime($_POST['thisyear']."/".$_POST['thismonth']."/01"));
    //file_put_contents("./dodebug/debug.txt", "thiscurday in thiscurday: $thiscurday.\n", FILE_APPEND);
    //file_put_contents("./dodebug/debug.txt", "thiscurdate in thiscurday: ".$_POST['thiscurdate']."\n", FILE_APPEND);
    if(trim($_POST['thiscurdate']) == "NONE")
    {
        //file_put_contents("./dodebug/debug.txt", "thiscurday in SelectWeekdircaldaily(): not supposed to be here.\n", FILE_APPEND);
        //We are coming from where user is trying to go to next months or previous months.  If we are at the current month, we want to show the current day,
        //otherwise, if we are in the previous month or the next month, we want to show the first.
        //Now find the dates here.
        //file_put_contents("./dodebug/debug.txt", "thiscurday in yr, mon: ".$_POST['thisyear']."/".$_POST['thismonth']."\n", FILE_APPEND);

        //file_put_contents("./dodebug/debug.txt", "thiscurdate in thiscurdate 598: ".date('Y/m', strtotime($thiscurday))." == ".date("Y/m")."\n", FILE_APPEND);
        if(date('Y/m', strtotime($thiscurday)) == date("Y/m"))
        {
            //file_put_contents("./dodebug/debug.txt", "do i get here.\n", FILE_APPEND);
            //We are at the current month, we want to start at the current date
            $thiscurday = date("Y/m/d");
        }
    }
    $thiscurdate = ($_POST['thiscurdate'] == "NONE") ? $thiscurday : $_POST['thiscurdate'];
    //file_put_contents("./dodebug/debug.txt", "thiscurday in SelectWeekdircaldaily(): $thiscurdate\n", FILE_APPEND);
    PaintCalendarDaily($_POST['thisrecno'], $thiscurdate, $_POST['selectview'], $_POST['from']);
}
function SelectWeekdircalweek()
{
    //When we go into Daily, we removed the table so when we come back to Weekly and Monthly, we can't.  We need to reload or find an alternative.
    
    //PaintCalendarWeekly($thisrecno, $thiscurdate, $start_date, $end_date, $selectview, $from);
    //file_put_contents("./dodebug/debug.txt", "firstofweek: ".$_POST['firstofweek']."\n", FILE_APPEND);
    //PaintCalendarWeekly($thisrecno, $thiscurdate, $selectview, $from)
    PaintCalendarWeekly($_POST['thisrecno'], json_decode($_POST['thiscurdate']), $_POST['selectview'], $_POST['from']);
}
function SelectWeekdir()
{

    $thisdatearray = json_decode($_POST['datearray']);
    $startdateday = date('d', strtotime(current($thisdatearray)));
    $enddateday = date('d', strtotime(end($thisdatearray)));
    $thisarray = [];
    reset($thisdatearray);
    if($_POST['thisdir'] == "Forward")
    {
        //Really, all we need to do is add 7 days to the current week and re-grab the day, if it goes into previous or next month or even year, we just change that in the dom.
        //file_put_contents("./dodebug/debug.txt", "thislastfirsday: ".$_POST['firstdayofweek']." > ".$_POST['lastdayofweek']."\n", FILE_APPEND);
       
        //file_put_contents("./dodebug/debug.txt", "thisdatearray: ".end($thisdatearray)."\n", FILE_APPEND);
        if($_POST['selectview'] == "Weekly")
        {
            $thiscurday = date('Y-m-d', strtotime(end($thisdatearray)));
        }
        else
        {
            $thiscurday = date('Y-m-d', strtotime(current($thisdatearray)));
        }
        //file_put_contents("./dodebug/debug.txt", "1thiscurday: ".$thiscurday."\n", FILE_APPEND);
        $thiscurday = date('Y-m-d', strtotime("$thiscurday, +1 day"));
        //file_put_contents("./dodebug/debug.txt", "1thiscurday: ".$thiscurday."\n", FILE_APPEND);
        $newmonth = date('m', strtotime($thiscurday));
        $newyear = date('Y', strtotime($thiscurday));
        if($_POST['selectview'] == "Weekly")
        {
            $startofweek = $thiscurday;
            $lastofweek = date('Y-m-d', strtotime("$thiscurday, +6 days"));
        }
    }
    else
    {
        $thiscurday = date('Y-m-d', strtotime(current($thisdatearray)));
        //file_put_contents("./dodebug/debug.txt", "thiscurday in previous 1: ".$thiscurday."\n", FILE_APPEND);
        $thiscurday = date('Y-m-d', strtotime("$thiscurday, -1 day"));
        //file_put_contents("./dodebug/debug.txt", "thiscurday in previous 2: ".$thiscurday."\n", FILE_APPEND);
        $newmonth = date('m', strtotime($thiscurday));
        $newyear = date('Y', strtotime($thiscurday));
        if($_POST['selectview'] == "Weekly")
        {
            $lastofweek = $thiscurday;
            $startofweek = date('Y-m-d', strtotime("$thiscurday, -6 days"));
        }
    }
    if($_POST['selectview'] == "Weekly")
    {
        $thisarray = [$newyear, $newmonth, $startofweek, $lastofweek];
    }
    else
    {
        $thisarray = [$newyear, $newmonth, $thiscurday];
    }
    echo json_encode($thisarray);
}
function SelectView()
{
    $from = "";
    //file_put_contents("./dodebug/debug.txt", "selectview year and month: ".($_POST['thisyear']."-".$_POST['thismonth'])."\n", FILE_APPEND);
    if(date('Y-m', strtotime($_POST['thisyear'].'-'.$_POST['thismonth'])) == date('Y-m'))
    {
        $thiscurdate = date('m/d/Y');
        //file_put_contents("./dodebug/debug.txt", "selectview thiscurday 1: ".$thiscurdate."\n", FILE_APPEND);
    }
    else
    {
        $tempdate = strtotime($_POST['thismonth']."/01/".$_POST['thisyear']);
        $thiscurdate = date('m/d/Y', $tempdate);
        //file_put_contents("./dodebug/debug.txt", "selectview thiscurday 2: ".$thiscurdate."\n", FILE_APPEND);
    }
    
    if($_POST['selectview'] == "Daily")
    {
        PaintCalendarDaily($_POST['thisrecno'], $thiscurdate, $_POST['selectview'], $_POST['thisday'], $from='Add');
    }
    else
    {
        PaintCalendar($_POST['thisrecno'], $thiscurdate, $_POST['selectview'], $from); 
    }
    
        //Daily
    //PaintCalendarDaily($thisrecno, $thiscurdate, $selectview, $from='Add');
    
}
function FilledService()
{
        global $db;
        
        $sql = "SELECT * FROM schedule_dates ";
        $sql .= "WHERE uf_recno = ".$_POST['thisrecno']." AND date = '".date('Y-m-d', strtotime($_POST['thisdate']))."' and iscancelled = false and isdeleted = false ORDER BY slot ";
        //file_put_contents("./dodebug/debug.txt", "fillservice sql: ".$sql."\n", FILE_APPEND);
        $result = $db -> PDOMiniquery($sql);
        $thissumtime = 0;
        //4,5,6,9,10,19,22,27
        if($db ->PDORowcount($result) > 0)
        {
            foreach($result as $rs)
            {
                if($rs['isOff'] != true)
                {
                    //For each record, we have to find out out the service type using the sr_recno, we can have 1 or more in format of 1,2,3,...,n
                    $sqlsr = "SELECT time FROM service WHERE recno IN (".$rs['sr_recno'].") AND isActive = true and isdeleted = false";
                    //file_put_contents("./dodebug/debug.txt", "FilledService sqlsr $thiscount: ".$sqlsr."\n", FILE_APPEND);
                    $resultsr = $db ->PDOMiniquery($sqlsr);
                    $timesr = 0;
                    $dataarray[$rs['recno']]['slot'] = $rs['slot']; 
                    foreach($resultsr as $rssr)
                    {
                        $timesr = $timesr + $rssr['time'];                    
                    }
                    $dataarray[$rs['recno']]['time'] = $timesr;
                    //file_put_contents("./dodebug/debug.txt", "return array: ".$rs['slot']." : ".$dataarray[$rs['recno']]['time']."\n", FILE_APPEND);
                }
                else
                {
                    $dataarray[$rs['recno']]['slot'] = $rs['slot'];
                    $dataarray[$rs['recno']]['time'] = "OFF";
                }
            }
            
            echo json_encode($dataarray);
        }
        else
        {
            echo "No Record";
        }
}
function FillGraph()
{
        global $db;
        
        $d_array = [];
        if(date('Y-m') == date('Y-m', strtotime($_POST['thisdate'])))
        {
            //If the current month, is the same as the thisdate, that means we are handling the current month.
            $start_date = date('Y-m-d');
            $end_date = date('Y-m-t');
        }
        else
        {
            //If we are here, that means we are handling either previous months, the past, or we are handling the future.
            //That means we will start with 1-31
            $start_date = date('Y-m-01', strtotime($_POST['thisdate']));
            $end_date = date('Y-m-t', strtotime($_POST['thisdate']));
        }

        $sql = "SELECT * FROM schedule_dates WHERE uf_recno = ".$_POST['thisrecno']." AND date BETWEEN '$start_date' AND '$end_date' ";
        $sql .= "and iscancelled = false and isdeleted = false ORDER BY date, slot";
        //file_put_contents("./dodebug/debug.txt", "filgraph sql: ".$sql."\n", FILE_APPEND);
        $result = $db -> PDOMiniquery($sql);
        $d_array = [];
        if($db ->PDORowcount($result) > 0)
        {
            foreach($result as $rs)
            {           
                $thisdate = date('j', strtotime($rs['date'])); //day of month, no leading 0
                $d_array[$thisdate][] = $rs['slot'];
                //file_put_contents("./dodebug/debug.txt", "return array: ".$rs['slot']." : ".$dataarray[$rs['recno']]['time']."\n", FILE_APPEND);
            }
            echo json_encode($d_array);
        }
        else
        {
            echo "No Record";
        }
}
function BookAppointment()
{
    global $db, $nd, $ne, $load_headers, $pr;
    //file_put_contents('./dodebug/debug.txt', "sr_recno: ".$_POST['thisservicerecno'], FILE_APPEND);
    //We want to check to make sure the email is in good format and the phone.
    $thisserver = $load_headers -> GET_THIS_SERVER();
    $thistable = "schedule_dates";
    $thisdata = array("uf_recno" => $_POST['thisrecno'],
                       "sr_recno" => $_POST['thisservicerecno'],
                       "guest" => $_POST['txt_name'],
                       "phone_number" => $_POST['txt_phoneno'],
                       "email" => $_POST['txt_email'],
                       "comment" => $_POST['txt_area'],
                       "date" => date('Y-m-d', strtotime($_POST['thisdate'])),
                       "slot" => $_POST['thisslot']);
    $result = $db->PDOInsert($thistable, $thisdata);
    //file_put_contents('./dodebug/debug.txt', "POST: ".$result, FILE_APPEND);
    $sdrecno = $result;  //schedule dates recno no
    if(isset($result))
    {
        $sql = "SELECT firstname, lastname FROM users WHERE recno = ".$_POST['thisrecno'];
        $result = $db ->PDOMiniquery($sql);
        foreach($result as $rs)
        {
            $sentto = Array();
            $replyto = Array();
            $ccto = Array();
            $bccto = Array();
            $attachment = Array();
            
            $totaltime = 0;
            $sendto[] = array($_POST['txt_email'] => $_POST['txt_name']);
            
            $subject = "Hair Cut Appointment at ".$_SESSION['companyname'];

            $body = "Your appointment at ".$_SESSION['companyname']." has been confirmed.  The appointment information is as below...<br/><br/>";
            $body .= "Barbar: ".$rs['firstname']." ".$rs['lastname']."<br/>";
            $body .= "Date: ".$_POST['thisdate']."<br/>";
            $body .= "Time: ".$_POST['thisslot']."<br/><br/>";

            $sqlsvc = "SELECT * FROM service WHERE recno in (".$_POST['thisservicerecno'].")";
            
            $resultsvc = $db ->PDOMiniquery($sqlsvc);
            $lineno = 1;
            
            $body .= "Service:<br/><br/>" ;
            foreach($resultsvc as $rssvc)
            {
                $body .= $lineno.". ".$rssvc['title']."<br/>";
                $totaltime = $totaltime + $rssvc['time'];
                $lineno++;
            }
            //file_put_contents('./dodebug/debug.txt', "thislot in min: ".$pr -> ConvertHourToMinute($_POST['thisslot']), FILE_APPEND);
            //file_put_contents('./dodebug/debug.txt', "totaltime: ".$totaltime, FILE_APPEND);
            
            $body .= "<br/>Your appointment will be from ".$_POST['thisslot']." to ".date("h:i", strtotime($pr -> ConvertMinToHour($pr -> ConvertHourToMinute($_POST['thisslot']) + $totaltime)));
            $body .= "<br/><br/>We hope to see you soon.  Have a wonderful day.<br/><br/>";
            
            $body .= "<a href='http://$thisserver/cancellation.php?recno=$sdrecno'>Click here to cancel your appointment.</a>";
            $sendstatus = sendmail($sendto, $replyto, $ccto, $bccto, $subject, $body, $attachment);
            
            //We need to send a link to customer for easy cancellation
            
            echo "Booked";
        }
    }
    else
    {
        echo "No Booking";
    }
}
function SubmitAppointment()
{
    global $db, $load_headers;
    $thisfields = array();
    $thiswhere = array();
    $thisfields = array('uf_recno', 'guest', 'email', 'note', 'date');
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
    }?>
    <span class="spn-app-cursor" onclick="doAppointment(this, <?php echo $_POST['thisrecno'] ?>, '<?php echo $_POST['thisdate'] ?>', '<?php echo $_POST['thisslot'] ?>', <?php echo $_POST['thiscounter'] ?>);">Click to make an appointment.</span><?php
}
function CancelAppointment()
{?>
    <span class="spn-app-cursor" onclick="doAppointment(this, <?php echo $_POST['recno'] ?>, '<?php echo $_POST['thisdate'] ?>', '<?php echo $_POST['slot'] ?>', <?php echo $_POST['counter'] ?>);">Click to make an appointment.</span><?php
}
function DoAppointments()
{
    global $db;
    $lineno = 1;
    $isrecord = false;
    $bgcolor = "#ffffff";
    //$sql = "SELECT * FROM service WHERE isactive = true and isdeleted = false";
    $sql = "SELECT sd.*, s.recno as service_recno, s.title, s.time, s.price FROM schedule_dates sd INNER JOIN service s WHERE sd.date= '".date('Y-m-d', strtotime($_POST['thisdate']))."' AND sd.slot = '".$_POST['slot']."' AND sd.uf_recno = ".$_POST['recno']." AND sd.iscancelled = false AND sd.isdeleted=false AND s.isActive=true AND s.isdeleted=false";
    //file_put_contents('./dodebug/debug.txt', "DoApp sql: $sql \n", FILE_APPEND);
    $result = $db ->PDOMiniquery($sql);
    $usethispidckeddayslot = "div-pickedday-slot";
    if($_POST['selectview'] == "Daily")
    {
        $usethispidckeddayslot = "div-pickedday-slot-daily";
    }?>
    <div class="<?php echo $usethispidckeddayslot?>" name="div_sub_app" id="div_sub_app">
        <div>
            <div class="center btn-filled-app-close">
                <div class="float-left">For <?php echo date('M d Y', strtotime($_POST['thisdate'])) ?> @ <?php echo $_POST['slot'] ?></div>
                    <button class="btn-cancel-appointment float-right" type="button" name="btn_app_cancel<?php echo $_POST['counter'] ?>" id="btn_app_cancel<?php echo $_POST['counter'] ?>" onclick="cancelAppointmentservice(this, <?php echo $_POST['recno'] ?>, <?php echo $_POST['counter'] ?>, '<?php echo $_POST['thisdate']?>', '<?php echo $_POST['slot'] ?>', '<?php echo $_POST['selectview']?>');">X</button>
            </div>
            <form name="frmservice" id="frmservice" method="post">
                <div>
                    <table class="table table-w100"><?php
                        if($db -> PDORowcount($result) > 0)
                        {
                            $isrecord = true;
                            $doonce = false;
                            
                            foreach($result as $rs)
                            {
                                $this_service_recno = explode(',', $rs['sr_recno']);
                                if($doonce == false)
                                {
                                    $doonce = true;?>
                                    <tr>
                                        <td class="align-right">Name:</td><td><input class="div-input-appoint float-left" style="width: 99.5%;" type="text" name="txt_name" id="txt_name" size="3" onchange="updateService(this, <?php echo $rs['recno'] ?>, <?php echo $rs['service_recno'] ?>, 'guest', '<?php echo $rs['date'] ?>', '<?php echo $rs['slot'] ?>');" value="<?php echo empty($rs['guest']) ? "" : $rs['guest'] ?>" /></td>
                                    </tr>
                                    <tr>
                                        <td class="align-right">Phone:</td><td><input class="div-input-appoint float-left" style="width: 99.5%;" type="text" name="txt_phoneno" id="txt_phoneno" size="3" onchange="updateService(this, <?php echo $rs['recno'] ?>, <?php echo $rs['service_recno'] ?>, 'phone_number', '<?php echo $rs['date'] ?>', '<?php echo $rs['slot'] ?>');" value="<?php echo empty($rs['phone_number']) ? "" : $rs['phone_number'] ?>" placeholder="1234567890" /></td>
                                    </tr>
                                    <tr>
                                        <td class="align-right">Email:</td><td><input class="div-input-appoint float-left" style="width: 99.5%;" type="text" name="txt_email" id="txt_email" size="3" onchange="updateService(this, <?php echo $rs['recno'] ?>, <?php echo $rs['service_recno'] ?>, 'email', '<?php echo $rs['date'] ?>', '<?php echo $rs['slot'] ?>');" value="<?php echo empty($rs['email']) ? "" : $rs['email'] ?>" /></td>
                                    </tr>
                                    <tr>
                                        <td class="align-right">Comment:</td><td><textarea class="float-left" name="txt_area" id="txt_area" style="width: 100%;" id="txt_area" onchange="updateService(this, <?php echo $rs['recno'] ?>, <?php echo $rs['service_recno'] ?>, 'comment', '<?php echo $rs['date'] ?>', '<?php echo $rs['slot'] ?>');" rows="2" cols="31"><?php echo empty($rs['comment']) ? "" : $rs['comment'] ?></textarea></td>
                                    </tr>
                                    <tr>
                                        <td class="text-center table-w100 schedule-service-tbl" colspan="2">Services</td></td><?php
                                }
                                $bgcolor = "#f2f2f2";
                                $usthisclass = "";
                                if(in_array($rs['service_recno'], $this_service_recno))
                                {
                                    //file_put_contents('./dodebug/debug.txt', "in array \n", FILE_APPEND);
                                    $usthisclass = "doApp-selected-service";
                                }//file_put_contents('./dodebug/debug.txt', "in array $usthisclass\n", FILE_APPEND);?>
                                <tr class="schedule-service-tbl-tr-bg <?php echo $usthisclass ?>" name="tr_service_<?php echo $rs['service_recno'] ?>_<?php echo $rs['time'] ?>" id="tr_service_<?php echo $rs['service_recno'] ?>_<?php echo $rs['time'] ?>" onclick="selectService(this);updateService(this, <?php echo $rs['recno'] ?>, <?php echo $rs['service_recno'] ?>, 'sr_recno', '<?php echo $rs['date'] ?>', '<?php echo $rs['slot'] ?>');">
                                    <td class="text-right" colspan="2" name="td_service_<?php echo $rs['recno'] ?>" id="td_service<?php echo $rs['recno'] ?>">
                                        <span class="align-right float-left" style="width: 7%;"><?php echo $lineno ?>.&nbsp;&nbsp;</span>
                                        <span class="align-left float-left" style="width: 65%; text-align: left;"><?php echo $rs['title'] ?></span>
                                        <span class="align-left float-left" style="width: 15%; text-align: left;"><?php echo $rs['time'] ?> mins</span>
                                        <span class="align-right float-left" style="width: 10%;">$<?php echo number_format((float)$rs['price'], 2, '.', '') ?></span>
                                    </td>
                                </tr><?php
                                $lineno++;
                            }
                        }
                        else
                        {
                            $doonce = false;
                            $sql = "SELECT * FROM service WHERE isActive = true and isdeleted = false";
                            $result = $db ->PDOMiniquery($sql);
                            foreach($result as $rs)
                            {
                                if($doonce == false)
                                {
                                    $doonce = true;?>
                                    <tr>
                                        <td class="align-right">Name:</td><td><input class="div-input-appoint float-left" style="width: 99.5%;" type="text" name="txt_name" id="txt_name" size="3" value="" /></td>
                                    </tr>
                                    <tr>
                                        <td class="align-right">Phone:</td><td><input class="div-input-appoint float-left" style="width: 99.5%;" type="text" name="txt_phoneno" id="txt_phoneno" size="3" value="" placeholder="1234567890" /></td>
                                    </tr>
                                    <tr>
                                        <td class="align-right">Email:</td><td><input class="div-input-appoint float-left" style="width: 99.5%;" type="text" name="txt_email" id="txt_email" size="3" value="" /></td>
                                    </tr>
                                    <tr>
                                        <td class="align-right">Comment:</td><td><textarea class="float-left" name="txt_area" id="txt_area" style="width: 100%;" id="txt_area" rows="2" cols="31"></textarea></td>
                                    </tr>
                                    <tr>
                                        <td class="text-center table-w100 schedule-service-tbl" colspan="2">Services</td></td><?php
                                }
                                $bgcolor = "#f2f2f2";?>
                                <tr class="schedule-service-tbl-tr-bg" name="tr_service_<?php echo $rs['recno'] ?>_<?php echo $rs['time'] ?>" id="tr_service_<?php echo $rs['recno'] ?>_<?php echo $rs['time'] ?>" onclick="selectService(this);">
                                    <td class="text-right" colspan="2" name="td_service<?php echo $lineno?>" id="td_service<?php echo $lineno?>">
                                        <span class="align-right float-left" style="width: 7%;"><?php echo $lineno ?>.&nbsp;&nbsp;</span>
                                        <span class="align-left float-left" style="width: 65%; text-align: left;"><?php echo $rs['title'] ?></span>
                                        <span class="align-left float-left" style="width: 15%; text-align: left;"><?php echo $rs['time'] ?> mins</span>
                                        <span class="align-right float-left" style="width: 10%;">$<?php echo number_format((float)$rs['price'], 2, '.', '') ?></span>
                                    </td>
                                </tr><?php
                                $lineno++;
                            }
                            
                        }?>
                        </tr>
                    </table>
                </div>
                <div>
                    <?php
                    if($isrecord == false)
                    {?>
                        <button class="btn-ok-appointment" type="button" name="btn_app_ok<?php echo $_POST['counter'] ?>" id="btn_app_ok<?php echo $_POST['counter'] ?>" onclick="bookAppointment(this, <?php echo $_POST['recno'] ?>, '<?php echo $_POST['thisdate']?>', '<?php echo $_POST['slot'] ?>', <?php echo $_POST['counter'] ?>);">Book Appointment</button><?php
                    }
                    else
                    {?>
                        <button class="btn-ok-appointment cursor-pointer" type="button" name="btn_app_ok<?php echo $_POST['counter'] ?>" id="btn_app_ok<?php echo $_POST['counter'] ?>" onclick="updateService(this, <?php echo $rs['recno'] ?>, <?php echo $rs['service_recno'] ?>, 'iscancelled', '<?php echo $rs['date'] ?>', '<?php echo $rs['slot'] ?>');">Cancel This Appointment</button><?php
                    }?>
                </div>
            </form>
        </div>
    </div>
<?php
}
function PickDates()
{
    $setthisDate = "";
    $usethisonclick = "";
    $classonoff = "";
    if(isset($_SESSION['user_recno'])) //this is the recno for the users table
    {
        $setthisDate = '<button class="div-pickedday-container-header-btn" id="btn_setthisdat" title="Edit ALL day" onclick="setthisDate(this, '.$_SESSION['user_recno'].', \''.date('d', strtotime($_POST['pickeddate'])).'\', \''.date('M d Y', strtotime($_POST['pickeddate'])).'\', \'All\');">E</button>';

    }?>
    <div class="div-pickedday-container" name="div_pickedday_container" id="div_pickedday_container" onmousedown="dragThis(this);">
        <div class="div-pickedday-container-header" name="div_pickedday_container_header" id="div_pickedday_container_header">
            <?php echo date('M d Y', strtotime($_POST['pickeddate'])) ?>
            <button type="button" class="div-pickedday-container-header-btn" name="btn_close_calender_picked" id="btn_close_calender_picked" onclick="closeDive();">X</button><?php echo $setthisDate ?>
        </div><?php
        $thiscurdate = date('Y-m-d', strtotime($_POST['pickeddate']));
        $slotarrayhr = ['10:00', '10:30', '11:00', '11:30', '12:00', '12:30', '1:00', '1:30', '2:00', '2:30', '3:00', '3:30', '4:00', '4:30', '5:00', '5:30'];
        $slotarraymil = ['10:00', '10:30', '11:00', '11:30', '12:00', '12:30', '13:00', '13:30', '14:00', '14:30', '15:00', '15:30', '16:00', '16:30', '17:00', '17:30'];
        //Since we don't have a record, we still need to show the opened time slots so we will loop through the slotarray.
        //However, we want to also check to see if we have passed certain hours, if we have, we do not allow for a click.
        $counter = 1;
        for($i=0; $i<count($slotarrayhr); $i++)
        {
            $setthisDate = "";
            $iscursor = "";
            $classonoff = "";
            if(isset($_SESSION['user_recno']))
            {
                $classonoff = 'class="img-monthly-on"';
                $iscursor = "cursor-pointer";
                $setthisDate = 'title="On/Off" onclick="setthisDate(this, '.$_SESSION['user_recno'].', \''.date('d', strtotime($_POST['pickeddate'])).'\', \''.date('M d Y', strtotime($_POST['pickeddate'])).'\', \''.$slotarrayhr[$i].'\', '.$counter.');"';
            }
            $usethisonclick = 'onclick="doAppointment(this, '.$_POST['recno'].', \''.$_POST['pickeddate'].'\', \''.$slotarrayhr[$i].'\', '.$counter.');"';
            $usethistext = "Click to make an appointment.";
            $usethisclass = "spn-app-cursor";
            //file_put_contents('./dodebug/debug.txt', "\n what is hr compare?: ".date("$thiscurdate $slotarray[$i]")." < ".date("Y-m-d h:i")."\n", FILE_APPEND);

            if(date("Y-m-d H:i", strtotime($thiscurdate." ".$slotarraymil[$i])) < date("Y-m-d H:i"))
            {
                //file_put_contents('./dodebug/debug.txt', "\n what is hr compare?: IN", FILE_APPEND);
                //If the slot time is less than the current time, we want to make it unavailable.
                $usethisonclick = "";
                $usethistext = "No longer Unavailable";
                $usethisclass = "";
            }
            ?>
            <div class="div-pickedday-container-header-body" name="div_pickedday_container_header<?php echo $counter ?>" id="div_pickedday_container_header-body<?php echo $counter ?>">
                <div class="div-pickedday-container-header-body-slots <?php echo $iscursor ?>" id="div_slot_<?php echo $counter ?>" <?php echo $setthisDate ?>><?php echo $slotarrayhr[$i] ?><img id="img_<?php echo $counter ?>" <?php echo $classonoff?>/></div>
                <div class="div-pickedday-container-header-body-app-ava <?php echo $usethisclass ?>" name="div_pickedday_container_header_body_app_ava<?php echo $counter ?>" id="div_pickedday_container_header_body_app_ava<?php echo $counter ?>" <?php echo $usethisonclick ?>>
                    <span name="span_app_text<?php echo $counter ?>" id="span_app_text<?php echo $counter ?>" ><?php echo $usethistext ?></span>
                </div>
            </div><?php
            $counter++;
        }?>
    </div><?php    
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
function ReselectDate()
{
    $from = $_POST['from']; //This will be either Add or Modify
    $thismonth = $_POST['thismonth'];
    $thisyear = $_POST['thisyear'];
    $tempdate = date('Y/m/01', strtotime("$thisyear/$thismonth/01"));
    $thiscurdate = date('m/01/Y', strtotime($tempdate));//We do this to include the 'Y' cuz it could go back to December if we are in January, this will make sure we get the right year.
    //file_put_contents("./dodebug/debug.txt", "thiscurdate inside ReselectDate: ".$thiscurdate, FILE_APPEND);
    //file_put_contents("./dodebug/debug.txt", "what is selectview?: ".$_POST['selectview']."\n", FILE_APPEND);
 
    PaintCalendar($_POST['recno'], $thiscurdate, $_POST['selectview'], $from);
}


function ManageSchedules()
{
    doMultidates($_POST['from'], $_POST['recno'], $_POST['selectview'], $_POST['thisdate']);
}
function ShowAllstar($thisclass, $thisrecno)
{?>
    <div class="schedule-div-thumbnail-container-image <?php echo $thisclass ?>" onclick="selectBarber('All');">
        <img class="schedule-thumbnail" src="../images/others/allstar.png" alt="../images/others/defaultimage.png" ><br>
        <span class="span-schedule-login">All</span>
    </div><?php
}
function LeftHeaders($recno, $thisclass, $media_dir, $login)
{?>
    <div class="schedule-div-thumbnail-container-image <?php echo $thisclass ?>" onclick="selectBarber(<?php echo $recno ?>);">
        <img class="schedule-thumbnail" src="../images/others/<?php echo $media_dir?>/avatar/thumbnail.png" alt="../images/others/defaultimage.png" ><br>
        <span class="span-schedule-login"><?php echo $login ?></span>
    </div><?php
}
function DateNav($from, $thisrecno)
{?>
    <div class="schedule-div-navbar-container">
        <div><?php
        $thismonth = date('M');
        $thisyear = date('Y');
        $montharray = array('01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'May', '06' => 'Jun', '07' => 'Jul', '08' => 'Aug', 
            '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dec');?>                
        <button class="btn-manage-schedule-date float-left" id="btnprevious" onclick="previousMonth(<?php echo $thisrecno ?>, '<?php echo $from?>');">Pre</button>
        <button class="circle-me-text-view-options-back float-left select-view-btn" style="display: none;" onclick="selectWeekdir(this, <?php echo $thisrecno ?>, 'Previous', '<?php echo $from?>');" id="btn_backward" ><</button>
        
        <select class='slt-manage-flight-month float-left' name='sltmonth' id='sltmonth' onchange="changeDate(<?php echo $thisrecno?>, '<?php echo $from?>');"><?php
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
        <select class='slt-manage-flight-month float-left' name='sltyear' id='sltyear' onchange="changeDate(<?=$thisrecno?>, '<?=$from?>');"><?php
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
        
        <button class="circle-me-text-view-options-forward float-left select-view-btn" style="display: none;" onclick="selectWeekdir(this, <?php echo $thisrecno ?>, 'Forward', '<?php echo $from?>');" id="btn_forward"  >></button>
        <button class="btn-manage-schedule-date float-left" id="btnprevious" onclick="nextMonth(<?php echo $thisrecno ?>, '<?=$from?>');">Next</button>
        </div>
        <?php
        if(isset($_SESSION['user_recno']))
        {?>
            <div class="float-left" style="margin-top: 10px;">
                <div class="circle-me-text-view-options-off float-left select-view" onclick="selectView(this);" id="div_view_daily">Daily</div>
                <div class="circle-me-text-view-options-off float-left select-view" onclick="selectView(this);" id="div_view_weekly">Weekly</div>
                <div class="circle-me-text-view-options-on float-left select-view" onclick="selectView(this);" id="div_view_monthly">Monthly</div>
            </div><?php
        }?>
    </div><?php
}
function RightHeaders($recno, $thisclass, $media_dir, $login)
{?>
    <div class="schedule-div-thumbnail-container-image <?php echo $thisclass ?>" onclick="selectBarber(<?php echo $recno ?>);">
        <img class="schedule-thumbnail" src="../images/others/<?php echo $media_dir?>/avatar/thumbnail.png" alt="../images/others/defaultimage.png" ><br>
        <span class="span-schedule-login"><?php echo $login ?></span>
    </div><?php
}
function doMultidates($from, $thisrecno, $selectview, $thisdate)
{ 
    global $db;
    
    $thiscurdate = empty($thisdate) ? date('Y-M') : $thisdate;  //$thisdate = 'mm-dd-yy|mm-dd-yy|' if we are looking at Weekly or 'mm-dd-yy' if we are looking at Daily or nothing for Monthly.
    //file_put_contents('./dodebug/debug.txt', "numbrow: ".$thiscurdate."\n", FILE_APPEND);
    $sql = "SELECT recno, media_dir, login FROM users WHERE isActive = true and isverified = true ORDER BY lastname";
    //file_put_contents('./dodebug/debug.txt', "sql: ".$sql, FILE_APPEND);
    $result = $db -> PDOMiniquery($sql); ?>
    <div class="div-calendar-holder">
        <div class="div-nev-holder"><?php
            $thisnumrows = ($db -> PDORowcount($result)) + 1; //The 1 will be for the Allstar icon.
            //file_put_contents('./dodebug/debug.txt', "numbrow: ".$thisnumrows, FILE_APPEND);
            $doonce = false;
            $counter = 1;
            foreach($result as $rs)
            {
                //If $thisrecno != All, that means we want to get just 1 person
                $thisclass = "";
            
                if($thisrecno != "All")
                {
                    $thisclass = "schedule-opac";
                    if($doonce == false)
                    {
                        $doonce =  true;
                        //ShowAllstar($thisclass, $thisrecno); //When we in and $thisrecno == 'All', we want to show the star as clear and everythign else as opac.
                        
                    }
                    if($rs['recno'] == $thisrecno)
                    {
                        $thisclass = "";
                    }
                    
                }
                else
                {
                    if($doonce == false)
                    {
                        $doonce =  true;
                        ShowAllstar($thisclass, $thisrecno); //When we in and $thisrecno == 'All', we want to show the star as clear and everythign else as opac.
                        
                    }
                    $thisclass = "schedule-opac";
                }
                
                if($counter < 6)
                {
                    if($doonce == false)
                    {
                        $doonce =  true;
                        ShowAllstar($thisclass);
                    }
                    LeftHeaders($rs['recno'], $thisclass, $rs['media_dir'], $rs['login']);
                }
                else if($counter == 6)
                {
                    DateNav($from, $thisrecno);
                }
                else if($counter > 6)
                {
                    //When $counter is 7 and up
                    RightHeaders($rs['recno'], $thisclass, $rs['media_dir'], $rs['login']);
                }
                
                $counter++;
                
            }
            if($counter < 6)
            {
                DateNav($from, $thisrecno);
                RightHeaders($rs['recno'], $thisclass, $rs['media_dir'], $rs['login']);
            }?>
        </div>
        <div class="div-calendar-holder-body" id="div_calendar_holder_body"><?php
                    paintCalendar($thisrecno, $thiscurdate, $selectview, $from);?>  
        </div><br /><br />
        <div style="width: 80%; margin: 0px auto;">
            <div class="float-left">
                <div class="circle-me-text-holidayoff float-left" style="float: left; width: 120px;">Holidays</div>
                <div class="circle-me-text" style="float: left; width: 120px;">Off</div>
                <div class="circle-me-text-lesserday" style="float: left; width: 120px;">Past</div>
                <div class="circle-me-text-curday" style="float: left; width: 120px;">Current</div>
                <div class="circle-me-text-greaterday" style="float: left; width: 120px;">Future</div>
            </div>
        </div>
    </div><?php
}
function PaintCalendar($thisrecno, $thiscurdate, $selectview, $from = "Add")
{
  
    if($selectview == 'Monthly')
    {
        PaintCalendarMonth($thisrecno, $thiscurdate, $selectview, $from='Add');
    }
    else
    {
        //Weekly
        //file_put_contents("./dodebug/debug.txt", "enddate: ".$end_date."\n", FILE_APPEND);
        PaintCalendarWeekly($thisrecno, $thiscurdate, $selectview, $from='Add');
    }
}
function PaintCalendarDaily($thisrecno, $thiscurdate, $selectview, $thisday="", $from="")
{
    global $db, $pr, $nd;
    //first day of the month
    $slotarray = ['10:00', '10:30', '11:00', '11:30', '12:00', '12:30', '1:00', '1:30', '2:00', '2:30', '3:00', '3:30', '4:00', '4:30', '5:00', '5:30'];
    $tempyear = date('Y', strtotime($thiscurdate));
    
    $divdayclass = "tbl-manage-schedule-calendar-dates-td-div-daily";
    $curmonthday = "";
    $holidays = $nd -> get_holiday_dates($tempyear);  
    $n = 1; //tracks the actual date inside the TD

    $fillslots = "fill-slots-daily";
    //First we have to find the current week of the month by getting current day before we go into this loop because this loop will only takes care of
    //this week.

    //file_put_contents("./dodebug/debug.txt", "what is curdate?: ".$thiscurdate." and $thisday\n", FILE_APPEND);
    $thiscurdate = empty($thisday) ? date("Y-m-d", strtotime($thiscurdate)) : date('Y-m-d', strtotime($thisday));
    //file_put_contents("./dodebug/debug.txt", "what is $usethisdate?: ".$thisday."\n", FILE_APPEND);
    $sql = "SELECT * FROM schedule_dates WHERE uf_recno = $thisrecno AND date = '".date('Y-m-d', strtotime($thiscurdate))."' ";
    $sql .= "and iscancelled = false and isdeleted = false ORDER BY date, slot";
    //file_put_contents("./dodebug/debug.txt", "filgraph sql: ".$sql."\n", FILE_APPEND);
    $result = $db -> PDOMiniquery($sql);
    $d_array = [];
    if($db ->PDORowcount($result) > 0)
    {
        foreach($result as $rs)
        {     
            $tempsrtime = 0;
            $thisservice = $rs['sr_recno'];
            $thisdate = date('j', strtotime($rs['date'])); //day of month, no leading 0
            $servicearray = [];
            $sqlsr = "SELECT * FROM service WHERE recno IN (".$thisservice.") AND isActive = true and isdeleted = false";
            $resultsr = $db -> PDOMiniquery($sqlsr);
            foreach($resultsr as $rssr)
            {
                $tempsrtime = $tempsrtime + $rssr['time'];
                $servicearray[] =  $rssr['title']." (".$rssr['time'].")";
                //file_put_contents("./dodebug/debug.txt", "filgraph sql: ".$thisdate.','.$rs['slot'].' => '.$rssr['recno'].' - '.$rssr['title']." (".$rssr['time'].")\n", FILE_APPEND);
            }
            $d_array[$thisdate][$rs['slot']][1][] = $tempsrtime;
            $d_array[$thisdate][$rs['slot']][2][] = $servicearray;
        }
    }          
    $usthisclass = "circle-me-text-daily";
    $usethisfunc = "";
    $curmonthday = date('Y-m-d', strtotime($thiscurdate)); //01/22/1982
    $datetype = "";
    $datetype = "";
    $datedescription = "";
    if(in_array($thiscurdate, $holidays))
    {
        //If we are here, that means we are either on a OFF day or Holiday.
        $usthisclass = "circle-me-text-holidayoff-daily";
        $datetype = $nd -> get_holidays_ele($curmonthday, 'datetype');
        $datedescription = $nd -> get_holidays_ele($curmonthday, 'description');
    }
    else
    {
        //If we are here, that means we are not in HOL or Off so we need to find out if today is a work day.
        //
        //file_put_contents('./dodebug/debug.txt', "\n what is date?: ".date('m/d/Y', strtotime("$tempyear/$tempmonth/$tempn"))." Sunday? ".date('N', strtotime("$tempyear/$tempmonth/$tempn")), FILE_APPEND)."\n";
        if(date('Y/m/d') == date('Y/m/d', strtotime($thiscurdate)) && 
                (int)(date('N', strtotime($thiscurdate))) != 6 && (int)(date('N', strtotime($thiscurdate))) != 7)
        {                            
            //file_put_contents('./dodebug/debug.txt', "\n what is date cur?: ".date('d/m/Y', strtotime("$tempyear/$tempmonth/$tempn")), FILE_APPEND);
            $usthisclass = "circle-me-text-curday-daily";
            $usethisfunc = "pickDates(this, '$thisrecno', $n, '$from');";
        }
        else if(date('Y/m/d') > date('Y/m/d', strtotime($thiscurdate)) && 
                (int)(date('N', strtotime($thiscurdate))) != 6 && (int)(date('N', strtotime($thiscurdate))) != 7)
        {
            //file_put_contents('./dodebug/debug.txt', "\n what is date future?: ".date('d/m/Y', strtotime("$tempyear/$tempmonth/$tempn")), FILE_APPEND);
            $usthisclass = "circle-me-text-lesserday-daily";
            $usethisfunc = "";
            //$usethisfunc = "pickDates(this, '$thisrecno', $n, '$from');";
        }
        else if(date('Y/m/d') < date('Y/m/d', strtotime($thiscurdate)) && 
                (int)(date('N', strtotime($thiscurdate))) != 6 && (int)(date('N', strtotime($thiscurdate))) != 7)
        {
            //file_put_contents('./dodebug/debug.txt', "\n what is date future?: ".date('d/m/Y', strtotime("$tempyear/$tempmonth/$tempn")), FILE_APPEND);
            $usthisclass = "circle-me-text-greaterday-daily";
            $usethisfunc = "pickDates(this, '$thisrecno', $n, '$from');";
        }
    }
    ShowBigservicesdaily($datetype, $thisrecno, $datedescription, $usethisfunc, $usthisclass, $thiscurdate, $slotarray, $d_array, $selectview, $fillslots, $divdayclass, $thisday, $from);  
}
function ShowBigservicesdaily($datetype, $thisrecno,$datedescription, $usethisfunc, $usthisclass, $thiscurdate, $slotarray, $d_array, $selectview, $fillslots, $divdayclass, $thisday, $from)
{
    global $pr;
    $j = 0;
    
    $n = empty($thisday) ? date('j', strtotime($thiscurdate)) : date('j', strtotime($thisday));?>
    <div class="div-daily-container">
        <input type="hidden" name="hid_daily_date" id="hid_daily_date" value="<?php echo $thiscurdate ?>" />
        <span class="<?php echo $usthisclass ?> weekly-col-day-lbl" id="<?php echo $thiscurdate ?>"><?=$n?></span>
        <div id="div_day<?=$n?>" class="<?php echo $divdayclass ?>"><?php
            if($datetype == "")
            {?>
                <div class="float-left" style="width: 60%;"><?php
                    //file_put_contents('./dodebug/debug.txt', "\n what is class?: $n - ".$usthisclass, FILE_APPEND);
                    if(str_contains($usthisclass, "-curday") || str_contains($usthisclass, "-greaterday"))
                    {
                        //$d_array[1][$rs['10:00']][] = 120; circle-me-text-lesserday
                        //We want to check if this day has data in the array first.?>
                        <div class="<?php echo $fillslots ?>">
                            <div class="float-left" style="width: 25%;"><?php
                                $usethisfunction = "";
                                $slotarray = ['10:00', '10:30', '11:00', '11:30', '12:00', '12:30', '1:00', '1:30', '2:00', '2:30', '3:00', '3:30', '4:00', '4:30', '5:00', '5:30'];
                                $slotarraymil = ['10:00', '10:30', '11:00', '11:30', '12:00', '12:30', '13:00', '13:30', '14:00', '14:30', '15:00', '15:30', '16:00', '16:30', '17:00', '17:30'];
                                for($i=0; $i<count($slotarray); $i++) 
                                {
                                    $j++;
                                    $temptimer = 0;
                                    //$d_array[$thisdate][$rs['slot']][1][] = $tempsrtime;
                                    if(isset($d_array[$n][$slotarray[$i]][1][0]))
                                    {
                                        $usethisfunction = "";
                                        $temptimer = $d_array[$n][$slotarray[$i]][1][0];
                                        $tothisslot = $temptimer;
                                        $tempservice = $d_array[$n][$slotarray[$i]][2][0];
                                        $thisslot = $slotarray[$i];
                                        if(date('Y-m-d H:m') <= date('Y-m-d H:m', strtotime("$thiscurdate $slotarraymil[$i]")))
                                        {
                                            $usethisfunction = 'class=\'cursor-pointer\' onclick="selectSlot(this, '.$thisrecno.', \''.$thiscurdate.'\', \''.$selectview.'\', \''.$slotarray[$i].'\', '.$n.', \''.$from.'\');"';
                                        }
                                        $temptimer = $temptimer - 30;
                                        unset($d_array[$n][$slotarray[$i]]);
                                   
                                        while($temptimer > 0)
                                        {
                                            $temptimer =  $temptimer - 30;
                                            unset($d_array[$n][$slotarray[$i]]);
                                            $i++;
                                        }
                                        $k = 1;
                                        //
                                        //file_put_contents('./dodebug/debug.txt', "\n what is in slot?: ".$pr->ConvertHourToMinute($thisslot)+intval($tothisslot), FILE_APPEND);?>
                                        <div style="height: auto;" <?php echo $usethisfunction ?>>
                                            <input type="hidden" id ="hid_daily_<?php echo $n ?>" value="<?php echo $thisslot ?> to <?php echo $pr->ConvertMinToHour($pr->ConvertHourToMinute($thisslot)+intval($tothisslot)) ?>" />
                                            <div class="float-left schedule-span-padding-left-daily" name="span_<?php echo $n ?>_s<?php echo $thisslot ?>" id="span_<?php echo $n ?>_s<?php echo $thisslot ?>"><?php echo $thisslot ?> to <?php echo $pr->ConvertMinToHour($pr->ConvertHourToMinute($thisslot)+intval($tothisslot)) ?></div>
                                                <div class="float-left align-left div-weekly-daily" id="div_<?php echo $n ?>_v<?php echo $thisslot ?>"><?php
                                                    //file_put_contents('./dodebug/debug.txt', "\n array size?: ".count($tempservice), FILE_APPEND);
                                                    foreach($tempservice as $item)
                                                    {?>
                                                        <span class="align-left float-left"><?php echo $k ?>.  <?php echo $item ?></span><?php
                                                        $k++;
                                                    }?>
                                                </div><?php
                                                    //unset($d_array[$n][$slotarray[$i]]);?>
                                        </div><?php
                                        if($j == 4)
                                        {
                                            $j=0;?>
                                            </div>
                                            <div class="float-left" style="width: 25%;"><?php
                                        }
                                        //Once we are done with this set, we want to remove it from the array.
                                        //unset($d_array[$n][$slotarray[$i]]);
                                    }
                                    else
                                    {
                                        if(date('Y-m-d H:m') <= date('Y-m-d H:m', strtotime("$thiscurdate $slotarraymil[$i]")))
                                        {
                                            $usethisfunction = 'class=\'cursor-pointer\' onclick="selectSlot(this, '.$thisrecno.', \''.$thiscurdate.'\', \''.$selectview.'\', \''.$slotarray[$i].'\', '.$n.', \''.$from.'\');"';
                                        }?>
                                        <div <?php echo $usethisfunction ?>>
                                            <div class="float-left schedule-span-padding-left-daily" id="span_<?php echo $n ?>_s<?php echo $slotarray[$i] ?>"><?php echo $slotarray[$i] ?></div>
                                            <div class="float-left align-left div-weekly-daily" id="span_<?php echo $n ?>_v<?php echo $slotarray[$i] ?>">------Open</div>
                                        </div><?php  
                                   
                                        if($j == 4)
                                        {
                                            $j=0;?>
                                            </div>
                                            <div class="float-left" style="width: 25%;"><?php
                                        }
                                    } 
                                }?>  
                                </div>
                        </div><?php
                    }?>
                </div>
                <div class="float-right div-info-container-daily" id="div_info_container_daily"></div><?php
            }
            else
            {?>
                <div>
                    <div class="hol-type"><?php echo $datetype ?></div>
                    <div class="hol-type"><?php echo $datedescription ?></div>
                </div><?php
            }?>
        </div>
    </div>
<?php
}
function PaintCalendarWeekly($thisrecno, $thiscurdateNA, $selectview, $from)
{
    global $db, $pr, $nd;
    //$thiscurdateNA is either a date or an array with dates, first item is the dir, Forward or Previous
    //If we are coming from Pre and Next, we will get a single date for $thiscurdate, but if we are doing the mini < and >, we get an array
    //first day of the month
    $slotarray = ['10:00', '10:30', '11:00', '11:30', '12:00', '12:30', '1:00', '1:30', '2:00', '2:30', '3:00', '3:30', '4:00', '4:30', '5:00', '5:30'];
 
    $curdate = date('Y-m-d');
    //file_put_contents("./dodebug/debug.txt", "we are here curdate in weekly: $thiscurdate\n", FILE_APPEND);

    if(is_array($thiscurdateNA))
    {
        //Moving weeks
        if(end($thiscurdateNA) == "Forward")
        {
            
            $thisday = prev($thiscurdateNA);
            //file_put_contents("./dodebug/debug.txt", "thisday in weekly: forward\n", FILE_APPEND);
            $thiscurdate = date('Y-m-d', strtotime("$thisday, +1 day"));
            $tempyear = date('Y', strtotime($thiscurdate)); 
            reset($thiscurdateNA);
            
            $start_date = $thiscurdate;
            $end_date = date('Y-m-d', strtotime("$thiscurdate, +6 days"));
            
        }
        else
        {
            reset($thiscurdateNA);
            $thisday = current($thiscurdateNA);
            //file_put_contents("./dodebug/debug.txt", "thisday in weekly previous: $thisday\n", FILE_APPEND);
            $end_date = date('Y-m-d', strtotime("$thisday, -1 day"));
            $start_date = date('Y-m-d', strtotime("$end_date, -6 days"));
            $tempyear = date('Y', strtotime($start_date)); 
            
        }
    }
    else
    {
        //Moving Months
       $tempyear = date('Y', strtotime($thiscurdateNA)); 
       $thiscurdate = $thiscurdateNA;
       
       if(date('Y/m', strtotime($curdate)) == date('Y/m', strtotime($thiscurdate)))
       {
            $start_date = date('Y-m-d');
            $dayofweek = date('w');
            //0 = Sunday, Saturday = 6
       }
       else
       {
            $start_date = date('Y-m-01', strtotime("$thiscurdate"));
            $dayofweek = date('w', strtotime($start_date));
       }
        $tempdayoftheweek = intval($dayofweek);
        $daytracker = 0;
        while($tempdayoftheweek > 0)
        {
            $daytracker++;
            $tempdayoftheweek--;
        }
        $start_date = date('Y-m-d', strtotime("$start_date, -$daytracker days"));
        $end_date = date('Y-m-d', strtotime("$start_date, +6 days"));
        
    }    
    $divdayclass = "tbl-manage-schedule-calendar-dates-td-div-weekly";
    $holidays = $nd -> get_holiday_dates($tempyear);  
    $thisweekday = "";
    $fillslots = "fill-slots-weekly";

    $d_array = [];
    
    //file_put_contents("./dodebug/debug.txt", "what is curdate weekly?: ".$thiscurdate."\n", FILE_APPEND);
    $sql = "SELECT * FROM schedule_dates WHERE uf_recno = $thisrecno AND date BETWEEN '$start_date' AND '$end_date' ";
    $sql .= "and iscancelled = false and isdeleted = false ORDER BY date, slot";
    //file_put_contents("./dodebug/debug.txt", "what is  weekly sql?: ".$sql."\n", FILE_APPEND);
    $result = $db -> PDOMiniquery($sql);
    $d_array = [];
    
    if($db ->PDORowcount($result) > 0)
    {
        foreach($result as $rs)
        {     
            $tempsrtime = 0;
            $thisservice = $rs['sr_recno'];
            $thisdate = date('j', strtotime($rs['date'])); //day of month, no leading 0
            $servicearray = [];
            $sqlsr = "SELECT * FROM service WHERE recno IN (".$thisservice.") AND isActive = true and isdeleted = false";
            $resultsr = $db -> PDOMiniquery($sqlsr);
            foreach($resultsr as $rssr)
            {
                $tempsrtime = $tempsrtime + $rssr['time'];
                $servicearray[] =  $rssr['title']." (".$rssr['time'].")";
                //file_put_contents("./dodebug/debug.txt", "filgraph sql: ".$thisdate.','.$rs['slot'].' => '.$rssr['recno'].' - '.$rssr['title']." (".$rssr['time']."\n", FILE_APPEND);
            }
            $d_array[$thisdate][$rs['slot']][1][] = $tempsrtime;
            $d_array[$thisdate][$rs['slot']][2][] = $servicearray;
        }
        //file_put_contents('./dodebug/debug.txt', "\n what is tempservice?: ".var_dump($d_array[25]).'\n', FILE_APPEND);
    }
    Writetableheader();?>
    <tr><?php     
        //file_put_contents("./dodebug/debug.txt", "what is start_date?: ".$start_date."\n", FILE_APPEND);
        for($j=0; $j<7; $j++) //tracks the columns, the 7th row is for the All buttons
        {
            $usthisclass = "circle-me-text-weekly";
            $usethisfunc = "";
            $curmonthday = date('Y-m-d', strtotime($start_date)); //01-22-1982
            $datetype = "";
            $datetype = "";
            $datedescription = "";
            if(in_array($start_date, $holidays))
            {
                //If we are here, that means we are either on a OFF day or Holiday.
                $usthisclass = "circle-me-text-holidayoff-weekly";
                $datetype = $nd -> get_holidays_ele($curmonthday, 'datetype');
                $datedescription = $nd -> get_holidays_ele($curmonthday, 'description');
            }
            else
            {
                //If we are here, that means we are not in HOL or Off so we need to find out if today is a work day.
                //
                //file_put_contents('./dodebug/debug.txt', "\n what is date?: ".date('m/d/Y', strtotime("$tempyear/$tempmonth/$tempn"))." Sunday? ".date('N', strtotime("$tempyear/$tempmonth/$tempn")), FILE_APPEND)."\n";
                if(date('Y/m/d') == date('Y/m/d', strtotime($start_date)) && 
                        (int)(date('N', strtotime($start_date))) != 6 && (int)(date('N', strtotime($start_date))) != 7)
                {                            
                    //file_put_contents('./dodebug/debug.txt', "\n what is date cur?: ".date('d/m/Y', strtotime("$tempyear/$tempmonth/$tempn")), FILE_APPEND);
                    $usthisclass = "circle-me-text-curday-weekly";
                    $thisweekday = (int)date('j', strtotime($start_date));
                    $usethisfunc = "pickDates(this, '$thisrecno', $thisweekday, '$from')"; 
                    
                }
                else if(date('Y/m/d') > date('Y/m/d', strtotime($start_date)) && 
                        (int)(date('N', strtotime($start_date))) != 6 && (int)(date('N', strtotime($start_date))) != 7)
                {
                    //file_put_contents('./dodebug/debug.txt', "\n what is date future?: ".date('d/m/Y', strtotime("$tempyear/$tempmonth/$tempn")), FILE_APPEND);
                    $usthisclass = "circle-me-text-lesserday-weekly";
                    $usethisfunc = "";
                    //$usethisfunc = "pickDates(this, '$thisrecno', $n, '$from');";
                }
                else if(date('Y/m/d') < date('Y/m/d', strtotime($start_date)) && 
                        (int)(date('N', strtotime($start_date))) != 6 && (int)(date('N', strtotime($start_date))) != 7)
                {
                    //file_put_contents('./dodebug/debug.txt', "\n what is date future?: ".date('d/m/Y', strtotime("$tempyear/$tempmonth/$tempn")), FILE_APPEND);
                    $usthisclass = "circle-me-text-greaterday-weekly";
                    $thisweekday = (int)date('j', strtotime($start_date));
                    $usethisfunc = "pickDates(this, '$thisrecno', $thisweekday, '$from')";
                }
            }
            
            ShowBigservicesweekly($thisrecno, $datetype, $datedescription, $usthisclass, $start_date, $slotarray, $d_array, $selectview, $fillslots, $divdayclass, $from);

            $start_date = date('Y-m-d', strtotime($start_date.' +1 day'));
        }?>                       
    </tr><?php
    Writetablefooter();
}
function ShowBigservicesweekly($thisrecno, $datetype, $datedescription, $usthisclass, $start_date, $slotarray, $d_array, $selectview, $fillslots, $divdayclass, $from)
{
    global $pr;
    //file_put_contents('./dodebug/debug.txt', "\n what is start_date?: ".$start_date, FILE_APPEND);
    $n = date('j', strtotime($start_date));?>
    <td class="tbl-manage-schedule-calendar-dates" name="day<?=$n?>" id="day<?=$n?>">
        <span class="<?php echo $usthisclass ?> weekly-col-day-lbl" id="<?php echo $start_date ?>"><?=$n?></span>
        <div id="div_day<?=$n?>" class="<?php echo $divdayclass ?>" style="overflow: auto; scrollbar-width: thin;"><?php
            if($datetype == "")
            {
                if(str_contains($usthisclass, "-curday") || str_contains($usthisclass, "-greaterday"))
                {
                    //$d_array[1][$rs['10:00']][] = 120; circle-me-text-lesserday
                    //We want to check if this day has data in the array first?>
                    <div class="<?php echo $fillslots ?> float-left"><?php
                        $slotarrayhr = ['10:00', '10:30', '11:00', '11:30', '12:00', '12:30', '1:00', '1:30', '2:00', '2:30', '3:00', '3:30', '4:00', '4:30', '5:00', '5:30'];
                        $slotarraymil = ['10:00', '10:30', '11:00', '11:30', '12:00', '12:30', '13:00', '13:30', '14:00', '14:30', '15:00', '15:30', '16:00', '16:30', '17:00', '17:30'];
                        for($i=0; $i<count($slotarrayhr); $i++) 
                        {
                            $thisslot = $slotarrayhr[$i];
                            $usethisfunc = "";
                            $usethisclass = "";
                            //file_put_contents('./dodebug/debug.txt', "\n what is date compare?: ".date("Y-m-d H:i", strtotime($start_date." ".$slotarraymil[$i]))." > ".date("Y-m-d H:i") , FILE_APPEND);
                            if(date("Y-m-d H:i", strtotime($start_date." ".$slotarraymil[$i])) >= date("Y-m-d H:i"))
                            {                                    
                                $usethisfunc = 'onclick="doAppointment(this, '.$thisrecno.', \''.$start_date.'\', \''.$thisslot.'\', '.$n.');"'; 
                                $usethisclass = "cursor-pointer";
                            }
                            if(isset($d_array[$n][$slotarrayhr[$i]][1][0]))
                            {
                                //file_put_contents('./dodebug/debug.txt', "\n what is service?: ".$slotarrayhr[$i] , FILE_APPEND);
                                $temptimer = $d_array[$n][$slotarrayhr[$i]][1][0];
                                $tempservice = $d_array[$n][$slotarrayhr[$i]][2][0];
                                
                                $tothisslot = $temptimer;
                                
                                unset($d_array[$n][$slotarrayhr[$i]]);

                                $temptimer = $temptimer - 30;
                                while($temptimer > 0)
                                {
                                    $temptimer =  $temptimer - 30;
                                    unset($d_array[$n][$slotarrayhr[$i]]);
                                    $i++;
                                }
                                $k = 1;
                                //$d_array[25]['10:00'][2][0] doAppointment(obj, recno, date, slot, counter)
                                //file_put_contents('./dodebug/debug.txt', "\n what is in slot?: $thisslot to ".$pr->ConvertMinToHour($pr->ConvertHourToMinute($thisslot)+intval($tothisslot)), FILE_APPEND);?>
                                <div class="div-weekly-min-height <?php echo $usethisclass ?>" <?php echo $usethisfunc ?>>
                                    <div class="float-left schedule-div-padding-left align-left div-weekly-date-lbl" id="div_<?php echo $n ?>_s<?php echo $thisslot ?>"><?php echo $thisslot ?> to <?php echo $pr->ConvertMinToHour($pr->ConvertHourToMinute($thisslot)+intval($tothisslot)) ?></div>
                                        <div class="float-right align-left div-weekly-servicelist" id="div_<?php echo $n ?>_v<?php echo $thisslot ?>"><?php
                                            foreach($tempservice as $item)
                                            {?>
                                                <span class="align-left float-left"><?php echo $k?>.  <?php echo $item ?></span><?php
                                                $k++;
                                            }?>
                                        </div><?php
                                        //unset($d_array[$n][$slotarray[$i]]);

                                    ?>
                                </div><?php
                                //Once we are done with this set, we want to remove it from the array.
                            }
                            else
                            {
                                $usethisfunc = "";
                                $usethisclass = "";
                                $setthisDate = "";
                                
                                //file_put_contents('./dodebug/debug.txt', "\n what is date compare?: ".date("Y-m-d H:i", strtotime($start_date." ".$slotarraymil[$i]))." > ".date("Y-m-d H:i") , FILE_APPEND);
                                if(date("Y-m-d H:i", strtotime($start_date." ".$slotarraymil[$i])) > date("Y-m-d H:i"))
                                {                                    
                                    $usethisfunc = 'onclick="doAppointment(this, '.$thisrecno.', \''.$start_date.'\', \''.$thisslot.'\', '.$n.');"'; 
                                    $usethisclass = "cursor-pointer";
                                }?>
                                <div class="div-weekly-min-height <?php echo $usethisclass ?>" <?php echo $usethisfunc ?>>
                                    <div class="float-left schedule-div-padding-left align-left div-weekly-date-lbl" id="div_<?php echo $n ?>_s<?php echo $slotarrayhr[$i] ?>"><?php echo $slotarrayhr[$i] ?></div>
                                    <div class="align-center" id="div_<?php echo $n ?>_v<?php echo $slotarrayhr[$i] ?>"></div>
                                </div><?php  
                                //unset($d_array[$n][$slotarray[$i]]);
                            } 
                        }?>  
                     
                    </div><?php
                }
                else
                {?>
                   &nbsp;
                <?php
                }
            }
            else
            {
                //file_put_contents('./dodebug/debug.txt', "\n datetype is not empty?", FILE_APPEND);?>
                <div class="div-weekly-min-height">
                    <div class="hol-type"><?php echo $datetype ?></div>
                    <div class="hol-type"><?php echo $datedescription ?></div>
                </div><?php

            }?>
        </div>
    </td>
<?php
}
function Writetableheader()
{?>
   <table class="tbl-manage-schedule-calendar" border="1" name="tbl_manage_schedule_calendar" id="tbl_manage_schedule_calendar">
                <thead>
                    <tr>
                        <td class="tbl-manage-schedule-calendar-header" id="btnsundays" name="btnsundays">Sun</td>
                        <td class="tbl-manage-schedule-calendar-header" id="btnmondays" name="btnmondays">Mon</td>
                        <td class="tbl-manage-schedule-calendar-header" id="btntuesdays" name="btntuesdays">Tue</td>
                        <td class="tbl-manage-schedule-calendar-header" id="btnwednesdays" name="btnwednesdays">Wed</td>
                        <td class="tbl-manage-schedule-calendar-header" id="btnthursdays" name="btnthursdays">Thu</td>
                        <td class="tbl-manage-schedule-calendar-header" id="btnfridays" name="btnfridays">Fri</td>
                        <td class="tbl-manage-schedule-calendar-header" id="btnsaturdays" name="btnsaturdays">Sat</td>
                    </tr>
                </thead>
                <tbody id="tbodymanageschedule"><?php
}
function Writetablefooter()
{?>
    </tbody>
   </table><?php
}
function PaintCalendarMonth($thisrecno, $thiscurdate, $selectview, $from='Add')
{
    global $db, $pr, $nd;
    //first day of the month
    $slotarray = ['10:00', '10:30', '11:00', '11:30', '12:00', '12:30', '1:00', '1:30', '2:00', '2:30', '3:00', '3:30', '4:00', '4:30', '5:00', '5:30'];
    $tempmonth = date('m', strtotime($thiscurdate));
    $tempyear = date('Y', strtotime($thiscurdate));
    $tempcurdate = date('d', strtotime($thiscurdate));
    $curdate = date('Y-m-d');
    //file_put_contents('./dodebug/debug.txt', "\n thiscurdate in month?: $thiscurdate \n", FILE_APPEND);
    //file_put_contents('./dodebug/debug.txt', "\n what is date?: ".$tempcurdate."/".$tempmonth."/".$tempyear, FILE_APPEND);
    //file_put_contents('./dodebug/debug.txt', "\n what is tempyear?: ".$tempyear, FILE_APPEND);
    $fistdayofmonth = date('m/01/Y', strtotime($thiscurdate));
    $lastdayofmonth = date('t', strtotime($thiscurdate));
    $firstdayofweek = date('w', strtotime($fistdayofmonth)); //Now I should get a number between 1 and 7, 1 = Monday and 7 = Sunday
    $curmonthday = "";
    $holidays = $nd -> get_holiday_dates($tempyear);  
    $isstarted = false;
    $n = 1; //tracks the actual date inside the TD
    $tempn = "";
    $nxtmonthdays = 0;    
    $divdayclass = "tbl-manage-schedule-calendar-dates-td-div";
    $d_array = [];
    if(date('Y-m') == date('Y-m', strtotime($thiscurdate)))
    {
        //If the current month, is the same as the thisdate, that means we are handling the current month.
        $start_date = date('Y-m-d');
        $end_date = date('Y-m-t');
    }
    else
    {
        //If we are here, that means we are handling either previous months, the past, or we are handling the future.
        //That means we will start with 1-31
        $start_date = date('Y-m-01', strtotime($thiscurdate));
        $end_date = date('Y-m-t', strtotime($thiscurdate));
    }
    //file_put_contents("./dodebug/debug.txt", "what is curdate?: ".$thiscurdate."\n", FILE_APPEND);
    $sql = "SELECT * FROM schedule_dates WHERE uf_recno = $thisrecno AND date BETWEEN '$start_date' AND '$end_date' ";
    $sql .= "and iscancelled = false and isdeleted = false ORDER BY date, slot";
    //file_put_contents("./dodebug/debug.txt", "Monthly sql: ".$sql."\n", FILE_APPEND);
    $result = $db -> PDOMiniquery($sql);
    $d_array = [];
    if($db ->PDORowcount($result) > 0)
    {
        foreach($result as $rs)
        {     
            $tempsrtime = 0;
            $thisservice = $rs['sr_recno'];
            $thisdate = date('j', strtotime($rs['date'])); //day of month, no leading 0
            if($rs['isOff'] != true)
            {
                $sqlsr = "SELECT * FROM service WHERE recno IN (".$thisservice.") AND isActive = true and isdeleted = false";
                $resultsr = $db -> PDOMiniquery($sqlsr);
                foreach($resultsr as $rssr)
                {
                    $tempsrtime = $tempsrtime + $rssr['time'];

                }
            }
            else
            {
                $tempsrtime = "OFF";
            }
            $d_array[$thisdate][$rs['slot']][] = $tempsrtime;
        }
    }
    WriteTableheader();
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
                    $usthisclass = "circle-me-text";
                    $usethisfunc = "";
                    $curmonthday = date('Y-m-d', strtotime("$tempyear/$tempmonth/$tempn")); //01/22/1982
                    $datetype = "";
                    $datetype = "";
                    $datedescription = "";
                    $fillslots = "fill-slots";
                    if(in_array($curmonthday, $holidays))
                    {
                        //If we are here, that means we are either on a OFF day or Holiday.
                        $usthisclass = "circle-me-text-holidayoff";
                        $datetype = $nd -> get_holidays_ele($curmonthday, 'datetype');
                        $datedescription = $nd -> get_holidays_ele($curmonthday, 'description');
                    }
                    else
                    {
                        //If we are here, that means we are not in HOL or Off so we need to find out if today is a work day.
                        //
                        //file_put_contents('./dodebug/debug.txt', "\n what is date?: ".date('m/d/Y', strtotime("$tempyear/$tempmonth/$tempn"))." Sunday? ".date('N', strtotime("$tempyear/$tempmonth/$tempn")), FILE_APPEND)."\n";
                        //if($tempn == date('d') && $tempmonth == date('m') && $tempyear == date('Y') && 
                          if(strtotime(date('Y/m/d', strtotime("$tempyear/$tempmonth/$tempn"))) == strtotime(date('Y/m/d')) &&
                                (int)(date('N', strtotime("$tempyear/$tempmonth/$tempn"))) != 6 && (int)(date('N', strtotime("$tempyear/$tempmonth/$tempn"))) != 7)
                        {                            
                            //file_put_contents('./dodebug/debug.txt', "\n what is date cur?: ".date('d/m/Y', strtotime("$tempyear/$tempmonth/$tempn")), FILE_APPEND);
                            $usthisclass = "circle-me-text-curday cursor-pointer";
                            $divdayclass = "tbl-manage-schedule-calendar-dates-td-div cursor-pointer";
                            $usethisfunc = "pickDates(this, '$thisrecno', $n, '$from');";

                        }
                        //else if((($tempn < date('d') && $tempmonth <= date('m') && $tempyear <= date('Y')) || ($tempmonth < date('m') && $tempyear <= date('Y'))) && 
                          else if(strtotime(date('Y/m/d', strtotime("$tempyear/$tempmonth/$tempn"))) < strtotime(date('Y/m/d')) &&
                                (int)(date('N', strtotime("$tempyear/$tempmonth/$tempn"))) != 6 && (int)(date('N', strtotime("$tempyear/$tempmonth/$tempn"))) != 7)
                        {
                            //file_put_contents('./dodebug/debug.txt', "\n what is date future?: ".date('d/m/Y', strtotime("$tempyear/$tempmonth/$tempn")), FILE_APPEND);
                            $usthisclass = "circle-me-text-lesserday";
                            $usethisfunc = "";
                            //$usethisfunc = "pickDates(this, '$thisrecno', $n, '$from');";
                        }
                        //else if((($tempn > date('d') && $tempmonth >= date('m') && $tempyear >= date('Y')) || ($tempmonth >= date('m') && $tempyear >= date('Y') || $tempyear > date('Y'))) && 
                          else if(strtotime(date('Y/m/d', strtotime("$tempyear/$tempmonth/$tempn"))) > strtotime(date('Y/m/d')) &&
                                (int)(date('N', strtotime("$tempyear/$tempmonth/$tempn"))) != 6 && (int)(date('N', strtotime("$tempyear/$tempmonth/$tempn"))) != 7)
                        {
                            //file_put_contents('./dodebug/debug.txt', "\n what is date future?: ".date('d/m/Y', strtotime("$tempyear/$tempmonth/$tempn")), FILE_APPEND);
                            $usthisclass = "circle-me-text-greaterday cursor-pointer";
                            $divdayclass = "tbl-manage-schedule-calendar-dates-td-div cursor-pointer";
                            $usethisfunc = "pickDates(this, '$thisrecno', $n, '$from');";
                        }
                    }
                    if($j == $firstdayofweek && $isstarted == false)
                    {
                        ShowBigservices($datetype, $datedescription, $usethisfunc, $usthisclass, $n, $slotarray, $d_array, $selectview, $fillslots, $divdayclass);     
                        $n++;
                        $isstarted = true;
                    }
                    else
                    {
                        if($isstarted == true && $n <= $lastdayofmonth)
                        {
                            $nxtmonthdays = 0;
                            ShowBigservices($datetype, $datedescription, $usethisfunc, $usthisclass, $n, $slotarray, $d_array, $selectview, $fillslots, $divdayclass);
                            $n++;
                        }
                        else
                        {
                            if($isstarted == false)
                            {
                                //If we get here, that means the days are still last month.  That means we need to find out the last few days or the number of days for last month.?>
                                <td class="tbl-manage-schedule-calendar-dates-pre" id="div_premonthday<?=$n?>" onclick="<?php echo $usethisfunc?>;">
                                    <span>&nbsp;</span>
                                    <div id="div_premonthday<?=$nxtmonthdays?>" class="tbl-manage-schedule-calendar-dates-td-div-pre"></div>
                                </td><?php  
                            }
                            else
                            {
                                //If we get here, that means we are going to next month so we can use a tracker to track 1 to n slots.  However, we also want to show when the previous month are also shown first before the current month
                                //then it may go into next month.  So we need a tracker to track if we are displaying previous month first.
                                $nxtmonthdays++;
                                $usthisclass = "circle-me-text";
                                $usethisfunc = "";
                                
                                //$curmonthday - this mark as the current day in format of Y-m-d, 2024-01-13
                                
                                //Because the month and yr maybe different then this calendar date, we have to find it.
                                $nxtmonthdate = date("Y-m-$nxtmonthdays", strtotime("+1 month", strtotime($thiscurdate)));
                                $nxtreformdate = date("m/$nxtmonthdays/Y", strtotime("+1 month", strtotime($thiscurdate)));
                                //file_put_contents('./dodebug/debug.txt', "\n what is compare date?: $curdate and $nxtmonthdate", FILE_APPEND);
                                $nd_array = [];
                                if(in_array($nxtmonthdate, $holidays))
                                {
                                    //If we are here, that means we are either on a OFF day or Holiday.
                                    $usthisclass = "circle-me-text-holidayoff";
                                    $datetype = $nd -> get_holidays_ele($curmonthday, 'datetype');
                                    $datedescription = $nd -> get_holidays_ele($curmonthday, 'description');
                                }
                                else
                                {
                                    //If we go to previous months, this code will think we are still trying to get next month even though it maybe still in previous months,
                                    //we must check for this as well.
                                    //file_put_contents('./dodebug/debug.txt', "\n what is comp?: ".strtotime(date('Y-m', strtotime($nxtmonthdate))) ." >= ".strtotime(date('Y-m')), FILE_APPEND);
                                    //if(strtotime(date('Y-m', strtotime($nxtmonthdate))) >= strtotime(date('Y-m-d')))
                                    //file_put_contents('./dodebug/debug.txt', "\n what is date future?: ".$nxtmonthdate." >= ".date('Y-m-d'), FILE_APPEND);
                                    if(strtotime($nxtmonthdate) >= strtotime(date('Y-m-d')))
                                    {       
                                        //file_put_contents('./dodebug/debug.txt', "\n what is date future?: here? \n", FILE_APPEND);
                                        if((int)(date('N', strtotime("$nxtmonthdate"))) != 6 && (int)(date('N', strtotime("$nxtmonthdate"))) != 7)
                                        {
                                            //file_put_contents('./dodebug/debug.txt', "\n what is date future?: ".date('d/m/Y', strtotime("$tempyear/$tempmonth/$tempn")), FILE_APPEND);
                                            $usthisclass = "circle-me-text-greaterday cursor-pointer";
                                            $divdayclass = "tbl-manage-schedule-calendar-dates-td-div";
                                            $usethisfunc = "pickDates(this, '$thisrecno', $nxtmonthdays, '$from', true, '$nxtreformdate');";
                                        }
                                        else
                                        {
                                            $nsql = "SELECT * FROM schedule_dates WHERE uf_recno = $thisrecno AND date = '$nxtmonthdate' ";
                                            $nsql .= "and iscancelled = false and isdeleted = false ORDER BY date, slot";
                                            //file_put_contents("./dodebug/debug.txt", "filgraph sql: not here?"."\n", FILE_APPEND);
                                            $nresult = $db -> PDOMiniquery($nsql);
                                  
                                            if($db ->PDORowcount($nresult) > 0)
                                            {
                                                foreach($nresult as $nrs)
                                                {     
                                                    $ntempsrtime = 0;
                                                    $nthisservice = $nrs['sr_recno'];
                                                    $nthisdate = date('j', strtotime($nrs['date'])); //day of month, no leading 0

                                                    $sqlsr = "SELECT * FROM service WHERE recno IN (".$nthisservice.") AND isActive = true and isdeleted = false";
                                                    $resultsr = $db -> PDOMiniquery($sqlsr);
                                                    foreach($resultsr as $rssr)
                                                    {
                                                        $ntempsrtime = $ntempsrtime + $rssr['time'];

                                                    }
                                                    $nd_array[$nthisdate][$nrs['slot']][] = $ntempsrtime;
                                                }
                                            } 
                                        }
                                    }
                                    else
                                    {
                                        if((int)(date('N', strtotime($nxtmonthdate))) != 6 && (int)(date('N', strtotime($nxtmonthdate))) != 7)
                                        {
                                            $usthisclass = "circle-me-text-lesserday";
                                            $usethisfunc = "";
                                        }
                                    }
                                }
                                //file_put_contents("./dodebug/debug.txt", "usthisclass sql: not here? ".$_SESSION['user']." - $usthisclass \n", FILE_APPEND);
                                ShowBigservices($datetype, $datedescription, $usethisfunc, $usthisclass, $nxtmonthdays, $slotarray, $nd_array, $selectview, $fillslots, $divdayclass);
                            }
                        }
                    }
                }
            }?>                       
        </tr><?php
    }
    Writetablefooter();
}
function ShowBigservices($datetype, $datedescription, $usethisfunc, $usthisclass, $n, $slotarray, $d_array, $selectview, $fillslots, $divdayclass)
{?>
    <td class="tbl-manage-schedule-calendar-dates" name="day<?=$n?>" id="day<?=$n?>">
        <span class="<?php echo $usthisclass ?>"><?=$n?></span>        
        <div id="div_day<?=$n?>" class="<?php echo $divdayclass ?>" onclick="<?php echo $usethisfunc?>"><?php
            if($datetype == "")
            {
                //file_put_contents('./dodebug/debug.txt', "\n what is class?: $n - ".$usthisclass, FILE_APPEND);
                if(str_contains($usthisclass, "-curday") || str_contains($usthisclass, "-greaterday"))
                {
                    
                    //$d_array[1][$rs['10:00']][] = 120; circle-me-text-lesserday
                    //We want to check if this day has data in the array first.
                    ?>
                    <div class="<?php echo $fillslots ?>">
                        <div style="width: 50%; float: left;"><?php
                            //$slotarray = ['10:00', '10:30', '11:00', '11:30', '12:00', '12:30', '1:00', '1:30', '2:00', '2:30', '3:00', '3:30', '4:00', '4:30', '5:00', '5:30']
                            for($i=0; $i<count($slotarray); $i++) 
                            {
                                if(isset($d_array[$n][$slotarray[$i]][0]))
                                {
                                    
                                    
                                    //file_put_contents('./dodebug/debug.txt', "\n what is in slot?: ".$temptimer, FILE_APPEND);?>
                                    <div>
                                        <span class="float-left schedule-span-padding-left" id="span_<?php echo $n ?>_s<?php echo $slotarray[$i] ?>"><?php echo $slotarray[$i] ?></span>
                                        <span class="align-left" id="span_<?php echo $n ?>_v<?php echo $slotarray[$i] ?>"><?php echo ($d_array[$n][$slotarray[$i]][0] == "OFF") ? 'OFF' : '----Booked' ?></span>
                                    </div><?php
                                    //$i++;
                                    //file_put_contents('./dodebug/debug.txt', "\n what is in slot?: ".$d_array[$n][$slotarray[$i]][0], FILE_APPEND);
                                    
                                    if($i == 7)
                                    {?>
                                        </div>
                                        <div class="float-right" style="width: 50%;"><?php
                                    }
                                    if($d_array[$n][$slotarray[$i]][0] != "OFF")
                                    {
                                        $temptimer = $d_array[$n][$slotarray[$i]][0];
                                        $temptimer = $temptimer - 30;
                                        while($temptimer > 0)
                                        {?>
                                            <div>
                                                <span class="float-left schedule-span-padding-left" id="span_<?php echo $n ?>_s<?php echo $slotarray[$i] ?>"><?php echo $slotarray[$i] ?></span>
                                                <span class="align-left" id="span_<?php echo $n ?>_v<?php echo $slotarray[$i] ?>">continuous</span>
                                            </div><?php

                                            $temptimer =  $temptimer - 30;
                                            $i++;
                                            if($i == 7)
                                            {?>
                                                </div>
                                                <div class="float-right" style="width: 50%;"><?php
                                            }
                                        }
                                    //Once we are done with this set, we want to remove it from the array.
                                    }
                                    else
                                    {
                                        $i++;
                                        if($i == 7)
                                        {?>
                                            </div>
                                            <div class="float-right" style="width: 50%;"><?php
                                        }
                                    }
                                    
                                    unset($d_array[$n][$slotarray[$i]]);
                                }
                                else
                                {?>
                                    <div>
                                        <span class="float-left schedule-span-padding-left" id="span_<?php echo $n ?>_s<?php echo $slotarray[$i] ?>"><?php echo $slotarray[$i] ?></span>
                                        <span class="align-left" id="span_<?php echo $n ?>_v<?php echo $slotarray[$i] ?>">------Open</span>
                                    </div><?php  
                                    if($i == 7)
                                    {?>
                                        </div>
                                        <div class="float-right" style="width: 50%;"><?php
                                    }
                                } 
                            }?>  
                            </div>
                    </div><?php
                }
                else
                {?>
                   &nbsp;
                <?php
                }
            }
            else
            {?>
                <div>
                    <div class="hol-type"><?php echo $datetype ?></div>
                    <div class="hol-type"><?php echo $datedescription ?></div>
                </div><?php
            }?>
        </div>
    </td>
<?php
}
function Main()
{
    global $load_headers;
        //We are sending false into the load_header_logo(false) because we do not want the logo to show, just the other stuffs.
        $load_headers::Load_Header_Logo(false);?>
    <div class="main-div schedule-main-div">
        <script type="text/javascript">
            $("body").data("recno", "<?php echo $_POST['recno'] ?>"); //This is the recno for the barbo
            $("body").data("employee_session", "<?php echo empty($_SESSION['user_recno']) ? "" : $_SESSION['user_recno'] ?>"); //This is the employee's recno in the users' table, when they login to cut hair.
        </script>
        <div class="main-div-body-schedule-right-container-holder" id="main_div_body_schedule_right_container_holder"></div>
        <div id="main_div_body_schedule_right_container" class="main-div-body-schedule-right-container"></div>
    </div><?php
}?>