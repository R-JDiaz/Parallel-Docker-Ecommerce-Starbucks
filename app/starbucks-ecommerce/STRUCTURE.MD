Main folder - Starbucks-ecommerce


scripts/
│   ├── send-otp.php # automation for sending otp
Code/
├── api/
│   ├── .htaccess 
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
|   |   |   └── send-otp.php
|   |   |   └── verify-otp.php
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
│       └── Cart.php
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
│   │   └── cart_items.php
│   │
│   ├── scripts/
│   │   └── data/                 # Contains actual data to inject
│   │   └── function.php          # contains function used in creating table 
│   │
│   ├── db2.php                   # Database configuration (&con)
│   └── seed.php                  # Script for seeding database
│
├── design/ = Actual frontend
│   ├── assets/
│   ├── home/
│   │   ├── assets/
│   │   ├── cart.js
│   │   ├── main.js -- bootstraping of js functions
│   │   ├── modal.js
│   │   ├── payment.js
│   │   ├── session.js
│   │   ├── history.js
├── frontend/ -- used for directing buttons in design/ home
│   ├── admin/
│   │   ├── panel/
|   │   │   ├── panel.html
|   │   │   ├── panel.css
|   │   │   ├── panel.js
│   ├
│   │   ├── inventory/
|   │   │   ├── inventory.html --> entry point of the inventory manegement choices
|   │   │   |
|   │   │   ├── components/
|   │   │   |   ├── threshold.html
|   │   │   |   ├── item_management.html
|   │   │   |   ├── account_management.html
|   │   │   | 
|   │   │   ├── css/
|   │   │   ├── item_management.css
|   │   │   ├── inventory.css
|   │   │   ├── threshold.css
|   │   │   | 
|   │   │   ├── js/
|   │   │   |   account_management.js
|   │   │   ├── item_management.js
|   │   │   ├── threshold.js
|   │   │  
│   │   ├── salesreport/
|   │   │   ├── salesreport.html
|   │   │   ├── components/
|   │   │   ├── css/
|   │   │   ├── js/
│   ├
│   ├── header/ (used in home.html for notifcation and admin dashboard button)
│   │   ├── headerComponents.js
│   │   ├── headerTemplate.js
│   │   ├── header.js
│   │   ├── headerUtils.js

│   │   ├── home.html
│   ├── home/
│   │   ├── home.html
│   │   ├── login.html
│   ├── js/
│   │   ├── api.js
│   │   ├── cart.js
│   │   ├── main.js -- bootstraping of js functions
│   │   ├── modal.js
│   │   ├── payment.js
│   │   ├── session.js
│   │   ├── history.js
│   │   ├── config.js - export const API_BASE_PATH = `${window.location.origin}/code/api/`;
│   ├
│   ├── menu/
|   │   ├── images/
│   │   ├── menu.html   # calls main.js in js folder 
│   │   ├── menu.css   
│   │   ├── menuMain.js # just for logout only
|   |
│   ├── history/
|   │   ├── history.html  -- calls the history.js in js folder
│   │   ├── history.css  
│   ├── cart/
│   │   ├── cart.html
│   │   ├── cart.css
│   │   ├── cartMain.js
│   ├
│   ├── login/
│   │   ├── login.html
│   │   ├── loginMain.js
│   │ 
|   │   ├── css/
|   │   │   ├── login.css
|   │   
|   │   ├── js/
|   │   │   ├── config.js    
|   │   │   ├── auth.js
|   │   │   ├── init.js
|   │   │   ├── signup.js
|   │ 
|   │   ├── components/
|   │   │   
|   │   │   ├── login-form.html
|   │   │   ├── signup-form.html
|   │   │   ├── start-screen.html








