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
            function updateSO(obj, recno){
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=UpdateSO&recno='+recno+'&field='+$(obj).prop('id')+'&value='+$(obj).val(), function(result){
                    //alert(result);
                    if(result == "Failed"){
                        //alert("Failed to update.  Please contact your administrator.");
                        return(false);
                    }
                });
            }
            function clearSignature(){
                $('#div_signature > img').remove();
                $("#div_signature").signature('clear');
            }
            function saveSignature(obj, recno){
                //alert($("#div_signature").signature('toDataURL', 'png'));
                thistodataurl = {cmd: 'UpdateSO', value: $("#div_signature").signature('toDataURL'), recno: recno, field: $(obj).prop('id')};
               // alert(thistodataurl);
                $.post('<?=$_SERVER['PHP_SELF']; ?>', thistodataurl, function(result){
                   //alert(result);
                    if(result == "Failed"){
                        //alert("Failed to update.  Please contact your administrator.");
                        return(false);
                    }
                    else
                    {
                        //alert(result);
                    }
                });
            }
            function updateSOitems(obj, sorecno, thiscrecno, thisirecno){
                //alert(sorecno+' - '+thiscrecno+' - '+thisirecno);
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=UpdateSOitems&sorecno='+sorecno+'&thiscrecno='+thiscrecno+'&thisirecno='+thisirecno+'&thisvalue='+$(obj).val(), function(result){
                    //alert(result);
                    if(result == "Failed"){
                        //alert("Failed to update.  Please contact your administrator.");
                        return(false);
                    }
                });
            }
            function addMoreselect(obj, thiscategory, sorecno, thiscrecno, thisirecno)
            {
                //alert('here agian');
                //Before we go forward we want to make sure user isn't adding an existing category
                if($(obj).text() == "+"){
                    $(obj).text("-");
                    $("#spanslt_tr_"+thiscrecno).show();
                }
                else{
                    $(obj).text("+");
                    $("#spanslt_tr_"+thiscrecno).hide();
                    return(false);
                }
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=AddMoreselect&thiscategory='+thiscategory+'&sorecno='+sorecno+'&thiscrecno='+thiscrecno+'&thisirecno='+thisirecno, function(result){
                    //alert(result);
                    if(result == "Failed"){
                        //alert("Failed enable select item list.  Please contact your administrator.");
                        return(false);
                    }
                    $("#spanslt_tr_"+thiscrecno).html(result);
                });
            }
            function addMoreitem(obj, sorecno, thiscrecno){
                //Before go we go farther, we must check to see if this item already exist in the form, if it is, don't add
                var thisitemrecno = $(obj).find(":selected").val();
                var thisitem = $(obj).find(":selected").text();
                tempid = "txtitem_"+thisitemrecno+"_"+sorecno;
                isexist = false;
                $('.service-order-inputs-item').each(function(){
                    //alert($(this).prop('id')+' == '+tempid);
                    if($(this).prop('id') == tempid){
                        alert("This item already exist in this form.  Can not add existing item.");
                        isexist = true;
                    }
                });
                if(thisitemrecno == "Select" || isexist == true)
                {
                    $(obj).val('Select');
                    return(false);
                }
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=AddMoreitem&thisitemrecno='+thisitemrecno+'&thisitem='+thisitem+'&sorecno='+sorecno+'&thiscrecno='+thiscrecno+'&thisirecno='+thisitemrecno, function(result){
                    //alert(result);
                    if(result == "Failed"){
                        //alert("Failed to add item.  Please contact your administrator.");
                        return(false);
                    }
                    $(obj).val('Select');
                    $("#div_service_order_data_container_"+thiscrecno).prepend(result);
                });
            }
            function getSltcategory(obj, recno){
                var thiscategoryrecno = $(obj).find(":selected").val();
                var thiscategory = $(obj).find(":selected").text();
                isexist = false;
                $('.span-category-name').each(function(){
                    //alert($(this).prop('id')+' == '+"spnAddmoreso_"+thiscategory);
                    if($(this).prop('id') == "spnAddmoreso_"+thiscategory){
                        isexist = true;
                        alert("This category already exist in this form.  Can not add this category.");
                        
                    }
                });
                if(thiscategory == "Select" || isexist ==  true){
                    return(false);
                }
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=GetSltcategory&thiscategory='+thiscategory+'&recno='+recno, function(result){
                    //alert(result);
                    if(result == "Failed"){
                        //alert("Failed to category.  Please contact your administrator.");
                        return(false);
                    }
                    $(obj).val('Select');
                    $("#tr_sltcategory_list").after(result);
                });
            }
            function addNewcategory(obj, thiscategory, thiscrecno, thisrecno){
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=AddNewcategory&thiscrecno='+thiscrecno+'&thisrecno='+thisrecno+'&thiscategory='+thiscategory, function(result){
                    //alert('here1 '+result);
                    if($(obj).text() == "+"){
                        $(obj).text("-");
                        $("#spanslt_tr_"+thiscrecno).show();
                    }
                    else{
                        $(obj).text("+");
                        $("#spanslt_tr_"+thiscrecno).hide();
                        return(false);
                    }
                    if(result == "Failed"){
                        //alert("Failed to category.  Please contact your administrator.");
                        return(false);
                    }
                    $("#spanslt_tr_"+thiscrecno).show();
                    $("#spanslt_tr_"+thiscrecno).html(result);
                });
            }
            function addMorenewitem(obj, thisrecno, thiscategory){
                //alert(thiscategory);
                var thiscategoryrecno = $(obj).find(":selected").val();
                var thiscategoryitem = $(obj).find(":selected").text();
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=AddMorenewitem&thiscategory='+thiscategory+'&thisrecno='+thisrecno+'&thisitemrecno='+thiscategoryrecno, function(result){
                    //alert(result);
                    if(result == "Failed"){
                        //alert("Failed to category.  Please contact your administrator.");
                        return(false);
                    }
                    $("#spanslt_tr_"+thiscategory).show();
                    //alert('what is this cat? '+thiscategory);
                    $("#tr_"+thiscategory).after(result);
                });
            }
            function spnXout(obj, sorecno, thiscrecno, thisirecno){
                //div_cat_item
                //alert(sorecno+' and '+thiscrecno+' '+thisirecno);
                $("#div_"+thiscrecno+"_"+thisirecno).remove();
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=spnXout&sorecno='+sorecno+'&thiscrecno='+thiscrecno+'&thisirecno='+thisirecno, function(result){
                    //alert(result);
                    if(result == "Failed"){
                        //alert("Failed to category.  Please contact your administrator.");
                        return(false);
                    }
                });
            }
            function spnXoutcategory(obj, sorecno, thiscrecno){
                //We will remove this TR and then go to service_orders_items and turn off all the items associate with this sorecno and this thiscrecno
                //alert('here: '+sorecno+' and '+thiscrecno);
                if(!confirm('You are about to delete all the items under this category.  Press OK to delete all.'))                
                {
                    return(false);
                }
                $(".tr-"+thiscrecno).remove();

                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=SpnXoutcategory&sorecno='+sorecno+'&thiscrecno='+thiscrecno, function(result){
                    //alert(result);
                    if(result == "Failed"){
                        //alert("Failed to category.  Please contact your administrator.");
                        return(false);
                    }
                });
            }
            function doPrint(recno){
                window.open("serviceorderprint.php?recno="+recno, "_blank");
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
function SpnXoutcategory()
{
    global $db;
    
    $thistable = "service_orders_items";
    $thisfield = array("All");
    $thisdata = array("isdeleted" => true);
    $thiswhere = array('fk_service_orders' => $_POST['sorecno'], 'fk_category' => $_POST['thiscrecno']);
    
    $resultsoi = $db->PDOQuery($thistable, $thisfield, $thiswhere);
    if($resultsoi)
    {    
        foreach($resultsoi as $rs)
        {
            $result = $db->PDOUpdate($thistable, $thisdata, $thiswhere, $rs['recno']);
            if(!$result)
            {
                echo "Failed";
            }
        }
    }
    else
    {
        echo "Failed";
    }
}
function spnXout()
{
    global $db;
    //file_put_contents("./dodebug/debug.txt", var_dump($_POST), FILE_APPEND);
    $thistable = "service_orders_items";
    $thisfield = array("All");
    $thisdata = array("isdeleted" => true);
    $thiswhere = array('fk_service_orders' => $_POST['sorecno'], 'fk_category' => $_POST['thiscrecno'], 'fk_item' => $_POST['thisirecno']);
    
    $resultsoi = $db->PDOQuery($thistable, $thisfield,$thiswhere);
    if($resultsoi)
    {     
        foreach($resultsoi as $rs)
        {
            $result = $db->PDOUpdate($thistable, $thisdata, $thiswhere, $rs['recno']);
            if(!$result)
            {
                echo "Failed";
            }
        }
    }
    else
    {
        echo "Failed";
    }
}
function AddMorenewitem()
{
    global $db;
    $sql = "SELECT c.recno crecno, c.name, i.recno as irecno, i.item ";
    $sql .= "FROM category c ";
    $sql .= "INNER JOIN items i ON c.name=i.category ";
    $sql .= "WHERE i.recno=".$_POST['thisitemrecno']." ";
    $sql .= "ORDER BY c.name, i.item";
    $result = $db->PDOMiniquery($sql);
    foreach($result as $rs)
    {?>
        <tr class="tr-<?=$rs['crecno']?>" id="tr_<?=$rs['crecno']?>_item">
            <td>
                <div id="div_service_order_data_container_<?=$rs['crecno']?>" class="div-service-order-data-container">
                    <div class="div_service-order-data-service-container">
                        <div class="div-service-order-servicelbl"><?=$rs['item']?>:</div>
                        <div class="div-service-order-service-input-container">
                            <input 
                                class="service-order-inputs service-order-inputs-item" type="text" id="txtitem_<?=$rs['item']?>_<?=$_POST['thisrecno']?>" 
                                onfocus="saveThisdata(this);" onchange="updateSOitems(this, <?=$_POST['thisrecno']?>, <?=$rs['crecno']?>, <?=$rs['irecno']?>);" 
                                value="" />
                        </div>
                    </div>
                 </div> 
             </td>
         </tr><?php    
    }
}
function AddNewcategory()
{
    global $db;
    //need the item recno, category recno and the so recno
    //file_put_contents("./dodebug/debug.txt", var_dump($_POST), FILE_APPEND);
    //First we want to see if we need to do an update or we need to do an insert, we will need to query the table to see if we get anything for this particular SO item,
    //if not, we insert, if, we update

    $thistable = "items";
    $thisfields = array('All');
    
    $thiswheres = array("category" => $_POST['thiscategory'], 'isactive' => true, 'isdeleted' => false);
    $result = $db->PDOQuery($thistable, $thisfields, $thiswheres);
    if($result)
    {?>
    <select id="sltaddmoreitems_<?=$_POST['thiscrecno']?>" onchange="addMorenewitem(this, <?=$_POST['thisrecno']?>, '<?=$_POST['thiscrecno']?>');">
           <option value="Select">Select to add</option><?php
           foreach($result as $rs)
           {?>
               <option value="<?=$rs['recno']?>"><?=$rs['item']?></option><?php
           }?>
       </select><?php
    }
    else
    {
        echo 'Failed';
    }
}
function GetSltcategory()
{
    global $db;
    $thistable = "category";
    $thisfields = array('All');
    $thiswheres = array('name' => $_POST['thiscategory']);
    $result = $db->PDOQuery($thistable, $thisfields, $thiswheres);
    if($result)
    {
        foreach($result as $rs)
        {?>
            <tr class="tr-<?=$rs['recno']?>" id="tr_<?=$rs['recno']?>"><td class="tbl-order-lbl" style="width: 100%; text-align: left; font-size: 1em; font-weight: bold; height: 20px; background-color: #FAFAFA;" colspan="4">
                <div style="float: left; margin-right: 5px;" class="div-category-list-item" id="div_category_<?=$_POST['thiscategory']?>_<?=$_POST['recno']?>">
                   <span id="spn_x_<?=$rs['recno']?>" onclick="spnXoutcategory(this, <?=$_POST['recno']?>, <?=$rs['recno']?>);" style="font-size: 1em; padding-right: 2px; color: darkred; font-weight: bold; cursor: pointer;">X</span><?=$_POST['thiscategory']?>
                </div>
                 <div id="spanslt_tr_<?=$rs['recno']?>" style="display: none; float: left;"></div>
                 <span class="span-category-name" id="spnAddmoreso_<?=$rs['recno']?>" onclick="addNewcategory(this, '<?=$_POST['thiscategory']?>', <?=$rs['recno']?>, <?=$_POST['recno']?>);" style="float: left; margin-left: 5px; font-size: 1.2em; cursor: pointer;">+</span>
                </td>
            </tr><?php 
        }
    }
    else
    {
        echo "Failed";
    }
    
}
function AddMoreitem()
{
    global $db;
    $thistable = "category";
    $thisfield = array("All");
    $thiswheres = array('recno' => $_POST['thiscrecno']);
    $result = $db->PDOQuery($thistable, $thisfield, $thiswheres);
    foreach($result as $rs)
    {?>
        <div id="div_<?=$_POST['thiscrecno']?>_<?=$_POST['thisirecno']?>" class="div_service-order-data-service-container">
            <div class="div-service-order-servicelbl"><span id="spn_x_<?=$rs['name']?>_<?=$_POST['thisitem']?>" onclick="spnXout(this, <?=$_POST['sorecno']?>, <?=$_POST['thiscrecno']?>, <?=$_POST['thisirecno']?>)" style="font-size: 1em; padding-right: 2px; color: darkred; font-weight: bold; cursor: pointer;">X</span><?=$_POST['thisitem']?>:</div>
            <div class="div-service-order-service-input-container">
                <input class="service-order-inputs" type="text" id="txt_<?=$_POST['thisirecno']?>_<?=$_POST['sorecno']?>" onfocus="saveThisdata(this);" onchange="updateSOitems(this, <?=$_POST['sorecno']?>, <?=$_POST['thiscrecno']?>, <?=$_POST['thisirecno']?>);" value="" />
            </div>
        </div><?php
    }
    
}
function AddMoreselect()
{
    global $db;
    //need the item recno, category recno and the so recno
    //file_put_contents("./dodebug/debug.txt", var_dump($_POST), FILE_APPEND);
    //First we want to see if we need to do an update or we need to do an insert, we will need to query the table to see if we get anything for this particular SO item,
    //if not, we insert, if, we update
    $thistable = "items";
    $thisfields = array('All');
    
    $thiswheres = array("category" => $_POST['thiscategory'], 'isactive' => true, 'isdeleted' => false);
    $result = $db->PDOQuery($thistable, $thisfields, $thiswheres);
    if($result)
    {?>
       <select id="sltaddmoreitems_<?=$_POST['thiscrecno']?>" onchange="addMoreitem(this, <?=$_POST['sorecno']?>, <?=$_POST['thiscrecno']?>);">
           <option value="Select">Select to add</option><?php
           foreach($result as $rs)
           {?>
               <option value="<?=$rs['recno']?>"><?=$rs['item']?></option><?php
           }?>
       </select><?php
    }
    else
    {
        echo 'Failed';
    }
    
}
function UpdateSOitems()
{
    global $db;
    //file_put_contents("./dodebug/debug.txt", var_dump($_POST), FILE_APPEND);
    //First we want to see if we need to do an update or we need to do an insert, we will need to query the table to see if we get anything for this particular SO item,
    //if not, we insert, if, we update
    $thistable = "service_orders_items";
    $thisfields = array('All');
    
    $thiswheres = array("fk_service_orders" => $_POST['sorecno'], 'fk_category' => $_POST['thiscrecno'], 'fk_item' => $_POST['thisirecno'], 'isdeleted' => false);
    $result = $db->PDOQuery($thistable, $thisfields, $thiswheres);
    if($result)
    {
        $thisdata = array('value' => $_POST['thisvalue']);
        $resultupdate = $db->PDOUpdate($thistable, $thisdata, $thiswheres);
        if(!$resultupdate)
        {
            echo "Failed";
        }
    }
    else
    {
        $thisdata = array("fk_service_orders" => $_POST['sorecno'], 'fk_category' => $_POST['thiscrecno'], 'fk_item' => $_POST['thisirecno'], 'value' => $_POST['thisvalue']);
        $resultinsert = $db->PDOInsert($thistable, $thisdata);
        if(!$resultinsert)
        {
            echo "Failed";
        }
    }
}
function UpdateSO()
{
    global $db;
    
    $realfield = substr($_POST['field'], 3);  //Get rid of first 3 chars to get the fieldname for table
    $thistable = "service_orders";
    if($realfield == "actualarrival" || $realfield == "actualdeparture" || $realfield == "gate")
    {
        $thistable = "flow";
    }
    if($realfield == "signature")
    {
        //If we are doing signatures field, we want to check to see if we already have existing row for this user.  If we do we will update the signature, if we don't
        //we will instead do an insert.  First we must query on this user.
        $thistemptable = "signatures";
        $thistempfields = array('recno', 'signature');
        //We get here when user entered just the first name, middle or last name.
        $thistempwhere = array("table_name" => "service_orders", "so_foreign_key" => $_POST['recno'], "em_foreign_key" => $_SESSION['employee_master_recno'], "isdeleted" => false);
        $tempresult = $db->PDOQuery($thistemptable, $thistempfields, $thistempwhere);
        
        if (!file_exists("./images/signatures/".$_SESSION['employee_master_recno'])) {
            mkdir("./images/signatures/".$_SESSION['employee_master_recno'], 0777, true);
        }
        if($tempresult)
        {
            //We will do an update
            //file_put_contents("./dodebug/debug.txt", "doing update to signature: value->".$_POST['value'], FILE_APPEND);
            foreach($tempresult as $trs)
            {
                
                $img = $_POST['value'];
                $data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $img));
                file_put_contents("./images/signatures/".$_SESSION['employee_master_recno']."/signatures_".$trs['recno']."_signature.png", $data);  
                $imagepath = "./images/signatures/".$_SESSION['employee_master_recno']."/signatures_".$trs['recno']."_signature.png";
                //The format for the naming the image is so every signature is different and for each employee...We will create a foolder that has their recno
                //in employee_master to reflect them.
                //We will include the name of the table and the recno of the table, ex: table = service_orders, and recno = 6, the name would be something like,
                //service_order_6_image.png
                //
                //file_put_contents("./dodebug/debug.txt", "Service Order -update value: ".$_POST['value'], FILE_APPEND);
                $thistempdata = array($realfield => $imagepath); //First we remove the first 3 char at start then we remove the last character
                $thistempwhere = array("recno" => $trs['recno']);
                //PDOUpdate($thistable=null, $thisdata = null, $thiswhere = null)
                $result = $db->PDOUpdate($thistemptable, $thistempdata, $thistempwhere, $_POST['recno']);
            }
        }
        else
        {
            //file_put_contents("./dodebug/debug.txt", "Service Order -insert value: ".$_POST['value'], FILE_APPEND);
            //We will do an INSERT
            file_put_contents("./dodebug/debug.txt", "doing insert to signature: ", FILE_APPEND);
            $thistempdata = array("so_foreign_key" => $_POST['recno'], "em_foreign_key" => $_SESSION['employee_master_recno'], "table_name" => "service_orders"); 
            $lastinsertid = $db->PDOInsert($thistemptable, $thistempdata);
            
            $img = $_POST['value'];
            $data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $img));
            file_put_contents("./images/signatures/".$_SESSION['employee_master_recno']."/signatures_".$lastinsertid."_signature.png", $data);  
            $imagepath = "./images/signatures/".$_SESSION['employee_master_recno']."/signatures_".$lastinsertid."_signature.png";
            //The format for the naming the image is so every signature is different and for each employee...We will create a foolder that has their recno
            //in employee_master to reflect them.
            //We will include the name of the table and the recno of the table, ex: table = service_orders, and recno = 6, the name would be something like,
            //service_order_6_image.png
           
            $thistempdata = array($realfield => $imagepath); //First we remove the first 3 char at start then we remove the last character
            $thistempwhere = array("recno" => $lastinsertid);
            //PDOUpdate($thistable=null, $thisdata = null, $thiswhere = null)
            $db->PDOUpdate($thistemptable, $thistempdata, $thistempwhere, $lastinsertid);
            //echo "Success";
        } 
        //Now that we have updated the signature, we will updated the completed by field in service_order to complete this SO
        $thistemptable = 'service_orders';
        $thistempdata = array('completedby' => $_SESSION['employee_master_recno']); //First we remove the first 3 char at start then we remove the last character
        $thistempwhere = array("recno" => $_POST['recno']);
        //PDOUpdate($thistable=null, $thisdata = null, $thiswhere = null)
        $db->PDOUpdate($thistemptable, $thistempdata, $thistempwhere, $_POST['recno']);
        
        //First we get the Engineer list so we have something to compare this user with
        $sqlengin = "SELECT flow.recno as flowrecno, flow.engineers FROM flow INNER JOIN service_orders so ON flow.recno=so.foreignkey_flow_recno WHERE so.recno = ".$_POST['recno'];
        $resultengin = $db->PDOMiniquery($sqlengin);
        if($db->PDORowcount($resultengin) > 0)
        {
            foreach($resultengin as $rsengin)
            {
                $thisengineers = $rsengin['engineers']; //we now got the list of recno in format of 1,2,3..,n
                $tempengineers = explode(',', $thisengineers);

                
                if(!in_array($_SESSION['employee_master_recno'], array_map('trim', $tempengineers)))
                {
                    $thistemptable = 'flow';
                    //file_put_contents("./dodebug/debug.txt", $thisengineers.",".$_SESSION['employee_master_recno'], FILE_APPEND);
                    if(!is_null($rsengin['engineers']))
                    {
                        $thistempdata = array('engineers' => $thisengineers.",".$_SESSION['employee_master_recno']);
                    }
                    else
                    {
                        $thistempdata = array('engineers' => $_SESSION['employee_master_recno']);
                    }
                    $thistempwhere = array("recno" => $rsengin['flowrecno']);
                    //PDOUpdate($thistable=null, $thisdata = null, $thiswhere = null)
                    $db->PDOUpdate($thistemptable, $thistempdata, $thistempwhere, $_POST['recno']);
                }
                
            }
        }
    }
    else
    {
        $thisdata = array($realfield => $_POST['value']); 
        $thiswhere = array("recno" => $_POST['recno']);
        //PDOUpdate($thistable=null, $thisdata = null, $thiswhere = null)
        $result = $db->PDOUpdate($thistable, $thisdata, $thiswhere, $_POST['recno']);
        //file_put_contents("./dodebug/debug.txt", var_dump($_POST), FILE_APPEND);
    }    
}
function Main()
{
    global $db, $load_headers, $pt;?>
    <div class="main-div"><?php
        $load_headers::Load_Header_Logo_Marquee();?>
        <br><br> <?php
        $load_headers::Load_Header_Logo_Main();
        
        $sql = "SELECT so.*, flow.recno as flow_recno, flow.customer, flow.actype, flow.flightnumber, flow.gate, flow.date, flow.engineers, flow.mechanics, flow.technicians, ";
        $sql .= "flow.schedulearrival, flow.estimatearrival, flow.actualarrival, flow.estimatedeparture, flow.scheduledeparture, flow.fromstation, flow.tostation, ";
        $sql .= "flow.actualdeparture, s.signature FROM service_orders so INNER JOIN flow ";
        $sql .= "ON flow.recno = so.foreignkey_flow_recno LEFT JOIN signatures s ON so.recno = s.so_foreign_key WHERE so.recno='".$_POST['recno']."' AND so.isdeleted = false";
        file_put_contents("./dodebug/debug.txt", "so_main: ".$sql."<br>", FILE_APPEND);
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
                        <tr><td class="tbl-order-lbl">
                                <div class="div-service-order-data-container">
                                    <div class="div-service-order-emplbl">From:</div>
                                    <div class="div-service-order-empinput"><?= $rs['fromstation'] ?></div>
                                </div>
                            </td>
                        </tr>
                        <tr><td class="tbl-order-lbl">
                                <div class="div-service-order-data-container">
                                    <div class="div-service-order-emplbl">To:</div>
                                    <div class="div-service-order-empinput"><?= $rs['tostation'] ?></div>
                                </div>
                            </td>
                        </tr>
                        <tr><?php
                            $thistempdepttime = "";
                            $thistemparrtime = "";
                            if(!is_null($rs['actualarrival']))
                            {
                                $thistemparrtime = $rs['actualarrival'];
                            }
                            else if(!is_null($rs['estimatearrival']))
                            {
                                $thistemparrtime = $rs['estimatearrival'];
                            }
                            else
                            {
                                $thistemparrtime = $rs['schedulearrival'];
                            }
                            $explodead = explode(" ", $thistemparrtime);
                            $thisarrivaltime = date('H:i', strtotime($explodead[1]));
                            
                            if(!is_null($rs['actualdeparture']))
                            {
                                $thistempdepttime = $rs['actualdeparture'];
                            }
                            else if(!is_null($rs['estimatedeparture']))
                            {
                                $thistempdepttime = $rs['estimatedeparture'];
                            }
                            else
                            {
                                $thistempdepttime = $rs['scheduledeparture'];
                            }
                            $explodead = explode(" ", $thistempdepttime);
                            $thisdepttime = date('H:i', strtotime($explodead[1]));
                            
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
                                            <input class="service-order-inputs" type="text" id="txtactualarrival" onfocus="saveThisdata(this);" onchange="checkTime(this);updateSO(this, <?=$rs['flow_recno']?>);" value="<?= $thisarrivaltime; ?>" <?=$isreadonly?>/>
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
                        file_put_contents("./dodebug/debug.txt", $sqlso, FILE_APPEND);
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
                                file_put_contents("./dodebug/debug.txt", 'so_signature: '.$sqls, FILE_APPEND);?>

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
                                //file_put_contents("./dodebug/debug.txt", "?", FILE_APPEND);
                                if(!is_null($rs['signature']))
                                {
                                    $thissignature = "<img src='".$rs['signature']."' />";
                                }?>
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