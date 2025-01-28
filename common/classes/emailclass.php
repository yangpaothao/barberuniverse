<?php
/**
 * Description of newDateClass
 *
 * @author Yang Pao Thao
 */
class Email_Class {
    public $cmail;
    public $isdate = false;
    function set_email($email)
    {
        $this->cmail = $email;
    }
    function get_email()
    {
        return($this->cmail);
    }
    public function validate_email()
    {
        //We just want to make sure the email is in good format.
        if(filter_var($this->cmail, FILTER_VALIDATE_EMAIL))
        {
            $isdate = true;
        }      
        return($this->isdate);
    }
    public function check_email()
    {
        //Now we want to check if this email already exist. We will get a record back if it exists.
        return("SELECT recno FROM users WHERE email = '".$this->cmail."'");
    }
    public function get_verification_subject()
    {
        $subject = "Account verification sent From ".$_SESSION['companyname'];
        
        return($subject);
    }
    public function get_active_subject()
    {
        $subject = "Employee Active report";
        
        return($subject);
    }
    public function get_active_body($thisserver, $thisemployee, $status)
    {
        $body = "This is a system generated email sent to you by ".$_SESSION['companyname'].".  Please do not respond.</br>";
        $body .= "This email is to inform you that $thisemployee status has changed to ".($status == 'true' ? 'Active' : 'Inactive').".";
        return($body);
    }
    public function get_terminated_body($thisserver, $thisemployee, $status)
    {
        $body = "This is a system generated email sent to you by ".$_SESSION['companyname'].".  Please do not respond.</br>";
        $body .= "This email is to inform you that $thisemployee status has changed to ".($status == 'true' ? 'Terminated' : 'Un-Terminated').".";
        return($body);
    }
    public function get_terminated_subject()
    {
        $subject = "Employee Termination report";
        
        return($subject);
    }
    public function get_verification_passwordreset()
    {
        $subject = "Account password reset sent From ".$_SESSION['companyname'];
        
        return($subject);
    }
    public function get_verification_body($thisserver, $realvericode)
    {
        $body = "This is a system generated email sent to you by ".$_SESSION['companyname'].".  Please do not respond.</br>";
        $body .= "You received this email because your administrator created an account for you.  However, before you can login to<br/>";
        $body .= "the website, you must change your password.  Please follow the link below.<br><br>";
        $body .= "<a href='http://$thisserver/passwordreset.php?vericode=".$realvericode."'>Click here to change your password.</a>";
        
        return($body);
    }
    public function get_registerguest_body($thisserver)
    {
        $body = "This is a system generated email sent to you by ".$_SESSION['companyname'].".  Please do not respond.</br>";
        $body .= "You received this email because you registered for an account.  Please use your email to sign in <br/>";
        $body .= "the website, you must change your password.  Please follow the link below.<br><br>";
        $body .= "<a href='http://$thisserver/passwordreset.php?vericode=".$realvericode."'>Click here to change your password.</a>";
        
        return($body);
    }
    public function get_passwordreset_body($thisserver)
    {
        $body = "This is a system generated email sent to you by ".$_SESSION['companyname'].".  Please do not respond.</br>";
        $body .= "This email is to let you know that your password has been successfully changed.<br/>";
        $body .= "If you didn't take this action, please contact your administrator right away.<br/><br/>";

        
        return($body);
    }
    public function get_password_reset($thisserver, $realvericode)
    {
        $body = "Please do not reply to this email.<br />";
        $body .= "You receive this email from ".$_SESSION['companyname']." because a password reset was requested.<br />";
        $body .= "If you have not requested for a password change, please ignore this email, otherwise, please follow <br />";
        $body .= "the link below to reset your password.<br><br>";
        $body .= "<a href='http://$thisserver/passwordreset.php?vericode=".$realvericode."'>Click here to verify your email and change your password.</a>";
        return($body);
    }
    public function confirm_cancellation($date, $from)
    {
        if($from != "Barber")
        {
            $body = "Your appointment with ".$_SESSION['companyname']." on ".date('m/d/Y', strtotime($date))." has been cancelled.  You do not need to take any action.  Hope to see you soon.<br />";
            $body .= "If you didn't take this action, please contact ".$_SESSION['companyname']." @ ".$_SESSION['company_phonenumber']." as soon as possible.  Thank you.";
        }
        else
        {
            $body = "Your appointment with ".$_SESSION['companyname']." on ".date('m/d/Y', strtotime($date))." has been cancelled by your barber ".$_SESSION['user']." due to a unforseen personal event.<br />";
            $body .= "Please go to our website @ ".$_SESSION['thiswebsite']." and reschedule.  We apologize for this inconvenience.  Hope to see you soon.  Thank you.<br />";
        }
        return($body);
    }
}
