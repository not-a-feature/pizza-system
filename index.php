<?php
require_once("config.php");

$conn = createConn($CRED_SERV_NAME, $CRED_USER, $CRED_PWD, $CRED_DBNAME);
$stmt = $conn->prepare("SELECT * from ingredients ORDER BY name;");
$stmt->execute();
$result = $stmt->get_result();
$ingredients = $result->fetch_all(MYSQLI_ASSOC);


$stmt = $conn->prepare("SELECT floor(time/{$SLOT_WIDTH})*$SLOT_WIDTH AS slot, count(uid) AS count FROM orders GROUP BY 1 ORDER BY 1;");
$stmt->execute();
$result = $stmt->get_result();
$occ_res = $result->fetch_all(MYSQLI_ASSOC);
$conn->close();

$occupancy = [];
foreach ($occ_res as &$res) {
    $occupancy[$res["slot"]] = $res["count"];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/index.css">
    <title>Pizza-System</title>
</head>
<body>

<div class="content">
    <h1>Pizza System</h1>
    <h3>Order a delicous pizza</h3>
    <div class="block"></div>
        <div class="social">
            <p class="icon clock"><?php echo $DATE;?></p>
            <p class="icon marker"><?php echo $LOCATION;?></p>
            <p class="icon email"><?php echo $CONTACT_EMAIL; ?></p>
        </div>
        <br>
        The basic pizza consists of a yeast dough and tomato sauce. Any additional ingredient / topping will be charged extra.
        Please visit us at <?php echo $LOCATION;?>. Your pizza will be freshly prepared when you arrive. Please bring cash to pay.
        <br><br>
        <?php
        if ($_SERVER['REQUEST_METHOD'] == "GET") {
            $order = $_GET["order"] ?: "";
            if ($order == "1") {
                echo "<div class='icon success'>Your order was placed!</div><br>";
            }
            else if ($order == "0"){
                echo "<div class='icon error'>Error while placing your order! Please contact: {$CONTACT_EMAIL}</div>";
            }
            if ($TIMESTAMP_LAST_ORDER < time()) {
                echo "<div class='icon error'>Ordering deadline exceeded.</div><br>";
            }
        }
        ?>
        <form action="order.php" method="post">
            <input type="text" placeholder="Name" name="name" maxlength=30 required><br>
            <input type="email" placeholder="E-Mail" name="email" maxlength=100 required><br>
            <div class="list">
            <p>Base price: <?php echo $BASE_PRICE;?>€</p>
            <?php
            foreach ($ingredients as &$ing) {
                echo "<label>";
                echo "<input type='checkbox' id='ingr-{$ing['uid']}' name='ingredients[]' value='{$ing['uid']}'>";
                echo  "{$ing['name']} {$ing['price']}€</label>";
            }
            ?>
            </div>
            <input name="remark" type="text" value="" placeholder="Remark"><br><br>

            Select a pick-up time. The higher the bar, the higher the demand. Therefore you may wait a few minutes longer.

            <div class="time-container" style="grid-template-columns: repeat(<?php echo $SLOTS;?>, auto);">
                <?php
                for ($i=$TIMESTAMP_START; $i < $TIMESTAMP_STOP; $i=$i+$SLOT_WIDTH) {
                    if (isset($occupancy[$i])) {
                        $h = $occupancy[$i]*$ADDITIONAL_TIME*4 + $PIZZA_TIME;
                    }
                    else {
                        $h = $PIZZA_TIME;
                    }
                    echo "<div class='time-occupancy' style='height: {$h}px;'></div>";
                }
                ?>
            </div>

            <input type="range"
                min="<?php echo $TIMESTAMP_START;?>"
                max="<?php echo $TIMESTAMP_STOP-1;?>"
                value="<?php echo ($TIMESTAMP_STOP - $TIMESTAMP_START)/2 + $TIMESTAMP_START;?>"
                class="slider" name="pickup-time" id="pickup-time">
            <output for="pickup-time" id="time-bubble" class="bubble"></output><br>
            <output for="pickup-time" id="time"></output><br>
            <output for="pickup-time"  id="wait"></output><br>
            <output id="price"></output><br>
        <div class='icon error' style="display: none" id="max_ingr_warning">Maximum number of ingredients exceeded.</div>
        <div class='icon error' style="display: none" id="overlapping_order">Too many orders! Choose another time-slot.</div>
        <input type='submit' value='Order Pizza' id='order_button'>
        </form>
        <script>
            // Pricelist dictionary
            priceList = {<?php
            foreach ($ingredients as &$ing) {
                echo "'ingr-{$ing['uid']}' : {$ing['price']}, ";
            }
            ?>};
            // Constants
            basePrice = <?php echo $BASE_PRICE;?>;
            max_ingr_count = <?php echo $MAX_NUMBER_INGREDIENTS;?>;
            block_overlapping_orders = <?php echo $BLOCK_OVERLAPPING_ORDERS ? "true" : "false";?>;
            slot_width = <?php echo $SLOT_WIDTH;?>; // in s
            first_run = true;

            // Event listeners for all ingredients
            checkboxes = document.querySelectorAll("input[type=checkbox]");
            max_ingr_warning = document.getElementById("max_ingr_warning");
            overlapping_order = document.getElementById("overlapping_order");
            order_button = document.getElementById("order_button");

            for (var i = 0; i < checkboxes.length; i++) {
                checkboxes[i].addEventListener("change", displaySum);
            }

            // Calculate and display the sum of currently selected ingredients
            function displaySum() {
                currentSum = basePrice;
                ingr_count = 0;
                for (var i = 0; i < checkboxes.length; i++) {
                    if (checkboxes[i].checked) {
                        ingr_count += 1;
                        currentSum += priceList[checkboxes[i].id];
                    }
                }
                // Check if number of selected ingredients is too high.
                if (max_ingr_count < ingr_count) {
                    // Show warning, disable button
                    max_ingr_warning.style.display = "";
                    order_button.disabled = true;

                }
                else {
                    // Hide warning, enable button
                    max_ingr_warning.style.display = "none";
                    order_button.disabled = false;
                }

                document.getElementById("price").innerHTML = "Price: " +
                    currentSum.toString() + "€";
            }
            displaySum();

            // Time Slider
            occupancy = {<?php
                foreach ($occ_res as &$res) {
                echo "'{$res['slot']}' : {$res['count']}, ";
            }
            ?>};

            time_per_pizza = <?php echo $PIZZA_TIME;?>;
            time_per_additional_pizza = <?php echo $ADDITIONAL_TIME;?>;
            slider = document.getElementById("pickup-time");
            slider.addEventListener("input", displayChoice);
            bubble = document.getElementById("time-bubble");

            function displayChoice() {
                // calculates the pickup and waiting time
                // Updates time-bubble beneath the slider
                // Dis-/Enables the order-button

                time = slider.valueAsNumber
                bin_slot = Math.floor(time / slot_width) * slot_width;
                if (bin_slot in occupancy) {
                    wait = occupancy[bin_slot] * time_per_additional_pizza + time_per_pizza;
                }
                else {
                    wait = time_per_pizza;
                }

                date_time = new Date(bin_slot*1000);
                human_time = String(date_time.getHours()).padStart(2, '0') +
                             ":" + String(date_time.getMinutes()).padStart(2, '0');
                // Update values to html
                document.getElementById("time").innerHTML = "Pick-up time: " + human_time;
                document.getElementById("wait").innerHTML = "Estimated waiting time: < "+
                    wait + " minutes";


                // Dis-/Enables Order Button
                if (block_overlapping_orders && slot_width/60 < wait) {
                    // Show too many orders warning, disable button

                    // Edge Case:  Don't show warning if order was successful
                    if (first_run && window.location.search != "") {
                        overlapping_order.style.display = "none";
                    }
                    else {
                        overlapping_order.style.display = "";
                    }
                    order_button.disabled = true;
                }
                else {
                    // Hide overlapping warning, enable button
                    overlapping_order.style.display = "none";
                    order_button.disabled = false;
                }

                // Update Bubble hovering
                // Inspired based on: https://css-tricks.com/value-bubbles-for-range-inputs/

                // pos of thumb in %
                position = (time - <?php echo $TIMESTAMP_START;?>) /
                            <?php echo $TIMESTAMP_STOP - $TIMESTAMP_START;?> * 100;

                // true slider and thumb size in px
                slider_width = parseFloat(window.getComputedStyle(slider).width);
                thumb_width = parseFloat(window.getComputedStyle(slider)
                                        .getPropertyValue("--thumbSize"));

                // Relative offset of bubble
                thumb_width_rel = thumb_width / slider_width * 100;
                pos_factor = (slider_width - thumb_width) / slider_width;
                position = (position + thumb_width_rel/2) * pos_factor;

                bubble.innerHTML = human_time;
                bubble.style.left = position + "%";

                first_run = false;
            }
            displayChoice();
        </script>
    </div>
</body>

</html>
