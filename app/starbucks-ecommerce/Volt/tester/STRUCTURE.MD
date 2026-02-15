*************************************** Folder Structure *************************************

[SOFTENG2]

[backend]
    [api]
        [controllers] 
            itemController.php -- uses from model folder like Class Item etc.
            checkoutController.php
            loginController.php
            paymentController.php
            signupController.php
            sizeController.php
            historyController.php

        [routes]
            items.php etc. -- uses controllers folder
            check_login.php
            checkout.php
            login.php
            orders.php
            payment.php
            receipt.php 
            signup.php
            sizes.php
            history.php


        index.php -- Entry Point of Request
            -- 

        [tcpdf] -- for receipt

    [model]
        Item.php --
        Address.php
        Auth.php
        Contact.php
        Order.php
        Payment.php
        size.php
        User.php

[database]
    [model] - database tables.
        address.php
        admins.php
        attributes_template.php
        auth.php
        category.php
        contacts.php
        itemattributes.php
        orderitems.php
        receipts.php
        size.php
        starbucksitem.php
        subcategoryitems.php
        user_order.php
        users.php

    [scripts] - contains data for injecting
        - [data] - contains the actual datas to inject to database

    db2.php - configuration of database -- &con - for global use of database
    seed.php - injecting the database

[frontend]
    [images] -- contains iamges of the items
    login2.html -- for log in/ sign up / go without account, have some scripts too uses [components] scripts.
    login2.css -- styling the login2
    menu2.html     -- for displaying the menu such as category choices then when choise, display the items in that category only , havbe some few scripts too 

    [components] -- for [login2.html]
        -- signup-form.html
        -- login-form.html
        -- start-screen.html
        -- errorMessage.html
        -- 

    [js]
        -- auth.js :  Backeand interaction and database to saved login. sign up etc.
        -- config : Base url connection

    [menu] 
        cart.js
        category.js
        init.js
        modal.js
        payment.js
        receipt.js

    

---------------------------------------------------------------------------------------------------------
[Backend Flow]

[API FLOW]

index.php --> checks URL type . ex. ("/items")
--> require = [routes/items.php] -- calls the items.php from [routes] folder
--> items.php uses the [controllers/itemcontroller.php] for response
--> [controllers/itemcontroller.php] uses [model/Item.php] logic functions business and database

[Folders]

-------------- Models and Controller connection --------------
[models]        --> Interact with DATABASE "Functions" ex. SELECT * FROM etc.
[controllers]   --> Checking Request Method "GET/POST/PUT/PATCH" and Validation before calls [models] functions

-------------- routes and index.php connection --------------
index.php       --> Main API checks the request ex. ("/items", "/suppliers")
                --> each request has equivalent routes
                --> ex. "/items" = [routes] -- items.php

[routes]        -->  use controller to respond from the request 
                --> index.php calls the files inside to respond

-------------------------------------------------------------------------------------------------
Flow of Website 

1. Home page (Display etc.)
2. Menu page
3. Category items
4. When adding to cart, Required Login or Sign up
5. Checkout 
6. Payment
7. Receipt




-------------------------------------------------------------------------------------------------



[database]
    [model]
    [scripts]
    db.php
    set.php


------------------------------ Structured clean modular ------------------------------------------

SOFTENG2/
├── backend/
│   ├── api/
│   │   ├── controllers/
│   │   │   └── itemController.php
│   │   ├── routes/
│   │   │   └── item.php
│   │   └── index2.php
│   └── model/
│       └── item.php
│
├── database/
│   ├── model/
│   ├── scripts/
│   │   └── data/
│   ├── db2.php
│   └── seed.php
│
├── frontend/
│   ├── components/
│   │   ├── signup.html
│   │   ├── login.html
│   │   ├── startScreen.html
│   │   └── errorMessage.html
│   ├── js/
│   │   ├── auth.js
│   │   ├── config.js
│   │   └── menu2.js
│   ├── images/
│   ├── login2.html
│   ├── login2.css
│   └── menu2.html

[updated folder structure with clean presentation]

SOFTENG2/
├── backend/
│   ├── api/
│   │   ├── controllers/
│   │   │   ├── itemController.php
│   │   │   ├── checkoutController.php
│   │   │   ├── loginController.php
│   │   │   ├── paymentController.php
│   │   │   ├── signupController.php
│   │   │   └── sizeController.php
|   |   |   └── historyController.php
│   │   │
│   │   ├── routes/
│   │   │   ├── items.php
│   │   │   ├── check_login.php
│   │   │   ├── checkout.php
│   │   │   ├── login.php
│   │   │   ├── orders.php
│   │   │   ├── payment.php
│   │   │   ├── receipt.php
│   │   │   ├── signup.php
│   │   │   └── sizes.php
|   |   |   └── history.php
│   │   │
│   │   ├── tcpdf/                # Library for PDF receipts
│   │   └── index.php             # Entry point of backend API
│   │
│   └── model/
│       ├── Address.php
│       ├── Auth.php
│       ├── Contact.php
│       ├── Item.php
│       ├── Order.php
│       ├── Payment.php
│       ├── Size.php
│       └── User.php
│
├── database/
│   ├── model/
│   │   ├── address.php
│   │   ├── admins.php
│   │   ├── attributes_template.php
│   │   ├── auth.php
│   │   ├── category.php
│   │   ├── contacts.php
│   │   ├── itemattributes.php
│   │   ├── orderitems.php
│   │   ├── receipts.php
│   │   ├── size.php
│   │   ├── starbucksitem.php
│   │   ├── subcategoryitems.php
│   │   ├── user_order.php
│   │   └── users.php
│   │
│   ├── scripts/
│   │   └── data/                 # Contains actual data to inject
│   │
│   ├── db2.php                   # Database configuration (&con)
│   └── seed.php                  # Script for seeding database
│
├── frontend/
│   ├── components/               # For login2.html
│   │   ├── signup-form.html
│   │   ├── login-form.html
│   │   ├── start-screen.html
│   │   └── errorMessage.html
│   │
│   ├── js/
│   │   ├── auth.js              # Handles signup/login backend interaction
│   │   ├── config.js            # Base URL config
│   │   
│   │       
|   |── menu/
|   |   ├── cart.js       
|   |   ├── category.js
|   |   ├── init.js
|   |   ├── modal.js
|   |   ├── payment.js
|   |   └── receipt.js
|   |   └── history.js
|   |
|   |
|   |
│   ├── images/                  # Contains images of items
│   ├── login2.html              # Login, signup, guest access page
│   ├── login2.css               # Styling for login2.html
│   └── menu2.html               # Category display and items page
|   └── style2.css              # css for menu2.html
|   └── history.html            # for displaying orders 
