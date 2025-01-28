<?php
require_once("./common/page.php");
require_once("./common/pdocon.php");
require_once("./common/sendmail.php");
require_once("./common/prompt.php");
$pt = new PROMPT();
$load_headers = new Page_Loader();
$db = new PDOCON();
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
            $(document).ready(function(){
               showServiceordersdefault();
            });
            function manageItem(obj){
                $(".div-menu-service-orders").each(function(){
                    $(this).css('background-color', '#1079B1');
                    $(this).css('color', 'white');
                })
                $(obj).css("background-color", "white");
                $(obj).css('color', 'black');
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=ManageItem', function(result){
                    $("#main_div_body_order_right_container").html(result);
                });
            }
            function manageItemdefault(){
                $(".div-menu-service-orders").each(function(){
                    $(this).css('background-color', '#1079B1');
                    $(this).css('color', 'white');
                })
                $("#div_manageitem").css("background-color", "white");
                $("#div_manageitem").css('color', 'black');
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=ManageItem', function(result){
                    $("#main_div_body_order_right_container").html(result);
                });
            }
            function addItem(obj){
                $(".div-menu-service-orders").each(function(){
                    $(this).css('background-color', '#1079B1');
                    $(this).css('color', 'white');
                })
                $(obj).css("background-color", "white");
                $(obj).css('color', 'black');
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=AddItem', function(result){
                    $("#main_div_body_order_right_container").html(result);
                });
            }
            function addItemdefault(){
                $(".div-menu-service-orders").each(function(){
                    $(this).css('background-color', '#1079B1');
                    $(this).css('color', 'white');
                })
                $("#div_additem").css("background-color", "white");
                $("#div_additem").css('color', 'black');
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=AddItem', function(result){
                    $("#main_div_body_order_right_container").html(result);
                });
            }
            function addItemcategory(obj){
                $(".div-menu-service-orders").each(function(){
                    $(this).css('background-color', '#1079B1');
                    $(this).css('color', 'white');
                })
                $(obj).css("background-color", "white");
                $(obj).css('color', 'black');
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=AddItemcategory', function(result){
                    $("#main_div_body_order_right_container").html(result);
                });
            }
            function addItemcategorydefault(){
                $(".div-menu-service-orders").each(function(){
                    $(this).css('background-color', '#1079B1');
                    $(this).css('color', 'white');
                })
                $("#div_additemcategory").css("background-color", "white");
                $("#div_additemcategory").css('color', 'black');
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=AddItemcategory', function(result){
                    $("#main_div_body_order_right_container").html(result);
                });
            }
            function showServiceordersdefault(){
                $(".div-menu-service-orders").each(function(){
                    $(this).css('background-color', '#1079B1');
                    $(this).css('color', 'white');
                })
                $("#div_show").css("background-color", "white");
                $("#div_show").css('color', 'black');
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=ShowServiceorders', function(result){
                    $("#main_div_body_order_right_container").html(result);
                });
            }
            function showServiceorders(obj){
                $(".div-menu-service-orders").each(function(){
                    $(this).css('background-color', '#1079B1');
                    $(this).css('color', 'white');
                })
                $(obj).css("background-color", "white");
                $(obj).css('color', 'black');
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=ShowServiceorders', function(result){
                    $("#main_div_body_order_right_container").html(result);
                });
            }
            function searchInterface(obj){
                $(".div-menu-service-orders").each(function(){
                    $(this).css('background-color', '#1079B1');
                    $(this).css('color', 'white');
                })
                $(obj).css("background-color", "white");
                $(obj).css('color', 'black');
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=SearchInterface', function(result){
                    $("#main_div_body_order_right_container").html(result);
                });
            }
            function submitCustomerform(){
                //We will check to see if the name exists in our database.
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=SubmitCustomerform&'+$("#frmaddcustomer").serialize(), function(result){
                        if(result == "Success")
                        {
                            alert("Customer has been successfully added.");
                            clearAddcustomerform();
                        }
                        else{
                            alert(result);
                            return(false);
                        }
                });
            }
            function validateCustomer(obj){
                switch($(obj).prop('id'))
                {
                    case "txtcustomer":
                        if($(obj).val().length == 0 && $(obj).val() == "")
                        {
                            return(false);
                        }
                        break;
                    case "txtairlinecode":
                        if($(obj).val().length != 3)
                        {
                            alert("The airline code has to be 3 characters long.  Please try again.");
                            $(obj).select();
                            return (false);
                        }
                        break;
                    case "txticaocode":
                        if($(obj).val().length != 3)
                        {
                            alert("The ICAO code has to be 3 characters long.  Please try again.");
                            $(obj).select();
                            return (false);
                        }
                        break;
                    case "txtiatacode":
                        if($(obj).val().length != 2)
                        {
                            alert("The ICAO code has to be 2 characters long.  Please try again.");
                            $(obj).select();
                            return (false);
                        }
                        break;
                }
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=ValidateCustomer&field='+$(obj).prop('id')+'&value='+$(obj).val(), function(result){
                    if(result != "Success")
                    {
                        alert(result);
                        $(obj).val('');
                        $(obj).focus();
                        return(false);
                    }
                    if($("#txtcustomer").val != "" && $("#txtairlinecode").val != "" && $("#txticaocode").val != "" && $("#txtiatacode").val != "")
                    {
                        $("#btnfrmaddcustomer").prop('disabled', false);
                    }
                });
            }
            //MGMT
            function showOrders(recno){
                window.location.href = "serviceorder.php?recno="+recno;
            }
            function deleteServiceorder(obj, recno, lineno){
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=DeleteServiceorder&recno='+recno, function(result){
                    //alert(result);
                    if(result == "Failed"){
                        alert("Error:  Contact administrator");
                        return(false);
                    }
                    else{
                        //We will remove the TR and renumber.
                        $("#tr"+lineno).remove();
                        i=1;
                        $(".tdnumbered").each(function(){
                            $(this).text(i);
                            i++;
                        });
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
                });
                
            }
            function submitCategory(){
                if($("#txtcategory").val() == "")
                {
                    alert("Please type a category.");
                    $("#txtcategory").focus();
                    return(false);
                }
                isfavorite = 'false';
                if($("#chkfavorite").is(":checked"))
                {
                    isfavorite = 'true'
                }
                //alert(isfavorite);
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=SubmitCategory&txtcategory='+$("#txtcategory").val()+'&chkfavorite='+isfavorite, function(result){
                    if(result == "Success"){
                        alert("Successfully added.");
                        addItemdefault();
                    }
                    else if(result == "Exists"){
                        alert("Can't add this category, already exists.  Please pick a different category name.");
                        $("#txtcategory").select();
                        return(false);
                    }
                    else{
                        alert("Failed to add category.  Contact administrator.");
                        $("#txtcategory").select();
                        return(false);
                    }
                });
            }
            function submitItems(){
                if($("#sltcategory").find(":selected").val() == "Select")
                {
                    alert("Please select a category for this item.  If no category, please go to 'Add Item Category' and add a category first then try again.");
                    $("#sltcategory").focus();
                    return(false);
                }
                if($("#txtitem").val() == "")
                {
                    alert("Please type an item for this category.");
                    $("#txtitem").focus();
                    return(false);
                }
                ischecked='false';
                if($("#chkfavoriteitem").is(":checked"))
                {
                    ischecked='true';
                }
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=SubmitItems&sltcategory='+$("#sltcategory").find(":selected").val()+'&txtitem='+$("#txtitem").val()+'&chkfavorite='+ischecked, function(result){
                    //alert(result);
                    if(result == "Success"){
                        alert("Successfully added item to category.");
                        addItemdefault();
                    }
                    else if(result == "Exists"){
                        alert("Can't add this item for this category, it already exists.  Please pick a different category name and or item.");
                        $("#txtitem").select();
                        return(false);
                    }
                    else{
                        alert("Failed to add category.  Contact administrator.");
                        $("#txtcategory").select();
                        return(false);
                    }
                });
            }
            function getCategoryitems(obj, from="display"){
                
                $('body').data('currentselectedcategory', $(obj).text());
                
                $("#div_itemavailablecontainer").empty();
                $(".category-list").each(function(){
                    $(this).css('background-color', '#E8EAEA');
                    $(this).css('color', 'black');
                })
                $(obj).css("background-color", "white");
                $(obj).css('color', 'black');
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=GetCategoryitems&category='+$(obj).text().trim()+'&from='+from, function(result){
                    //alert(result);
                    if(result != 'Failed'){
                        $("#imgeditcurrentitem").show();
                        $("#div_itemcurrentcontainer").html(result);
                    }
                });
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=GetCategoryitemsavailable&category='+$(obj).text().trim()+'&from='+from, function(result){
                    //alert(result);
                    if(result != 'Failed'){
                        $("#imgeditavailableitem").show();
                        $("#div_itemavailablecontainer").html(result);
                    }                    
                });
            }
            function selectItem(obj){
                //alert($(obj).css('background-color'));
                if($(obj).css('background-color') == 'rgb(232, 234, 234)')
                {
                    $(obj).css('background-color', 'rgb(68, 68, 68)');
                    $(obj).css('color', 'rgb(255, 255, 255)');
                }
                else
                {
                    $(obj).css('background-color', 'rgb(232, 234, 234)');
                    $(obj).css('color', 'rgb(0, 0, 0)');
                }
            }
            function moveItems(obj){
                recnolist = "";
                if($(obj).prop('id') == "btnmovetoright" || $(obj).prop('id') == "btnmdelte"){
                    //Now what we want to do now is to look through the highlighted divs from the current list and move to the available
                    //list, we will then turn the isactive flag to false
                    $(".item-current").each(function(){
                        if($(this).css('background-color') == 'rgb(68, 68, 68)'){
                            splitid = $(this).prop('id').split("_");
                            if(recnolist == ""){
                                recnolist = splitid[1];
                            }
                            else{
                                recnolist += ","+splitid[1];
                            }
                            //need to change the class name
                            if($(obj).prop('id') == "btnmdelte"){
                                $(this).remove();
                            }
                            else{                                
                                $(this).css('background-color', 'rgb(232, 234, 234)');
                                $(this).css('color', 'rgb(0, 0, 0)');
                                $(this).prop('class', 'item-available');
                                $(this).prependTo($("#div_itemavailablecontainer"));
                            }
                        }
                    });
                }
                if($(obj).prop('id') == "btnmovetoleft" || $(obj).prop('id') == "btnmdelte"){
                    $(".item-available").each(function(){
                        if($(this).css('background-color') == 'rgb(68, 68, 68)'){
                            splitid = $(this).prop('id').split("_");
                            if(recnolist == ""){
                                recnolist = splitid[1];
                            }
                            else{
                                recnolist += ","+splitid[1];
                            }
                            if($(obj).prop('id') == "btnmdelte"){
                                $(this).remove();
                            }
                            else{ 
                                $(this).css('background-color', 'rgb(232, 234, 234)');
                                $(this).css('color', 'rgb(0, 0, 0)');
                                $(this).prop('class', 'item-current');
                                $(this).prependTo($("#div_itemcurrentcontainer"));
                            }
                        }
                    });
                }
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=moveItems&thisdir='+$(obj).prop('id')+'&recnolist='+recnolist, function(result){
                
                });
            }
            function enableEditcategorylist(obj){
                //alert('here');
                from = 'edit';
                if($(obj).prop('src').indexOf('penediting') != -1)
                {
                    from = "display";
                    $(obj).prop('src', './images/others/penedit.png');
                }
                else
                {
                    $(obj).prop('src', './images/others/penediting.png');
                }
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=GetCategorylistedit&category='+$(obj).text().trim()+'&from='+from, function(result){
                    //alert(result);
                    if(result != 'Failed'){
                        $("#div_categorycontainer").html(result);
                    }
                    else{
                        alert("Failed to load items for this category.");
                        return(false);
                    }
                });
            }
            function enableEdititemlist(obj){
                from = 'edit';
                if($(obj).prop('src').indexOf('penediting') != -1)
                {
                    from = "display";
                    $(obj).prop('src', './images/others/penedit.png');
                }
                else
                {
                    $(obj).prop('src', './images/others/penediting.png');
                }
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=GetCategoryitems&category='+$('body').data('currentselectedcategory')+'&from='+from, function(result){
                    //alert(result);
                    if(result != 'Failed'){
                        $("#div_itemcurrentcontainer").html(result);
                    }
                    else{
                        alert("Failed to load items for this category.");
                        return(false);
                    }
                });
            }
            function enableEdititemavaillist(obj){
                //alert('here');
                from = 'edit';
                if($(obj).prop('src').indexOf('penediting') != -1)
                {
                    from = "display";
                    $(obj).prop('src', './images/others/penedit.png');
                }
                else
                {
                    $(obj).prop('src', './images/others/penediting.png');
                }
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=GetCategoryitemsavailable&category='+$('body').data('currentselectedcategory')+'&from='+from, function(result){
                    //alert(result);
                    if(result != 'Failed'){
                        $("#div_itemavailablecontainer").html(result);
                    }
                    else{
                        alert("Failed to load items for this category.");
                        return(false);
                    }
                });
            }
            function updatCategorylist(obj, recno){
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=UpdatCategorylist&recno='+recno+'&thisval='+$(obj).val(), function(result){
                    //alert(result);
                    if(result == 'Failed'){
                        alert("Failed to load items for this category.");
                        return(false);
                    }
                });
            }
            function updateItemlist(obj, recno){
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=UpdateItemlist&recno='+recno+'&thisval='+$(obj).val(), function(result){
                    //alert(result);
                    if(result == 'Failed'){
                        alert("Failed to load items for this category.");
                        return(false);
                    }
                });
            }
            function updateFavorite(obj, thistable, recno){
                thisfavorimg = $(obj).prop('src');
                from = "";
                if(thisfavorimg.indexOf('favoritewhitestar') != -1){
                    //If we clicked on the white star, we get here, we are attempting to make this item a favor on the list so we will turn this to blue star and update the table to reflect this change.
                    $(obj).prop('src', './images/others/favoritebluestar.png');
                    from = 'true';
                }
                else{
                    $(obj).prop('src', './images/others/favoritewhitestar.png');
                    from = 'false';
                }
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=UpdateFavorite&recno='+recno+'&from='+from+'&thistable='+thistable, function(result){
                    //alert(result);
                    if(result == 'Failed'){
                        alert("Failed to update favorite list.");
                        return(false);
                    }
                });
            }
            function printBlankso(){
                window.open('./serviceorderprintblank.php', '_blank');
            }
            function printSelected(){
                var recarray = [];
                $(".print-check").each(function(){
                    if($(this).is(":checked"))
                    {
                        splitid = $(this).prop('id').split('_');
                        recarray.push(splitid[1]);
                    }
                });
                if(recarray.length == 0)
                {
                    alert("No Service Order has been checked.  At least ones must be checked.")
                    return(false);
                }
                thisarraypara = JSON.stringify(recarray);
                window.open('./serviceorderprintmultiple.php?thisarray='+thisarraypara, '_blank');
            }
            function doCheckall(obj){
                $(".print-check").each(function(){
                    if($(obj).is(":checked"))
                    {
                        $(this).prop('checked', true);
                    }
                    else
                    {
                        $(this).prop('checked', false);
                    }
                });
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
function UpdateFavorite()
{
    global $db;
    //file_put_contents("./dodebug/debug.txt", var_dump($_POST), FILE_APPEND);
    $thistable = $_POST['thistable'];
    $isfavor = false;
    if($_POST['from'] == 'true')
    {
        $isfavor = true;
    }
    $thisdata = array('favorite' => $isfavor);
    $thiswhere = array('recno' => $_POST['recno']);
    $result = $db->PDOUpdate($thistable, $thisdata, $thiswhere, $_POST['recno']);
    if($result)
    {
        echo 'Success';
    }
    else 
    {
        echo 'Failed';
    }
}
function UpdateItemlist()
{
    global $db;
    $thistable = 'items';
    $thisdata = array('item' => $_POST['thisval']);
    $thiswhere = array('recno' => $_POST['recno']);
    $result = $db->PDOUpdate($thistable, $thisdata, $thiswhere, $_POST['recno']);
    if($result)
    {
        echo 'Success';
    }
    else 
    {
        echo 'Failed';
    }
}
function UpdatCategorylist()
{
    global $db;
    $thistable = 'category';
    $thisdata = array('name' => $_POST['thisval']);
    $thiswhere = array('recno' => $_POST['recno']);
    $result = $db->PDOUpdate($thistable, $thisdata, $thiswhere, $_POST['recno']);
    if($result)
    {
        echo 'Success';
    }
    else 
    {
        echo 'Failed';
    }
}
function GetCategorylistedit()
{
    global $db;
    $thistable = "category";
    $thisfields = array("All");
    $result = $db->PDOQuery($thistable, $thisfields);
    
    $i = 1;?>
    <div class='item-available' style="text-indent: 10px; height: 40px; width: 100%; float: left; text-align: left; cursor: pointer; color: black; line-height: 40px; background-color: #E8EAEA; font-size: .9em;"><?php
        foreach($result as $rs)
        {
            $thisfavoriteimage = '<img id="imgfavor'.$rs['recno'].'" title="Click to add to favor list."  src="./images/others/favoritewhitestar.png" style="cursor: pointer; height: 20px; width: 20px; padding-right: 10px; float: left; line-height: 40px;" onclick="updateFavorite(this, \'category\', '.$rs['recno'].');"/>';
            if($rs['favorite'] == true)
            {
                $thisfavoriteimage = '<img id="imgfavor'.$rs['recno'].'" title="Click to remove from favor list."  src="./images/others/favoritebluestar.png" style="cursor: pointer; height: 20px; width: 20px; padding-right: 10px; float: left;" onclick="updateFavorite(this, \'category\', '.$rs['recno'].');"/>';
            }
            
            if($_POST['from'] == "display")
            {?>
                <div>
                    <div style="width: 10%;"><?=$thisfavoriteimage;?></div>
                    <div class="category-list" id="div_<?=$i?>" style="height: 40px; width: 85%; float: left; text-align: left; cursor: pointer; color: black;" onclick="getCategoryitems(this);">
                        <?=$rs['name']?>
                    </div>
                </div><?php
            }
            else
            {?>
               <input type="text" name="txtcategorylist_<?=$rs['recno']?>" name="txtcategorylist_<?=$rs['recno']?>" onchange="updatCategorylist(this, <?=$rs['recno']?>);" style="height: 98%; width: 90%; float: left;" value="<?=$rs['name']?>"/><?php 
            }
        }?>
    </div><?php
}
function moveItems()
{
    global $db;
    
    $sql = "UPDATE items SET ";
    if($_POST['thisdir'] == "btnmovetoright")
    {
       $sql .= "isactive = false ";
    }
    else if($_POST['thisdir'] == "btnmovetoleft")
    {
        $sql .= "isactive = true ";
    }
    else
    {
        $sql .= "isdeleted = true ";
    }
    $sql .= "WHERE recno IN (".$_POST['recnolist'].")";
    //file_put_contents("./dodebug/debug.txt", $sql, FILE_APPEND);
    $result = $db->PDOMiniquery($sql);
    if($result)
    {
        echo "Success";
    }
    else
    {
        echo "Failed";
    }
}
function GetCategoryitemsavailable()
{
    global $db;
    //file_put_contents("./dodebug/debug.txt", var_dump($_POST), FILE_APPEND);
    $thistable = "items";
    $thisfields = array("All");
    $thiswheres = array('category' => trim($_POST['category']), 'isactive' => false, 'isdeleted' => false);
    $result = $db->PDOQuery($thistable, $thisfields, $thiswheres);
    if($result)
    {
        $i = 0;
         foreach($result as $rs)
        {
            $i++;
            if($_POST['from'] == "display")
            {
                $thisfavoriteimage = '<img id="imgfavor'.$rs['recno'].'" title="Click to add to favor list."  src="./images/others/favoritewhitestar.png" style="cursor: pointer; height: 20px; width: 20px; padding-right: 10px; float: left;" onclick="updateFavorite(this, \'items\', '.$rs['recno'].');"/>';
                if($rs['favorite'] == true)
                {
                    $thisfavoriteimage = '<img id="imgfavor'.$rs['recno'].'" title="Click to remove from favor list."  src="./images/others/favoritebluestar.png" style="cursor: pointer; height: 20px; width: 20px; padding-right: 10px; float: left;" onclick="updateFavorite(this, \'items\', '.$rs['recno'].');"/>';
                }?>
                <div class='item-available' id="div_<?=$rs['recno']?>" style="text-indent: 10px; height: 40px; width: 85%; float: left; text-align: left; cursor: pointer; color: black; background-color: #E8EAEA; font-size: .8em;" onclick="selectItem(this);">
                    <div style="width: 10%;"><?=$thisfavoriteimage;?></div>
                    <div>
                        <?=$rs['item']?>
                    </div>
                </div><?php
            }
            else
            {?>
                <div class='item-available' style="text-indent: 10px; height: 40px; width: 100%; float: left; text-align: left; cursor: pointer; color: black; line-height: 40px; background-color: #E8EAEA; font-size: .8em;">
                    <input type="text" name="txtitemlist_<?=$rs['recno']?>" name="txtitemlist_<?=$rs['recno']?>" onchange="updateItemlist(this, <?=$rs['recno']?>);" style="height: 98%; width: 90%;" value="<?=$rs['item']?>"/>   
                </div><?php                
            }
        }
    }
    else
    {
        echo "Failed";
    }
}
function GetCategoryitems()
{
    global $db;
    //file_put_contents("./dodebug/debug.txt", var_dump($_POST), FILE_APPEND);
    //from will be 'display' or 'edit'
    $thistable = "items";
    $thisfields = array("All");
    $thiswheres = array('category' => trim($_POST['category']), 'isactive' => true, 'isdeleted' => false);
    $result = $db->PDOQuery($thistable, $thisfields, $thiswheres);
    if($result)
    {
        //file_put_contents("./dodebug/debug.txt", "in result", FILE_APPEND);
        $i=0;
        foreach($result as $rs)
        {
            $i++;
            if($_POST['from'] == "display")
            {
                $thisfavoriteimage = '<img id="imgfavor'.$rs['recno'].'" title="Click to add to favor list."  src="./images/others/favoritewhitestar.png" style="cursor: pointer; height: 20px; width: 20px; padding-right: 10px; float: left;" onclick="updateFavorite(this, \'items\', '.$rs['recno'].');"/>';
                if($rs['favorite'] == true)
                {
                    $thisfavoriteimage = '<img id="imgfavor'.$rs['recno'].'" title="Click to remove from favor list."  src="./images/others/favoritebluestar.png" style="cursor: pointer; height: 20px; width: 20px; padding-right: 10px; float: left;" onclick="updateFavorite(this, \'items\', '.$rs['recno'].');"/>';
                }?>
                <div class='item-current' id="div_<?=$rs['recno']?>" style="text-indent: 10px; height: 40px; width: 85%; float: left; text-align: left; cursor: pointer; color: black; background-color: #E8EAEA; font-size: .8em;" onclick="selectItem(this);">
                    <div style="width: 10%;"><?=$thisfavoriteimage;?></div>
                    <div>
                        <?=$rs['item']?>   
                    </div>
                </div><?php
            }
            else
            {?>
                <div class='item-current' style="text-indent: 10px; height: 40px; width: 100%; float: left; text-align: left; cursor: pointer; color: black; line-height: 40px; background-color: #E8EAEA; font-size: .9em;">
                    <input type="text" name="txtitemlist_<?=$rs['recno']?>" name="txtitemlist_<?=$rs['recno']?>" onchange="updateItemlist(this, <?=$rs['recno']?>);" style="height: 98%; width: 90%;" value="<?=$rs['item']?>"/>   
                </div><?php                
            }
        }
    }
    else
    {
        //file_put_contents("./dodebug/debug.txt", "no result", FILE_APPEND);
        echo "Failed";
    }
}
function ManageItem()
{
    global $db;
    $thistable = "category";
    $thisfields = array("All");
    $result = $db->PDOQuery($thistable, $thisfields);?>
    <div style="float: left; width: 800px; color: white; margin: left;">
        <div style="width: 200px; height: 760px; float: left; background-color: #E8EAEA; padding-top: 10px; overflow: auto;">
            <div style="height: 40px; width: 100%; float: left; text-align: center; cursor: pointer; color: black; font-size: 1.2em; font-weight: bold; padding-bottom: 10px;">
                <img title='Click to enable editing items.' onclick="enableEditcategorylist(this);" id='imgcategorypen' style="float: left;" src='./images/others/penedit.png'/><u>Category</u>
            </div>
            <div id="div_categorycontainer"><?php
                $i = 1;
                foreach($result as $rs)
                {
                    $thisfavoriteimage = '<img id="imgfavor'.$rs['recno'].'" title="Click to add to favor list."  src="./images/others/favoritewhitestar.png" style="cursor: pointer; height: 20px; width: 20px; padding-right: 10px; float: left;" onclick="updateFavorite(this, \'category\', '.$rs['recno'].');"/>';
                    if($rs['favorite'] == true)
                    {
                        $thisfavoriteimage = '<img id="imgfavor'.$rs['recno'].'" title="Click to remove from favor list."  src="./images/others/favoritebluestar.png" style="cursor: pointer; height: 20px; width: 20px; padding-right: 10px; float: left;" onclick="updateFavorite(this, \'category\', '.$rs['recno'].');"/>';
                    }?>
                    <div>
                        <div style="width: 10%;"><?=$thisfavoriteimage;?></div>
                        <div class="category-list" id="div_<?=$i?>" style="height: 40px; width: 85%; float: left; text-align: left; cursor: pointer; color: black;" onclick="getCategoryitems(this);">
                            <?=$rs['name']?>
                        </div>
                    </div><?php
                    $i++;
                }?>
            </div>
        </div>
        <div id="div_curentitems" style="width: 200px; height: 760px; float: left; background-color: #E8EAEA; padding-top: 10px; overflow: auto; margin-left: 10px;">
            <div style="height: 40px; width: 100%; float: left; text-align: center; cursor: pointer; color: black; font-size: 1.2em; font-weight: bold; padding-bottom: 10px;">
                <img title='Click to enable editing items.' onclick="enableEdititemlist(this);" id="imgeditcurrentitem" style="float: left; display: none;" src='./images/others/penedit.png'/><u>Current Items</u>
            </div>
            <div id='div_itemcurrentcontainer'></div>
        </div>
        <div id="div_move" style="width: 40px; height: 760px; float: left; padding: 10px; display: flex; flex-direction: column; justify-content: center;">
            
            <button id="btnmovetoright" style="width: 100%;" onclick="moveItems(this);">>></button>
            <button id="btnmovetoleft" style="width: 100%;" onclick="moveItems(this);"><<</button>
            <br><br><br><br>
            <button id="btnmdelte" style="width: 100%; color: darkred;" title='Delete the selected items.' onclick="moveItems(this);">X</button>
        </div>
        <div id="div_availableitems" style="width: 200px; height: 760px; float: left; background-color: #E8EAEA; padding-top: 10px; overflow: auto;">
            <div style="height: 40px; width: 100%; float: left; text-align: center; cursor: pointer; color: black; font-size: 1.2em; font-weight: bold; padding-bottom: 10px;">
                <img title='Click to enable editing items.' onclick="enableEdititemavaillist(this);" id="imgeditavailableitem" style="float: left; display: none;" src='./images/others/penedit.png'/><u>Available Items</u>
            </div>
            <div id="div_itemavailablecontainer"></div>
        </div>                   
    </div><?php  
}
function SubmitItems()
{
    global $db;
   // $sql1 = "SELECT * FROM items WHERE category = '".$_POST['sltcategory']."' AND items = '".$_POST['txtitem']."'";
    //file_put_contents("./dodebug/debug.txt", var_dump($_POST), FILE_APPEND);
    $thistable = "items";
    $thisfields = array('All');
    $thiswheres = array("category" => $_POST['sltcategory'], "item" => $_POST['txtitem']);
    $result = $db->PDOQuery($thistable, $thisfields, $thiswheres);
    if(!$result)
    {
        $isfavorite = false;
        if($_POST['chkfavorite'] == 'true')
        {
            $isfavorite = true;
        }
        //file_put_contents("./dodebug/debug.txt", 'do i get in here?', FILE_APPEND);
        $thisdata = array('category' => $_POST['sltcategory'], 'item' => $_POST['txtitem'], 'favorite' => $isfavorite);
        $result = $db->PDOInsert($thistable, $thisdata);
        if($result)
        {
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
        echo "Exists";
    }
}
function AddItem()
{
    global $db;
    $thistable = "category";
    $thisfields = array("All");
    $thiswheres = array("isdeleted" => false);
    $result = $db->PDOQuery($thistable, $thisfields, $thiswheres);?>
    <div style="width: 600px;">
        <table id="tblnew_note" name="tblnew_note" class="tbl-new-note" style="float: left; width: 100%;">     
            <tr>
                <td class="tbltr-note-lbl">Category: </td>
                <td class="noteinput">
                    <select class="required" name="sltcategory" id="sltcategory">
                        <option value="Select" selected>Select</option><?php
                        if($result)
                        {
                            foreach($result as $rs)
                            {?>
                                <option value="<?=$rs['name']?>"><?=$rs['name']?></option><?php
                            }
                        }?>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="tbltr-note-lbl">Item: </td>
                <td class="noteinput"><input class="required" type="text" id="txtitem" name="txtitem" style="width: 98%; resize: none;" row="4" /></td>
            </tr>
            <tr>
                <td class="tbltr-note-lbl">Make Favorite: </td>
                <td class="noteinput"><input type="checkbox" id="chkfavoriteitem" name="chkfavoriteitem" value="Favorite" style="height: 20px; width: 20px; resize: none;" row="4" />Make Favorite</td>
            </tr>
            <tr>
                <td class="noteinput" colspan="2"><button class="button" style="height: 40px; display: block; margin: auto;" id="btnsubmit" name="btnsubmit" value="Submit" onclick="submitItems();">Submit</button></td>
            </tr>
        </table>
    </div><?php    
}
function SubmitCategory()
{
    global $db;
    $isfavorite = false;
    if($_POST['chkfavorite'] == 'true')
    {
        $isfavorite = true;
    }
    $thistable = "category";
    $thisfields = Array("name");
    $thiswheres = array("name" => $_POST['txtcategory']);
    $result = $db->PDOQuery($thistable, $thisfields, $thiswheres);
    if(!$result)
    {
        $thisdata = array('name' => $_POST['txtcategory'], 'favorite' => $isfavorite);
        $result = $db->PDOInsert($thistable, $thisdata);
        if($result)
        {
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
        echo "Exists";
    }
}
function addItemcategory()
{?>
    <div style="width: 600px;">
        <table id="tblnew_note" name="tblnew_note" class="tbl-new-note" style="float: left; width: 100%;">     
            <tr>
                <td class="tbltr-note-lbl">Category: </td>
                <td class="noteinput"><input class="required" type="text" id="txtcategory" name="txtcategory" style="width: 98%; resize: none;" row="4" /></td>
            </tr>
            <tr>
                <td class="tbltr-note-lbl">Make Favorite: </td>
                <td class="noteinput"><input type="checkbox" id="chkfavorite" name="chkfavorite" value="Favorite" style="height: 20px; width: 20px; resize: none;" row="4" />Make Favorite</td>
            </tr>
            <tr>
                <td class="noteinput" colspan="2"><button class="button" style="height: 40px; display: block; margin: auto;" id="btnsubmit" name="btnsubmit" value="Submit" onclick="submitCategory();">Submit</button></td>
            </tr>
            
        </table>
    </div><?php
}
function DeleteServiceorder()
{
    global $db;
    $thistable = 'service_orders';
    $thisdata = array('isdeleted' => true);
    $thiswhere = array('recno' => $_POST['recno']);
    $db->PDOUpdate($thistable, $thisdata, $thiswhere, $_POST['recno']);
    
    $thistable = "service_orders";
    $thisfields = Array("foreignkey_flow_recno");
    
    $result = $db->PDOQuery($thistable, $thisfields, $thiswhere);
    $thisflowrecno = "";
    foreach($result as $rs)
    {
       $thisflowrecno = $rs['foreignkey_flow_recno'];
    }
    $thistable = 'flow';
    $thisdata = array('isdeleted' => true);
    $thiswhere = array('recno' => $thisflowrecno);
    $result = $db->PDOUpdate($thistable, $thisdata, $thiswhere, $thisflowrecno);
    if(($result))
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
    global $db;
    //file_put_contents("./dodebug/debug.txt", var_dump($_POST), FILE_APPEND);
    $sql = "SELECT flow.customer, flow.actype, flow.flightnumber, so.completedby, so.recno FROM flow INNER JOIN service_orders so ON so.foreignkey_flow_recno = flow.recno WHERE ";
    if($_POST['txtserviceorder'] != "")
    {
        //If there is a serviceorder entered, we don't need to look anymore, we query the service order number.
        $sql .= "so.recno = ".$_POST['txtserviceorder'];
    }
    else
    {
        //If it is any other fields, we will handle it here.
        $sql .= "1=1 ";
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
        if($_POST['txtdate'] != "")
        {
            $sql .= "AND flow.date = '".date('Y-m-d', strtotime($_POST['txtdate']))."' ";
        }
        if($_POST['txtdatefrom'] != '' && $_POST['txtdateto'] != '')
        {
            $sql .= "AND flow.date BETWEEN '".date('Y-m-d', strtotime($_POST['txtdatefrom']))."' AND '".date('Y-m-d', strtotime($_POST['txtdateto']))."' ";
        }
        if($_POST['sltcompletedby'] != "All")
        {
            if($_POST['sltcompletedby'] == "Open")
            {
                $sql .= "AND completedby IS NULL ";
            }
            else
            {
                //We assume 'Completed'
                $sql .= "AND completedby IS NOT NULL ";
            }
            
        }
        $sql .= "ORDER BY flow.customer, flow.date";
    } 
    $result = $db->PDOMiniquery($sql);?>
    <div style="width: 100%; overflow-y: auto;">
        <table id="tbl_order_data" class="tbl-order-data">
            <thead>
                <tr style="background-color: #173346;">
                    <th style="width: 20px !important; position: sticky; top: 0px; z-index: 10; padding-right: 10px;"><input class="print-check" type="checkbox" id="chkall" name="chkal" onclick="doCheckall(this);"/>Chk All</th>
                    <th style="width: 160px !important; position: sticky; top: 0px; z-index: 10; padding-left: 10px;" title="Customer">Customer</th>
                    <th style="width: 60px !important; position: sticky; top: 0px; z-index: 10; padding-left: 10px;" title="Aircraft Type">A/C Type</th>
                    <th style="width: 50px; position: sticky; top: 0px; z-index: 10; padding-left: 10px;" title="Flight Number">Flt No.</th>
                    <th style="width: 40px; position: sticky; top: 0px; z-index: 10; padding-right: 10px;" title="Schedule Arrival">Service No.</th>
                    <th style="width: 40px; position: sticky; top: 0px; z-index: 10; padding-right: 10px;" title="Complete Status">Status</th>
                    <th style="background-color: white;"><div style="float: right; "><img style="width: 20px; height: 20px; cursor: pointer;" title="Click to print selected Service Orders" onclick="printSelected();" src="./images/others/printericon.png"/></div></th>
                 </tr>
            </thead>
            <tbody><?php
                if($result)
                {
                    $i=1;
                    $issocompletedby = "white";
                    
                    foreach($result as $rs)
                    { 
                        $issocompletedby = "white";
                        if(!is_null($rs['completedby']))
                        {
                            $issocompletedby = "#D3EDFE";
                        }?>
                        <tr style="height: 40px; background-color: <?=$issocompletedby?>" id="tr<?=$i?>">
                            <td class="tdnumbered" style="text-align: right; height: auto; width: 20px !important; padding-right: 10px;" >
                                <input class="print-check" type="checkbox" id="chk_<?=$rs['recno']?>" name="chk_<?=$rs['recno']?>" /> 
                                <?= $i ?>
                            </td>
                            <td style="height: auto; width: 160px; padding-left: 10px; cursor: pointer;" onclick="showOrders(<?=$rs['recno']?>);"><?= $rs['customer']?></td>
                            <td style="height: auto; width: 60px; padding-left: 10px; cursor: pointer;" onclick="showOrders(<?=$rs['recno']?>);"><?= $rs['actype']?></td>
                            <td style="height: auto; width: 50px; padding-left: 20px; cursor: pointer;" onclick="showOrders(<?=$rs['recno']?>);"><?= $rs['flightnumber']?></td>
                            <td style="height: auto; width: 40px; padding-right: 20px; text-align: right; cursor: pointer;" onclick="showOrders(<?=$rs['recno']?>);"><?= $rs['recno']?></td>
                            <td style="height: auto; width: 40px; padding-right: 20px; text-align: right; cursor: pointer;" onclick="showOrders(<?=$rs['recno']?>);"><?=($rs['completedby'] != NULL ? 'Completed' : 'Incomplete');?></td>
                            <td style="height: auto; width: 60px; padding-left: 10px;">
                                <button class="serviceorder-button-delete" id="btnisdeleted_<?=$i?>" value="Delete" onclick="deleteServiceorder(this, <?= $rs['recno'] ?>, <?=$i?>);">Delete</button>
                            </td>
                        </tr><?php
                        $i++;
                    }
                    if($i==1)
                    {?>
                        <tr id="tr_nodata">
                            <td style="width: 100%; text-align: center;" colspan="5">There is no data</td>
                        </tr><?php 
                    }
                }?>                
            </tbody>
        </table>
    </div><?php
}
function SearchInterface()
{?>
    <div class="div-select-header" id="div_select_header">
        <div class="search-service-orders">Service Order#:<br><input type="text" name="txtserviceorder" id="txtserviceorder" onchange="searchFororders(this);" value="" autofocus placeholder="Enter a service order #"/></div>
        <div class="search-service-orders">Customer:<br><input type="text" name="txtcustomer" id="txtcustomer" value="" onkeyup="searchFororders(this);" onfocus="searchFororders(this);" placeholder="Type a customer name"/></div>
        <div class="search-service-orders">Aircraft Type:<br><input type="text" name="txtactype" id="txtactype" value="" onkeyup="searchFororders(this);" onfocus="searchFororders(this);" placeholder="Enter Aircraft Type"/></div>
        <div class="search-service-orders">Flight Number:<br><input type="text" name="txtflightnumber" id="txtflightnumber" onkeyup="searchFororders(this);" onfocus="searchFororders(this);" value="" placeholder="Flight Number"/></div>
        <div class="search-service-orders">Date:<br><input type="text" name="txtdate" id="txtdate" value="" onfocus="getJDate(this);" onchange="searchFororders(this);" placeholder="Enter a date 10/21/2022"/></div>
        <div class="search-service-orders">Date Range From:<br><input type="text" name="txtdatefrom" id="txtdatefrom" onfocus="getJDate(this);" onchange="searchFororders(this);" value="" placeholder="Enter a date 10/21/2022"/></div>
        <div class="search-service-orders">Date Range To:<br><input type="text" name="txtdateto" id="txtdateto" onfocus="getJDate(this);" onchange="searchFororders(this);" value="" placeholder="Enter a date 10/21/2022"/></div>
        <div class="search-service-orders">
            Status:<br>
            <select id="sltcompletedby" name="sltcompletedby" onchange="searchFororders(this);">
                <option value="All" selected>All</option>
                <option value="Open">Open</option>
                <option value="Completed">Completed</option>
            </select>
        </div>
        <div style="float: left;">
            <button id="btnclear" value="Search" style="width: 80px; height: 30px;" onclick="searchFororders(this);">Clear</button>
        </div>
    </div>
    <div class="main-div-body-order-right-container-search" id="div_select_body_container"></div>
<?php
}
function ShowServiceorders()
{
    global $db;
    $thisfields = Array();
    $thiswheres = Array();
    //QueryMe($thistype=null, $thistable=null, $thisfields=null, $thiswheres=null, $thisorderby=null, $thisgroupby=null, $ordering=null)
    $sql = "SELECT so.recno, flow.customer, flow.actype, flow.flightnumber FROM service_orders so INNER JOIN flow ";
    $sql .= "ON flow.recno = so.foreignkey_flow_recno WHERE so.isdeleted = false";
    $result = $db->PDOMiniquery($sql);
    if($result) //Nott sure if isset() will check if some items is returned or at least something in asso array.
    {
       foreach($result as $rs)
       {?>
            <div>
                <table id="tbl_order_data" class="tbl-order-data">
                    <thead>
                        <tr style="background-color: #173346;">
                            <th style="width: 20px !important; position: sticky; top: 0px; z-index: 10; padding-right: 10px;"></th>
                            <th style="width: 160px !important; position: sticky; top: 0px; z-index: 10; padding-left: 10px;" title="Customer">Customer</th>
                            <th style="width: 60px !important; position: sticky; top: 0px; z-index: 10; padding-left: 10px;" title="Aircraft Type">A/C Type</th>
                            <th style="width: 50px; position: sticky; top: 0px; z-index: 10; padding-left: 10px;" title="Flight Number">Flt No.</th>
                            <th style="width: 40px; position: sticky; top: 0px; z-index: 10; padding-right: 10px;" title="Schedule Arrival">Order No.</th><?php
                            if(in_array('Delete', $_SESSION['thisauth']))
                            {?>
                                <th></th><?php
                            }?>
                         </tr>
                    </thead>
                    <tbody><?php
                        $i=1;
                        if($result)
                        {
                            foreach($result as $rs)
                            {?>
                                <tr id="tr<?=$i?>">
                                    <td class="tdnumbered" style="text-align: right; height: 40px; width: 20px !important; padding-right: 10px;" onclick="showOrders(this, <?=$rs['recno']?>, <?=$i?>);"><?= $i ?></td>
                                    <td style="height: 40px; width: 160px; padding-left: 10px; cursor: pointer;" onclick="showOrders(<?=$rs['recno']?>);"><?= $rs['customer']?></td>
                                    <td style="height: 40px; width: 60px; padding-left: 10px; cursor: pointer;" onclick="showOrders(<?=$rs['recno']?>);"><?= $rs['actype']?></td>
                                    <td style="height: 40px; width: 50px; padding-left: 20px; cursor: pointer;" onclick="showOrders(<?=$rs['recno']?>);"><?= $rs['flightnumber']?></td>
                                    <td style="height: 40px; width: 40px; padding-right: 20px; text-align: right; cursor: pointer;" onclick="showOrders(<?=$rs['recno']?>);"><?= $rs['recno']?></td><?php
                                    if(in_array('Delete', $_SESSION['thisauth']))
                                    {?>
                                        <td style="height: 40px; width: 60px; padding-left: 10px;"><button class="serviceorder-button-delete" id="btnisdeleted_<?=$i?>" value="Delete" onclick="deleteServiceorder(this, <?= $rs['recno'] ?>, <?=$i?>);">Delete</button></td><?php
                                    }?>
                                </tr><?php
                                $i++;
                            }
                            if($i==1)
                            {?>
                                <tr id="tr_nodata"><td style="width: 100%; text-align: center;" colspan="5">There is no data</td></tr><?php
                            }
                        }?> 
                    </tbody>
                </table>
            </div><?php
       }
    }
}
function SearchUser()
{
    global $db;
    if(count($_SESSION['usersearchlist']) > 0)
    {
        //If this session variable has stuffs in it, that means user already started the search and
        //we want to use this array rather than going back to the database every time user typed a char.
        //file_put_contents("./dodebug/debug.txt", 'inside session', FILE_APPEND);
        SearchUserexistinglist($_POST['txtsearchuser']);
        exit();
    }
    //file_put_contents("./dodebug/debug.txt", var_dump($_SESSION['usersearchlist']), FILE_APPEND);
    $thisfields = Array();
    $thiswheres = Array();
    //QueryMe($thistype=null, $thistable=null, $thisfields=null, $thiswheres=null, $thisorderby=null, $thisgroupby=null, $ordering=null)
    $thistable = "employee_master";
    $thisfields = array('recno', 'firstname', 'middlename', 'lastname');
    $tempexplodeuser = explode(' ', $_POST['txtsearchuser']);
    //We get here when user entered just the first name, middle or last name.
    $thiswhere = array("firstname LIKE" => strtolower($_POST['txtsearchuser']), "middlename LIKE" => strtolower($_POST['txtsearchuser']), "lastname LIKE" => strtolower($_POST['txtsearchuser']));
    $result = $db->PDOQuery($thistable, $thisfields, $thiswhere);
    $realname = "";?>   
    <select name="sltsearchuser" id="sltsearchuser" style="z-index: 999; width: 200px; height: 200px; position: absolute; text-align: left; margin-top: 40px; margin-left: -310px;"  size="5" onchange="hideSelectsearch();"><?php
        if(isset($result)) //Nott sure if isset() will check if some items is returned or at least something in asso array.
        {  
           foreach($result as $rs)
           {
               $realname = $rs['firstname'];
               if(!is_null($rs['middlename']) && $rs['middlename'] != "")
               {
                   $realname .= " ". substr($rs['middlename'], 0, 1). ".";
               }
               $realname .= " ".$rs['lastname'];
               $_SESSION['usersearchlist'][$rs['recno']] = $realname;?>
                <option value="<?= $rs['recno']?>"><?= $realname ?></option><?php 
           }
        }?>
    </select><?php
}
function Main()
{
    global $load_headers;?>
    <div class="main-div">
        <?php
        $load_headers::Load_Header_Logo_Marquee();?>
        <br><br> <?php
        $load_headers::Load_Header_Logo_Main();?>
        <div class="main-div-body-order">
            <table>
                <tr>
                    <td>
                        <div class="main-div-body-order-left" style="margin-top: -30px;">
                            <div class="main-div-body-order-header">Service Orders</div>
                            <div style="float: left;">
                                <div class="div-menu-service-orders" id="div_show" onclick="showServiceorders(this);">Show Service Orders</div>
                                <div class="div-menu-service-orders" id="div_search" onclick="searchInterface(this);">Search Service Order</div><?php                                
                                if(in_array('Write', $_SESSION['thisauth']) || in_array('Modify', $_SESSION['thisauth']) || in_array('Delete', $_SESSION['thisauth']))
                                {?>  
                                    <div class="div-menu-service-orders" id="div_additemcategory" onclick="addItemcategory(this);">Add Item Category</div> 
                                    <div class="div-menu-service-orders" id="div_additem" onclick="addItem(this);">Add Items</div> 
                                    <div class="div-menu-service-orders" id="div_manageitem" onclick="manageItem(this);">Manage Items</div><?php 
                                }?>
                                <div class="div-menu-service-orders" id="div_manageitem" onclick="printBlankso();">Print Service Order Blank</div> 
                            </div> 
                        </div>
                    </td>
                    <td>
                        <div id="main_div_body_order_right_container" class="main-div-body-order-right-container"></div>
                    </td>
                </tr>
            </table>
        </div>
        <?php
        $load_headers::Load_Footer();?>
    </div><?php
}?>