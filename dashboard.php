<?php
require("./common/page.php");
require("./common/pdocon.php");
require("./common/classes/pageloaderclass.php");
require_once("./common/sendmail.php");
require("./common/classes/emailclass.php");
require_once("./common/prompt.php");
$pt = new PROMPT();
$load_headers = new PageloaderClass();
$db = new PDOCON();
$ne = new Email_Class();
//file_put_contents("./dodebug/debug.txt", 'menuresult: '.$thisauth, FILE_APPEND);
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
            //file_put_contents("./dodebug/debug.txt", 'menuresult: '.$thisauth[0], FILE_APPEND);
            //$thisauth now holds an array of 'Read', 'Write', 'Modify', and or 'Delete',
        ?>
        <script type="text/javascript">
            $(document).ready(function(){
                manageUsers($("#div_manageuser")[0]);
            });
            function adminMenuslt(obj){
                $(".div-menu-admin").each(function(){
                    $(this).css('background-color', '#1079B1');
                    $(this).css('color', 'white');
                })
                $(obj).css("background-color", "white");
                $(obj).css('color', 'black');
            }
            function manageUsers(obj){
                adminMenuslt(obj);
                $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=ManageUsers', function(result){
                    $("#main_div_body_admin_right_container").html(result);
                    searchUser();
                });
            }
            function getUserdata(obj, recno){
                //recno - this is the recno for the recno column in the users table
                $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=GetUserdata&recno='+recno, function(result){
                    //alert(result);
                    if($("#sltsearchuser").length){
                        $("#sltsearchuser").remove();  //We want to remove this select because we are rebuilding it with a new updated slt.
                    }
                    $("#div_mgm_search").html(result);
                       
                }); 
            }
            function clearSearchuser(){
                $("#sltsearchuser").remove();
                $("#tbluserdata").remove();
                $("#txtsearchuser").val('');
                $("#txtsearchuser").focus();
            }
           
            function searchUser(){
                thisactive = 'false';
                thisterminate = 'false';
                if($("#chkactive").is(":checked")){
                    thisactive = 'true';
                }
                if($("#chkterminate").is(":checked")){
                    thisterminate = 'true';
                }
                if($("#txtsearchuser").val().trim().length == 0){
                    clearSearchuser();
                    $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=SearchUserunset&txtsearchuser='+$("#txtsearchuser").val(), function(){
                        return(false);
                    }); 
                }             
                $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=SearchUser&txtsearchuser='+$("#txtsearchuser").val()+'&isTerminated='+thisterminate+'&isActive='+thisactive, function(result){
                    if($("#sltsearchuser").length){
                        $("#sltsearchuser").remove();
                    }
                    $("#tbluserdata").remove();
                    $("#div_mgm_search").after(result);
                }); 
            }
            function updateUser(obj, recno){                 
                if($(obj).prop('id') == "chkisTerminated" || $(obj).prop('id') == "chkisActive" || $(obj).prop('id') == "chkisAdmin"){
                    if($(obj).prop('id') == "chkisTerminated"){
                        if($(obj).is(":checked")){
                            realval = 'true';
                            $("#chkisActive").prop('checked', false);
                        }
                        else{
                            realval = 'false';
                        }
                    }
                    if($(obj).prop('id') == "chkisActive"){
                        if($(obj).is(":checked")){
                            realval = 'true';
                        }
                        else{
                            realval = 'false';
                        }
                    }
                    if($(obj).prop('id') == "chkisAdmin"){
                        if($(obj).is(":checked")){
                            realval = 'true';
                        }
                        else{
                            realval = 'false';
                        }
                    }
                }
                else{
                    realval = $(obj).val();
                }
                $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=UpdateUser&thisrecno='+recno+'&thisfield='+$(obj).prop('id').slice(3)+'&thisvalue='+realval, function(result){
                    //alert(result);
                    if(result == "Success"){
                        //alert('Updated');
                    }
                    else if(result == "Failed"){
                        alert('Failed to update.  Contact Administrator.');
                        $(obj).val($("body").data($(obj).prop('id')));
                        $(obj).focus();
                    }
                    else{
                        alert(result);
                        //alert($("body").data($(obj).prop('id')));
                        $(obj).val($("body").data($(obj).prop('id')));
                        $(obj).focus();
                    }
                });
            }
            function getVal(obj){
                //alert($(obj).val());
                if($(obj).prop('id') != "chkterminate" && $(obj).prop('id') != "chkactive" && $(obj).prop('id') != "chkdeleted"){
                    $("body").data($(obj).prop('id'), $(obj).val());
                }
                else{
                    if($(obj).is(":checked")){
                        $("body").data($(obj).prop('id'), "checked");
                    }
                    else{
                        $("body").data($(obj).prop('id'), "");
                    }
                        
                }
            }
            function reloadUser(obj){
                searchUser();
            }
            function manageServices(obj){
                adminMenuslt(obj);
                $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=ManageServices', function(result){
                    $("#main_div_body_admin_right_container").html(result);
                    searchServices();
                }); 
            }
            function searchServices(){
                thisactive = 'false';
                thisdeleted = 'false';
                
                if($("#rdoactive").is(":checked")){
                    rdoservice = $("#rdoactive").val();
                }
                if($("#rdodeleted").is(":checked")){
                    rdoservice = $("#rdodeleted").val();
                }
                $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=SearchServices&rdoservice='+rdoservice, function(result){
                    if($("#tbl_admin_service").length > 0){
                        $("#tbl_admin_service").remove();
                    }
                    $("#div_mgm_search").after(result);
                    $("#tbl_admin_service").dataTable({
                        "pageLength": 18
                    });
                }); 
            }
            function reloadService(){
                searchServices();
            }
            function getService(obj, recno){
                $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=GetService&recno='+recno, function(result){
                    //alert(result);
                    if($("#div_mgm_search").length){
                        $("#div_mgm_search").remove();  //We want to remove this select because we are rebuilding it with a new updated slt.
                    }
                    $("#div_search_containter").html(result);
                       
                }); 
            }
            function updateService(obj, thisrecno, thisfield){                
                if($(obj).prop('id') == "chkactive" || $(obj).prop('id') == "chkdeleted"){
                    if($(obj).is(":checked")){
                        realval = 'true';
                    }
                    else{
                        realval = 'false';
                    }
                }
                else{
                    realval = $(obj).val();
                }
                $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=UpdateService&thisrecno='+thisrecno+'&thisfield='+thisfield+'&thisval='+realval, function(result){
                    //alert(result);
                    if(result != 'Success'){
                        alert(result);
                        $(obj).val($("body").data($(obj).prop('id')));
                        $(obj).focus();
                        $(obj).select();
                    }
                       
                }); 
            }
            function addService(){
                $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=AddService', function(result){
                    if($("#div_mgm_search").length){
                        $("#div_mgm_search").remove();  //We want to remove this select because we are rebuilding it with a new updated slt.
                    }
                    $("#div_search_containter").html(result);
                }); 
            }
            function submitNewservice(obj){
 
                let thisArray = [['title', $("#txtarea_service").val()],
                            ['time', $("#txttime").val()],
                            ['description', $("#textarea_comment").val()],
                            ['price', $("#txtprice").val()],
                            ['isactive', 'true']];

                $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=SubmitNewservice&thisarray='+JSON.stringify(thisArray), function(result){
                    //alert(result);
                    if(result != "Success"){
                        alert(result);
                    }
                    else{
                        alert("Successfully added.");
                        $("#tblservicedata").find('input:text').val('');
                        $("#txtarea_service").val('');
                        $("#textarea_comment").val('');
                    }
                }); 
            }
            function addCompany(obj){
                adminMenuslt(obj);
                $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=AddCompany', function(result){
                    if($("#div_mgm_search").length){
                        $("#div_mgm_search").remove();  //We want to remove this select because we are rebuilding it with a new updated slt.
                    }
                    $("#div_search_containter").html(result);
                }); 
            }
            function updateCompanyinfo(obj){
                $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=UpdateCompanyinfo&thisrecno='+thisrecno+'&thisfield='+thisfield+'&thisval='+realval, function(result){
                    //alert(result);
                    if(result != "Success"){
                        alert(result);
                    }
                }); 
            }
            function submitNewcompany(){
                if($("#txtname").val() == ""){
                     alert("Company name can not be empty.");
                     return(false);
                }
                var form_data = new FormData($('#frmcompany')[0]);
                $.ajax({
                    type: 'POST',
                    url: '<?php echo $_SERVER['PHP_SELF']; ?>?cmd=SubmitNewcompany',
                    data: form_data,
                    processData: false,
                    contentType: false,
                    success: function(result) {
                        if(result != "Success"){
                            alert(result);
                            preventDefault();
                            return(false);
                        }
                        else{
                            alert(result);
                        }
                    }
                });
            }
            function showCompanyimage(){
                $.post('<?php echo $_SERVER['PHP_SELF']; ?>', 'cmd=ShowCompanyimage', function(result){
                    //alert(result);
                    if(result != "Success"){
                        $("#div_search_containter").html(result);
                    }
                }); 
            }
            function selectImage(obj, thisfield, thisimage){
                //We are updating thisfield, either profile_image or thumb_nail in table attachments, depending on what they clicked in profile.
                //thisimage is the new image that will replace
                //alert("img_"+thisimage);
                //$("#"+thisimage).addClass("admin-company-image-bucket-selected"); //this line doesn't work for some reason so we are focusing a reload of the tab on success call below.
                $.post('<?=$_SERVER['PHP_SELF']; ?>', 'cmd=SelectImage&thisfield='+thisfield+'&thisimage='+thisimage, function(result){
                    //alert(result);
                    //If no error, we should return the old image so we can manipulate the dom, $thisoldimage
                    if(result != "Success"){
                        alert("Failed to select image.  Please contact I.T for help.");
                    }
                    else{
                        addCompany($("#div_managecompany")[0]);
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
function SelectImage()
{
    global $db;
    
    $thistable = "company_info";
    $thisdata = [$_POST['thisfield'] => $_POST['thisimage']];
    $thiswhere = ['foreign_ur' => $_SESSION['user_recno']];
    $result = $db->PDOUpdate($thistable, $thisdata, $thiswhere, $_SESSION['user_recno']);
    if(!isset($result))
    {
        echo "Failed";
    }
    else
    {
        $_SESSION['main_logo'] = $_POST['thisimage']; //We must update the session because we declared it in login.php however we now changed so we must update.
        echo "Success";
    }
}
function ShowCompanyimage()
{
    global $db; //$thistable=null, $thisfields=null, $thiswhere=null, $thisorderby=null, $thisgroupby=null, $ordering=null)
    $thismediadir = "";
    $thisfrontimage = "";
    $thisthumbnail = "";
    $usethiscssborder = "";
    $usethistitle = "";
    $sql = "SELECT u.media_dir, a.name, a.file FROM users u INNER JOIN attachments a ON a.foreign_ur = u.recno WHERE a.foreign_ur = ".$_SESSION['user_recno']." AND a.isDeleted = false";
   
    $rows = $db->PDOMiniquery($sql);
    //./images/others/$thismedia/avatar/$profileimage"
    foreach($rows as $rs)
    {
        $thismediadir = $rs['media_dir'];
        if($rs['name'] == "profile_image")
        {
            $thisfrontimage = $rs['file'];
        }
        if($rs['name'] == "thumb_nail")
        {
            $thisthumbnail = $rs['file'];
        }
    }        
    $thisdir = "./images/others/$thismediadir/avatar/*";
    $thispath = "./images/others/$thismediadir/avatar";?>
    <div class="div-admin-image-container" id="div_admin_image_container"><?php
        foreach(glob($thisdir) as $file)
        {
            if(!is_dir($file)) 
            {
                //basename($file) will be name.filetype, ex: name.png
                //file_put_contents('./dodebug/debug.txt', 'profile state: '.basename($file).' == '.$thisfrontimage.' || '.basename($file).' == '.$thisthumbnail.' \n', FILE_APPEND);
                $usethiscssborder = "";
                $usethistitle = "";
                if(strtolower(basename($file)) == strtolower($thisfrontimage) || strtolower(basename($file)) == strtolower($thisthumbnail))
                {
                    $usethiscssborder = "admin-company-image-bucket-selected";
                    $usethistitle = "Selected";
                }
                ?>
                <div onclick="selectImage(this, 'mainlogo', '<?php echo basename($file) ?>');">
                    <img id="img_<?php echo basename($file) ?>" class="adminbucketimage <?php echo $usethiscssborder ?>" title="<?php echo $usethistitle ?>" src="<?php echo $thispath ?>/<?php echo basename($file) ?>" onerror="this.src='<?php echo $thispath ?>/defaultimage.png'"></a>
                    <br/><span class="admin-span-image-disc"><?php echo basename($file) ?></span>
                </div><?php
            }
        }?>
    </div><?php
}
function SubmitNewcompany()
{
    global $db, $pt;
    //file_put_contents("./dodebug/debug.txt", "admin company here: ".$_FILES['thisfile']["name"]." \n", FILE_APPEND);
    $thistable = "company_info";
    $msg = "";
    $_POST['txtforeign_ur'] = $_SESSION['user_recno'];
    $thisdata = $pt ->PostIt($_POST); //PostIt is a function that will return an associative array with non-empty values and substring first 3 chars
    
    //$thisrecno is the recno of $thistable
    $thisrecno = $db ->PDOInsert($thistable, $thisdata, $_SESSION['user_recno']);
    if(isset($thisrecno))
    {
        //file_put_contents("./dodebug/debug.txt", "admin company = here \n", FILE_APPEND);
        //Assuming we are here, we want to now handle the upload.
        //First we want to check if './images/others/$_SESSION['media_dir']/logo/ exist, if not, we create it before we move file into it.
        $thisdir = "./images/others/".$_SESSION['media_dir']."/logo";
        if (!file_exists($thisdir)) {
            mkdir("./images/others/".$_SESSION['media_dir']."/logo", 0777, true);
        }
        //Once we confirmed that it is there after, now we want to move the file or files there and also update the name of the file to the table.
        
        $msg = $pt ->UploadFile($thisdir, $_FILES["thisfile"], $thistable, "mainlogo", $thisrecno);

    }
    else
    {
        $msg = "Failed to insert";
    }
    echo $msg;
}
function UpdateCompanyinfo()
{
    global $db;
    $thismsg = "";
    //We want to check time and place
    switch($_POST['thisfield'])
    {
        case 'time':
        case 'price':
            if(!is_numeric($_POST['thisval']))
            {
                $thismsg = "This field must be a number.";
            }
            break;
        default:
            break;
    }
    if($thismsg == "")
    {
        $thistable = "service";
        if($_POST['thisval'] == "true" || $_POST['thisval'] == "false")
        {
            if($_POST['thisval'] == "true")
            {
                $thisdata = Array($_POST['thisfield'] => true);
            }
            else
            {
                $thisdata = Array($_POST['thisfield'] => false);
            }
        }
        else
        {
            $thisdata = Array($_POST['thisfield'] => $_POST['thisval']);
        }
        $thiswhere = Array('recno' => $_POST['thisrecno']);
        $result = $db ->PDOUpdate($thistable, $thisdata, $thiswhere, $_SESSION['user_recno']);
        if(!is_null($result)) //Nott sure if isset() will check if some items is returned or at least something in asso array.
        {
           $thismsg = "Success";
        }
    }
    echo $thismsg;
}
function AddCompany()
{
    global $db;
    $thisname = "";
    $thisaddress = "";
    $thiscity = "";
    $thisstate = "";
    $thiszipcode = "";
    $thislogo = "";
    $thispaymentcompany = "";
    $thisapiid = "";
    $thisapikey = "";
    $usthisfunc = "";
    $thisbutton = "style='display: none;'";
    $sql = "SELECT * FROM company_info WHERE foreign_ur = '".$_SESSION['user_recno']."'";
    $result = $db ->PDOMiniquery($sql);
    if($db ->PDORowcount($result) > 0)
    {
        foreach($result as $rs)
        {
            $thisname = $rs['name'];
            $thispaymentcompany = $rs['api_company'];
            $thisapiid = $rs['api_id'];
            $thisapikey = $rs['api_key'];
            $thisaddress = $rs['address'];
            $thiscity = $rs['city'];
            $thisstate = $rs['state'];
            $thiszipcode = $rs['zipcode'];
            $thislogo = $rs['mainlogo'];
        }
        $usthisfunc = 'onfocus = "getVal(this)" onchange = "updateCompanyinfo(this);"';
        $thisbutton = "";
    }?>
    <form name="frmcompany" id="frmcompany" method="post" enctype="multipart/form-data">
        <table id="tblservicedata" class="tbl-admin-company">
            <tr>
                <td class="tbl-admin-company-lbl">Name Of Company: <span class="asterisk"> * </span></td>
                <td><input type="text" class="admin-company-input" style="width: 98%;" id="txtname" name="txtname" <?php echo $usthisfunc ?> value="<?php echo $thisname ?>" required /></td>
            </tr>
            <tr>
                <td class="tbl-admin-company-lbl">Payment Company: </td>
                <td><input type="text" class="admin-company-input" style="width: 98%;" id="txtapi_company" name="txtapi_company" <?php echo $usthisfunc ?> value="<?php echo $thispaymentcompany ?>" /></td>
            </tr>
            <tr>
                <td class="tbl-admin-company-lbl">API ID: </td>
                <td><input type="text" class="admin-company-input" style="width: 98%;" id="txtapi_id" name="txtapi_id" <?php echo $usthisfunc ?> value="<?php echo $thisapiid ?>" /></td>
            </tr>
            <tr>
                <td class="tbl-admin-company-lbl">API Key: </td>
                <td><input type="text" class="admin-company-input" style="width: 98%;" id="txtapi_key" name="txtapi_key" <?php echo $usthisfunc ?> value="<?php echo $thisapikey ?>" /></td>
            </tr>
            <tr>
                <td class="tbl-admin-company-lbl">Address: </td>
                <td><input type="text" class="admin-company-input" style="width: 98%;" id="txtaddress" name="txtaddress" <?php echo $usthisfunc ?> value="<?php echo $thisaddress ?>" /></td>
            </tr>
            <tr>
                <td class="tbl-admin-company-lbl">City: </td>
                <td><input type="text" class="admin-company-input" id="txtcity" name="txtcity" <?php echo $usthisfunc ?> value="<?php echo $thiscity ?>" /></td>
            </tr>
            <tr>
                <td class="tbl-admin-company-lbl">State: </td>
                <td><input type="text" class="admin-company-input" id="txtstate" name="txtstate" <?php echo $usthisfunc ?> value="<?php echo $thisstate ?>" /></td>
            </tr>
            <tr>
                <td class="tbl-admin-company-lbl">Zipcode: </td>
                <td><input type="text" class="admin-company-input" id="txtzipcode" name="txtzipcode" <?php echo $usthisfunc ?> value="<?php echo $thiszipcode ?>" /></td>
            </tr>
            <tr>
                <td class="tbl-admin-company-lbl">Main Banner: </td>
                <td><input type="file" class="admin-company-input" id="txtmainbanner" accept="image/png, image/gif, image/jpeg" name="thisfile[]" multiple="multiple"  /><div class="align-left">jpeg, gif, png ONLY</div></td>
            </tr><?php
            if($thisname != "")
            {?> 
                <tr>
                    <td class="tbl-admin-company-lbl">Active:</td>
                    <td><input type="checkbox" class="admin-company-input" id="chkactive" name="chkactive" <?php echo $usthisfunc ?> checked dissabled /></td>
                </tr>
                <tr>
                    <td class="tbl-admin-company-lbl">Deleted:</td>
                    <td><input type="checkbox" class="admin-company-input" id="chkdeleted" name="chkdeleted" <?php echo $usthisfunc ?> disabled /></td>
                </tr><?php 
            }
            if($thisname == "")
            {?> 
                <tr>
                    <td colspan="2" class="align-center"><button name="btnsubmit" id="btnsubmit" onclick="submitNewcompany();">Submit</button></td>
                </tr><?php
            }
            if($thisname != "")
            {?>
                <tr>
                   <td colspan="2" class="align-center">
                        <div style="width: 100%; height: 30px; background-color: #1079B1;">    
                           <div style="width: 120px; height: 100%; line-height: 30px; background-color: white; color: black;">Main Logo</div>
                        </div>
                        <div class="div-admin-image-container" id="div_profile_image_container"><?php
                            $thisdir = "./images/others/".$_SESSION['media_dir']."/logo/*";
                            $thispath = "./images/others/".$_SESSION['media_dir']."/logo";
                            BuildCompanyimagecontainer('mainlogo', $thisdir, $thispath, $thislogo);?>
                        </div>
                   </td>
                </tr><?php
            }?>
        </table>
    </form><?php 
}
function BuildCompanyimagecontainer($from, $thisdir, $thispath, $sltedimage)
{
    //$from is so far 'Main Logo'
    foreach(glob($thisdir) as $file)
    {
        if(!is_dir($file)) 
        {
            //basename($file) will be name.filetype, ex: name.png
            //file_put_contents('./dodebug/debug.txt', 'profile state: '.basename($file).' == '.$thisfrontimage.' || '.basename($file).' == '.$thisthumbnail.' \n', FILE_APPEND);
            $usethiscssborder = "";
            $usethistitle = "";
            //file_put_contents("./dodebug/debug.txt", "admin IMAGE container: ".strtolower(basename($file))." == ".strtolower($sltedimage)." \n", FILE_APPEND);
            if(!is_null($sltedimage))
            {
                if(strtolower(basename($file)) == strtolower($sltedimage))
                {
                    $usethiscssborder = "admin-company-image-bucket-selected";
                    $usethistitle = "Selected";
                }
            }?>
            <div onclick="selectImage(this, 'mainlogo', '<?php echo basename($file) ?>');">
                <img name="<?php echo basename($file) ?>" id="<?php echo basename($file) ?>" class="admin-bucket-image <?php echo $usethiscssborder ?>" title="<?php echo $usethistitle ?>" src="<?php echo $thispath ?>/<?php echo basename($file) ?>"></a>
                <br/><span class="admin-span-image-disc"><?php echo basename($file) ?></span>
            </div><?php
        }
    }
}
function SubmitNewservice()
{
    global $db;
    $thismsg = "";
    //We want to check time and place
    $thisdata = Array();
    $thistable = "service";
    foreach(json_decode($_POST['thisarray']) as $key => $value)
    {   
        //file_put_contents("./dodebug/debug.txt", "admin thisarray = $value[0] == $value[1] \n", FILE_APPEND);
        if($value[0] == 'time' && !is_numeric($value[1]))
        {
            $thismsg = "Time must be a number.";
            break;
        }
        if($value[0] == 'price' && !is_numeric($value[1]))
        {
            $thismsg = "Price must be a number.";
            break;
        }    
        $thisdata[$value[0]] = $value[1];  
    }
    if($thismsg == "")
    {
        $result = $db ->PDOInsert($thistable, $thisdata, $_SESSION['user_recno']);
        //file_put_contents("./dodebug/debug.txt", "admin menu sql = ".$result." \n", FILE_APPEND);
        if(!is_null($result)) //Nott sure if isset() will check if some items is returned or at least something in asso array.
        {
           $thismsg = "Success";
        }
    }
    echo $thismsg;
}
function AddService()
{?>
    <table id="tblservicedata" class="tbl-admin-service">
        <tr>
            <td class="tbl-admin-register-lbl">Service: <span class="asterisk"> * </span></td>
            <td><textarea cols="60" rows="4" class="required admin-services-input" id="txtarea_service" name="txtarea_service"></textarea></td>
        </tr>
        <tr>
            <td class="tbl-admin-register-lbl">Time: <span class="asterisk"> * </span></td>
            <td><input type="text" class="firstname required admin-services-input" id="txttime" name="txttime" value="" required />mins.</td>
        </tr>
        <tr>
            <td class="tbl-admin-register-lbl">Price: <span class="asterisk"> * </span></td>
            <td><input type="text" class="lastname required admin-services-input" id="txtprice" name="txtprice" value="" onfocus="getVal(this);" /></td>
        </tr>
        <tr>
            <td class="tbl-admin-register-lbl">Comment: </td>
            <td><textarea class="admin-services-input" cols="60" rows="4" id="textarea_comment" name="textarea_comment"></textarea></td>
        </tr>
        <tr>
            <td class="tbl-admin-register-lbl">Active:</td>
            <td><input type="checkbox" class="lastname required admin-services-input" id="chkactive" name="chkactive" checked dissabled /></td>
        </tr>
        <tr>
            <td class="tbl-admin-register-lbl">Deleted:</td>
            <td><input type="checkbox" class="lastname required admin-services-input" id="chkdeleted" name="chkdeleted" disabled /></td>
        </tr>
        <tr>
            <td colspan="2" class="align-center"><button name="btnsubmit" id="btnsubmit" onclick="submitNewservice();">Submit</button></td>
        </tr>
    </table><?php 
}
function UpdateService()
{
    global $db;
    $thismsg = "";
    //We want to check time and place
    switch($_POST['thisfield'])
    {
        case 'time':
        case 'price':
            if(!is_numeric($_POST['thisval']))
            {
                $thismsg = "This field must be a number.";
            }
            break;
        default:
            break;
    }
    if($thismsg == "")
    {
        $thistable = "service";
        if($_POST['thisval'] == "true" || $_POST['thisval'] == "false")
        {
            if($_POST['thisval'] == "true")
            {
                $thisdata = Array($_POST['thisfield'] => true);
            }
            else
            {
                $thisdata = Array($_POST['thisfield'] => false);
            }
        }
        else
        {
            $thisdata = Array($_POST['thisfield'] => $_POST['thisval']);
        }
        $thiswhere = Array('recno' => $_POST['thisrecno']);
        $result = $db ->PDOUpdate($thistable, $thisdata, $thiswhere, $_SESSION['user_recno']);
        if(!is_null($result)) //Nott sure if isset() will check if some items is returned or at least something in asso array.
        {
           $thismsg = "Success";
        }
    }
    echo $thismsg;
}
function GetService()
{
    global $db;
    $thisfields = Array();
    $thiswheres = Array();
    //QueryMe($thistype=null, $thistable=null, $thisfields=null, $thiswheres=null, $thisorderby=null, $thisgroupby=null, $ordering=null)
    $thistable = "service";
    $thisfields = array('All');
    $thiswhere = array("recno" => $_POST['recno']);
    //$thiswhere = array("recno" => $_POST['recno']);
    $result = $db->PDOQuery($thistable, $thisfields, $thiswhere);
    if(isset($result)) //Nott sure if isset() will check if some items is returned or at least something in asso array.
    {
       foreach($result as $rs)
       {?>
            <table id="tbluserdata" class="tbl-admin-service">
                <tr>
                    <td class="tbl-admin-register-lbl">Service: <span class="asterisk"> * </span></td>
                    <td><textarea cols="60" rows="4" class="required admin-services-input" onfocus="getVal(this);" id="txtarea_service" name="txtarea_service" onchange="updateService(this, <?php echo $_POST['recno'] ?>, 'title');"><?php echo  $rs['title']; ?></textarea></td>
                </tr>
                <tr>
                    <td class="tbl-admin-register-lbl">Time: <span class="asterisk"> * </span></td>
                    <td><input type="text" class="firstname required admin-services-input" id="txttime" name="txttime" value="<?php echo  $rs['time']; ?>" onfocus="getVal(this);" onchange="updateService(this, <?php echo $_POST['recno'] ?>, 'time');" required />mins.</td>
                </tr>
                <tr>
                    <td class="tbl-admin-register-lbl">Price: <span class="asterisk"> * </span></td>
                    <td><input type="text" class="lastname required admin-services-input" id="txtprice" name="txtprice" value="<?php echo number_format($rs['price'], 2); ?>" onfocus="getVal(this);" onchange="updateService(this, <?php echo $_POST['recno'] ?>, 'price');" /></td>
                </tr>
                <tr>
                    <td class="tbl-admin-register-lbl">Comment: </td>
                    <td><textarea class="admin-services-input" cols="60" rows="4" id="textarea_comment" name="textarea_comment" onfocus="getVal(this);" onchange="updateService(this, <?php echo $_POST['recno'] ?>, 'description');"><?php echo  $rs['description']; ?></textarea></td>
                </tr>
                <tr>
                    <td class="tbl-admin-register-lbl">Active:</td>
                    <td><input type="checkbox" class="lastname required admin-services-input" id="chkactive" name="chkactive" value="<?php echo  $rs['isactive']; ?>" onfocus="getVal(this);" onchange="updateService(this, <?php echo $_POST['recno'] ?>, 'isactive');" <?php echo ($rs['isactive'] == true) ? 'checked' : '' ?>/></td>
                </tr>
                <tr>
                    <td class="tbl-admin-register-lbl">Deleted:</td>
                    <td><input type="checkbox" class="lastname required admin-services-input" id="chkdeleted" name="chkdeleted" value="<?php echo  $rs['isdeleted']; ?>" onfocus="getVal(this);" onchange="updateService(this, <?php echo $_POST['recno'] ?>, 'isdeleted');" <?php echo ($rs['isdeleted'] == true) ? 'checked' : '' ?> /></td>
                </tr>
            </table><?php 
       }
    }
}
function ManageServices()
{
    ManageSearchmenus("Services");
}
function SearchServices()
{    
    global $db;
    
    $sql = "SELECT * FROM service WHERE ";
    //file_put_contents("./dodebug/debug.txt", "admin menu sql = ".$_POST['isDeleted']." and ". $_POST['isActive']." \n", FILE_APPEND);
    if($_POST['rdoservice'] == 'active')
    {
        $sql .= "isdeleted = false AND isactive=true";
    }
    if($_POST['rdoservice'] == 'deleted')
    {
        $sql .= "isactive = false and isdeleted=true";
    }    
    //file_put_contents("./dodebug/debug.txt", "admin menu sql = $sql \n", FILE_APPEND);
    $result = $db ->PDOMiniquery($sql);?>
    <table class="tbl-admin-service" id="tbl_admin_service" name="tbl_admin_service">
        <thead>
            <tr>
                <td>No.</td>
                <td>Service <button class="float-right" title="Add Service" style="width: 30px; height: 30px;" onclick="addService();">+</button></td>
                <td>Time</td>
                <td>Price</td>
            </tr>
        </thead>
        <tbody>
            <?php
            if($db ->PDORowcount($result) > 0)
            {
                $i = 1;
                foreach($result as $rs)
                {?>
                    <tr onclick="getService(this, <?php echo $rs['recno']?>);">
                        <td class="td-num-rows align-right"><?php echo $i ?>.</td>
                        <td><?php echo $rs['title'] ?></td>
                        <td><?php echo $rs['time'] ?></td>
                        <td>$<?php echo number_format($rs['price'], 2) ?></td>
                    </tr><?php
                    $i++;
                }
            }?>
        </tbody>
    </table><?php
}
function ManageSearchmenus($from)
{
    //$from - Users
    if($from == "Users")
    {?>
        <div id="div_search_containter" class="div-search-containter">
            <div class="float-left" id="div_mgm_search">
                <input class="txt-search-admin" type="text" id="txtsearchuser" name="txtsearchuser" value="" placeholder="Enter a name to start search." onclick="searchUser();" onkeyup="searchUser(this);" />
                <button type="button" onclick="clearSearchuser();">Clear</button>
                <div class="float-right">
                    <div class="float-left div-chk-active">Active: <input type="checkbox" name="chkactive" id="chkactive" onclick="reloadUser(this);" checked /></div>
                    <div class="float-left">Terminated: <input type="checkbox" name="chkterminate" id="chkterminate" onclick="reloadUser(this);" /></div>
                </div>
            </div>
        </div><?php
    }
    if($from == "Services")
    {?>
        <div id="div_search_containter" class="div-search-containter">
            <div class="float-left" id="div_mgm_search">
                <div class="float-right">
                    <div class="float-left div-chk-active"><input type="radio" name="rdoservice" id="rdoactive" value="active" onclick="reloadService(this);" checked />Active</div>
                    <div class="float-left"><input type="radio" name="rdoservice" id="rdodeleted" value="deleted" onclick="reloadService(this);" />Deleted</div>
                </div>
            </div>
        </div><?php        
    }
}
function UpdateUser()
{
    global $db, $pt, $ne, $load_headers;
    $thisreturn = "";
    $tempcheck = false;
    $thisfields = Array();
    $thistable = "users";
    $thisfield = $_POST['thisfield'];
    $thisserver = $load_headers -> GET_THIS_SERVER(); //This will be 'localhost' or the webhosting domain, ex:  https://www.somedomain.com
    if($_POST['thisfield'] == "birthday" || $_POST['thisfield'] == "hiredate")
    {
        $formatthisdate = date('Y-m-d', strtotime($_POST['thisvalue']));
        $thisdata = array($_POST['thisfield'] => $formatthisdate); 
    }
    else if($_POST['thisfield'] == "login" || $_POST['thisfield'] == 'email')
    {
        if($_POST['thisfield'] == "email")
        {
            //WE want to do a final check on email, make sure it is a valid email.
            if($ne ->validate_email($_POST['thisvalue']) == false)
            {
                $thisreturn = "Bad email format.  Please check email and try again.";
            }
        }
        if($_POST['thisfield'] == "login")
        {
        //Can't get this to work.
            $thisfield = [$_POST['thisfield']];
            $thiswhere = array($_POST['thisfield'] => $_POST['thisvalue']);
            $sqlcheck = $pt -> CheckIfexist($thistable, $thisfield, $thiswhere);
            if($sqlcheck)
            {
                $thisreturn = "This ".$_POST['thisfield']." already exist.  Please use a different one.";
            }
        }
        $thisdata[$_POST['thisfield']] = $_POST['thisvalue'];
    }
    else if($_POST['thisfield'] == "isTerminated")
    {
        //file_put_contents("./dodebug/debug.txt", "chk: ".$_POST['thisfield']." = ".$_POST['thisvalue']."\n", FILE_APPEND);
        if($_POST['thisvalue'] == "true")
        {
            $thisdata[$_POST['thisfield']] = true;
            $thisdata['isActive'] = false;
        }
        else
        {
            $thisdata[$_POST['thisfield']] = false;
            $thisdata['isActive'] = true;
        }
    }
    else if($_POST['thisfield'] == "isActive")
    {
        //file_put_contents("./dodebug/debug.txt", "chk: ".$_POST['thisfield']." = ".$_POST['thisvalue']."\n", FILE_APPEND);
        if($_POST['thisvalue'] == "true")
        {
            $thisdata[$_POST['thisfield']] = true;
        }
        else
        {
            $thisdata[$_POST['thisfield']] = false;
        }
    }
    else if($_POST['thisfield'] == "isAdmin")
    {
        //file_put_contents("./dodebug/debug.txt", "chk: ".$_POST['thisfield']." = ".$_POST['thisvalue']."\n", FILE_APPEND);
        if($_POST['thisvalue'] == "true")
        {
            $thisdata[$_POST['thisfield']] = true;
        }
        else
        {
            $thisdata[$_POST['thisfield']] = false;
        }
    }
    else
    {
        $thisdata[$_POST['thisfield']] = $_POST['thisvalue'];  
    }
    if($thisreturn == "")
    {
        $thiswhere = array("recno" => $_POST['thisrecno']);
        $result = $db->PDOUpdate($thistable, $thisdata, $thiswhere, $_POST['thisrecno']);
        //file_put_contents("./dodebug/debug.txt", var_dump($_POST), FILE_APPEND);
        if(isset($result))
        {
            $thisreturn = 'Success';
            
            //Since we successfully updated the system when it comes to terminating or setting inactive on an employee,
            //we want to email people who are admin status
            if($_POST['thisfield'] == "isActive" || $_POST['thisfield'] == "isTerminated")
            {
                $sentto = Array();
                $replyto = Array();
                $ccto = Array();
                $bccto = Array();
                $attachment = Array();
                $tempempname = "";
                $sql = "SELECT firstname, middlename, lastname, email FROM users WHERE isAdmin = true";
                $result = $db ->PDOMiniquery($sql);
                foreach($result as $rs)
                {
                    $sendto[] = array($rs['email'] => $rs['firstname'].empty($rs['middlename'] ? ' ' : $rs['middlename'])." ".$rs['lastname']);
                    //$sendto[] = array("14058890899@sms.smtp2go.com");
                }
                $sql = "SELECT firstname, middlename, lastname FROM users WHERE recno = ".$_POST['thisrecno'];
                $result = $db ->PDOMiniquery($sql);
                foreach($result as $rs)
                {
                    $tempempname = $rs['firstname'].empty($rs['middlename'] ? ' ' : $rs['middlename'])." ".$rs['lastname'];
                }
                if($_POST['thisfield'] == "isActive")
                {
                    $subject = $ne ->get_active_subject();
                    $body = $ne ->get_active_body($thisserver, $tempempname, $_POST['thisvalue']);
                }
                else
                {
                    $subject = $ne ->get_terminated_subject($thisserver, $tempempname);
                    $body = $ne ->get_terminated_body($thisserver, $tempempname, $_POST['thisvalue']);
                }
                $sendstatus = sendmail($sendto, $replyto, $ccto, $bccto, $subject, $body, $attachment);
            }
        }
        else
        {
            $thisreturn = 'Failed';
        }
    }
    echo $thisreturn;
}
function SearchUserunset()
{
    $_SESSION['usersearchlist'] = array();
    //file_put_contents("./dodebug/debug.txt", 'clearing session', FILE_APPEND);
}
function SearchServicerunset()
{
    $_SESSION['servicesearchlist'] = array();
    //file_put_contents("./dodebug/debug.txt", 'clearing session', FILE_APPEND);
}
function GetUsers()
{
    global $db;
    $thisfields = Array();
    $thiswheres = Array();
    $thisname = "";
    //QueryMe($thistype=null, $thistable=null, $thisfields=null, $thiswheres=null, $thisorderby=null, $thisgroupby=null, $ordering=null)
    $thistable = "users";
    $thisfields = array('All');
    //file_put_contents("./dodebug/debug.txt", $_POST['isActive']." and ".$_POST['isTerminated']."\n", FILE_APPEND);
    $sql = "SELECT * FROM users WHERE ";
    if($_POST['isActive'] == 'true' && $_POST['isTerminated'] == 'true')
    {
        $sql .= "isActive = true OR isTerminated = true";
    }
    else
    {
        if($_POST['isActive'] == 'true')
        {
            $sql .= "isActive = true AND ";
        }
        else
        {
            $sql .= "isActive = false AND ";
        }
   
        if($_POST['isTerminated'] == 'true')
        {
            $sql .= "isTerminated = true ";
        }
        else
        {
            $sql .= "isTerminated = false ";
        }
    }
    $result = $db->PDOMiniquery($sql);
    if($db ->PDORowcount($result) > 0) //Nott sure if isset() will check if some items is returned or at least something in asso array.
    {?>
       <table id="tbluserdata" class="tbl-admin-register float-left"><?php
        foreach($result as $rs)
        {
            $thisname = $rs['firstname'].(empty($rs['middlename']) ? " " : " ".$rs['middlename'])." ".$rs['lastname'];?>
            <tr>
                <td><span class="align-left float-left get-users cursor-pointer" id="spn_user_<?php echo $rs['recno'] ?>" onclick="getUserdata(this, <?php echo $rs['recno'] ?>);"><?php echo $thisname ?></span></td>
            </tr><?php
        }?>
       </table><?php 
    }
}
function GetUserdata()
{
    global $db;
    $thisfields = Array();
    $thiswheres = Array();
    //QueryMe($thistype=null, $thistable=null, $thisfields=null, $thiswheres=null, $thisorderby=null, $thisgroupby=null, $ordering=null)
    $thistable = "users";
    $thisfields = array('All');
    $thiswhere = array("recno" => $_POST['recno']);
    //$thiswhere = array("recno" => $_POST['recno']);
    $result = $db->PDOQuery($thistable, $thisfields, $thiswhere);
    if(isset($result)) //Nott sure if isset() will check if some items is returned or at least something in asso array.
    {
       foreach($result as $rs)
       {?>
            <table id="tbluserdata" class="tbl-admin-register">
                <tr>
                    <td class="tbl-admin-register-lbl">Employee Number: </td>
                    <td class="registrationinput"><input type="text" class="firstname required" id="txtemployeenumber" name="txtemployeenumber" value="<?php echo  $rs['employeenumber']; ?>" readonly /></td>
                </tr>
                <tr>
                    <td class="tbl-admin-register-lbl">First Name: <span class="asterisk"> * </span></td>
                    <td class="adminuserinput"><input type="text" class="firstname required" id="txtfirstname" name="txtfirstname" value="<?php echo  $rs['firstname']; ?>" onfocus="getVal(this);" onchange="updateUser(this, <?php echo $_POST['recno'] ?>);" required /></td>
                </tr>
                <tr>
                    <td class="tbl-admin-register-lbl">Middle Name: </td>
                    <td class="adminuserinput"><input type="text" class="middlename" id="txtmiddlename" name="txtmiddlename" value="<?php echo  $rs['middlename']; ?>" onfocus="getVal(this);" onchange="updateUser(this, <?php echo $_POST['recno'] ?>);" /></td>
                </tr>
                <tr>
                    <td class="tbl-admin-register-lbl">Last Name: <span class="asterisk"> * </span></td>
                    <td class="adminuserinput"><input type="text" class="lastname required" id="txtlastname" name="txtlastname" value="<?php echo  $rs['lastname']; ?>" onfocus="getVal(this);" onchange="updateUser(this, <?php echo $_POST['recno'] ?>);" /></td>
                </tr>
                <tr>
                    <td class="tbl-admin-register-lbl">Login: <span class="asterisk"> * </span></td>
                    <td class="adminuserinput"><input type="text" class="login" id="txtlogin" name="txtlogin" value="<?php echo  $rs['login']; ?>"  onfocus="getVal(this);" onchange="updateUser(this, <?php echo $_POST['recno'] ?>);" /></td>
                </tr>
                <tr>
                    <td class="tbl-admin-register-lbl">Birth Day: </td>
                    <td class="adminuserinput">&nbsp;<input type="text" class="birthday datepicker" id="txtbirthday" name="txtbirthday" size="20" onfocus="getVal(this);" value="<?php echo (empty($rs['birthday']) ? "" : date('m/d/Y', strtotime($rs['birthday']))); ?>" onfocus="getJDate(this);" onchange="updateUser(this, <?php echo $_POST['recno'] ?>);" placeholder="dd/mm/yyy ex: 01/22/2022" />dd/mm/yyyy ex: 01/22/2022</td>
                </tr>
                <tr>
                    <td class="tbl-admin-register-lbl">Hire Date: <span class="asterisk"> * </span></td>
                    <td class="adminuserinput">&nbsp;<input type="text" class="birthday" id="txthiredate" name="txthiredate" size="20" onfocus="getVal(this);" value="<?php echo  date('m/d/Y', strtotime($rs['hiredate'])); ?>" onfocus="getJDate(this);"`onchange="updateUser(this, <?php echo $_POST['recno'] ?>);" placeholder="dd/mm/yyy ex: 01/22/2022" />dd/mm/yyyy ex: 01/22/2022</td>
                </tr>
                <tr>
                    <td class="tbl-admin-register-lbl">Email: <span class="asterisk"> * </span></td>
                    <td class="adminuserinput"><input type="text" class="email required" id="txtemail" name="txtemail" value="<?php echo  $rs['email']; ?>" onfocus="getVal(this);" onchange="updateUser(this, <?php echo $_POST['recno'] ?>);" size="20" /></td>
                </tr>
                <tr>
                    <td class="tbl-admin-register-lbl">Address: </td>
                    <td class="adminuserinput"><input type="text" class="address" id="txtaddress" name="txtaddress" value="<?php echo  $rs['address']; ?>" onfocus="getVal(this);" onchange="updateUser(this, <?php echo $_POST['recno'] ?>);" /></td>
                </tr>
                <tr>
                    <td class="tbl-admin-register-lbl">City: </td>
                    <td class="adminuserinput"><input type="text" class="city " id="txtcity" name="txtcity" value="<?php echo  $rs['city']; ?>" onfocus="getVal(this);" onchange="updateUser(this, <?php echo $_POST['recno'] ?>);" /></td>
                </tr>
                <tr>
                    <td class="tbl-admin-register-lbl">State: </td>
                    <td class="adminuserinput"><input type="text" class="state" id="txtstate" name="txtstate" value="<?php echo  $rs['state']; ?>" onfocus="getVal(this);" onchange="updateUser(this, <?php echo $_POST['recno'] ?>);" /></td>
                </tr>
                <tr>
                    <td class="tbl-admin-register-lbl">Zip-code: </td>
                    <td class="adminuserinput"><input type="text" class="zipcode" id="txtzipcode" name="txtzipcode" value="<?php echo  $rs['zipcode']; ?>" onfocus="getVal(this);" onchange="updateUser(this, <?php echo $_POST['recno'] ?>);" /></td>
                </tr>
                <tr>
                    <td class="tbl-admin-register-lbl">Admin: </td>
                    <td class="adminuserinput"><input type="checkbox" class="admin-chkbox-active" id="chkisAdmin" name="chkisAdmin" onfocus="getVal(this);" onchange="updateUser(this, <?php echo $_POST['recno'] ?>);" <?php echo empty($rs['isAdmin']) ? '' : 'checked' ?> /></td>
                </tr>
                <tr>
                    <td class="tbl-admin-register-lbl">Active: </td>
                    <td class="adminuserinput"><input type="checkbox" class="admin-chkbox-active" id="chkisActive" name="chkisActive" onfocus="getVal(this);" onchange="updateUser(this, <?php echo $_POST['recno'] ?>);" <?php echo empty($rs['isActive']) ? '' : 'checked' ?> /></td>
                </tr>
                <tr>
                    <td class="tbl-admin-register-lbl">Terminate: </td>
                    <td class="adminuserinput"><input type="checkbox" class="admin-chkbox-terminate" id="chkisTerminated" name="chkisTerminated" onfocus="getVal(this);" onchange="updateUser(this, <?php echo $_POST['recno'] ?>);" <?php echo empty($rs['isTerminated']) ? '' : 'checked' ?> /></td>
                </tr>
            </table><?php 
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
    $thistable = "users";
    $thisfields = array('recno', 'firstname', 'middlename', 'lastname');
    $tempexplodeuser = explode(' ', $_POST['txtsearchuser']);
    //We get here when user entered just the first name, middle or last name.

    if($_POST['isActive'] == 'true' && $_POST['isTerminated'] == 'true')
    {
        $sql = "SELECT recno, firstname, middlename, lastname FROM users WHERE isActive = true OR isTerminated = true";
    }
    else
    {
        $sql = "SELECT recno, firstname, middlename, lastname FROM users WHERE ";
    
        if($_POST['isActive'] == 'true')
        {
            $sql .= "isActive = true ";
        }
        else
        {
            $sql .= "isActive = false ";
        }
        if($_POST['isTerminated'] == 'true')
        {
            $sql .= "AND isTerminated = true ";
        }
        else
        {
            $sql .= "AND isTerminated = false ";
        }
    }

    $result = $db ->PDOMiniquery($sql);
    
    $realname = "";?>   
    <select name="sltsearchuser" id="sltsearchuser" style="z-index: 999; width: 200px; height: 200px; position: absolute; text-align: left; margin-top: 40px; margin-left: -520px;"  size="5" onchange="hideSelectsearch();"><?php
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
                <option class="admin-search-user" onclick="getUserdata(this, <?php echo $rs['recno'] ?>);" value="<?php echo  $rs['recno']?>"><?php echo  $realname ?></option><?php 
           }
        }?>
    </select><?php
}
function SearchUserexistinglist($thisstr)
{
    //file_put_contents("./dodebug/debug.txt", "sessionlist: ".var_dump($_SESSION['usersearchlist']), FILE_APPEND);
    $realname = "";?>   
    <select name="sltsearchuser" id="sltsearchuser" style="z-index: 999; width: 200px; height: 200px; position: absolute; text-align: left; margin-top: 40px; margin-left: -520px;"  size="5" onchange="hideSelectsearch();"><?php
        foreach($_SESSION['usersearchlist'] as $rs => $value)
        {
           if(strpos(strtolower($value), strtolower($thisstr)) !== false)
           {?>
                <option  class="admin-search-user" onclick="getUserdata(this, <?php echo $rs ?>);" value="<?php echo $rs ?>"><?php echo $value ?></option><?php 
            }
        }?>
    </select><?php
}
function ManageUsers()
{
    ManageSearchmenus('Users');
    
}
function Main()
{
    global $load_headers;?>
    <div class="main-div"><?php
        $load_headers::Load_Header_Logo(false);?>
        <div class="main-div-body-admin">
            <table>
                <tr>
                    <td>
                        <div class="main-div-body-admin-left">
                            <div class="main-div-body-admin-header">DashBoard</div>
                            <div style="float: left;">
                                <div class="div-menu-dashboard" id="div_customer" onclick="manageUsers(this);">Search Customer</div>
                                <div class="div-menu-dashboard" id="div_services" onclick="manageServices(this);">Analyze Services</div>
                                <div class="div-menu-dashboard" id="div_revenues" onclick="addCompany(this);">Analyze Revenues</div>
                            </div> 
                        </div>
                    </td>
                    <td>
                        <div id="main_div_body_admin_right_container" class="main-div-body-admin-right-container"></div>
                    </td>
                </tr>
            </table>
        </div>
        <?php
        $load_headers::Load_Footer();?>
    </div><?php
}?>