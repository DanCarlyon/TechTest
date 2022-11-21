<?php

/**
 * Auto load our classes
 */
spl_autoload_register(function ($class_name) {
    include 'classes/' . $class_name . '.php';
});

// Requires credentials for your database
$db = new Database('', '', '', '');
$pricingClass = new Prices($db);

// Running this via console should output the results
// php index.php

$scenarioOne = ["customer" => 1, "products" => ["A" => 10]];
print_r("Customer 1 buys 10 of SKU A" . PHP_EOL);
print_r($pricingClass->getTotalForOrder($scenarioOne));


$scenarioTwo = ["customer" => 1, "products" => ["A" => 30]];
print_r("Customer 1 buys 30 of SKU A" . PHP_EOL);
print_r($pricingClass->getTotalForOrder($scenarioTwo));


$scenarioThree = ["customer" => 1, "products" => ["A" => 50]];
print_r("Customer 1 buys 50 of SKU A" . PHP_EOL);
print_r($pricingClass->getTotalForOrder($scenarioThree));


$scenarioFour = ["customer" => 2, "products" => ["A" => 20, "B" => 20]];
print_r("Customer 2 buys 20 of SKU A and 20 of SKU B" . PHP_EOL);
print_r($pricingClass->getTotalForOrder($scenarioFour));


$scenarioFive = ["customer" => 2, "products" => ["A" => 30, "B" => 30]];
print_r("Customer 2 buys 30 of SKU A and 30 of SKU B" . PHP_EOL);
print_r($pricingClass->getTotalForOrder($scenarioFive));


$scenarioSix = ["customer" => 3, "products" => ["A" => 30, "B" => 30]];
print_r("Customer 3 buys 30 of SKU A and 30 of SKU B" . PHP_EOL);
print_r($pricingClass->getTotalForOrder($scenarioSix));


$scenarioSeven = ["customer" => 2, "products" => ["A" => 30, "B" => 30], "sale" => true];
print_r("Customer 2 buys 30 of SKU A and 30 of SKU B" . PHP_EOL);
print_r($pricingClass->getTotalForOrder($scenarioSeven));


$scenarioEight = ["customer" => 3, "products" => ["A" => 30, "B" => 30], "sale" => true];
print_r("Customer 3 buys 30 of SKU A and 30 of SKU B" . PHP_EOL);
print_r($pricingClass->getTotalForOrder($scenarioEight));