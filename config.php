<?php
// SQL / DB Credentials
$CRED_SERV_NAME = "";
$CRED_USER = "";
$CRED_PWD = "";
$CRED_DBNAME = "";
/////////////////////////////////////////////////////////////////////////////////////////////////
$CONTACT_EMAIL = "pizza@juleskreuer.eu";

$DATE = "04.07.2022 11-14 Uhr"; // Date/Time of the event. (Human readable)
$LOCATION = "Sand Kitchen A104"; // Location of the event.
$BASE_PRICE = 2.5;   // in € 

// Time of the event //   Hour  Min  Sec  Month Day   Year
$TIMESTAMP_START = mktime('11', '0', '0', '07', '04', '2022');
$TIMESTAMP_STOP =  mktime('14', '0', '0', '07', '04', '2022');
$SLOTS = 24;         // Number of order-slots: 3h, 18 Slots -> 10min per slot

// Time of the last possible order
$TIMESTAMP_LAST_ORDER = mktime('6', '0', '0', '07', '04', '2022');

$PIZZA_TIME = 5; // Time needed for one pizza in minutes.
$ADDITIONAL_TIME = 2.5; // time needed for any additional pizza.
$BLOCK_OVERLAPPING_ORDERS = True; // Do not allow orders if there are too many others
$MAX_NUMBER_INGREDIENTS = 6; // Maximum number of ingredients.

/////////////////////////////////////////////////////////////////////////////////////////////////
$SLOT_WIDTH = ($TIMESTAMP_STOP-$TIMESTAMP_START)/$SLOTS;

function createConn($CRED_SERV_NAME, $CRED_USER, $CRED_PWD, $CRED_DBNAME) {
    // Creates a standart DB connection.
    $conn = new mysqli($CRED_SERV_NAME, $CRED_USER, $CRED_PWD, $CRED_DBNAME);
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}
?>