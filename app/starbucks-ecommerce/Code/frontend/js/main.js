import { API_BASE_PATH } from './config.js';
import { loadSizes, checkLoginOnLoad } from './session.js';
import { loadCategory, showCategories, loadTopSelling } from './api.js';
import { openModal, closeModal, addToCart } from './modal.js';
import { checkout, closePaymentModal, processPayment } from './payment.js';
// main.js
import { logout } from '../login/js/auth.js';  // add this


// 🔹 Profile modal elements
const profileIcon = document.getElementById("profile-icon");
const profileModal = document.getElementById("profile-modal");
const closeProfile = document.getElementById("close-profile");

if (profileIcon && profileModal && closeProfile) {
  profileIcon.addEventListener("click", () => {
    profileModal.style.display = "block";
    loadUserProfile();
  });

  closeProfile.addEventListener("click", () => {
    profileModal.style.display = "none";
  });

  // Update profile image from URL input
  const updateBtn = document.getElementById("update-profile-image");
  if (updateBtn) {
    updateBtn.addEventListener("click", () => {
      const url = document.getElementById("profile-image-url").value;
      if (url) {
        document.getElementById("profile-image").src = url;
        profileIcon.src = url;
      }
    });
  }

// Profile form submission
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
      const response = await fetch(`${API_BASE_PATH}/profile`, {   // ✅ only this one
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
}}


// 🔹 Load dropdowns
async function loadCountries() {
  const res = await fetch(`${API_BASE_PATH}/getCountries`);
  const countries = await res.json();
  const select = document.getElementById("country");
  if (!select) return;
  select.innerHTML = `<option value="">-- Select Country --</option>`;
  countries.forEach(c => {
    select.innerHTML += `<option value="${c.id}">${c.name}</option>`;
  });
}

async function loadProvinces(countryId) {
  const res = await fetch(`${API_BASE_PATH}/getProvince?country_id=${countryId}`);
  const provinces = await res.json();
  const select = document.getElementById("province");
  if (!select) return;
  select.innerHTML = `<option value="">-- Select Province --</option>`;
  provinces.forEach(p => {
    select.innerHTML += `<option value="${p.id}">${p.name}</option>`;
  });
}

async function loadCities(provinceId) {
  const res = await fetch(`${API_BASE_PATH}/getCities?province_id=${provinceId}`);
  const cities = await res.json();
  const select = document.getElementById("city");
  if (!select) return;
  select.innerHTML = `<option value="">-- Select City --</option>`;
  cities.forEach(c => {
    select.innerHTML += `<option value="${c.id}">${c.name}</option>`;
  });
}

// 🔹 Attach dropdown events
document.getElementById("country")?.addEventListener("change", (e) => {
  const countryId = e.target.value;
  if (countryId) {
    loadProvinces(countryId);
    document.getElementById("city").innerHTML = `<option value="">-- Select City --</option>`;
  }
});

document.getElementById("province")?.addEventListener("change", (e) => {
  const provinceId = e.target.value;
  if (provinceId) loadCities(provinceId);
});

// 🔹 Load user profile
async function loadUserProfile() {
  try {
    const res = await fetch(`${API_BASE_PATH}/profile`, {
      method: "GET",
      credentials: "include"
    });
    const data = await res.json();
    if (!data.status) return;

    const user = data.user;
    document.getElementById("first_name").value = user.first_name || "";
    document.getElementById("middle_name").value = user.middle_name || "";
    document.getElementById("last_name").value = user.last_name || "";
    document.getElementById("street").value = user.address?.street || "";
    if (user.image_url) {
      document.getElementById("profile-image").src = user.image_url;
      profileIcon.src = user.image_url;
      document.getElementById("profile-image-url").value = user.image_url;
    }

    await loadCountries();
    if (user.address?.country) {
      await loadProvinces(user.address.country);
      document.getElementById("country").value = user.address.country;

      if (user.address?.province) {
        await loadCities(user.address.province);
        document.getElementById("province").value = user.address.province;

        if (user.address?.city) {
          document.getElementById("city").value = user.address.city;
        }
      }
    }
  } catch (err) {
    console.error("Failed to load user profile:", err);
  }
}

// 🔹 Profile image picker modal
const imagePickerModal = document.getElementById("image-picker-modal");
const openImagePicker = document.getElementById("open-image-picker");
const closeImagePicker = document.getElementById("close-image-picker");
const imageGrid = document.getElementById("image-picker-grid");

const availableImages = ["male.png", "male2.png", "female.png", "female2.png"];

openImagePicker?.addEventListener("click", () => {
  if (!imageGrid) return;
  imageGrid.innerHTML = "";

  availableImages.forEach(file => {
    const img = document.createElement("img");
    img.src = `profiles/${file}`;
    img.alt = file;
    img.style.cssText = "width:80px;height:80px;cursor:pointer;border-radius:50%;margin:5px;";
    img.addEventListener("click", () => {
      document.getElementById("profile-image").src = img.src;
      document.getElementById("profile-icon").src = img.src;
      document.getElementById("profile-image-url").value = img.src;
      imagePickerModal.style.display = "none";
    });
    imageGrid.appendChild(img);
  });

  imagePickerModal.style.display = "block";
});

closeImagePicker?.addEventListener("click", () => {
  imagePickerModal.style.display = "none";
});

window.addEventListener("click", (e) => {
  if (e.target === imagePickerModal) imagePickerModal.style.display = "none";
});

// 🔹 Guest/login check
loadSizes();
if (!localStorage.getItem("isGuest") && !localStorage.getItem("user")) {
  localStorage.setItem("isGuest", true);
} else {
  checkLoginOnLoad();
}

// 🔹 Expose functions globally
window.loadCategory = loadCategory;
window.showCategories = showCategories;
window.loadTopSelling = loadTopSelling;
window.openModal = openModal;
window.closeModal = closeModal;
window.addToCart = addToCart;
window.checkout = checkout;
window.closePaymentModal = closePaymentModal;
window.processPayment = processPayment;
window.logout = logout;

