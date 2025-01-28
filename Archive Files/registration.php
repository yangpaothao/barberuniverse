<?php
require("./common/page.php");?>
<!DOCTYPE html>
<html>
    <head>
        <?php
            $load_headers = new Page_Loader();
            $temp_host = filter_input(INPUT_SERVER, 'SERVER_NAME'); // will get 'localhost'
            $temp_page = filter_input(INPUT_SERVER, 'PHP_SELF'); // will look like /index.php or /somedir/somepage.php
            $explode_page = explode("/", $temp_page); //This variable will now be an array and the page name is the last element of this array
            $this_page = end($explode_page); //this variable will hold the page name like index.php
            $load_headers::Load_Header(strtok($this_page, ".")); //by using strtok($this_page, "."), we will get just 'index'.
        ?>
    </head>
    <body>
        <?php
            Main();
        ?>
    </body>
</html>
<?php
function Main()
{
    global $load_headers;?>
    <div class="main-div">
        <?php
        $load_headers::Load_Header_Logo_Marquee();?>
        <br><br> <?php
        $load_headers::Load_Header_Logo_Main();?>
        <br>
        <div class="main-div-body">
            <form name="frmregistration" id="frmregistration" method="post">
                <div>
                    <div class="registration-header">
                        Registration
                    </div>
                    <div class="registration">
                        <div class="registrationlbl">First Name: </div>
                        <div class="registrationinput"><input type="text" class="firstname required" id="txtfirstname" name="txtfirstname" value="" onchange="loadLogin(this);" autofocus required /></div>
                    </div>
                    <div class="registration">
                        <div class="registrationlbl">Middle Name: </div>
                        <div class="registrationinput"><input type="text" class="middlename" id="txtmiddlename" name="txtmiddlename" value="" /></div>
                    </div>
                    <div class="registration">
                        <div class="registrationlbl">Last Name: </div>
                        <div class="registrationinput"><input type="text" class="lastname required" id="txtlastname" name="txtlastname" value="" onchange="loadLogin(this);" /></div>
                    </div>
                    <div class="registration">
                        <div class="registrationlbl">Login: </div>
                        <div class="registrationinput"><input type="text" class="login" id="txtlogin" name="txtlogin" value="" onchange="loadLogin(this);"  /></div>
                    </div>
                    <div class="registration">
                        <div class="registrationlbl">Birth Day: </div>
                        <div class="registrationinput">&nbsp;<input type="text" class="birthday" id="txtbirthday" name="txtbirthday" value="" onchange="checkDate(this);" placeholder="dd/mm/yyy ex: 01/22/2022" />dd/mm/yyy ex: 01/22/2022</div>
                    </div>
                    <div class="registration">
                        <div class="registrationlbl">Email: </div>
                        <div class="registrationinput"><input type="text" class="email required" id="txtemail" name="txtemail" value="" onchange="validateEmail();" size="16" /></div>
                    </div>
                    <div class="registration">
                        <div class="registrationlbl">Address: </div>
                        <div class="registrationinput"><input type="text" class="address" id="txtaddress" name="txtaddress" value="" /></div>
                    </div>
                    <div class="registration">
                        <div class="registrationlbl">City: </div>
                        <div class="registrationinput"><input type="text" class="city " id="txtcity" name="txtcity" value="" /></div>
                    </div>
                    <div class="registration">
                        <div class="registrationlbl">State: </div>
                        <div class="registrationinput"><input type="text" class="state" id="txtstate" name="txtstate" value="" /></div>
                    </div>
                    <div class="registration">
                        <div class="registrationlbl">Zip-code: </div>
                        <div class="registrationinput"><input type="text" class="zipcode" id="txtzipcode" name="txtzipcode" value="" /></div>
                    </div>
                    <div class="div-buttons">
                        <button type="submit" form="frmregistration" value="Submit" onclick="submitRegistrationform();">Submit</button>
                        <button value="Cancel" onclick="clearRegistrationform();">Clear</button>
                    </div>
                </div>
            </form>
        </div>
    </div><?php
}