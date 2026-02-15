******************************* DATABASE SUMMARY **********************************

[model]         --> contains Table for DATABASE
    attributes_template     --> Coffeine level etc.
    category                --> Drinks, Sandwich etc.
    itemattributes          --> Contains Attribute templates and starcbucksitem linked
    starbucksitem           --> Base Item
    subcategory             --> Drinks = Expresso
    contacts                --> Email, Phone
    user                    --> User of website "Customer"
    admin                   --> Manager
    auth                    --> Logins (user, admin)
    address                 --> Location (user, admin) for delivery & supply purposes
    userorder               --> Individual order itself contains price & quantity
    order_item              --> contains the userorders to combine all and can get the total price etc.
    receipt                 --> Contains receipt of the order (payment, change etc.)
    discount                --> contains pwd, senior, store discounts apply to the total price!
    city                    --> Used in address 
    province                --> used in address
    country                 --> used in address
    inventory setting       --> global scope of threshold for items

StarbucksItem -->
    CREATE TABLE IF NOT EXISTS starbucksitem (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        price DECIMAL(10,2) NOT NULL,               -- base price before size modifier
        category_id INT NOT NULL,
        subcategory_id INT NOT NULL,
        description TEXT,
        image_url VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,StarbucksItem -->
    CREATE TABLE IF NOT EXISTS starbucksitem (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        price DECIMAL(10,2) NOT NULL,               -- base price before size modifier
        category_id INT NOT NULL,
        subcategory_id INT NOT NULL,
        description TEXT,
        image_url VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES category(id),
        FOREIGN KEY (subcategory_id) REFERENCES subcategory(id)

Category -->
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

Subcategory --> 
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL,
        category_id INT NOT NULL,
        FOREIGN KEY (category_id) REFERENCES category(id) ON DELETE CASCADE

Attribute Templates -->
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

Item Attributes --> 
        id INT AUTO_INCREMENT PRIMARY KEY,
        item_id INT NOT NULL,
        attribute_template_id INT NOT NULL,
        attribute_value VARCHAR(30) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (item_id) REFERENCES starbucksitem(id) ON DELETE CASCADE,
        FOREIGN KEY (attribute_template_id) REFERENCES attribute_template(id) ON DELETE CASCADE

ingredient 
        CREATE TABLE IF NOT EXISTS item_ingredient
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        quantity_in_stock DECIMAL(10, 2) DEFAULT 0, -- bulk stock in base unit
        stock_unit VARCHAR(20),                     -- 'kg', 'L', 'pcs'
        supplier_id INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

Item_ingredient
    CREATE TABLE IF NOT EXISTS item_ingredient (
        id INT AUTO_INCREMENT PRIMARY KEY,
        item_id INT NOT NULL,
        ingredient_id INT NOT NULL,
        quantity_value DECIMAL(10, 2) NOT NULL,  -- amount per item
        quantity_unit VARCHAR(20) NOT NULL,      -- e.g., 'g', 'ml', 'pcs'
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (item_id) REFERENCES starbucksitem(id) ON DELETE CASCADE,
        FOREIGN KEY (ingredient_id) REFERENCES ingredient(id) ON DELETE CASCADE,
        UNIQUE (item_id, ingredient_id)

ready_item_stock
    CREATE TABLE IF NOT EXISTS ready_item_stock (
        id INT AUTO_INCREMENT PRIMARY KEY,
        item_id INT NOT NULL,
        size_id INT NOT NULL,
        quantity INT NOT NULL DEFAULT 0, -- how many ready-to-sell items
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (item_id) REFERENCES starbucksitem(id) ON DELETE CASCADE,
        FOREIGN KEY (size_id) REFERENCES size(id) ON DELETE CASCADE,
        UNIQUE (item_id, size_id)
 
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES category(id),
        FOREIGN KEY (subcategory_id) REFERENCES subcategory(id)

Category -->
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

Subcategory --> 
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL,
        category_id INT NOT NULL,
        FOREIGN KEY (category_id) REFERENCES category(id) ON DELETE CASCADE


ingredient 
        CREATE TABLE IF NOT EXISTS item_ingredient
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        quantity_in_stock DECIMAL(10, 2) DEFAULT 0, -- bulk stock in base unit
        stock_unit VARCHAR(20),                     -- 'kg', 'L', 'pcs'
        supplier_id INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

Item_ingredient
    CREATE TABLE IF NOT EXISTS item_ingredient (
        id INT AUTO_INCREMENT PRIMARY KEY,
        item_id INT NOT NULL,
        ingredient_id INT NOT NULL,
        quantity_value DECIMAL(10, 2) NOT NULL,  -- amount per item
        quantity_unit VARCHAR(20) NOT NULL,      -- e.g., 'g', 'ml', 'pcs'
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (item_id) REFERENCES starbucksitem(id) ON DELETE CASCADE,
        FOREIGN KEY (ingredient_id) REFERENCES ingredient(id) ON DELETE CASCADE,
        UNIQUE (item_id, ingredient_id)

Attribute Templates -->
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,


Item Attributes --> 
        id INT AUTO_INCREMENT PRIMARY KEY,
        item_id INT NOT NULL,
        attribute_template_id INT NOT NULL,
        attribute_value VARCHAR(30) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (item_id) REFERENCES starbucksitem(id) ON DELETE CASCADE,
        FOREIGN KEY (attribute_template_id) REFERENCES attribute_template(id) ON DELETE CASCADE

Admin -->
        CREATE TABLE admin (
        id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(50) NOT NULL,
        middle_name VARCHAR(50),
        last_name VARCHAR(50) NOT NULL,
        role VARCHAR(50) DEFAULT 'admin',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

User -->
        id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(50) NOT NULL,
        middle_name VARCHAR(50),
        last_name VARCHAR(50) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

Auth -->
        CREATE TABLE auth (
        id INT AUTO_INCREMENT PRIMARY KEY,
        account_type ENUM('user', 'admin') NOT NULL,
        account_id INT NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT uniq_auth_account UNIQUE (account_type, account_id),
        INDEX idx_auth_type_id (account_type, account_id)

Contact -->
        CREATE TABLE contact (
        id INT AUTO_INCREMENT PRIMARY KEY,
        contactable_type ENUM( 'user', 'admin') NOT NULL,
        contactable_id INT NOT NULL,
        contact_type ENUM('email', 'phone') NOT NULL,
        value VARCHAR(100) NOT NULL,
        UNIQUE (contactable_type, contactable_id, contact_type, value),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

Address -->
        CREATE TABLE address (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        addressable_type ENUM('user','admin') NOT NULL,
        addressable_id INT UNSIGNED NOT NULL,
        street VARCHAR(255),
        country_id INT UNSIGNED,
        province_id INT UNSIGNED,
        city_id INT UNSIGNED,
        CONSTRAINT fk_address_unique UNIQUE (addressable_type, addressable_id),
        FOREIGN KEY (country_id) REFERENCES country(id) ON DELETE SET NULL,
        FOREIGN KEY (province_id) REFERENCES province(id) ON DELETE SET NULL,
        FOREIGN KEY (city_id) REFERENCES city(id) ON DELETE SET NULL

Userorder --> 
        CREATE TABLE userorder (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        total_amount DECIMAL(10,2) DEFAULT 0,
        status VARCHAR(50) DEFAULT 'pending',
        placed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
        
Orderitem -->
        CREATE TABLE order_item (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        item_id INT NOT NULL,
        quantity INT NOT NULL,
        unit_price DECIMAL(10,2) NOT NULL,
        total_price DECIMAL(10,2) GENERATED ALWAYS AS (quantity * unit_price) STORED,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES `userorder`(id) ON DELETE CASCADE,
        FOREIGN KEY (item_id) REFERENCES starbucksitem(id) ON DELETE CASCADE

Receipt -->
        CREATE TABLE receipt (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        discount_type ENUM('none', 'senior', 'store_card', 'custom') DEFAULT 'none',
        discount_value DECIMAL(5,2) DEFAULT 0.00, -- percentage like 10.00 or 12.00
        discount_amount DECIMAL(10,2) DEFAULT 0.00, -- actual amount deducted
        final_amount DECIMAL(10,2) NOT NULL, -- total after discount
        payment_amount DECIMAL(10,2) NOT NULL, -- amount customer paid
        change_amount DECIMAL(10,2) GENERATED ALWAYS AS (payment_amount - final_amount) STORED,
        issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES userorder(id) ON DELETE CASCADE

Discount 
    CREATE TABLE discount (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL, -- 'Senior', 'PWD', etc.
        description TEXT,
        discount_percentage DECIMAL(5,2) NOT NULL CHECK (discount_percentage >= 0.00 AND discount_percentage <= 100.00),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )

City -->
        CREATE TABLE city
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        province_id INT UNSIGNED NOT NULL,
        name VARCHAR(100) NOT NULL,
        postal_code VARCHAR(20),
        FOREIGN KEY (province_id) REFERENCES province(id) ON DELETE CASCADE

Country -->
        CREATE TABLE country
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE

Province -->
        CREATE TABLE province
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        country_id INT UNSIGNED NOT NULL,
        name VARCHAR(100) NOT NULL,
        FOREIGN KEY (country_id) REFERENCES country(id) ON DELETE CASCADE

Inventory Settings -->
    CREATE TABLE inventory_settings 
        id INT AUTO_INCREMENT PRIMARY KEY,
        global_threshold INT NOT NULL,
        updated_by INT,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (updated_by) REFERENCES admin(id) ON DELETE SET NULL

SIZE SYSTEM -->
    The size system is category-specific and managed through size tables:
    
    Available Sizes:
    - Default: Used for food items (no price modifier)
    - Tall/Grande/Venti: Used for beverages only (with price modifiers)
    - Small/Medium/Large: Used for merchandise items (with price modifiers)
    
    Size Relationships:
    - item_size: Links starbucksitem to available sizes
    - merchandise_size: Links merchandise to available sizes
    
    Price Modifiers:
    - Tall: +₱0.00
    - Grande: +₱10.00  
    - Venti: +₱20.00
    - Small: +₱0.00
    - Medium: +₱5.00
    - Large: +₱10.00


