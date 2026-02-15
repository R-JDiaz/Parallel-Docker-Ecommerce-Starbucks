<?php

require_once(__DIR__ . '/../../db2.php');
require_once(__DIR__ . '/../function.php');

// Fetch all Foreign keys!
$beveragesId = getIdByName($con, 'category', 'Beverages');
$foodId = getIdByName($con, 'category', 'Food');

$hotCoffeeId = getIdByName($con, 'subcategory', 'Hot Coffee');
$coldCoffeeId = getIdByName($con, 'subcategory', 'Cold Coffee');
$frappuccinoId = getIdByName($con, 'subcategory', 'Frappuccino');
$teaLatteId = getIdByName($con, 'subcategory', 'Tea Latte');
$refreshersId = getIdByName($con, 'subcategory', 'Refreshers');
$hotBreakfastId = getIdByName($con, 'subcategory', 'Hot Breakfast');
$lunchSandwichesId = getIdByName($con, 'subcategory', 'Lunch Sandwiches');
$bakeryId = getIdByName($con, 'subcategory', 'Bakery');
$saladsId = getIdByName($con, 'subcategory', 'Salads');
$snacksId = getIdByName($con, 'subcategory', 'Snacks');

// --- Insert items (without quantity, since stock is tracked in ready_item_stock)
insertData($con, 'starbucksitem', 
    ['name', 'price', 'category_id', 'subcategory_id', 'description', 'image_url'], [

    // Hot Coffee
    ['Veranda Blend Hot', 180.00, $beveragesId, $hotCoffeeId, 'A mellow and soft coffee with subtle flavors of soft cocoa and lightly toasted nuts', 'Hot Coffee/Veranda_Blend_Hot.jpg'],
    ['Chai Latte', 195.00, $beveragesId, $hotCoffeeId, 'Black tea infused with cinnamon, clove and other warming spices', 'Hot Coffee/chai latte.jpg'],
    ['Dark Rose Sumatra', 200.00, $beveragesId, $hotCoffeeId, 'Full-bodied with a smooth mouthfeel, lingering flavors of dried herbs and fresh earth', 'Hot Coffee/dark rose sumatra.jpg'],
    ['Misto', 165.00, $beveragesId, $hotCoffeeId, 'A one-to-one combination of fresh-brewed coffee and steamed milk', 'Hot Coffee/misto.jpg'],
    ['Pike Place', 175.00, $beveragesId, $hotCoffeeId, 'Smooth and balanced with rich, approachable flavors', 'Hot Coffee/pike place.jpg'],
    ['Verona', 185.00, $beveragesId, $hotCoffeeId, 'Well-balanced and rich with a dark cocoa texture', 'Hot Coffee/verona.jpg'],

    // Cold Coffee
    ['Salted Caramel Cream Cold Brew', 220.00, $beveragesId, $coldCoffeeId, 'Slow-steeped cold brew topped with salted caramel cream', 'Cold Coffee/SaltedCaramelCreamColdBrew.jpg'],
    ['Vanilla Sweet Cream Cold Brew', 210.00, $beveragesId, $coldCoffeeId, 'Slow-steeped cold brew topped with vanilla sweet cream', 'Cold Coffee/VanillaSweetCreamColdBrew.jpg'],
    ['Chocolate Cream Cold', 195.00, $beveragesId, $coldCoffeeId, 'Rich cold brew with chocolate cream topping', 'Cold Coffee/chocolate cream cold.jpg'],
    ['Cold Brew', 180.00, $beveragesId, $coldCoffeeId, 'Slow-steeped for 20 hours for a super-smooth taste', 'Cold Coffee/cold.jpg'],

    // Frappuccino
    ['Blended Average Frappuccino', 235.00, $beveragesId, $frappuccinoId, 'A perfectly balanced blended beverage', 'Frappuccino/blended average.jpg'],
    ['Caramel Frappuccino', 245.00, $beveragesId, $frappuccinoId, 'Coffee blended with caramel syrup and topped with whipped cream', 'Frappuccino/caramel frappucino.jpg'],
    ['Mocha Crumble Creamy Frappuccino', 255.00, $beveragesId, $frappuccinoId, 'Coffee and chocolate blended with crumble topping', 'Frappuccino/mocha crumble creamy.jpg'],
    ['Mocha Frappuccino', 240.00, $beveragesId, $frappuccinoId, 'Coffee blended with rich chocolate and topped with whipped cream', 'Frappuccino/mocha frappuccino.jpg'],

    // Tea Latte
    ['Chai Latte', 195.00, $beveragesId, $teaLatteId, 'Black tea infused with warming spices', 'tea latte/chai latte.jpg'],
    ['London Fog', 205.00, $beveragesId, $teaLatteId, 'Earl Grey tea with vanilla and steamed milk', 'tea latte/london fog.jpg'],
    ['Matcha Latte', 215.00, $beveragesId, $teaLatteId, 'Smooth and creamy matcha green tea with steamed milk', 'tea latte/matcha latte.jpg'],

    // Refreshers
    ['Strawberry Açaí Lemonade Refresher', 190.00, $beveragesId, $refreshersId, 'Sweet strawberry flavors with açaí notes and lemonade', 'Refreshers/Strawberry Açaí Lemonade Refresher.jpg'],
    ['Mango Fruit Refresher', 185.00, $beveragesId, $refreshersId, 'Tropical mango flavors shaken with ice', 'Refreshers/mango fruit.jpg'],
    ['Summer Berry Lemonade', 195.00, $beveragesId, $refreshersId, 'Mixed berries with refreshing lemonade', 'Refreshers/summer berry lemonade.jpg'],

    // Hot Breakfast
    ['Bacon, Gouda & Egg Sandwich', 295.00, $foodId, $hotBreakfastId, 'Applewood-smoked bacon, aged Gouda and egg on an artisan roll', 'Hot breakfast/Bacon, Gouda & Egg Sandwich.jpg'],
    ['Double-Smoked Bacon, Cheddar & Egg Sandwich', 315.00, $foodId, $hotBreakfastId, 'Double-smoked bacon, aged cheddar and egg on a toasted English muffin', 'Hot breakfast/Double-Smoked Bacon, Cheddar & Egg Sandwich.jpg'],
    ['Egg, Pesto & Mozzarella Sandwich', 285.00, $foodId, $hotBreakfastId, 'Cage-free egg with basil pesto and mozzarella on focaccia', 'Hot breakfast/Egg, Pesto & Mozzarella Sandwich.jpg'],
    ['Sausage, Cheddar & Egg Sandwich', 305.00, $foodId, $hotBreakfastId, 'Savory sausage, aged cheddar and egg on an English muffin', 'Hot breakfast/Sausage, Cheddar & Egg Sandwich.jpg'],
    ['Turkey Bacon, Cheddar & Egg White Sandwich', 295.00, $foodId, $hotBreakfastId, 'Turkey bacon, aged cheddar and egg whites on an English muffin', 'Hot breakfast/Turkey Bacon, Cheddar & Egg White Sandwich.jpg'],

    // Lunch Sandwiches
    ['Crispy Grilled Cheese on Sourdough', 265.00, $foodId, $lunchSandwichesId, 'Melted cheese grilled to perfection on artisan sourdough', 'Lunch Sandwiches/Crispy Grilled Cheese on Sourdough.jpg'],
    ['Ham & Swiss on Baguette', 285.00, $foodId, $lunchSandwichesId, 'Sliced ham and Swiss cheese on a fresh baguette', 'Lunch Sandwiches/Ham & Swiss on Baguette.jpg'],
    ['Tomato & Mozzarella on Focaccia', 275.00, $foodId, $lunchSandwichesId, 'Fresh tomato and mozzarella on herb focaccia bread', 'Lunch Sandwiches/Tomato & Mozzarella on Focaccia.jpg'],
    ['Turkey, Provolone & Pesto on Ciabatta', 295.00, $foodId, $lunchSandwichesId, 'Sliced turkey, provolone and basil pesto on ciabatta', 'Lunch Sandwiches/Turkey, Provolone & Pesto on Ciabatta.jpg'],

    // Bakery
    ['Baked Apple Croissant', 185.00, $foodId, $bakeryId, 'Buttery croissant filled with baked apple and cinnamon', 'bakery/Baked Apple Croissant.jpg'],
    ['Butter Croissant', 155.00, $foodId, $bakeryId, 'Classic French butter croissant, flaky and golden', 'bakery/Butter Croissant.jpg'],
    ['Chocolate Croissant', 175.00, $foodId, $bakeryId, 'Buttery croissant filled with rich chocolate', 'bakery/Chocolate Croissant.jpg'],
    ['Ham & Swiss Croissant', 195.00, $foodId, $bakeryId, 'Flaky croissant filled with ham and Swiss cheese', 'bakery/Ham & Swiss Croissant.jpg'],
    ['Vanilla Bean Custard Danish', 165.00, $foodId, $bakeryId, 'Sweet Danish pastry filled with vanilla bean custard', 'bakery/Vanilla Bean Custard Danish.jpg'],

    // Salads
    ['Tomato & Mozzarella Salad', 225.00, $foodId, $saladsId, 'Fresh tomatoes and mozzarella with basil', 'Salads/Tomato & Mozzarella on Focaccia.webp'],
    ['Tuna Pasta Salad', 245.00, $foodId, $saladsId, 'Tuna pasta salad with vegetables and herbs', 'Salads/tuna pasta salad.webp'],

    // Snacks
    ['All In™ Madagascar Vanilla, Honey & Almonds Bar', 125.00, $foodId, $snacksId, 'Nutritious bar with vanilla, honey and almonds', 'snacks/All In™ Madagascar Vanilla, Honey & Almonds Bar.jpg'],
    ['KIND - Almond Coconut Cashew Chai', 135.00, $foodId, $snacksId, 'Nut bar with almond, coconut, cashew and chai spices', 'snacks/KIND - Almond Coconut Cashew Chai.jpg'],
    ['KIND® Salted Caramel & Dark Chocolate Nut Bar', 140.00, $foodId, $snacksId, 'Nut bar with salted caramel and dark chocolate', 'snacks/KIND® Salted Caramel & Dark Chocolate Nut Bar.jpg'],
    ['Perfect Bar® Peanut Butter', 145.00, $foodId, $snacksId, 'Whole food protein bar with peanut butter', 'snacks/Perfect Bar® Peanut Butter.jpg'],
]);

?>
