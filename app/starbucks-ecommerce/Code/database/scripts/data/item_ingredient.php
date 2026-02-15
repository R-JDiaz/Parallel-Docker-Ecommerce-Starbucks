<?php
require_once(__DIR__ . '/../../db2.php');
require_once(__DIR__ . '/../function.php');

// Helper: get item & ingredient IDs
function id($table, $name) {
    global $con;
    return getIdByName($con, $table, $name);
}

// Helper: map items
function mapItem($itemName, $ingredients) {
    $itemId = id('starbucksitem', $itemName);
    $result = [];
    foreach ($ingredients as $ingName => $data) {
        $ingId = id('ingredient', $ingName);
        $result[] = [$itemId, $ingId, $data['qty'], $data['unit']];
    }
    return $result;
}

$allMappings = [];

// --- Drinks: Hot Coffee
$allMappings = array_merge($allMappings, mapItem('Veranda Blend Hot', ['Espresso Shot'=>['qty'=>50,'unit'=>'ml'], 'Milk'=>['qty'=>0,'unit'=>'ml']]));
$allMappings = array_merge($allMappings, mapItem('Chai Latte', ['Milk'=>['qty'=>200,'unit'=>'ml'], 'Vanilla Syrup'=>['qty'=>10,'unit'=>'ml']]));
$allMappings = array_merge($allMappings, mapItem('Dark Rose Sumatra', ['Espresso Shot'=>['qty'=>50,'unit'=>'ml'], 'Milk'=>['qty'=>0,'unit'=>'ml']]));
$allMappings = array_merge($allMappings, mapItem('Misto', ['Espresso Shot'=>['qty'=>50,'unit'=>'ml'], 'Milk'=>['qty'=>50,'unit'=>'ml']]));
$allMappings = array_merge($allMappings, mapItem('Pike Place', ['Espresso Shot'=>['qty'=>50,'unit'=>'ml'], 'Milk'=>['qty'=>0,'unit'=>'ml']]));
$allMappings = array_merge($allMappings, mapItem('Verona', ['Espresso Shot'=>['qty'=>50,'unit'=>'ml'], 'Milk'=>['qty'=>0,'unit'=>'ml']]));

// --- Cold Coffee
$allMappings = array_merge($allMappings, mapItem('Salted Caramel Cream Cold Brew', ['Espresso Shot'=>['qty'=>60,'unit'=>'ml'], 'Caramel Syrup'=>['qty'=>15,'unit'=>'ml'], 'Milk'=>['qty'=>30,'unit'=>'ml']]));
$allMappings = array_merge($allMappings, mapItem('Vanilla Sweet Cream Cold Brew', ['Espresso Shot'=>['qty'=>60,'unit'=>'ml'], 'Vanilla Syrup'=>['qty'=>15,'unit'=>'ml'], 'Milk'=>['qty'=>30,'unit'=>'ml']]));
$allMappings = array_merge($allMappings, mapItem('Chocolate Cream Cold', ['Espresso Shot'=>['qty'=>60,'unit'=>'ml'], 'Chocolate Syrup'=>['qty'=>20,'unit'=>'ml'], 'Milk'=>['qty'=>30,'unit'=>'ml']]));
$allMappings = array_merge($allMappings, mapItem('Cold Brew', ['Espresso Shot'=>['qty'=>60,'unit'=>'ml'], 'Ice'=>['qty'=>5,'unit'=>'pcs']]));

// --- Frappuccino
$allMappings = array_merge($allMappings, mapItem('Blended Average Frappuccino', ['Ice Cream Mix'=>['qty'=>100,'unit'=>'ml'], 'Milk'=>['qty'=>50,'unit'=>'ml'], 'Ice'=>['qty'=>5,'unit'=>'pcs']]));
$allMappings = array_merge($allMappings, mapItem('Caramel Frappuccino', ['Ice Cream Mix'=>['qty'=>100,'unit'=>'ml'], 'Caramel Syrup'=>['qty'=>15,'unit'=>'ml'], 'Milk'=>['qty'=>50,'unit'=>'ml'], 'Ice'=>['qty'=>5,'unit'=>'pcs']]));
$allMappings = array_merge($allMappings, mapItem('Mocha Crumble Creamy Frappuccino', ['Ice Cream Mix'=>['qty'=>100,'unit'=>'ml'], 'Chocolate Syrup'=>['qty'=>15,'unit'=>'ml'], 'Milk'=>['qty'=>50,'unit'=>'ml'], 'Ice'=>['qty'=>5,'unit'=>'pcs']]));
$allMappings = array_merge($allMappings, mapItem('Mocha Frappuccino', ['Ice Cream Mix'=>['qty'=>100,'unit'=>'ml'], 'Chocolate Syrup'=>['qty'=>15,'unit'=>'ml'], 'Milk'=>['qty'=>50,'unit'=>'ml'], 'Ice'=>['qty'=>5,'unit'=>'pcs']]));

// --- Tea Latte
$allMappings = array_merge($allMappings, mapItem('Chai Latte', ['Milk'=>['qty'=>200,'unit'=>'ml'], 'Vanilla Syrup'=>['qty'=>10,'unit'=>'ml']]));
$allMappings = array_merge($allMappings, mapItem('London Fog', ['Milk'=>['qty'=>200,'unit'=>'ml'], 'Vanilla Syrup'=>['qty'=>10,'unit'=>'ml']]));
$allMappings = array_merge($allMappings, mapItem('Matcha Latte', ['Milk'=>['qty'=>200,'unit'=>'ml'], 'Matcha Powder'=>['qty'=>10,'unit'=>'g']]));

// --- Refreshers
$allMappings = array_merge($allMappings, mapItem('Strawberry Açaí Lemonade Refresher', ['Strawberries'=>['qty'=>5,'unit'=>'pcs'], 'Vanilla Syrup'=>['qty'=>10,'unit'=>'ml'], 'Ice'=>['qty'=>5,'unit'=>'pcs']]));
$allMappings = array_merge($allMappings, mapItem('Mango Fruit Refresher', ['Mango'=>['qty'=>5,'unit'=>'pcs'], 'Ice'=>['qty'=>5,'unit'=>'pcs']]));
$allMappings = array_merge($allMappings, mapItem('Summer Berry Lemonade', ['Blackberries'=>['qty'=>5,'unit'=>'pcs'], 'Ice'=>['qty'=>5,'unit'=>'pcs']]));

// --- Hot Breakfast
$allMappings = array_merge($allMappings, mapItem('Bacon, Gouda & Egg Sandwich', ['Bacon'=>['qty'=>30,'unit'=>'g'], 'Cheddar Cheese'=>['qty'=>20,'unit'=>'g'], 'Egg'=>['qty'=>1,'unit'=>'pcs'], 'Bread'=>['qty'=>1,'unit'=>'pcs']]));
$allMappings = array_merge($allMappings, mapItem('Double-Smoked Bacon, Cheddar & Egg Sandwich', ['Bacon'=>['qty'=>30,'unit'=>'g'], 'Cheddar Cheese'=>['qty'=>20,'unit'=>'g'], 'Egg'=>['qty'=>1,'unit'=>'pcs'], 'Bread'=>['qty'=>1,'unit'=>'pcs']]));
$allMappings = array_merge($allMappings, mapItem('Egg, Pesto & Mozzarella Sandwich', ['Egg'=>['qty'=>1,'unit'=>'pcs'], 'Pesto'=>['qty'=>10,'unit'=>'g'], 'Mozzarella'=>['qty'=>20,'unit'=>'g'], 'Bread'=>['qty'=>1,'unit'=>'pcs']]));
$allMappings = array_merge($allMappings, mapItem('Sausage, Cheddar & Egg Sandwich', ['Sausage'=>['qty'=>30,'unit'=>'g'], 'Cheddar Cheese'=>['qty'=>20,'unit'=>'g'], 'Egg'=>['qty'=>1,'unit'=>'pcs'], 'Bread'=>['qty'=>1,'unit'=>'pcs']]));
$allMappings = array_merge($allMappings, mapItem('Turkey Bacon, Cheddar & Egg White Sandwich', ['Turkey Bacon'=>['qty'=>20,'unit'=>'g'], 'Cheddar Cheese'=>['qty'=>20,'unit'=>'g'], 'Egg White'=>['qty'=>1,'unit'=>'pcs'], 'Bread'=>['qty'=>1,'unit'=>'pcs']]));

// --- Bakery
$allMappings = array_merge($allMappings, mapItem('Baked Apple Croissant', ['Butter'=>['qty'=>10,'unit'=>'g'], 'Apple'=>['qty'=>1,'unit'=>'pcs'], 'Flour'=>['qty'=>50,'unit'=>'g']] ));
$allMappings = array_merge($allMappings, mapItem('Butter Croissant', ['Butter'=>['qty'=>10,'unit'=>'g'], 'Flour'=>['qty'=>50,'unit'=>'g']] ));
$allMappings = array_merge($allMappings, mapItem('Chocolate Croissant', ['Butter'=>['qty'=>10,'unit'=>'g'], 'Chocolate Syrup'=>['qty'=>10,'unit'=>'ml'], 'Flour'=>['qty'=>50,'unit'=>'g']] ));
$allMappings = array_merge($allMappings, mapItem('Ham & Swiss Croissant', ['Ham'=>['qty'=>20,'unit'=>'g'], 'Cheddar Cheese'=>['qty'=>20,'unit'=>'g'], 'Flour'=>['qty'=>50,'unit'=>'g']] ));
$allMappings = array_merge($allMappings, mapItem('Vanilla Bean Custard Danish', ['Vanilla Syrup'=>['qty'=>10,'unit'=>'ml'], 'Flour'=>['qty'=>50,'unit'=>'g'], 'Egg'=>['qty'=>1,'unit'=>'pcs']] ));

// --- Salads
$allMappings = array_merge($allMappings, mapItem('Tomato & Mozzarella Salad', ['Tomato'=>['qty'=>1,'unit'=>'pcs'], 'Mozzarella'=>['qty'=>20,'unit'=>'g']] ));
$allMappings = array_merge($allMappings, mapItem('Tuna Pasta Salad', ['Tomato'=>['qty'=>1,'unit'=>'pcs'], 'Dried Herbs'=>['qty'=>5,'unit'=>'g']] ));

// --- Snacks
$allMappings = array_merge($allMappings, mapItem('All In™ Madagascar Vanilla, Honey & Almonds Bar', ['Vanilla Syrup'=>['qty'=>10,'unit'=>'ml']] ));
$allMappings = array_merge($allMappings, mapItem('KIND - Almond Coconut Cashew Chai', ['Flour'=>['qty'=>20,'unit'=>'g']] ));
$allMappings = array_merge($allMappings, mapItem('KIND® Salted Caramel & Dark Chocolate Nut Bar', ['Caramel Syrup'=>['qty'=>10,'unit'=>'ml'], 'Chocolate Syrup'=>['qty'=>10,'unit'=>'ml']] ));
$allMappings = array_merge($allMappings, mapItem('Perfect Bar® Peanut Butter', ['Flour'=>['qty'=>20,'unit'=>'g'], 'Butter'=>['qty'=>10,'unit'=>'g']] ));

// --- Finally insert all into item_ingredient
insertData($con, 'item_ingredient', ['item_id','ingredient_id','quantity_value','quantity_unit'], $allMappings);

echo "All Starbucks items linked with ingredients successfully!";
?>
