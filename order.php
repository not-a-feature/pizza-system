<?php
require_once("config.php");
error_reporting(E_ALL);

function insertDB($name, $email, $ingredients, $time, $remark) {
    // Inserts an order into the DB.
    global $CRED_SERV_NAME, $CRED_USER, $CRED_PWD, $CRED_DBNAME;
    $conn = createConn($CRED_SERV_NAME, $CRED_USER, $CRED_PWD, $CRED_DBNAME);
    $stmt = $conn->prepare("INSERT INTO orders (name, email, ingredients, time, remark) VALUES (?, ?, ?, ?, ?);");
    $stmt->bind_param('sssis', $name, $email, $ingredients, $time, $remark);
    $res = $stmt->execute();
    $conn->close();
    return $res;
}

function checkOccupancy($time) {
    // Checks if too many order were placed in one timesplot
    // returns False if too many other orders are present.

    global $CRED_SERV_NAME, $CRED_USER, $CRED_PWD, $CRED_DBNAME;
    global $BLOCK_OVERLAPPING_ORDERS, $SLOT_WIDTH, $PIZZA_TIME, $ADDITIONAL_TIME;
   
    if (!$BLOCK_OVERLAPPING_ORDERS) {
        return TRUE;
    }
    $conn = createConn($CRED_SERV_NAME, $CRED_USER, $CRED_PWD, $CRED_DBNAME);
    $stmt = $conn->prepare("SELECT count(uid) AS count FROM orders WHERE time = ?;");
    $stmt->bind_param('i', $time);
    $stmt->execute();
    $result = $stmt->get_result();
    $occupancy = $result->fetch_all(MYSQLI_ASSOC)[0]["count"];
    $conn->close();
    
    return ($PIZZA_TIME + $occupancy*$ADDITIONAL_TIME) <= $SLOT_WIDTH/60;
}
function sendMail($recipient, $time) {
    // Sends a confirmation mail.
    global $CONTACT_EMAIL;
    $subject = "Pizza Order " . date('Y-m-d H:i', $time);
    $msg = "Your pizza order was placed. Please pick it up at " . date('Y-m-d H:i', $time);
    $headers = "From:" . $CONTACT_EMAIL;
    mail($recipient,$subject,$msg,$headers);
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if ($TIMESTAMP_LAST_ORDER < time()) {
        // too late
        header("Location: /index.php?order=0");
        exit();
    } 

    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $ingredients = "";

    if (isset($_POST["ingredients"])) {
        $ingredients = array_map('intval', $_POST["ingredients"]);
        $ingredients = implode(',', $ingredients);
    }
    $time = filter_input(INPUT_POST, 'pickup-time', FILTER_SANITIZE_NUMBER_INT);
    $time = intval($time);
    $remark = filter_input(INPUT_POST, 'remark', FILTER_SANITIZE_SPECIAL_CHARS);
    
    $time = floor($time/$SLOT_WIDTH)*$SLOT_WIDTH;
    if ($time < $TIMESTAMP_START || $time > $TIMESTAMP_STOP) {
        // Time out of bounds
        header("Location: /index.php?order=0");
        exit();
    } 
    if ($MAX_NUMBER_INGREDIENTS < count($ingredients)) {
        // To many ingredients
        header("Location: /index.php?order=0");
        exit();
    }
    
    if (!checkOccupancy($time)) {
        // To many ingredients
        header("Location: /index.php?order=0");
        exit();
    }
    

    $res = insertDB($name, $email, $ingredients, $time, $remark);
    if ($res) {
        sendMail($email, $time);
        header("Location: /index.php?order=1");
        exit();
    }
     header("Location: /index.php?order=0");
}
else {
    header("Location: /index.php");
}
?>
