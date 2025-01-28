function checkPassword(obj){
    $('body').data('txtpassword', $(obj).val());
    var regex = /^(?=.*[0-9])(?=.*[!@#$%^&*])[a-zA-Z0-9!@#$%^&*]{6,16}$/;
    if($(obj).val().length < 8 && !regex.test($(obj).val())){
        alert("Make sure your password meets the minimum requirements.");
        $(obj).select();
        return(false);
    }
    else{
        return(true);
    }
}
function validateLogin(obj){
    if($(obj).val().length < 3){
        alert('Login must be atleast 3 characters long.');
        $(obj).focus();
        return(false);
    }
    else{
        return(true);
    }
}
function checkConfirmpassword(obj){
    var regex = /^(?=.*[0-9])(?=.*[!@#$%^&*])[a-zA-Z0-9!@#$%^&*]{6,16}$/;
    if($(obj).val().length < 8 && !regex.test($(obj).val())){
        alert("Make sure your confirm password meets the minimum requirements.");
        $(obj).select();
        return(false);
    }
    if($('body').data('txtpassword') != "" && $(obj).val() != ""){
        if($('body').data('txtpassword') != $(obj).val()){
            alert("Password does not match, please try again.");
            $(obj).select();
            return(false);
        }
    }
    else{
        return(true);
    }
}
function getJDate(obj, isAlert){
    $(obj).datepicker({
        dateFormat: "mm/dd/yy",
        changeMonth: true,
        changeYear: true,
        onClose: function(){
            checkDate(obj, isAlert);
        }
    }).datepicker("show"); 
}
function checkDate(obj, isAlert){
    regexdate = /(0\d{1}|1[0-2])\/([0-2]\d{1}|3[0-1])\/(19|20)\d{2}/;
    if(!regexdate.test($(obj).val())){
        if(isAlert == true){
            alert('You have entered an invalide date.  Please check and try again.');
        }
        $(obj).val('');
        $(obj).focus();
        return(false);
    }
    else{
        return(true);
    }
}
function validateEmail(obj){
    //alert($(obj).val());
    let regex = /^((?!\.)[\w\-_.]*[^.])(@\w+)(\.\w+(\.\w+)?[^.\W])$/;
            ///^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;

    if(!regex.test($(obj).val())){
        alert("Please enter a valid email.");
        return(false);
    }
    else{
        return(true);
    }
}
function checkTime(obj){
    thistime = $(obj).val();
    if(thistime.indexOf(':') == -1)
    {
        temptime1 = thistime.substr(0,2);
        temptime2 = thistime.substr(2);
        thistime = temptime1+":"+temptime2;
    }
    var regexp = /^(?:[01][0-9]|2[0-3]):[0-5][0-9](?::[0-5][0-9])?$/;
    if(regexp.test(thistime) == false){
        alert('Please enter a correct time in format of military time 00:00 or 0000 between 00:00 - 23:59.');
        $(obj).val($('body').data($(obj).prop('id')));
        $(obj).focus();
        return(false);
    } 
    else{
        $(obj).val(thistime);
    }
        
}
function saveThisdata(obj){
    $('body').data($(obj).prop('id'), $(obj).val());
}
function isNumbercheck(obj){
    if(!$.isNumeric($(obj).val())){
        alert('Please enter an interger value.');
        $(obj).val();
        return(false);
    }
    else{
        return(true);
    }
}
function isPhonenumber(obj){
    if(obj.length != 10){
        alert("Make sure you enter a valid phone#.");
        return(false);
    }
}
