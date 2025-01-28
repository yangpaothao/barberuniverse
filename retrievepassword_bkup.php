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
                //alert($('body').data('recno'));
                
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=ManageSchedules&from=Add&recno='+$('body').data('recno')).done(function(result){
                    $("#main_div_body_schedule_right_container").html(result);
                    //fillGraph();
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

                                $("#span_"+thisday+"_v"+thisdata[i]).text("-------Booked");
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

            function pickDates(obj, thisrecno, thisday, from){
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
                        //alert(myData);
                        $.each(myData, function(key, data){
                            //If we are here, we know we have to have at least 1 match appointment so we will
                            //loop through the div-pickedday-container-header-body-slots class and get the text
                            //and if it matches the slot, then we paint the corresponding subdiv.
                            //alert(key + ' -> '+ data['slot'] + ' and ' + data['time']);
                           $(".div-pickedday-container-header-body-slots").each(function(){
                               
                                //alert($(this).text().trim().slice(0, -1)+ ' == '+ data['slot'].trim());
                               
                               //We want to slice the last char to get rid of ':' so we get a string of 0:00
                               //alert(jQuery.type($(this).text().trim().slice(0, -1))+ ' and '+ jQuery.type(data['slot'].trim()));
                               
                               if($(this).text().slice(0, -1).trim() === data['slot'].trim()){
                                   //alert('here');
                                   //Since we have a match, we want the i.d and get the counter
                                   tempid = $(this).prop('id');  //div_slot_counter
                                   splittempid = tempid.split('_');
                                   thiscounter = splittempid[2];
                                   //alert(thiscounter);
                                   //Now we get the real id, we want to change the background color and the text
                                   //span_app_text+counter
                                    //So we will need to check on time, if time is greater than 30, then, we have to take up 2 slots and so on...
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
                                        $("#span_app_text"+thiscounter).removeClass("spn-app-cursor");
                                        $("#div_pickedday_container_header_body_app_ava"+thiscounter).css("background-color", "#660000");
                                        
                                    }
                                    return(false);
                                }
                            });
                        })
                    }
                });
                
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
                        $("#tbodymanageschedule").html(result);
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
                        $("#tbodymanageschedule").html(result);
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
                        $("#tbodymanageschedule").html(result);
                    }
                });
            }
            function closeDive(){
                $("#div_pickedday_container").remove();
                $("#main_div_body_schedule_right_container_holder").hide();
            }
            function selectBarber(recno){
                //We get here when user clicked on the barber image.
                //recno should be a number but it can be 'All' as well.
                //User must select a barber to be able to schedule an appointment.  User will not be able to get the appointment interace until 
                //it loads with individual.
                window.location.href = "schedule.php?recno="+recno;
                
            }
            function doAppointment(obj, recno, date, slot, counter){
                $("#div_pickedday_container_header_body_app_ava"+counter).css("background-color", "lightblue");
                //alert("recno: "+recno+" and date: "+date+" slot: "+slot);
                //!@#$%
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=DoAppointments&recno='+recno+'&counter='+counter+'&thisdate='+date+'&slot='+slot, function(result){
                    $("#div_pickedday_container").css("z-index", 100);
                    $("#main_div_body_schedule_right_container_holder").css("z-index", 200);
                    $("#main_div_body_schedule_right_container").append(result);
                    $("#div_sub_app").css("z-index", 300);
                });
            }
            function cancelAppointment(obj, recno, counter, thisdate, slot){
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=CancelAppointment&recno='+recno+'&counter='+counter+'&thisdate='+thisdate+'&slot='+slot, function(result){
                    $("#div_pickedday_container_header_body_app_ava"+counter).html(result);
                });
            }
            function cancelAppointmentservice(obj, recno, counter, thisdate, slot){
                $("#div_sub_app").remove();
                $("#main_div_body_schedule_right_container_holder").css("z-index", 100);
                $("#div_pickedday_container").css("z-index", 200);
                $("#div_pickedday_container_header_body_app_ava"+counter).css("background-color", "#a6a6a6");
            }
            function bookAppointment(obj,thisrecno, thisdate, thisslot, counter){
                //We want to make sure name, phone#, and email is not empty
                if($("#txt_name").val() == ""){
                    alert("Please enter a name.");
                    return(false);
                }
                if(isPhonenumber($("#txt_phoneno").val()) == false){
                    $("#txt_phoneno").focus();
                    return(false);
                }   
                
                if(validateEmail($("#txt_email").val()) == false){
                    $("#txt_email").focus();
                    return(false);
                }
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
                    }
                    else{
                        alert("Booking failed.  Please try again.");
                        return(false);
                    }
                });
            }
            function selectService(obj, thisrecno){
                //darker one is rgb(242, 242, 242), //very light gray
                //The green one is rgb(0, 153, 0),  //green
                
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
            function dragThis(obj){
                $(obj).draggable();
            }
        </script>
    </head>
    <body>
        <?php
            Main();
        ?>
    </body>
</html><?php
function FilledService()
{
        global $db;
        
        $sql = "SELECT * FROM schedule_dates ";
        $sql .= "WHERE uf_recno = ".$_POST['thisrecno']." AND date = '".date('Y-m-d', strtotime($_POST['thisdate']))."' and iscancelled = false and ";
        $sql .= "isdeleted = false ORDER BY slot ";
        //file_put_contents("./dodebug/debug.txt", "pickdates sql: ".$sql."\n", FILE_APPEND);
        $result = $db -> PDOMiniquery($sql);
        $thissumtime = 0;
        $thiscount = 0;
        if($db ->PDORowcount($result) > 0)
        {
            foreach($result as $rs)
            {
                $thiscount++;
                //For each record, we have to find out out the service type using the sr_recno, we can have 1 or more in format of 1,2,3,...,n
                $sqlsr = "SELECT time FROM service WHERE recno IN (".$rs['sr_recno'].") AND isactive = true and isdeleted = false";
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
            
            echo json_encode($dataarray);
        }
        else
        {
            echo "No Record";
        }
}
function FillCalendar($thiscurdate, $from, $thisrecno)
{
    //$thiscurdate comes in format of 'Y-M'
    //$thisrecno comes in as 'All' or a number, a recno
    
    global $db;
    $sql = "SELECT sd.recno, sd.uf_recno, sd.date, user.recno, user.login FROM schedule_date sd INNER user user ON sd.uf_recno = user.recno ";
    $sql .= "WHERE sd.date >= CURDATE() AND user.isactive = true AND user.isverified = true AND sd.iscancelled = false AND isdeleted = false ORDER BY date";
    $result = $db -> PDOMiniquery($sql);
    foreach($result as $rs)
    {
        
    }
    $xval = [];
    $yval = [];
    if(!$result)
    {
        foreach($result as $rs)
        {
            $xval[] = $rs['login'];
            $xval[] = $rs['login'];
        }
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
        file_put_contents("./dodebug/debug.txt", "filgraph sql: ".$sql."\n", FILE_APPEND);
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
    $bgcolor = "#ffffff";
    $sql = "SELECT * FROM service WHERE isactive = true and isdeleted = false";
    $result = $db ->PDOMiniquery($sql);?>
    <div class="div-pickedday-slot" name="div_sub_app" id="div_sub_app">
        <div>
            <div class="center btn-filled-app-close">
                Please fill in the fields below.
                <button class="btn-cancel-appointment float-right" type="button" name="btn_app_cancel<?php echo $_POST['counter'] ?>" id="btn_app_cancel<?php echo $_POST['counter'] ?>" onclick="cancelAppointmentservice(this, <?php echo $_POST['recno'] ?>, <?php echo $_POST['counter'] ?>, '<?php echo $_POST['thisdate']?>', '<?php echo $_POST['slot'] ?>');">X</button>
            </div>
            <form name="frmservice" id="frmservice" method="post">
                <div>
                    <table class="table table-w100">
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
                            foreach($result as $rs)
                            {
                                $bgcolor = "#f2f2f2";?>
                                <tr class="schedule-service-tbl-tr-bg" name="tr_service_<?php echo $rs['recno'] ?>_<?php echo $rs['time'] ?>" id="tr_service_<?php echo $rs['recno'] ?>_<?php echo $rs['time'] ?>" onclick="selectService(this, <?php echo $rs['recno'] ?>);">
                                    <td class="text-right" colspan="2" name="td_service<?php echo $lineno?>" id="td_service<?php echo $lineno?>">
                                        <span class="align-right float-left" style="width: 7%;"><?php echo $lineno ?>.&nbsp;&nbsp;</span>
                                        <span class="align-left float-left" style="width: 65%; text-align: left;"><?php echo $rs['title'] ?></span>
                                        <span class="align-left float-left" style="width: 15%; text-align: left;"><?php echo $rs['time'] ?> mins</span>
                                        <span class="align-right float-left" style="width: 10%;">$<?php echo number_format((float)$rs['price'], 2, '.', '') ?></span>
                                    </td>
                                </tr><?php
                                $lineno++;
                            }?>
                        </tr>
                    </table>
                </div>
                <div>
                    <button class="btn-ok-appointment" type="button" name="btn_app_ok<?php echo $_POST['counter'] ?>" id="btn_app_ok<?php echo $_POST['counter'] ?>" onclick="bookAppointment(this, <?php echo $_POST['recno'] ?>, '<?php echo $_POST['thisdate']?>', '<?php echo $_POST['slot'] ?>', <?php echo $_POST['counter'] ?>);">Book Appointment</button>
                </div>
            </form>
        </div>
    </div>
<?php
}
function PickDates()
{?>
    <div class="div-pickedday-container" name="div_pickedday_container" id="div_pickedday_container" onmousedown="dragThis(this);">
        <div class="div-pickedday-container-header" name="div_pickedday_container_header" id="div_pickedday_container_header">
            <?php echo $_POST['pickeddate'] ?>
            <button type="button" class="div-pickedday-container-header-btn" name="btn_close_calender_picked" id="btn_close_calender_picked" onclick="closeDive();">X</button>
        </div><?php
        $thiscurdate = date('Y-m-d', strtotime($_POST['pickeddate']));
        $slotarray = ['10:00', '10:30', '11:00', '11:30', '12:00', '12:30', '1:00', '1:30', '2:00', '2:30', '3:00', '3:30', '4:00', '4:30', '5:00', '5:30'];
        //Since we don't have a record, we still need to show the opened time slots so we will loop through the slotarray.
        //However, we want to also check to see if we have passed certain hours, if we have, we do not allow for a click.
        $counter = 1;
        for($i=0; $i<count($slotarray); $i++)
        {
            $usethisonclick = 'onclick="doAppointment(this, '.$_POST['recno'].', \''.$_POST['pickeddate'].'\', \''.$slotarray[$i].'\', '.$counter.');"';
            $usethistext = "Click to make an appointment.";
            //file_put_contents('./dodebug/debug.txt', "\n what is hr compare?: ".date("$thiscurdate $slotarray[$i]")." > ".date("Y-m-d H:i")."\n", FILE_APPEND);
            //file_put_contents('./dodebug/debug.txt', "\n what is hr compare?: ".strtotime(date("$thiscurdate $slotarray[$i]"))." < ".strtotime(date("Y-m-d H:i"))."\n", FILE_APPEND);
            if(strtotime(date("$thiscurdate $slotarray[$i]")) < strtotime(date("Y-m-d H:i")))
            {
                //file_put_contents('./dodebug/debug.txt', "\n what is hr compare?: IN", FILE_APPEND);
                //If the slot time is less than the current time, we want to make it unavailable.
                $usethisonclick = "";
                $usethistext = "No longer Unavailable";
            }?>
            <div class="div-pickedday-container-header-body" name="div_pickedday_container_header<?php echo $counter ?>" id="div_pickedday_container_header-body<?php echo $counter ?>">
                <div class="div-pickedday-container-header-body-slots" id="div_slot_<?php echo $counter ?>"><?php echo $slotarray[$i]?> :</div>
                <div class="div-pickedday-container-header-body-app-ava" name="div_pickedday_container_header_body_app_ava<?php echo $counter ?>" id="div_pickedday_container_header_body_app_ava<?php echo $counter ?>">
                    <span class="spn-app-cursor" name="span_app_text<?php echo $counter ?>" id="span_app_text<?php echo $counter ?>" <?php echo $usethisonclick ?>><?php echo $usethistext ?></span>
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
    $pickeddates = json_decode($_POST['pickedDates']);
    $tempdate = strtotime($thismonth."/01/".$thisyear);
    $thiscurdate = date('m/d/Y', $tempdate);//We do this to include the 'Y' cuz it could go back to December if we are in January, this will make sure we get the right year.
    //file_put_contents("./dodebug/debug.txt", "thiscurdate: ".$thiscurdate, FILE_APPEND);
    //file_put_contents("./dodebug/debug.txt", "thispickeddate: ".$pickeddates, FILE_APPEND);

    PaintCalendar($_POST['recno'], $thiscurdate, $pickeddates, $from);
}


function ManageSchedules()
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
        
    }
    doMultidates($_POST['from'], $_POST['recno']);
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
    <div class="schedule-div-navbar-container"><?php
        $thismonth = date('M');
        $thisyear = date('Y');
        $montharray = array('01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'May', '06' => 'Jun', '07' => 'Jul', '08' => 'Aug', 
            '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dec');?>                
        <button class="btn-manage-schedule-date" id="btnprevious" onclick="previousMonth('<?php echo $from?>');">Pre</button>
        <select class='slt-manage-flight-month' name='sltmonth' id='sltmonth' onchange="changeDate(<?php echo $thisrecno?>, '<?php echo $from?>');"><?php
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
        <select class='slt-manage-flight-month' name='sltyear' id='sltyear' onchange="changeDate(<?=$thisrecno?>, '<?=$from?>');"><?php
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
        <button class="btn-manage-schedule-date" id="btnprevious" onclick="nextMonth('<?=$from?>');">Next</button>
    </div><?php
}
function RightHeaders($recno, $thisclass, $media_dir, $login)
{?>
    <div class="schedule-div-thumbnail-container-image <?php echo $thisclass ?>" onclick="selectBarber(<?php echo $recno ?>);">
        <img class="schedule-thumbnail" src="../images/others/<?php echo $media_dir?>/avatar/thumbnail.png" alt="../images/others/defaultimage.png" ><br>
        <span class="span-schedule-login"><?php echo $login ?></span>
    </div><?php
}
function doMultidates($from, $thisrecno)
{ 
    global $db;
    
    $thiscurdate = date('Y-M');
    $sql = "SELECT recno, media_dir, login FROM users WHERE isactive = true and isverified = true ORDER BY lastname";
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
        <div class="div-calendar-holder-body">
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
                    paintCalendar($thisrecno, $thiscurdate, array(), $from);?>  
                </tbody>
            </table>
        </div><br /><br />
        <div style="width: 80%; margin: 0px auto;">
            <div class="circle-me-text-holidayoff" style="float: left; width: 120px;">Holidays</div>
            <div class="circle-me-text" style="float: left; width: 120px;">Off</div>
            <div class="circle-me-text-lesserday" style="float: left; width: 120px;">Past</div>
            <div class="circle-me-text-curday" style="float: left; width: 120px;">Current</div>
            <div class="circle-me-text-greaterday" style="float: left; width: 120px;">Future</div>
        </div>
    </div><?php
}
function PaintCalendar($thisrecno, $thiscurdate, $pickeddates=[], $from='Add')
{
    global $pr, $nd;
    //first day of the month
    $tempmonth = date('m', strtotime($thiscurdate));
    $tempyear = date('Y', strtotime($thiscurdate));
    $tempcurdate = date('d', strtotime($thiscurdate));
    $curdate = date('Y-m-d');
    //file_put_contents('./dodebug/debug.txt', "\n thiscurdate?: ".$thiscurdate, FILE_APPEND);
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
                        if($tempn == date('d') && $tempmonth == date('m') && $tempyear == date('Y') && 
                                (int)(date('N', strtotime("$tempyear/$tempmonth/$tempn"))) != 6 && (int)(date('N', strtotime("$tempyear/$tempmonth/$tempn"))) != 7)
                        {                            
                            //file_put_contents('./dodebug/debug.txt', "\n what is date cur?: ".date('d/m/Y', strtotime("$tempyear/$tempmonth/$tempn")), FILE_APPEND);
                            $usthisclass = "circle-me-text-curday";
                            $usethisfunc = "pickDates(this, '$thisrecno', $n, '$from');";

                        }
                        else if((($tempn < date('d') && $tempmonth <= date('m') && $tempyear <= date('Y')) || ($tempmonth < date('m') && $tempyear <= date('Y')))&& 
                                (int)(date('N', strtotime("$tempyear/$tempmonth/$tempn"))) != 6 && (int)(date('N', strtotime("$tempyear/$tempmonth/$tempn"))) != 7)
                        {
                            //file_put_contents('./dodebug/debug.txt', "\n what is date future?: ".date('d/m/Y', strtotime("$tempyear/$tempmonth/$tempn")), FILE_APPEND);
                            $usthisclass = "circle-me-text-lesserday";
                            $usethisfunc = "";
                            //$usethisfunc = "pickDates(this, '$thisrecno', $n, '$from');";
                        }
                        else if((($tempn > date('d') && $tempmonth >= date('m') && $tempyear >= date('Y')) ||
                            ($tempmonth >= date('m') && $tempyear >= date('Y') || $tempyear > date('Y'))) && 
                                (int)(date('N', strtotime("$tempyear/$tempmonth/$tempn"))) != 6 && (int)(date('N', strtotime("$tempyear/$tempmonth/$tempn"))) != 7)
                        {
                            //file_put_contents('./dodebug/debug.txt', "\n what is date future?: ".date('d/m/Y', strtotime("$tempyear/$tempmonth/$tempn")), FILE_APPEND);
                            $usthisclass = "circle-me-text-greaterday";
                            $usethisfunc = "pickDates(this, '$thisrecno', $n, '$from');";
                        }
                    }
                    if($j == $firstdayofweek && $isstarted == false)
                    {?>
                        <td class="tbl-manage-schedule-calendar-dates" name="day<?=$n?>" id="day<?=$n?>" onclick="<?php echo $usethisfunc?>;">
                            <span class="<?php echo $usthisclass ?>"><?=$n?></span>
                            <div id="div_day$<?=$n?>" class="tbl-manage-schedule-calendar-dates-td-div"><?php
                                if($datetype == "")
                                {
                                    if($usthisclass != "circle-me-text-lesserday" && $usthisclass != "circle-me-text")
                                    {?>
                                        <div class="fill-slots">
                                            <div style="width: 50%; float: left;">
                                                <div><span class="float-left schedule-span-padding-left" id="span_<?php echo $n ?>_s10:00">10:00</span><span class="align-left" id="span_<?php echo $n ?>_v10:00">-------Open</span></div>
                                                <div><span class="float-left schedule-span-padding-left" id="span_<?php echo $n ?>_s10:30">10:30</span><span class="align-left" id="span_<?php echo $n ?>_v10:30">-------Open</span></div>
                                                <div><span class="float-left schedule-span-padding-left" id="span_<?php echo $n ?>_s11:00">11:00</span><span class="align-left" id="span_<?php echo $n ?>_v11:00">-------Open</span></div>
                                                <div><span class="float-left schedule-span-padding-left" id="span_<?php echo $n ?>_s11:30">11:30</span><span class="align-left" id="span_<?php echo $n ?>_v11:30">-------Open</span></div>
                                                <div><span class="float-left schedule-span-padding-left" id="span_<?php echo $n ?>_s12:00">12:00</span><span class="align-left" id="span_<?php echo $n ?>_v12:00">-------Open</span></div>
                                                <div><span class="float-left schedule-span-padding-left" id="span_<?php echo $n ?>_s12:30">12:30</span><span class="align-left" id="span_<?php echo $n ?>_v12:30">-------Open</span></div>
                                                <div><span class="float-left schedule-span-padding-left" id="span_<?php echo $n ?>_s1:00">1:00</span><span class="align-left" id="span_<?php echo $n ?>_v1:00">-------Open</span></div>
                                                <div><span class="float-left schedule-span-padding-left" id="span_<?php echo $n ?>_s1:30">1:30</span><span class="align-left" id="span_<?php echo $n ?>_v1:30">-------Open</span></div>
                                            </div>
                                            <div class="float-right" style="width: 50%;">
                                                <div><span class="float-left schedule-span-padding-left" id="span_<?php echo $n ?>_s2:00">2:00</span><span class="align-left" id="span_<?php echo $n ?>_v2:00">-------Open</span></div>
                                                <div><span class="float-left schedule-span-padding-left" id="span_<?php echo $n ?>_s2:30">2:30</span><span class="align-left" id="span_<?php echo $n ?>_v2:30">-------Open</span></div>
                                                <div><span class="float-left schedule-span-padding-left" id="span_<?php echo $n ?>_s3:00">3:00</span><span class="align-left" id="span_<?php echo $n ?>_v3:00">-------Open</span></div>
                                                <div><span class="float-left schedule-span-padding-left" id="span_<?php echo $n ?>_s3:30">3:30</span><span class="align-left" id="span_<?php echo $n ?>_v3:30">-------Open</span></div>
                                                <div><span class="float-left schedule-span-padding-left" id="span_<?php echo $n ?>_s4:00">4:00</span><span class="align-left" id="span_<?php echo $n ?>_v4:00">-------Open</span></div>
                                                <div><span class="float-left schedule-span-padding-left" id="span_<?php echo $n ?>_s4:30">4:30</span><span class="align-left" id="span_<?php echo $n ?>_v4:30">-------Open</span></div>
                                                <div><span class="float-left schedule-span-padding-left" id="span_<?php echo $n ?>_s5:00">5:00</span><span class="align-left" id="span_<?php echo $n ?>_v5:00">-------Open</span></div>
                                                <div><span class="float-left schedule-span-padding-left" id="span_<?php echo $n ?>_s5:30">5:30</span><span class="align-left" id="span_<?php echo $n ?>_v5:30">-------Open</span></div>
                                            </div>
                                        </div><?php
                                    }
                                    else
                                    {?>
                                        &nbsp;<?php
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
                        </td><?php
                        $n++;
                        $isstarted = true;
                    }
                    else
                    {
                        if($isstarted == true && $n <= $lastdayofmonth)
                        {
                            $nxtmonthdays = 0;?>
                            <td class="tbl-manage-schedule-calendar-dates" name="day<?=$n?>" id="day<?=$n?>" onclick="<?php echo $usethisfunc?>;">
                                <span class="<?php echo $usthisclass ?>"><?=$n?></span>
                                <div id="div_day<?=$n?>" class="tbl-manage-schedule-calendar-dates-td-div"><?php
                                    if($datetype == "")
                                    {
                                        if($usthisclass != "circle-me-text-lesserday" && $usthisclass != "circle-me-text")
                                        {?>
                                            <div class="fill-slots">
                                                <div style="width: 50%; float: left;">
                                                    <div><span class="float-left schedule-span-padding-left" id="span_<?php echo $n ?>_s10:00">10:00</span><span class="align-left" id="span_<?php echo $n ?>_v10:00">-------Open</span></div>
                                                    <div><span class="float-left schedule-span-padding-left" id="span_<?php echo $n ?>_s10:30">10:30</span><span class="align-left" id="span_<?php echo $n ?>_v10:30">-------Open</span></div>
                                                    <div><span class="float-left schedule-span-padding-left" id="span_<?php echo $n ?>_s11:00">11:00</span><span class="align-left" id="span_<?php echo $n ?>_v11:00">-------Open</span></div>
                                                    <div><span class="float-left schedule-span-padding-left" id="span_<?php echo $n ?>_s11:30">11:30</span><span class="align-left" id="span_<?php echo $n ?>_v11:30">-------Open</span></div>
                                                    <div><span class="float-left schedule-span-padding-left" id="span_<?php echo $n ?>_s12:00">12:00</span><span class="align-left" id="span_<?php echo $n ?>_v12:00">-------Open</span></div>
                                                    <div><span class="float-left schedule-span-padding-left" id="span_<?php echo $n ?>_s12:30">12:30</span><span class="align-left" id="span_<?php echo $n ?>_v12:30">-------Open</span></div>
                                                    <div><span class="float-left schedule-span-padding-left" id="span_<?php echo $n ?>_s1:00">1:00</span><span class="align-left" id="span_<?php echo $n ?>_v1:00">-------Open</span></div>
                                                    <div><span class="float-left schedule-span-padding-left" id="span_<?php echo $n ?>_s1:30">1:30</span><span class="align-left" id="span_<?php echo $n ?>_v1:30">-------Open</span></div>
                                                </div>
                                                <div class="float-right" style="width: 50%;">
                                                    <div><span class="float-left schedule-span-padding-left" id="span_<?php echo $n ?>_s2:00">2:00</span><span class="align-left" id="span_<?php echo $n ?>_v2:00">-------Open</span></div>
                                                    <div><span class="float-left schedule-span-padding-left" id="span_<?php echo $n ?>_s2:30">2:30</span><span class="align-left" id="span_<?php echo $n ?>_v2:30">-------Open</span></div>
                                                    <div><span class="float-left schedule-span-padding-left" id="span_<?php echo $n ?>_s3:00">3:00</span><span class="align-left" id="span_<?php echo $n ?>_v3:00">-------Open</span></div>
                                                    <div><span class="float-left schedule-span-padding-left" id="span_<?php echo $n ?>_s3:30">3:30</span><span class="align-left" id="span_<?php echo $n ?>_v3:30">-------Open</span></div>
                                                    <div><span class="float-left schedule-span-padding-left" id="span_<?php echo $n ?>_s4:00">4:00</span><span class="align-left" id="span_<?php echo $n ?>_v4:00">-------Open</span></div>
                                                    <div><span class="float-left schedule-span-padding-left" id="span_<?php echo $n ?>_s4:30">4:30</span><span class="align-left" id="span_<?php echo $n ?>_v4:30">-------Open</span></div>
                                                    <div><span class="float-left schedule-span-padding-left" id="span_<?php echo $n ?>_s5:00">5:00</span><span class="align-left" id="span_<?php echo $n ?>_v5:00">-------Open</span></div>
                                                    <div><span class="float-left schedule-span-padding-left" id="span_<?php echo $n ?>_s5:30">5:30</span><span class="align-left" id="span_<?php echo $n ?>_v5:30">-------Open</span></div>
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
                            </td><?php
                            $n++;
                        }
                        else
                        {
                            if($isstarted == false)
                            {
                                //If we get here, that means the days are still last month.  That means we need to find out the last few days or the number of days for last month.?>
                                <td class="tbl-manage-schedule-calendar-dates-pre" id="div_premonthday<?=$n?>" onclick="<?php echo $usethisfunc?>;">
                                    <span>&nbsp;</span>
                                    <div id="div_premonthday<?=$nxtmonthdays?>" class="tbl-manage-schedule-calendar-dates-td-div-pre">
                                        
                                    </div></td><?php  
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
                                //file_put_contents('./dodebug/debug.txt', "\n what is compare date?: $curdate and $nxtmonthdate", FILE_APPEND);
                                
                                if(in_array($nxtmonthdate, $holidays))
                                {
                                    //If we are here, that means we are either on a OFF day or Holiday.
                                    $usthisclass = "circle-me-text-holidayoff";
                                    $datetype = $nd -> get_holidays_ele($curmonthday, 'datetype');
                                    $datedescription = $nd -> get_holidays_ele($curmonthday, 'description');
                                }
                                else
                                {
                                    if(strtotime($nxtmonthdate) < strtotime($curdate) && 
                                    (int)(date('N', strtotime("$nxtmonthdate"))) != 6 && (int)(date('N', strtotime("$nxtmonthdate"))) != 7)
                                    {
                                        //file_put_contents('./dodebug/debug.txt', "\n what is date future?: ".date('d/m/Y', strtotime("$tempyear/$tempmonth/$tempn")), FILE_APPEND);
                                        $usthisclass = "circle-me-text-lesserday";
                                        $usethisfunc = "pickDates(this, '$thisrecno', $n, '$from');";
                                    }
                                    else if(strtotime($nxtmonthdate) == strtotime($curdate) && 
                                            (int)(date('N', strtotime("$nxtmonthdate"))) != 6 && (int)(date('N', strtotime("$nxtmonthdate"))) != 7)
                                    {
                                        $usthisclass = "circle-me-text-curday";
                                        $usethisfunc = "pickDates(this, '$thisrecno', $nxtmonthdays, '$from');";
                                    
                                    }
                                    else if(strtotime($nxtmonthdate) > strtotime($curdate) && 
                                            (int)(date('N', strtotime("$nxtmonthdate"))) != 6 && (int)(date('N', strtotime("$nxtmonthdate"))) != 7)
                                    {
                                        //file_put_contents('./dodebug/debug.txt', "\n what is date future?: ".date('d/m/Y', strtotime("$tempyear/$tempmonth/$tempn")), FILE_APPEND);
                                        $usthisclass = "circle-me-text-greaterday";
                                        $usethisfunc = "pickDates(this, '$thisrecno', $nxtmonthdays, '$from');";
                                    }
                                }?>
                                <td class="tbl-manage-schedule-calendar-dates-nxt" id="div_nxtmonthday<?=$n?>" onclick="<?php echo $usethisfunc?>;">
                                    <span class="<?php echo $usthisclass ?>"><?php echo $nxtmonthdays ?></span>
                                    <div id="div_nxtmonthday<?=$nxtmonthdays?>" class="tbl-manage-schedule-calendar-dates-td-div-nxt"><?php
                                        if($datetype == "")
                                        {
                                            if($usthisclass != "circle-me-text-lesserday" && $usthisclass != "circle-me-text")
                                            {?>
                                                <div class="fill-slots">
                                                    <div style="width: 50%; float: left;">
                                                        <div><span class="float-left schedule-span-padding-left" id="span_nxtmonthday_<?php echo $nxtmonthdays ?>_s10:00">10:00</span><span class="align-left" id="span_nxtmonthday_<?php echo $nxtmonthdays ?>_v10:00">-------Open</span></div>
                                                        <div><span class="float-left schedule-span-padding-left" id="span_nxtmonthday_<?php echo $nxtmonthdays ?>_s10:30">10:30</span><span class="align-left" id="span_nxtmonthday_<?php echo $nxtmonthdays ?>_v10:30">-------Open</span></div>
                                                        <div><span class="float-left schedule-span-padding-left" id="span_nxtmonthday_<?php echo $nxtmonthdays ?>_s11:00">11:00</span><span class="align-left" id="span_nxtmonthday_<?php echo $nxtmonthdays ?>_v11:00">-------Open</span></div>
                                                        <div><span class="float-left schedule-span-padding-left" id="span_nxtmonthday_<?php echo $nxtmonthdays ?>_s11:30">11:30</span><span class="align-left" id="span_nxtmonthday_<?php echo $nxtmonthdays ?>_v11:30">-------Open</span></div>
                                                        <div><span class="float-left schedule-span-padding-left" id="span_nxtmonthday_<?php echo $nxtmonthdays ?>_s12:00">12:00</span><span class="align-left" id="span_nxtmonthday_<?php echo $nxtmonthdays ?>_v12:00">-------Open</span></div>
                                                        <div><span class="float-left schedule-span-padding-left" id="span_nxtmonthday_<?php echo $nxtmonthdays ?>_s12:30">12:30</span><span class="align-left" id="span_nxtmonthday_<?php echo $nxtmonthdays ?>_v12:30">-------Open</span></div>
                                                        <div><span class="float-left schedule-span-padding-left" id="span_nxtmonthday_<?php echo $nxtmonthdays ?>_s1:00">1:00</span><span class="align-left" id="span_nxtmonthday_<?php echo $nxtmonthdays ?>_v1:00">-------Open</span></div>
                                                        <div><span class="float-left schedule-span-padding-left" id="span_nxtmonthday_<?php echo $nxtmonthdays ?>_s1:30">1:30</span><span class="align-left" id="span_nxtmonthday_<?php echo $nxtmonthdays ?>_v1:30">-------Open</span></div>
                                                    </div>
                                                    <div class="float-right" style="width: 50%;">
                                                        <div><span class="float-left schedule-span-padding-left" id="span_nxtmonthday_<?php echo $nxtmonthdays ?>_s2:00">2:00</span><span class="align-left" id="span_nxtmonthday_<?php echo $nxtmonthdays ?>_v2:00">-------Open</span></div>
                                                        <div><span class="float-left schedule-span-padding-left" id="span_nxtmonthday_<?php echo $nxtmonthdays ?>_s2:30">2:30</span><span class="align-left" id="span_nxtmonthday_<?php echo $nxtmonthdays ?>_v2:30">-------Open</span></div>
                                                        <div><span class="float-left schedule-span-padding-left" id="span_nxtmonthday_<?php echo $nxtmonthdays ?>_s3:00">3:00</span><span class="align-left" id="span_nxtmonthday_<?php echo $nxtmonthdays ?>_v3:00">-------Open</span></div>
                                                        <div><span class="float-left schedule-span-padding-left" id="span_nxtmonthday_<?php echo $nxtmonthdays ?>_s3:30">3:30</span><span class="align-left" id="span_nxtmonthday_<?php echo $nxtmonthdays ?>_v3:30">-------Open</span></div>
                                                        <div><span class="float-left schedule-span-padding-left" id="span_nxtmonthday_<?php echo $nxtmonthdays ?>_s4:00">4:00</span><span class="align-left" id="span_nxtmonthday_<?php echo $nxtmonthdays ?>_v4:00">-------Open</span></div>
                                                        <div><span class="float-left schedule-span-padding-left" id="span_nxtmonthday_<?php echo $nxtmonthdays ?>_s4:30">4:30</span><span class="align-left" id="span_nxtmonthday_<?php echo $nxtmonthdays ?>_v4:30">-------Open</span></div>
                                                        <div><span class="float-left schedule-span-padding-left" id="span_nxtmonthday_<?php echo $nxtmonthdays ?>_s5:00">5:00</span><span class="align-left" id="span_nxtmonthday_<?php echo $nxtmonthdays ?>_v5:00">-------Open</span></div>
                                                        <div><span class="float-left schedule-span-padding-left" id="span_nxtmonthday_<?php echo $nxtmonthdays ?>_s5:30">5:30</span><span class="align-left" id="span_nxtmonthday_<?php echo $nxtmonthdays ?>_v5:30">-------Open</span></div>
                                                    </div>
                                                </div><?php
                                            }
                                            else
                                            {?>
                                                &nbsp;<?php
                                            }
                                        }
                                        else
                                        {?>
                                            <div>
                                                <div class="hol-type"><?php echo $datetype ?></div>
                                                <div class="hol-type"><?php echo $datedescription ?></div>
                                            </div>
                                            <?php
                                        }?>
                                    </div>
                                </td><?php    
                            }
                        }
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
        <script type="text/javascript">
            $("body").data("recno", "<?php echo $_POST['recno'] ?>");
        </script>
        <div class="main-div-body-schedule-right-container-holder" id="main_div_body_schedule_right_container_holder"></div>
        <div id="main_div_body_schedule_right_container" class="main-div-body-schedule-right-container"></div>
    </div><?php
}?>