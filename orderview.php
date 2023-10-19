<?php
require_once("config.php");

$conn = createConn($CRED_SERV_NAME, $CRED_USER, $CRED_PWD, $CRED_DBNAME);

$stmt = $conn->prepare("SELECT * from orders ORDER BY time, uid;");
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);

$stmt = $conn->prepare("SELECT uid, name, price from ingredients ORDER BY name;");
$stmt->execute();
$result = $stmt->get_result();
$ingr_res = $result->fetch_all(MYSQLI_ASSOC);

$conn->close();

// Remap $ingredients.
$ingredients = [];
$ingredient_count = [];
$priceList = [];
foreach ($ingr_res as $trash => $ingr) {
    $ingredients[$ingr["uid"]] = $ingr["name"];
    $priceList[$ingr["uid"]] = $ingr["price"];
    $ingredient_count[$ingr["name"]] = 0;
}
// Count how often an ingredient was orderd.
foreach ($orders as &$order) {
    foreach (explode(",", $order["ingredients"]) as &$iID) {
        $ingredient_count[$ingredients[intval($iID)]] += 1;
    }
}

$ingredient_count["No Ingredient"] = $ingredient_count[""];
$ingredient_count["Total orders"] = count($orders);
unset($ingredient_count[""]);
// Get longest ingredient name for css offset
$largest_ing_name = max(array_map('strlen', array_keys($ingredient_count)));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/orderview.css">
    <title>Pizza-System</title>
    <script>
function searchTable() {
    var filter, found, orders, td;
    filter =  document.getElementById("search").value.toLowerCase();
    orders = document.getElementsByClassName("order");

    for (i = 0; i < orders.length; i++) {
        found = false;
        td = orders[i].getElementsByTagName("div");
        for (j = 0; j < td.length; j++) {
            if (td[j].textContent.toLowerCase().includes(filter)) {
                found = true;
            }
        }
        if (found || filter == "") {
            orders[i].style.display = "";
        } else {
            orders[i].style.display = "none";
        }
    }
}
</script>
</head>
<body>

<div class="content">
    <h1>Pizza System</h1>
    <h3>Order List</h3>
    <div class="block"></div>
        <div class="social">
            <p class="icon clock"><?php echo $DATE;?></p>
            <p class="icon marker"><?php echo $LOCATION;?></p>
            <p class="icon email"><?php echo $CONTACT_EMAIL; ?></p>
        </div>
        <h3>Number of orders</h3>
        <div class="ingredient-container">
                <?php
                foreach ($ingredient_count as $iName => $iCount) {
                    $w = 15 + $iCount*20;
                    echo "<div class='ingredient-row'>";
                    echo "<div class='ingredient-name' style='width: {$largest_ing_name}ch;'>$iName</div>";
                    echo "<div class='ingredient-count-container'>";
                    echo "<div class='ingredient-count' style='width: {$w}px;'>$iCount</div>";
                    echo "<div class='ingredient-count-mob'>$iCount</div>";
                    echo "</div></div>";
                }
                ?>
        </div>
        <h3>Orders by time</h3>
        <input type="text" id="search" placeholder="&nbsp;&nbsp;🔍&nbsp;&nbsp;Suchen" onkeyup="searchTable()">
        <div id="orderlist">
<?php

    foreach ($orders as &$order) {
        $time = date('H:i', $order['time']);
        $price = $BASE_PRICE;
        foreach (explode(",", $order["ingredients"]) as &$iID) {
            $price = $price + $priceList[intval($iID)];
        }
        echo <<<EOD
<div class='order'>
    <div class='uid'>{$order['uid']}</div>
    <div class='time'>{$time}</div>
    <div class='price'>{$price}€</div>
    <div class='name'>{$order['name']}</div>
    <div class='ingredients'>
EOD;
        foreach (explode(",", $order["ingredients"]) as &$iID) {
            echo "{$ingredients[intval($iID)]}, ";
        }
        echo <<<EOD
</div>
<div class='remark'>{$order['remark']}</div>
</div>

EOD;
    }
?>
</div>

</div>
</body>
</html>