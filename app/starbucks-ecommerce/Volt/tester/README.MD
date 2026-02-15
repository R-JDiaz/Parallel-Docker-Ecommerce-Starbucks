Soft eng Project --> E commerce "Starbucks"

[Database] --> contains creation of database
--> function logic for creating tables
--> inserting pre define fields only once , if exists skip
--> function for inserting starbucksitem (display in frontend)

[Backend] --> Backend logic

    [api] --> contains multiple routes assigned to different data (items etc.)
        [controller]            --> contains the controller using the files in [model] folder
            ItemController.php  --> uses the fucntions in Item.php from [model]

    index.php --> gets all the request from the frontend
              --> checks the URL type ("/items", "/suppliers" etc.) then call [routes] files for response

    [model] --> contains database logic (using the global "$conn" to access database indirectly)
        [Item.php]          --> contains logic for starbucksitem only
        [Ingredient.php]    --> contains logic for ingredients only
        [Supplier.php]      --> contains logic for suppliers only

    [routes] --> Response for index.php
        ! All this file is using the itemcontroller from [api] to execute functions
        [items.php]         --> request = "/items"
        [ingredients.php]   --> request = "/ingredients"
        [orders.php]        --> request = "/order"
        [suppliers.php]     --> request = "/supplier"


---------------------------------------------------------------------------------------------------
Improvements needed!

need to improve the signing in! the phone number should be strictly ! 11 characters and based on the choosen country!
and also the address!

---------------------------------------
