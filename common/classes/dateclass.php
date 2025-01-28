<?php
require_once("./common/pdocon.php");
$db = new PDOCON();

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

/**
 * Description of newDateClass
 *
 * @author Yang Pao Thao
 */
class Date_Class {
    public $cdate;
    public $isdate = false;
    function set_date($date)
    {
        $this->cdate = date('Y-m-d', strtotime($date));
    }
    function get_date()
    {
        return($this->cdate);
    }
    function validate_date()
    {
        $pattern = '/^([0-9]{1,2})\\/([0-9]{1,2})\\/([0-9]{4})$/';
        if (!preg_match($pattern, $this->cdate)) 
        {
            $isdate = true;
        }
        return($isdate);
    }
    public function compare_dates($cdate1, $cdate2, $cplace)
    {
        //Compare two dates $cdate1 <= $cdate2
        //dates should come in in format of yyyy-mm-dd string
        //$cplace will have value of  'Greater', and 'Equal'
        if($cplace == "Greater")
        {
            if(strtotime($cdate1) < strtotime($cdate2))
            {
                $isdate = true;
            }
        }
        else
        {
            if(strtotime($cdate1) <= strtotime($cdate2))
            {
                $isdate = true;
            }
        }     
        return($isdate);
    }
    public function GetHolidays($returnthis)
    {
        global $db;
        //$returnthis 
        //- Dates, will return just the date in a single array such as array('01-01-2024', ....)
        //- All
        //Get a list of approved off days including holidays.
        //Excluding weekends
        $datetracker = 0;
        $datearray = [];
        $sql = "SELECT * FROM holidays WHERE datetype = 'HOL' AND isDeleted = false ORDER BY dates";
        $result = $db ->PDOMiniquery($sql);
        if($db ->PDORowcount($result) > 0)
        {
            foreach($result as $rs)
            {
                if($returnthis == 'Dates')
                {
                    $datetracker++;
                    $datearray[] = $rs['dates'];
                }
                else
                {
                    $datetracker++;
                    $datearray[] = array($rs['dates'] => $rs['type']."|".$rs['description']);
                    //array would be something like = ('dates' => 'type|descriptions', ....);
                }
            }
        }
        if($datetracker > 0)
        {
            return($datearray);
        }
        else
        {
            return(null);
        }
    }
    public function get_holidays_ele($thisdate, $thisfield)
    {
        //$thisdate will be the date 01/22/1982
        //$datetype will be the fields in the table, dates, datetype, description, isDeleted
        global $db;
    
        $sql = "SELECT $thisfield FROM holidays WHERE dates = '".date('Y-m-d', strtotime($thisdate))."' AND isDeleted = false";
        $result = $db-> PDOMiniquery($sql);
        if($db -> PDORowcount($result) > 0)
        {
            foreach($result as $rs)
            {
                return($rs["$thisfield"]);
            }
        }
        else
        {
            return(null);
        }
    }
    public function get_holiday_dates($thiscuryear)
    {
        //Federal holidays
        //The reason for this function is to get the holidays for a year that is in the future
        //that has not yet been entered into the holidays table.  We do this because some holidays are
        //not on the same day each year.  Also because some holidays may not be observed.
        
        //Now before we do anything, we want to query table holidays to see if it is already in the table, if not, we will want to insert to the table.
        global $db;
        $holidayarray = [];
        $sql = "SELECT * FROM holidays WHERE dates LIKE '$thiscuryear%' AND datetype = 'HOL' AND isDeleted = false";
        //file_put_contents('./dodebug/debug.txt', "\n what is year in get_holiday_dates?: \n".$thiscuryear, FILE_APPEND);
        //file_put_contents('./dodebug/debug.txt', "\n what is sql in get_holiday_dates?: \n".$sql, FILE_APPEND);
        $result = $db-> PDOMiniquery($sql);
        if($db -> PDORowcount($result) == 0)
        {
            //If we are here, that means it's the first time the user tries to do anything that would pull or check for the holidays and it hasn't
            //been entered into the table yet so we need to enter into it.
            $thistable = 'holidays';
            $thisfields = ["dates", "datetype", "description"];
            $thisdata = array("dates" => date('Y-01-01', strtotime("first day of january $thiscuryear")), 'datetype' => 'HOL', 'description' => "New Year's Day");  
            $db->PDOInsert($thistable, $thisdata);
            
            $thisdata = array("dates" => date('Y-m-d', strtotime("third monday of January $thiscuryear")), 'datetype' => 'HOL', 'description' => "Martin Luther King, Jr. Day");  
            $db->PDOInsert($thistable, $thisdata);
            
            $thisdata = array("dates" => date('Y-m-d', strtotime("third monday of February $thiscuryear")), 'datetype' => 'HOL', 'description' => "Washington's Birthday");  
            $db->PDOInsert($thistable, $thisdata);

            $thisdata = array("dates" => date('Y-m-d', strtotime("last monday of may $thiscuryear")), 'datetype' => 'HOL', 'description' => "Memorial Day");  
            $db->PDOInsert($thistable, $thisdata);

            $thisdata = array("dates" => date('Y-m-d', strtotime("$thiscuryear-06-19")), 'datetype' => 'HOL', 'description' => "Juneteenth National Independence Day");  
            $db->PDOInsert($thistable, $thisdata);

            $thisdata = array("dates" => date('Y-m-d', strtotime("$thiscuryear-07-04")), 'datetype' => 'HOL', 'description' => "Independence Day");  
            $db->PDOInsert($thistable, $thisdata);

            $thisdata = array("dates" => date('Y-m-d', strtotime("first monday of september $thiscuryear")), 'datetype' => 'HOL', 'description' => "Labor Day");  
            $db->PDOInsert($thistable, $thisdata);

            $thisdata = array("dates" => date('Y-m-d', strtotime("Second Monday of October $thiscuryear")), 'datetype' => 'HOL', 'description' => "Columbus Day");  
            $db->PDOInsert($thistable, $thisdata);
            
            $thisdata = array("dates" => date('Y-m-d', strtotime("$thiscuryear-11-11")), 'datetype' => 'HOL', 'description' => "Veterans Day");  
            $db->PDOInsert($thistable, $thisdata);

            $thisdata = array("dates" => date('Y-m-d', strtotime("November $thiscuryear fourth thursday")), 'datetype' => 'HOL', 'description' => "Thanksgiving Day");  
            $db->PDOInsert($thistable, $thisdata);

            $thisdata = array("dates" => date('Y-m-d', strtotime("$thiscuryear-12-25")), 'datetype' => 'HOL', 'description' => "Christmas Day");  
            $db->PDOInsert($thistable, $thisdata);
            get_holiday_dates($thiscuryear); //After we submitted the holidays for the first time, we will call the function again so we can pull the dates and send it back.
        }
        else
        {
            foreach($result as $rs)
            {
                $datearray[] = $rs['dates'];
                
            }
            return($datearray);
        }
    }
}
