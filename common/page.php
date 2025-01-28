<?php
session_start();
//date_default_timezone_set('America/Chicago'); //THIS MAKES THE WEBSITE USE THIS TIMEZONE AS THE TIME.
//date_default_timezone_set('Australia/Sydney'); //THIS MAKES THE WEBSITE USE THIS TIMEZONE AS THE TIME.
date_default_timezone_set('America/Chicago'); //THIS MAKES THE WEBSITE USE THIS TIMEZONE AS THE TIME.
$temp_page = filter_input(INPUT_SERVER, 'PHP_SELF'); // will look like /index.php or /somedir/somepage.php
$explode_page = explode("/", $temp_page); //This variable will now be an array and the page name is the last element of this array
$this_page = end($explode_page);
if(!isset($_SESSION['user']) && $this_page != "verifyme.php" && $this_page != "index.php" && $this_page != "retrievepassword.php" && $this_page != "resetpassword.php"  && 
        $this_page != "apicurl.php"  && $this_page != "registration.php" && $this_page != "passwordreset.php" && $this_page != "login.php" && $this_page != "schedule.php" &&
        $this_page != "cancellation.php")
{
    header("Location: /index.php"); //Unless this is the main/front page, if user does not have a logged session, they will be forced to login first.
    exit();
}
if(!function_exists('GetTimes'))
{
    function GetTimes()
    {
        $thisutctime = gmdate('H:i:s');
        $thisdate = date('d M y');
        $thislocaltime = localtime(time(), true);
        $thislocalactualtime = ($thislocaltime['tm_hour'] < 10 ? "0".$thislocaltime['tm_hour'] : $thislocaltime['tm_hour']).":".($thislocaltime['tm_min'] < 10 ? "0".$thislocaltime['tm_min'] : $thislocaltime['tm_min']).":".($thislocaltime['tm_sec'] < 10 ? "0".$thislocaltime['tm_sec'] : $thislocaltime['tm_sec']);
        $thisarray = Array();
        $thisarray = array('thisutctime' => $thisutctime,
                           'thisdate' => $thisdate,
                           'thislocaltime' => $thislocalactualtime);
        echo json_encode($thisarray);
    }
}
if(!function_exists('Logout'))
{
    
    function Logout()
    {
        if(isset($_SESSION))
        {
            //file_put_contents("./dodebug/debug.txt", 'Function exists logging out', FILE_APPEND);
            session_unset();
            session_destroy();
            echo 'Success';
        }
        else
        {
           echo 'Failed'; 
        }
    }
}?>
