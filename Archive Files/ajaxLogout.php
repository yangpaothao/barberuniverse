<?php
    require("./common/page.php");
    if(isset($_SESSION))
    {
        session_unset();
        session_destroy();
        echo 'Success';
    }
    else
    {
       echo 'Failed'; 
    }
    
?>
