<?php
require './square/vendor/autoload.php';

use Square\SquareClientBuilder;
use Square\Authentication\BearerAuthCredentialsBuilder;
use Square\Environment;
use Square\Exceptions\ApiException;
//getenv('SQUARE_ACCESS_TOKEN')
//file_put_contents('./dodebug/debug.txt', "autoload square?: here", FILE_APPEND);
$client = SquareClientBuilder::init()
  ->bearerAuthCredentials(
      BearerAuthCredentialsBuilder::init(
        getenv('SQUARE_ACCESS_TOKEN')
    )
  ) 
  ->environment(Environment::SANDBOX)    
  ->build();

try {

    $apiResponse = $client->getLocationsApi()->listLocations();

    if ($apiResponse->isSuccess()) {
        $result = $apiResponse->getResult();
        foreach ($result->getLocations() as $location) {
            printf(
                "%s: %s, %s, %s<p/>", 
                $location->getId(),
                $location->getName(),
                $location->getAddress()->getAddressLine1(),
                $location->getAddress()->getLocality()
            );
        }

    } else {
        $errors = $apiResponse->getErrors();
        foreach ($errors as $error) {
            printf(
                "%s<br/> %s<br/> %s<p/>", 
                $error->getCategory(),
                $error->getCode(),
                $error->getDetail()
            );
        }
    }

} catch (ApiException $e) {
    echo "ApiException occurred: <b/>";
    echo $e->getMessage() . "<p/>";
}


