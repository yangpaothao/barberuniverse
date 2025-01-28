<?php
    require_once("./page.php");
    require_once("./pdocon.php");
    $apiKey = "gAQm6Gz1h4nNN23sO7nKitfq1OMczW53";
    $url = "https://aeroapi.flightaware.com/aeroapi/";
    
    $db = new PDOCON();
    $thisday = date('m/d/Y');
    $thistomorrow = date('m/d/Y', strtotime('+1 day'));
    $tempdatearray = array($thisday, $thistomorrow);
    for($i=0; $i<count($tempdatearray); $i++)
    {
        //First, we want to refresh flow with schedule flight, we want to insert what is in schedule flight that is not yet into flow.
        //$sql = "SELECT fs.* FROM flight_schedule fs LEFT OUTER JOIN flow f ON fs.customer = f.customer AND fs.actype = f.actype AND fs.flightnumber = f.flightnumber ";
        //$sql .= "WHERE fs.dates LIKE '%$tempdatearray[$i]%' AND fs.isdeleted = false AND f.date IS NULL ";
        //$sql .= "AND (fs.dates LIKE '%$tempdatearray[$i]%' AND fs.isdeleted = false AND f.date = '".date('Y-m-d', strtotime($tempdatearray[$i]))."')";
        
        $sql = "SELECT fs.* FROM flight_schedule fs LEFT OUTER JOIN flow f ON fs.customer = f.customer AND fs.actype = f.actype AND fs.flightnumber = f.flightnumber ";
        $sql .= "WHERE fs.dates LIKE '%$tempdatearray[$i]%' AND fs.isdeleted = false AND f.date IS NULL";
        //file_put_contents("./dodebug/debug.txt", $sql."===", FILE_APPEND);
        $result = $db->PDOMiniquery($sql);
        foreach($result as $rs)
        {
            $thistable = "flow";
            $thisdata = array("customer" => $rs['customer'], "actype" => $rs['actype'], "flightnumber" => $rs['flightnumber'], "date" => date('Y-m-d', strtotime($tempdatearray[$i])),
                            "schedulearrival" => ($rs['schedulearrival'] != NULL) ? $rs['schedulearrival'] : NULL, 
                            "scheduledeparture" => ($rs['scheduledeparture'] != NULL) ? $rs['scheduledeparture'] : NULL);
            $db->PDOInsert($thistable, $thisdata);
            
            $thistable = "service_orders";
            $thisdata = array("customer" => $rs['customer'], "actype" => $rs['actype'], "flightnumber" => $rs['flightnumber'], "date" => date('Y-m-d', strtotime($tempdatearray[$i])),
                            "schedulearrival" => ($rs['schedulearrival'] != NULL) ? $rs['schedulearrival'] : NULL, 
                            "scheduledeparture" => ($rs['scheduledeparture'] != NULL) ? $rs['scheduledeparture'] : NULL);
        }
    }
    $sql = "SELECT flow.*, cm.icaocode FROM flow JOIN customer_master cm ON flow.customer=cm.customer ";
    $sql .= "WHERE flow.date BETWEEN '".date('Y-m-d', strtotime($thisday))."' AND '".date('Y-m-d', strtotime($thistomorrow))."' AND isdeparted=false AND isdeleted = false ORDER BY date, customer";
    //file_put_contents("./dodebug/debug.txt", $sql."===", FILE_APPEND);
    $result = $db->PDOMiniquery($sql);
    $identarray = [];
    if(isset($result))
    {
        
        foreach($result as $rs)
        {
            $identarray[] = $rs['icaocode'].$rs['flightnumber']; //We would get ex: CPA36            
        }
    }
    $explodeident = "{ ident{".implode(" ", $identarray)."}}";
    //echo $explodeident;
        $queryParams = array(
        
        'start' => date('Y-m-d'),
        'end' => date('Y-m-d', strtotime($thistomorrow))
    );
    //At this point we should get an array = ['flight1', 'flight2',...,'flightn'];
    
    //$thisflight = 'CPA520';
    //$url = $url . 'flights/' . $thisflight . '?' . http_build_query($queryParams);
    

    $url = $url . 'SearchBirdseyeInFlight/' .$explodeident.'?'. http_build_query($queryParams);
    echo $url;
    //echo $url;
    //return(false);
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $headers = array(
    "Accept: application/json; charset=UTF-8",
    "x-apikey: $apiKey",
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    $resp = curl_exec($curl);
    curl_close($curl);
    echo $resp;

?>
    

