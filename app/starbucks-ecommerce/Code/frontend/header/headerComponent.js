// headerComponent.js - Main HeaderComponent class
import { headerHTML } from './headerTemplate.js';
import { headerCSS } from './headerStyles.js';
import { logout } from "../../frontend/login/js/auth.js";
import { notifyAdminLowStock } from "../admin/inventory/js/ingredient_management.js";

import {  
  loadUserProfile, 
  ensureCSSVariables, 
  loadCountries,
  loadProvinces,
  loadCities
} from './headerUtils.js';

class HeaderComponent {
    constructor() {
        this.headerHTML = headerHTML;
        this.headerCSS = headerCSS;
        this.lowStockItems = []; // Track low stock items
    }

    render() {
        // Create header container
        const headerContainer = document.createElement('header');
        headerContainer.innerHTML = this.headerCSS + this.headerHTML;

        // Add to the beginning of the body
        document.body.insertBefore(headerContainer, document.body.firstChild);

        // Add CSS to prevent space at top
        const bodyStyle = document.createElement('style');
        bodyStyle.textContent = `
            body {
                margin: 0 !important;
                padding: 0 !important;
            }

            html, body {
                margin: 0;
                padding: 0;
                overflow-x: hidden;
            }

            body {
                padding-top: 0 !important;
            }

            section:first-of-type {
                padding-top: 0 !important;
            }
        `;
        document.head.appendChild(bodyStyle);

        // Make sure CSS variables are defined
        ensureCSSVariables();

        // Load and display user profile image if available
        this.loadProfileImage();

        // Setup profile modal functionality
        this.setupProfileModal();

        // Update section padding when window resizes
        this.updateSectionPadding();
        window.addEventListener('resize', this.updateSectionPadding.bind(this));

        // Initialize profile modal functionality
        this.initProfileModal();

        // ✅ Listen for low stock notifications from IngredientManager
        this.setupLowStockListener();

    }   

    updateSectionPadding() {
        const header = document.querySelector('#head-nav');
        const firstSection = document.querySelector('section:first-of-type');

        if (header && firstSection) {
            const headerHeight = header.offsetHeight;
            firstSection.style.paddingTop = headerHeight + 'px';
        }
    }

    async loadProfileImage() {
        const isLoggedIn = localStorage.getItem("isLoggedIn") === "true";
        if (!isLoggedIn) return;

        // Then try to fetch from API for the latest image
        const response = await fetch(`${API_BASE_PATH}/profile`, {
            method: "GET",
            credentials: "include"
        });
    }

    setupProfileModal() {
        // Wait for DOM to be fully loaded
        setTimeout(() => {
            // --- Profile Modal Setup ---
            const profileIcon = document.getElementById('profile-icon');
            const profileModal = document.getElementById('profile-modal');
            const closeProfile = document.getElementById('close-profile');

            if (profileIcon && profileModal && closeProfile) {
                profileIcon.addEventListener('click', () => {
                    profileModal.style.display = "block";
                    if (typeof loadUserProfile === 'function') {
                        loadUserProfile();
                    }
                });

                closeProfile.addEventListener('click', () => {
                    profileModal.style.display = "none";
                });

                window.addEventListener('click', (event) => {
                    if (event.target === profileModal) {
                        profileModal.style.display = "none";
                    }
                });
            }

            // --- 🔹 Notification Modal Setup ---
            const notifIcon = document.getElementById('notification-icon');
            const notifModal = document.getElementById('notification-modal');
            const closeNotif = document.getElementById('close-notification');

            if (notifIcon && notifModal && closeNotif) {
                notifIcon.addEventListener('click', () => {
                    notifModal.style.display = "block";
                });

                closeNotif.addEventListener('click', () => {
                    notifModal.style.display = "none";
                });

                window.addEventListener('click', (event) => {
                    if (event.target === notifModal) {
                        notifModal.style.display = "none";
                    }
                });
            }

            // --- Dropdowns for Profile Modal ---
            const countrySelect = document.getElementById("country");
            const provinceSelect = document.getElementById("province");
            const citySelect = document.getElementById("city");

            if (countrySelect) {
                loadCountries();
                countrySelect.addEventListener("change", (e) => {
                    const countryId = e.target.value;
                    if (countryId) {
                        loadProvinces(countryId);
                        if (citySelect) {
                            citySelect.innerHTML = `<option value="">-- Select City --</option>`;
                        }
                    }
                });
            }

            if (provinceSelect) {
                provinceSelect.addEventListener("change", (e) => {
                    const provinceId = e.target.value;
                    if (provinceId) loadCities(provinceId);
                });
            }
        }, 100);
    }

initProfileModal() {
    const userData = localStorage.getItem("loggedInUser");
    const isLoggedIn = localStorage.getItem("isLoggedIn") === "true";

    if (isLoggedIn && userData) {
        const user = JSON.parse(userData);

        // ✅ Check if user.type is admin
        if (user.type && user.type.toLowerCase() === "admin") {
            const adminLink = document.getElementById("admin-link");
            const notifIcon = document.getElementById("notification-container");

            if (adminLink) {
                adminLink.style.display = "inline-block";
            }

            if (notifIcon) {
                // ✅ Only show notification icon on home.html
                const currentPage = window.location.pathname.split("/").pop();
                if (currentPage === "home.html") {
                    notifIcon.style.display = "inline-block";
                } else {
                    notifIcon.style.display = "none"; // hide on other pages
                }
            }
        }

        // ✅ Change login button → logout
        const signUpButton = document.querySelector('#icon-list button');
        if (signUpButton) {
            signUpButton.textContent = "LOGOUT";
            signUpButton.onclick = () => {
                logout();
            };
        }
    }

    // ✅ Profile form submission
    const profileForm = document.getElementById('profile-form');
    if (profileForm) {
        profileForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const payload = {
                first_name: document.getElementById("first_name").value,
                middle_name: document.getElementById("middle_name").value,
                last_name: document.getElementById("last_name").value,
                street: document.getElementById("street").value,
                country: parseInt(document.getElementById("country").value) || null,
                province: parseInt(document.getElementById("province").value) || null,
                city: parseInt(document.getElementById("city").value) || null,
            };

            try {
                const response = await fetch(`${API_BASE_PATH}/profile`, {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    credentials: "include",
                    body: JSON.stringify(payload)
                });

                const data = await response.json();
                if (data.status) {
                    alert(data.message || "Profile updated successfully!");
                    document.getElementById("profile-modal").style.display = "none";
                } else {
                    alert(data.message || "Failed to update profile.");
                }
            } catch (error) {
                console.error("Error updating profile:", error);
                alert("Error updating profile");
            }
        });
    }
}

    /**
     * ✅ Setup listener for low stock notifications from IngredientManager
     */
setupLowStockListener() {
    console.log("👂 HeaderComponent listening for lowStockNotification...");
    
    // 🔄 Listen on window instead of document
    window.addEventListener("lowStockNotification", (e) => {
        console.log("📥 HeaderComponent RECEIVED notification:", e.detail);
        const { item, threshold } = e.detail;
        this.addNotification(item, threshold);
    });

    window.addEventListener("updateLowStockList", (e) => {
        const { currentLowStockIds } = e.detail;
    });
}


    /**
     * ✅ Add notification to the notification list
     */
addNotification(item, threshold) {
    console.log("📝 Adding notification for:", item, "Threshold:", threshold);
    const notifList = document.getElementById("notification-list");
    const notifBadge = document.getElementById("notification-badge");
    
    if (!notifList) {
        console.warn("⚠ notification-list element not found in DOM");
        return;
    }

    // Check if notification already exists for this item
    const existingNotif = Array.from(notifList.children).find(li => 
        li.dataset.itemId === String(item.id)
    );

    if (existingNotif) {
        existingNotif.innerHTML = `
            <strong>${this.escapeHtml(item.name)}</strong> is low:
            <span style="color:red;">${item.quantity_in_stock} ${item.stock_unit}</span>
            (Threshold: ${threshold} ${item.stock_unit})
        `;
        return;
    }

    // Create new notification
    const li = document.createElement("li");
    li.dataset.itemId = item.id;
    li.innerHTML = `
        <strong>${this.escapeHtml(item.name)}</strong> is low:
        <span style="color:red;">${item.quantity_in_stock} ${item.stock_unit}</span>
        (Threshold: ${threshold} ${item.stock_unit})
    `;

    // ✅ Add click handler to jump to ingredient
    li.addEventListener("click", () => {
        // close notif modal
        const notifModal = document.getElementById("notification-modal");
        if (notifModal) notifModal.style.display = "none";

        // scroll to ingredient in the list
        const ingElem = document.querySelector(`.ingredient-item[data-id="${item.id}"]`);
        if (ingElem) {
            ingElem.scrollIntoView({ behavior: "smooth", block: "center" });
            ingElem.classList.add("highlight");

            // remove highlight after 2s
            setTimeout(() => ingElem.classList.remove("highlight"), 2000);
        }
    });

    // Add to beginning of list
    notifList.insertBefore(li, notifList.firstChild);

    // Track low stock item
    this.lowStockItems.push({
        id: item.id,
        name: item.name,
        quantity: item.quantity_in_stock,
        unit: item.stock_unit,
        threshold
    });

    // Update badge count
    if (notifBadge) {
        notifBadge.textContent = this.lowStockItems.length;
        notifBadge.style.display = this.lowStockItems.length > 0 ? "inline-block" : "none";
    }

    // Show alert for admin
    const userData = localStorage.getItem("loggedInUser");
    const isLoggedIn = localStorage.getItem("isLoggedIn") === "true";
    
    if (isLoggedIn && userData) {
        const user = JSON.parse(userData);
        if (user.type && user.type.toLowerCase() === "admin") {
            console.log(`⚠ Admin Alert: ${item.name} is below threshold!`);
        }
    }
}
escapeHtml(text) {
    if (typeof text !== "string") return text;
    return text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}
}
export { HeaderComponent };