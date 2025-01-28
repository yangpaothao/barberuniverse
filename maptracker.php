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
                //Since we instantiate 'div_signature' for this instance, everything must reference this id or it will not work.  See clearSignature as an example function.
                if($("#div_signature").length){
                    $("#div_signature").signature({color: '#00f'});
                }
                //$(selector).signature('draw', sig)
            });
        </script>
    </head>
    <body style="overflow-y: hidden;">
        <?php
            Main();
        ?>
    </body>
</html>
<?php

function Main()
{
    global $db, $load_headers, $pt;?>
    <div class="main-div"><?php
        $load_headers::Load_Header_Logo_Marquee();?>
        <br><br> <?php
        $load_headers::Load_Header_Logo_Main();
        
        $sql = "SELECT so.*, flow.recno as flow_recno, flow.customer, flow.actype, flow.flightnumber, flow.gate, flow.date, flow.engineers, flow.mechanics, flow.technicians, ";
        $sql .= "flow.schedulearrival, flow.estimatearrival, flow.actualarrival, flow.estimatedeparture, flow.scheduledeparture, ";
        $sql .= "flow.actualdeparture, s.signature FROM service_orders so INNER JOIN flow ";
        $sql .= "ON flow.recno = so.foreignkey_flow_recno LEFT JOIN signatures s ON so.recno = s.so_foreign_key WHERE so.recno='".$_POST['recno']."' AND so.isdeleted = false";
        $result = $db->PDOMiniquery($sql);?>        
        <div class="main-div-body">
            <div style="border: 1px solid black; padding: 20px;">
                <table class="tbl-orders" style="font-size: .8em;"><?php
                    foreach($result as $rs)
                    {?>
                        <tr><td colspan="4">
                                <div class="div-service-order-data-container" style="font-weight: bold; font-size: 1.5em;">
                                    <div style="float: left;">SO#:&nbsp;<?=$rs['recno'];?></div>
                                    <div><img style="float: right; width: 20px; height: 20px; cursor: pointer;" src="./images/others/printericon.png" onclick="doPrint(<?=$_POST['recno']?>);" /></div>
                                <div>
                            </td>
                        </tr>
                        <tr><td class="tbl-order-lbl">
                                <div class="div-service-order-data-container">
                                    <div class="div-service-order-emplbl">Customer Name:</div>
                                    <div class="div-service-order-empinput"><?= $rs['customer'] ?></div>
                                </div>
                            </td>
                        </tr>
                        <tr><td class="tbl-order-lbl">
                                <div class="div-service-order-data-container">
                                    <div class="div-service-order-emplbl">Aircraft Type:</div>
                                    <div class="div-service-order-empinput"><?= $rs['actype'] ?></div>
                                </div>
                            </td>
                        </tr>
                        <tr><td class="tbl-order-lbl">
                                <div class="div-service-order-data-container">
                                    <div class="div-service-order-emplbl">Flight Number:</div>
                                    <div class="div-service-order-empinput"><?= $rs['flightnumber'] ?></div>
                                </div>
                            </td>
                        </tr>
                        <tr><?php
                            $thisdepttime = "";
                            $thisarrtime = "";
                            if(!is_null($rs['actualarrival']))
                            {
                                $thisarrtime = $rs['actualarrival'];
                            }
                            else if(!is_null($rs['estimatearrival']))
                            {
                                $thisarrtime = $rs['estimatearrival'];
                            }
                            else
                            {
                                $thisarrtime = $rs['schedulearrival'];
                            }
                            if(!is_null($rs['actualdeparture']))
                            {
                                $thisdepttime = $rs['actualdeparture'];
                            }
                            else if(!is_null($rs['estimatedeparture']))
                            {
                                $thisdepttime = $rs['estimatedeparture'];
                            }
                            else
                            {
                                $thisdepttime = $rs['scheduledeparture'];
                            }
                            $isreadonly = "";
                            if(!is_null($rs['completedby']))
                            {
                                $isreadonly = "readonly";
                            }?>
                            <td class="tbl-order-lbl">
                                <div class="div-service-order-data-container">
                                    <div style="width: 190px; height: 100%; float: left;">
                                        <div class="div-service-order-flightlbl" style="width: 100px; float: left; vertical-align: bottom; padding-right: 10px;">Date:</div>
                                        <div style="float: left; width: 70px; text-align: left;">
                                            <input class="service-order-inputs" type="text" id="txtdate" onfocus="getJDate(this);" placeholder="dd/mm/yyy ex: 01/22/2022" onchange="updateSO(this, <?=$rs['flow_recno']?>);" value="<?= date('m/d/Y', strtotime($rs['date'])); ?>"  <?=$isreadonly?> />
                                        </div>
                                    </div>
                                    <div style="width: 160px; height: 100%; float: left;">
                                        <div class="div-service-order-flightlbl" style="width: 100px;">Arrival Time:</div>
                                        <div style="float: left; width: 40px; text-align: left; vertical-align: bottom;">
                                            <input class="service-order-inputs" type="text" id="txtactualarrival" onfocus="saveThisdata(this);" onchange="checkTime(this);updateSO(this, <?=$rs['flow_recno']?>);" value="<?= $thisarrtime; ?>" <?=$isreadonly?>/>
                                        </div>
                                    </div>
                                    <div style="width: 120px; height: 100%; float: left;">
                                        <div class="div-service-order-flightlbl" style="width: 60px;">Gate:</div>
                                        <div style="float: left; width: 40px; text-align: left; vertical-align: bottom;">
                                            <input class="service-order-inputs" type="text" id="txtgate" onfocus="saveThisdata(this);" onchange="updateSO(this, <?=$rs['flow_recno']?>);" value="<?= $rs['gate']; ?>"  <?=$isreadonly?>/></div>
                                    </div>
                                    <div style="width: 160px; height: 100%; float: left;">
                                        <div class="div-service-order-flightlbl" style="width: 100px;">Departure Time:</div>
                                        <div style="float: left; width: 40px; height: 20px; text-align: left; vertical-align: bottom; border-bottom: 1px solid black;">
                                            <input class="service-order-inputs" type="text" id="txtactualdeparture" onfocus="saveThisdata(this);" onchange="checkTime(this);updateSO(this, <?=$rs['flow_recno']?>);" value="<?= $thisdepttime; ?>"  <?=$isreadonly?>/></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr id="tr_sltcategory_list">
                            <td class="tbl-order-lbl" style="width: 100%; text-align: center; font-size: 1em; font-weight: bold; height: 40px;" colspan="4"><?php
                                $thissotable = "category";
                                $thissofield = array("All");
                                $thissowheres = array("isdeleted" => false);
                                $thisordedrby = array("name");
                                $resultso = $db->PDOQuery($thissotable, $thissofield, $thissowheres, $thisordedrby);?>                                
                                <select name="sltsocategory" id="sltsocategory" onchange="getSltcategory(this, <?=$_POST['recno']?>);" style="float: left;">
                                    <option value="Select">Add more category</option><?php
                                    foreach($resultso as $rsso)
                                    {?>
                                        <option value="<?=$rsso['recno']?>"><?=$rsso['name']?></option><?php 
                                    }?>
                                </select>
                            </td>
                        </tr>
                        <!--Now what we want to do in this section is to show the favorites categories and their favorite items in the case that they want to display these rather than having to reselect 
                            this items every time the application loaded.
                            Section items starts here ---------------------------------------------------------
                        -->
                        <?php
                        //First we want to check if this Service Order has already been touched 
                        $thistable = "service_orders_items";
                        //$thisfields = array('All');
                        //$thiswheres = array('fk_service_orders' => $_POST['recno']);
                        //$resultsoi = $db->PDOQuery($thistable, $thisfields, $thiswheres);
                        $sqlso = "SELECT soi.*,items.item,c.name FROM $thistable soi INNER JOIN items ON soi.fk_item = items.recno INNER JOIN category c ON soi.fk_category = c.recno ";
                        $sqlso .= "WHERE soi.isdeleted = false AND fk_service_orders = ".$_POST['recno']." ORDER BY c.name";
                        //file_put_contents("./dodebug/debug.txt", $sqlso, FILE_APPEND);
                        $sqlsoresult = $db->PDOMiniquery($sqlso);
                        if($db->PDORowcount($sqlsoresult) > 0)
                        {
                            $tempcat = "";
                            $curcat = "";
                            $lineno=0;
                            foreach($sqlsoresult as $rsseciont)
                            {
                                $curcat = $rsseciont['name'];
                                $thiscrecno = $rsseciont['fk_category']; //recno from category table
                                $thisirecno = $rsseciont['fk_item']; //recno from items table
                                if($tempcat == "" || $tempcat != $curcat)
                                {
                                    $lineno++;
                                    if($tempcat != $curcat)
                                    {?>
                                                </div> 
                                            </td>
                                        </tr><?php
                                    }?>
                                   <tr class="tr-<?=$thiscrecno?>" id="tr_<?=$thiscrecno?>"><td class="tbl-order-lbl" style="width: 180px; text-align: left; font-size: 1em; font-weight: bold; height: 20px; background-color: #FAFAFA;" colspan="4">
                                           <div style="float: left; margin-right: 5px;" class="div-category-list-item" id="div_category_<?=$thiscrecno?>_<?=$_POST['recno']?>">
                                                <span id="spn_x_<?=$thiscrecno?>" onclick="spnXoutcategory(this, <?=$_POST['recno']?>, <?=$thiscrecno?>);" style="font-size: 1em; padding-right: 2px; color: darkred; font-weight: bold; cursor: pointer;">X</span><?=$curcat?>
                                           </div>
                                           <div id="spanslt_tr_<?=$thiscrecno?>" style="display: none; float: left;"></div>
                                           <span class="span-category-name" id="spnAddmoreso_<?=$thiscrecno?>" onclick="addMoreselect(this, '<?=$curcat?>', <?=$_POST['recno']?>, <?=$thiscrecno?>, <?=$thisirecno?>);" style="float: left; margin-left: 5px; font-size: 1.2em; cursor: pointer;">+</span>
                                       </td>
                                   </tr>
                                   <tr class="tr-<?=$thiscrecno?>" id="tr_<?=$thiscrecno?>_item">
                                       <td>
                                           <div id="div_service_order_data_container_<?=$thiscrecno?>" class="div-service-order-data-container"><?php
                                }
                                $tempcat = $curcat;?>
                                <div id="div_<?=$thiscrecno?>_<?=$thisirecno?>" class="div_service-order-data-service-container">
                                    <div class="div-service-order-servicelbl">
                                        <span id="spn_x_<?=$thiscrecno?>_<?=$thisirecno?>" onclick="spnXout(this, <?=$_POST['recno']?>, <?=$thiscrecno?>, <?=$thisirecno?>)" style="font-size: 1em; padding-right: 2px; color: darkred; font-weight: bold; cursor: pointer;">X</span>
                                        <?=$rsseciont['item']?>:
                                    </div>
                                    <div class="div-service-order-service-input-container">
                                        <input class="service-order-inputs service-order-inputs-item" type="text" id="txtitem_<?=$thisirecno?>_<?=$_POST['recno']?>" onfocus="saveThisdata(this);" onchange="updateSOitems(this, <?=$_POST['recno']?>, <?=$thiscrecno?>, <?=$thisirecno?>);" onfocus="saveThisdata(this);" value="<?=$rsseciont['value'];?>" />
                                    </div>
                                </div><?php
                            }?>
                                    </div> 
                                </td>
                            </tr><?php
                        }
                        else
                        {
                            //If there is no item records, that means user hasn't done anything with this Service Order and therefore, it is still blank.
                            //We then go come here to get the favorite list of category and items.
                            $sql = "SELECT c.recno crecno, c.name, i.recno as irecno, i.item ";
                            $sql .= "FROM category c ";
                            $sql .= "INNER JOIN items i ON c.name=i.category ";
                            $sql .= "WHERE i.favorite = true AND i.isactive = true AND i.isdeleted = false and c.isdeleted = false AND c.favorite = true ";
                            $sql .= "ORDER BY c.name, i.item";
                            
                            $resultsection = $db->PDOMiniquery($sql);
                            if($db->PDORowcount($resultsection) > 0)
                            {
                                $tempcat = "";
                                $curcat = "";
                                $thiscrecno = 0;
                                $thisirecno = 0;
                                $lineno = 0;
                                foreach($resultsection as $rsseciont)
                                {
                                    $curcat = $rsseciont['name'];
                                    $thiscrecno = $rsseciont['crecno']; //recno from category table
                                    $thisirecno = $rsseciont['irecno']; //recno from items table
                                    if($tempcat == "" || $tempcat != $curcat)
                                    {
                                        $lineno++;
                                        if($tempcat != $curcat)
                                        {?>
                                                    </div> 
                                                </td>
                                            </tr><?php
                                        }?>
                                       <tr class="tr-<?=$thiscrecno?>" id="tr_<?=$thiscrecno?>"><td class="tbl-order-lbl" style="width: 100%; text-align: left; font-size: 1em; font-weight: bold; height: 20px; background-color: #FAFAFA;" colspan="4">
                                            <div style="float: left; margin-right: 5px;" class="div-category-list-item" id="div_category_<?=$curcat?>_<?=$_POST['recno']?>">
                                                <span id="spn_x_<?=$thiscrecno?>" onclick="spnXoutcategory(this, <?=$_POST['recno']?>, <?=$thiscrecno?>);" style="font-size: 1em; padding-right: 2px; color: darkred; font-weight: bold; cursor: pointer;">X</span><?=$curcat?>
                                            </div>
                                            <div id="spanslt_tr_<?=$thiscrecno?>" style="display: none; float: left;"></div>
                                            <span class="span-category-name" id="spnAddmoreso_<?=$curcat?>" onclick="addMoreselect(this, '<?=$curcat?>', <?=$_POST['recno']?>, <?=$thiscrecno?>, <?=$thisirecno?>);" style="float: left; margin-left: 5px; font-size: 1.2em; cursor: pointer;">+</span>
                                           </td>
                                       </tr>
                                       <tr class='tr-<?=$thiscrecno?>' id='tr_<?=$thiscrecno?>_item'>
                                           <td>
                                               <div id="div_service_order_data_container_<?=$thiscrecno?>" class="div-service-order-data-container"><?php
                                    }
                                    $tempcat = $curcat;?>
                                    <div id="div_<?=$thiscrecno?>_<?=$thisirecno?>" class="div_service-order-data-service-container">
                                        <div class="div-service-order-servicelbl"><span id="spn_x_<?=$thiscrecno?>_<?=$thisirecno?>" onclick="spnXout(this, <?=$_POST['recno']?>, <?=$thiscrecno?>, <?=$thisirecno?>)" style="font-size: 1em; padding-right: 2px; color: darkred; font-weight: bold; cursor: pointer;">X</span><?=$rsseciont['item']?>:</div>
                                        <div class="div-service-order-service-input-container">
                                            <input class="service-order-inputs service-order-inputs-item" type="text" id="txtitem_<?=$thisirecno?>_<?=$_POST['recno']?>" onfocus="saveThisdata(this);" onchange="updateSOitems(this, <?=$_POST['recno']?>, <?=$thiscrecno?>, <?=$thisirecno?>);" onfocus="saveThisdata(this);" value="" />
                                        </div>
                                    </div><?php
                                }?>
                                        </div> 
                                    </td>
                                </tr><?php
                            }
                        }
                    }?>
                    <!-- section items ends here ---------------------------------------------------------- -->
                    <tr><td class="tbl-order-lbl" style="width: 100%; text-align: center; font-size: 1em; font-weight: bold; height: 40px;" colspan="4">Technicians:</td></tr>
                    <tr>
                        <td class="tbl-order-lbl" colspan="4"><?php
                            if(!is_null($rs['completedby']))
                            {
                                $sqls = "SELECT em.recno, em.firstname, em.middlename, em.lastname FROM employee_master em WHERE em.recno IN (".$rs['engineers'].") ORDER BY em.lastname";
                                //file_put_contents("./dodebug/debug.txt", $sqls, FILE_APPEND);?>

                                    <div style="width: 650px; height: 150px; margin: 0px auto; "><?php
                                    $reseults = $db->PDOMiniquery($sqls); //Someone completed this SO# so we show all the people that worked on this SO
                                    foreach($reseults as $rss)
                                    {
                                        $sqlsignature = "SELECT * FROM signatures WHERE so_foreign_key = ".$_POST['recno']." AND em_foreign_key = ".$rss['recno'];
                                        $reseultsignature = $db->PDOMiniquery($sqlsignature);
                                        //Now we will need to query the signature base on this employee's recno.
                                        //if{$rss['recno'] 
                                        $thissignature = "";
                                        foreach($reseultsignature as $rss2)
                                        {
                                            $thissignature = $rss2['signature'];
                                        }?>
                                        <div style="width: 150px; height: 145px; margin: 0px auto; float: left;">
                                            <div class="div-signature-lbl" style="text-align: left;">Engineer:</div><br>
                                            <div class="div-signature-line" style="text-align: left;"><?= $rss['firstname']; ?>&nbsp;<?=($rss['middlename'] == null ? '' : $rss['middlename'])?>&nbsp;<?=$rss['lastname']?></div><br/><br/>
                                            <div style="height: 60px; width: 120px;">
                                                <?php
                                                if($thissignature != "")
                                                {?>
                                                    <img style="width: 100%; height: 100%;" src='<?=$thissignature?>' /><?php
                                                }?>
                                            </div>
                                        </div><?php
                                    }?>
                                    </div>
                                <?php
                            }
                            else
                            {
                                $thissignature = "";
                                if(!is_null($rs['signature']))
                                {
                                    $thissignature = "<img src='".$rs['signature']."' />";
                                }
                                ?>
                                    <div style="width: 650px; height: 180px; margin: 0px auto; ">
                                        <div style="width: 220px; height: 145px; margin: 0px auto;">
                                            <div class="div-signature-lbl">Engineer:</div>
                                            <div class="div-signature-line"><?= $_SESSION['fullname']; ?></div><br/><br/>
                                            <div id="div_signature" style="height: 100px; width: 300px;"></div>
                                            <button style="text-align: center; margin: 0px auto; width: 60px; height: 40px;" onclick="clearSignature();" id="btnclearsignature">Clear</button>
                                            <button style="text-align: center; margin: 0px auto; width: 120px; height: 40px;" onclick="saveSignature(this,<?=$_POST['recno']?>);" id="btnsignature">Complete</button>
                                        </div>

                                    </div>
                                <?php
                            }?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <?php
        $load_headers::Load_Footer();?>
    </div><?php
}