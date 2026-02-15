export const headerHTML = `
    <ul id="head-nav">
        <li class="logo-container">
            <img class="logo" src="../assets/starbucksLogo-nocircle.png" alt="starbucks" onclick="window.location.href='../home/home.html'">
        </li>
        <li class="nav-list">
            <nav>
                <a href="../home/home.html">Home</a>
                <a href="../menu/menu.html">Menu</a>
                <a href="../aboutUs/aboutUs.html">About Us</a>
                <a href="../../frontend/admin/panel/panel.html" id="admin-link" style="display:none;">Admin Dashboard</a>
            </nav>
        </li>
        <li class="icon-list-container">
            <ul id="icon-list">
                <li>
                    <a href="../cart/cart.html">
                        <img class="icon" src="../assets/shopping-cart.png" alt="cart">
                    </a>
                </li>
                <!-- 🔹 Notification Icon (only visible for admin) -->
            <!-- 🔹 Notification Icon (only visible for admin) -->
            <li id="notification-container" style="display:none; position: relative;">
                <img class="icon" id="notification-icon" src="../assets/notification.png" alt="notifications">
                <span id="notification-badge" style="
                    display:none;
                    position:absolute;
                    top:-5px;
                    right:-5px;
                    background:red;
                    color:white;
                    border-radius:50%;
                    padding:2px 6px;
                    font-size:12px;
                ">0</span>
            </li>

                <li>
                    <img class="icon" id="profile-icon" src="../assets/user.png" alt="user">
                </li>
                <li>
                    <button onclick="window.location.href='../login/login.html'">LOG IN</button>
                </li>
            </ul>
        </li>
    </ul>

    <!-- Profile Modal -->
    <div id="profile-modal" class="modal">
        <div class="modal-content">
            <span id="close-profile" class="close">&times;</span>
            <form id="profile-form">
                <label>First Name</label>
                <input type="text" id="first_name" name="first_name">
                <label>Middle Name</label>
                <input type="text" id="middle_name" name="middle_name">
                <label>Last Name</label>
                <input type="text" id="last_name" name="last_name">
                <h3>Address</h3>
                <input type="text" id="street" placeholder="Street">
                <label for="country">Country</label>
                <select id="country"></select>
                <label for="province">Province</label>
                <select id="province"></select>
                <label for="city">City</label>
                <select id="city"></select>
                <button type="submit">Save Changes</button>
            </form>
        </div>
    </div>

    <!-- 🔹 Notifications Modal (only for admin) -->
    <div id="notification-modal" class="modal">
        <div class="modal-content">
            <span id="close-notification" class="close">&times;</span>
            <h2>Admin Notifications</h2>
            <ul id="notification-list">
                <!-- Notifications will be dynamically inserted here -->
            </ul>
        </div>
    </div>

`;
