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
    global $db;?>  
    <div class="main-div-body">
        <div style="height: 700px; boarder: 1px solid black;">
            <table style="font-size: .8em; background-color: white; boarder: none;">
                    <tr><td colspan="4">
                            <div class="div-service-order-data-container" style="font-weight: bold; font-size: 1.5em;">
                                <div style="float: left;">SO#:&nbsp;</div>
                                <div><img style="float: right; width: 120px; height: 80px; cursor: pointer; vertical-align: top; margin-top: -20px;" src="./images/headers/qantasmainlogo.svg"  /></div>
                            <div>
                        </td>
                    </tr>
                    <tr><td class="tbl-order-lbl">
                            <div class="div-service-order-data-container">
                                <div class="div-service-order-emplbl" style="font-weight: bold;">Customer Name:</div>
                                <div class="div-service-order-empinput"></div>
                            </div>
                        </td>
                    </tr>
                    <tr><td class="tbl-order-lbl">
                            <div class="div-service-order-data-container">
                                <div class="div-service-order-emplbl" style="font-weight: bold;">Aircraft Type:</div>
                                <div class="div-service-order-empinput"></div>
                            </div>
                        </td>
                    </tr>
                    <tr><td class="tbl-order-lbl">
                            <div class="div-service-order-data-container">
                                <div class="div-service-order-emplbl" style="font-weight: bold;">Flight Number:</div>
                                <div class="div-service-order-empinput"></div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="tbl-order-lbl">
                            <div class="div-service-order-data-container">
                                <div style="width: 190px; height: 100%; float: left;">
                                    <div class="div-service-order-flightlbl" style="width: 100px; float: left; vertical-align: bottom; padding-right: 10px; font-weight: bold;">Date:</div>
                                    <div style="float: left; width: 70px; text-align: left;">
                                        <input class="service-order-inputs" type="text" id="txtdate" value="" />
                                    </div>
                                </div>
                                <div style="width: 160px; height: 100%; float: left;">
                                    <div class="div-service-order-flightlbl" style="width: 100px;">Arrival Time:</div>
                                    <div style="float: left; width: 40px; text-align: left; vertical-align: bottom;">
                                        <input class="service-order-inputs" type="text" id="txtactualarrival"  value="" />
                                    </div>
                                </div>
                                <div style="width: 120px; height: 100%; float: left;">
                                    <div class="div-service-order-flightlbl" style="width: 60px;">Gate:</div>
                                    <div style="float: left; width: 40px; text-align: left; vertical-align: bottom;">
                                        <input class="service-order-inputs" type="text" id="txtgate" value="" /></div>
                                </div>
                                <div style="width: 160px; height: 100%; float: left;">
                                    <div class="div-service-order-flightlbl" style="width: 100px;">Departure Time:</div>
                                    <div style="float: left; width: 40px; height: 20px; text-align: left; vertical-align: bottom; border-bottom: 1px solid black;">
                                        <input class="service-order-inputs" type="text" id="txtactualdeparture" value="" /></div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php
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
                               <tr class="tr-<?=$thiscrecno?>" id="tr_<?=$thiscrecno?>">
                                   <td class="tbl-order-lbl" style="width: 100%; text-align: left; font-size: 1em; font-weight: bold; height: 20px;" colspan="4">
                                        <div style="float: left; margin-right: 5px;" class="div-category-list-item" id="div_category">
                                            <?=$curcat?>
                                        </div>
                                   </td>
                               </tr>
                               <tr class='tr-<?=$thiscrecno?>' id='tr_<?=$thiscrecno?>_item'>
                                   <td>
                                       <div id="div_service_order_data_container_<?=$thiscrecno?>" class="div-service-order-data-container"><?php
                            }
                            $tempcat = $curcat;?>
                            <div id="div_<?=$thiscrecno?>_<?=$thisirecno?>" class="div_service-order-data-service-container">
                                <div class="div-service-order-servicelbl"><?=$rsseciont['item']?>:</div>
                                <div class="div-service-order-service-input-container">
                                    <input class="service-order-inputs service-order-inputs-item" type="text" id="txtitem_<?=$thisirecno?>" value="" />
                                </div>
                            </div><?php
                        }?>
                                </div> 
                            </td>
                        </tr><?php
                    } ?>
                <!-- section items ends here ---------------------------------------------------------- -->
                <tr><td class="tbl-order-lbl" style="width: 100%; text-align: center; font-size: 1em; font-weight: bold; height: 40px;" colspan="4">Technicians:</td></tr>
                <tr>
                    <td class="tbl-order-lbl" colspan="4">
                        <div style="width: 650px; height: 180px; margin: 0px auto; ">
                            <div style="width: 220px; height: 145px; margin: 0px auto;">
                                <div class="div-signature-lbl">Engineer:</div>
                                <div class="div-signature-line"></div><br/><br/>
                                <div id="div_signature" style="height: 100px; width: 300px;"></div>
                            </div>

                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div><?php
}