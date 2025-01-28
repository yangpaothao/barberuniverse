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
            $( document ).ready(function() {
                window.setInterval( function() {
                   paintFid();
                 }, 10000);
            });
            function paintFid(){
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=PaintFid', function(result){
                    $("#div_flow_body_container").html(result);
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
function PaintFid()
{
    global $db, $pt;

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
    
    
    $thisfields = array('All');
    $thistable = "flow";
    $thiswhere = array('isdeparted' => false, 'isdeleted' => false);
    $sql = "SELECT *  FROM flow WHERE flow.date <= '".date('Y-m-d', strtotime($thistomorrow))."' AND isdeparted=false AND isdeleted = false ORDER BY date, customer";
    $result = $db->PDOMiniquery($sql);
    
    $thistable = "note";
    $thisfields = array('recno', 'note');
    $thiswhere = array('isactive' => true, 'isdeleted' => false);    
    $noterows = $db->PDOQuery($thistable, $thisfields, $thiswhere);?>
    <div style=" width: 100%; overflow-y: auto;">
        <table id="tbl_flow_data" class="tbl-flow-data">
            <tr>
                <th style="width: 20px !important; position: sticky; top: 0px; z-index: 10;"></th>
                <th style="width: 160px !important; position: sticky; top: 0px; z-index: 10;">Cust</th>
                <th style="width: 60px !important; position: sticky; top: 0px; z-index: 10;">A/C Type</th>
                <th style="position: sticky; top: 0px; z-index: 10;">Flt No.</th>
                <th style="position: sticky; top: 0px; z-index: 10;">From Station</th>
                <th style="position: sticky; top: 0px; z-index: 10;">SArr.</th>
                <th style="position: sticky; top: 0px; z-index: 10;">EArr.</th>
                <th style="position: sticky; top: 0px; z-index: 10;">AArr.</th>
                <th style="position: sticky; top: 0px; z-index: 10;">Gate</th>  
                <th style="position: sticky; top: 0px; z-index: 10;">To Station</th>
                <th style="position: sticky; top: 0px; z-index: 10;">SDep.</th>
                <th style="position: sticky; top: 0px; z-index: 10;">EDep.</th>
                <th style="position: sticky; top: 0px; z-index: 10;">ADep.</th>
                <th style="width: 200px !important; position: sticky; top: 0px; z-index: 10;">Engineer</th>
                <th style="width: 300px !important; position: sticky; top: 0px;">Note</th>
            </tr><?php
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
                                <td colspan="18" style="text-align: center; font-weight: bold; font-size: 1.2em; background-color: #181818; color: white; height: 40px;"><?= date('d M y', strtotime($curdate));?></td>
                            </tr><?php
                        }
                        if($tempdate == "")
                        {//We only come in here the first time and only 1 time.?>
                            <tr>
                                <td colspan="18" style="text-align: center; font-weight: bold; font-size: 1.2em; background-color: #181818; color: white; height: 40px;"><?= date('d M y', strtotime($curdate));?></td>
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
                    }?>
                    <tr id="tr<?=$i?>" style="background-color: <?= $thisbgcolor?>; color: <?= $thisfontcolor ?>;";>
                        <td style="height: 40px; width: 20px !important; color: <?= $thisfontcolor ?>;"><?= $i ?></td>
                        <td style="height: 40px; color: <?= $thisfontcolor ?>;"><?= $rs['customer']?></td>
                        <td style="height: 40px; color: <?= $thisfontcolor ?>;"><?= $rs['actype']?></td>
                        <td style="height: 40px; color: <?= $thisfontcolor ?>;"><?= $rs['flightnumber']?></td>
                        <td style="height: 40px; color: <?= $thisfontcolor ?>;"><?= $rs['fromstation']?></td>
                        <td style="height: 40px; color: <?= $thisfontcolor ?>;"><?= $rs['schedulearrival']?></td>
                        <td style="height: 40px; color: <?= $thisfontcolor ?>;"><?= $rs['estimatearrival']?></td>
                        <td style="height: 40px; color: <?= $thisfontcolor ?>;"><?= $rs['actualarrival']?></td>
                        <td style="height: 40px; color: <?= $thisfontcolor ?>;"><?= $rs['gate']?></td>
                        <td style="height: 40px; color: <?= $thisfontcolor ?>;"><?= $rs['tostation']?></td>
                        <td style="height: 40px; color: <?= $thisfontcolor ?>;"><?= $rs['scheduledeparture']?></td>
                        <td style="height: 40px; color: <?= $thisfontcolor ?>;"><?= $rs['estimatedeparture']?></td>
                        <td style="height: 40px; color: <?= $thisfontcolor ?>;"><?= $rs['actualdeparture']?></td>
                        <td style="height: 40px; width: 300px !important; color: <?= $thisfontcolor ?>;"><?php
                            $thisrecno = $rs['recno'];
                            $pt->SltEngineer()->GetString($rs['engineers']);?>
                        </td>
                        <td style="height: 40px; width: 300px !important; color: <?= $thisfontcolor ?>"><?= $rs['note']?></td>
                    </tr><?php
                    $i++;
                }
            }
            else
            {?>
            <tr><td>There is no data</td><tr>
            <?php
            }?>  
        </table>
    </div><?php
}
function Main()
{
    global $db, $load_headers, $pt;
  
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
                    <div class="div-fid-note" id="div_flow_note" style="background-color: black;">
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
                <div class="div-flow-body-container" id="div_flow_body_container">
                    <?php PaintFid(); ?>
                </div>
            </div>
        </div>
        <?php
        $load_headers::Load_Footer();?>
    </div><?php
}