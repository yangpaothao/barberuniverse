<?php
class PROMPT 
{
    //PDOQuery($thistable=null, $thisfields=null, $thiswhere=null, $thisorderby=null, $thisgroupby=null, $ordering=null, $ons=null, $distinct=null)
    private $db = null;
    private $thisarray = [];
    function __construct() 
    {
        $this->db = $this->GetDBcon();
    }
    private function GetDBcon()
    {
        return new PDOCON();  //Return a connection
    }
    function SltService()
    {
        $this->thisarray = [];
        $thistable = "service";
        $thisfields = array("recno", "title", "time", "price");
        $thiswheres = array("isactive" => true, "isdeleted" => false);
        //->PDOQuery($thistable, $thisfields, $thiswhere);
        $result = $this->db->PDOQuery($thistable, $thisfields, $thiswheres);
        $tempname = "";
        if(!is_null($result))
        {
            foreach($result as $rs)
            {
                //file_put_contents("./dodebug/debug.txt", 'recno: '.$rs['recno'].' AND name: '.$tempname, FILE_APPEND);
                $this->thisarray[$rs['recno']]= $rs['title']." ".$rs['time']."mins/$".number_format($rs['price'], 2);
                //$this->thisarray is now a multi array
            }
        }
        return($this);
    }
    function ConvertMilToHr($thishour)
    {
        return(date('h:m', strtotime($thishour)));
    }
    function ConvertHourToMinute($thishour)
    {
        if(strpos($thishour, ":") !== false)
        {
            $xplodenumb = explode(":", $thishour);
            
            return((60 * (int)$xplodenumb[0]) + (int)$xplodenumb[1]);
        }
        else
        {
            return($thishour * 60);
        }
        
        
    }
    function ConvertMinToHour($thisminutes)
    {
        //return(intdiv($thisminutes, 60).":".($thisminutes % 60));
        if($thisminutes < 60)
        {
            return("00:$thisminutes");
        }
        else
        {
            return(date('h:i', strtotime(intdiv($thisminutes, 60).":".($thisminutes % 60))));
        }
    }
    function SltCustomer($thisvalue=null)
    {
        //if $thisvaslue has no parameter coming in then we assume default to recno as the KEY, ex key = value
        //and if there is a parameter, it has to be a field that user wants to use as value.    //value = value,
        
        $this->thisarray = [];
        $thistable = "customer_master";
        $thisfields = array("recno", "customer");
        $thiswheres = array("iscargo" => true, "isactive" => true);
        //->PDOQuery($thistable, $thisfields, $thiswhere);
        $result = $this->db->PDOQuery($thistable, $thisfields, $thiswheres, array('customer'), null, null, null, 'DISTINCT');
        $tempname = "";
        $tempkey = $thisvalue;
        if(!is_null($result))
        {
            foreach($result as $rs)
            {
                $tempname = $rs['customer'];
                if(is_null($thisvalue))
                {
                    $tempkey = 'recno';
                }
                //file_put_contents("./dodebug/debug.txt", 'recno: '.$rs['recno'].' AND name: '.$tempname, FILE_APPEND);
                $this->thisarray[$rs[$tempkey]]= $tempname;
                //$this->thisarray is now a multi array
            }
        }
        return($this);
    }
    function JSONEncode($data)
    {
        return(json_encode($data, true));
    }
    function JSONDecode($data)
    {
        return(json_decode($data));
    }
    function GetSelect($thisid, $thisdefault, $isrequired, $ismultiple, $thisonchange="", $isshown=true, $isdisable=false, $isdummy = false)
    {
        //string - $thisid - will be the id of this element
        //      if it has more than 1 item in this array, that means we want to combined the values into one string, firstg ex: string1 + string2 + string3 = string1string2string3.
        //      if there is only 1 item in this array, that means we will juse ust the value of this field as is for the text.
        //string - $thisdefault - is if there is a default select that user wants, it can be a string fo something or "", empty.
        //boolen - $isrequired - if this feild is required.
        //string - $thisonchange - the onchange function in format of nameoffunction(parameter1, parameter2,......,paramtern)
        //$isdummy - will include the default select
        
        $tempquired = "";
        $temponchange = "";
        $tempmultiple = "";
        $tempdisabled = "";
        $ishowing = "";
        $tempdefault = array();
        if($ismultiple == true)
        {
            $tempmultiple = "multiple='multiple'";
        }
        if($thisonchange != "")
        {
            $temponchange = "onchange='$thisonchange'";
        }
        if($isrequired == true)
        {
            $tempquired = 'required';
        }
        if($thisdefault != "")
        {
            $tempdefault = explode(',', $thisdefault);
        }
        if($isdisable == true)
        {
            $tempdisabled = 'disabled';
        }
        if($isshown == true)
        {
            $ishowing = "display: none;";
        }?>
        <select class="promp-select2 <?=$tempquired?>" style="width: 100%; height: 80%; white-space: nowrap; <?php echo $ishowing ?>" id="<?=$thisid?>" name="<?=$thisid?>"  <?=$temponchange?> <?=$tempmultiple?> <?=$tempdisabled?>><?php
            if($isdummy == true)
            {?>
                <option value="Select">Select</option><?php
            }
            foreach($this->thisarray as $key => $value)
            {
                $tempselect = "";
                if(in_array($key, $tempdefault))
                {
                    $tempselect = "selected";
                }?>
                <option value="<?=$key?>" <?=$tempselect?>><?=$value?></option><?php
            }?>
        </select><?php
    }
    function GetString($thisdefault)
    {
        //string - $thisid - will be the id of this element
        //      if it has more than 1 item in this array, that means we want to combined the values into one string, firstg ex: string1 + string2 + string3 = string1string2string3.
        //      if there is only 1 item in this array, that means we will juse ust the value of this field as is for the text.
        //string - $thisdefault - is if there is a default select that user wants, it can be a string fo something or "", empty.
        //boolen - $isrequired - if this feild is required.
        //string - $thisonchange - the onchange function in format of nameoffunction(parameter1, parameter2,......,paramtern)
        $tempdefault = [];
        if($thisdefault != "")
        {
            $tempdefault = explode(',', $thisdefault);
        }
        $tempname = "";
        foreach($this->thisarray as $key => $value)
        {
            $tempselect = "";
            if(in_array($key, $tempdefault))
            {
                if($tempname == "")
                {
                    $tempname = "$value";
                }
                else
                {
                    $tempname .= ", $value";
                }
            }
        }
        echo $tempname;
    }
    function GetStates($thisstate)
    {
        $statearray = [
                        'Alaska' => 'AK', 
                        'Arkansas' => 'AR',
                        'American Samoa' => 'AS',
                        'California' => 'CA',
                        'Colorado' => 'CO',
                        'Connecticut' => 'CT',
                        'District of Columbia' => 'DC',
                        'Georgia' => 'GA',
                        'Florida' => 'FL',
                        'Guam' => 'GU',
                        'Hawaii' => 'HI',
                        'Iowa' => 'IA',
                        'Idaho' => 'ID',
                        'Illinois' => 'IL',
                        'Indiana' => 'IN',
                        'Kansas' => 'KS',
                        'Kentucky' => 'KY',
                        'Louisiana' => 'LA',
                        'Massachusetts' => 'MA',
                        'Maryland' => 'MD',
                        'Maine' => 'ME',
                        'Michigan' => 'MI',
                        'Minnesota' => 'MN',
                        'Missouri' => 'MO',
                        'Mississippi' => 'MS',
                        'Montana' => 'MT',
                        'North Carolina' => 'NC',
                        'North Dakota' => 'ND',
                        'New Hampshire' => 'NH',
                        'New Jersey' => 'NJ',
                        'New Mexico' => 'NM',
                        'Nevada' => 'NV',
                        'New York' => 'NY',
                        'Ohio' => 'OH',
                        'Oklahoma' => 'OK',
                        'Oregon' => 'OR',
                        'Pennsylvania' => 'PA',
                        'Puerto Rico' => 'PR',
                        'Rhode Island' => 'RI',
                        'South Carolina' => 'SC',
                        'South Dakota' => 'SD',
                        'Tennessee' => 'TN',
                        'Texas' => 'TX',
                        'Northern Mariana Islands' => 'MP',
                        'Utah' => 'UT',
                        'Virginia' => 'VA',
                        'Virgin Islands' => 'VI',
                        'Vermont' => 'VT',
                        'Washington' => 'WA',
                        'Wisconsin' => 'WI',
                        'West Virginia' => 'WV',
                        'Wyoming' => 'WY'];
        
    
        if(strlen($thisstate) > 2)
        {
            foreach($statearray as $tempstate => $tempabb){
                if(strtolower($tempstate) == strtolower($thisstate))
                {
                    //file_put_contents('./dodebug/debug.txt', " 1promp temp state: $tempabb \n", FILE_APPEND);
                    return($tempabb);
                }
            }
        }
        else
        {
            file_put_contents('./dodebug/debug.txt', "3promp temp state: not here \n", FILE_APPEND);
            if (in_array($thisstate, $statearray)) {
                return($thisstate);
            }
            else
            {
                return("Bad State.");
            }
        }
    }
    function CheckIfexist($thistable, $thisfields, $thiswhere)
    {
        $result = $this->db->PDOQuery($thistable, $thisfields, $thiswhere);

        if(!is_null($result))
        {
            //file_put_contents("./dodebug/debug.txt", "admin check: here \n", FILE_APPEND);
            return(true); //exists
        }
        else
        {
            //file_put_contents("./dodebug/debug.txt", "admin check: !here \n", FILE_APPEND);
            return(false); //does not exist
        }
    }
    function UploadFile($filepath, $thisfile, $thistable, $thisfield, $thisrecno)
    {
        //$thisfile will come in as $_FILES["thisfile"], to be sure it will be countable below, in the declaration, make it a file
        //$filepath will be the path to the dir
        //$thistable will be the table we want to update the file name to
        //$thisfield is the field we will update with the file
        //$thisrecno is the recno of $thistable
        
        $countfiles = count($thisfile['name']); 
        $strattachments = "";  
        $typeisgood = "";
        for($i=0;$i<$countfiles;$i++)
        {
            $filename = $thisfile['name'][$i];
            //Now that we have the $filename, we can check for the file type and size and all the goodies.
            $allowedTypes = array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF);
            $detectedType = exif_imagetype($thisfile['tmp_name'][$i]);
            if(in_array($detectedType, $allowedTypes))
            {
               if($strattachments == "")
                {
                    $strattachments = $filename;
                }
                else
                {
                    $strattachments .= ",$filename";
                }
                move_uploaded_file($thisfile['tmp_name'][$i],"$filepath/$filename");
            }
            else
            {
                $typeisgood = "BAD";
            }
        }
        if($typeisgood != "BAD")
        {
            $thisdata = array($thisfield => $filename);
            $thiswheres = array('recno' => $thisrecno);
            $result = $this->db->PDOUPDATE($thistable, $thisdata, $thiswheres, $thisrecno);
            //file_put_contents("../dodebug/debug.txt", "not here 1", FILE_APPEND);
            if($result)
            {
                echo 'Success';
            }
            else
            {
                echo 'Failed';
            }
        }
        else
        {
            echo "Bad file type.  File type must be PNG, JPEG or GIF.";
        }
    }
    function PostIt($thispost)
    {
        //PostIt is a function that will return an associative array with non-empty values and substring first 3 chars
        $thisdata = [];
        foreach($thispost as $key => $value)
        {
            //file_put_contents("./dodebug/debug.txt", "admin company = $key == $value \n", FILE_APPEND);
            if($value != "" and $key != "cmd")
            {
                $thisfield = substr($key, 3);
                $thisdata[$thisfield] = $value;
            }
        }
        return($thisdata);
    }
}?>