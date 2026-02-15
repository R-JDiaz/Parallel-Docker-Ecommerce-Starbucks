// signup.js
import { API_BASE_PATH } from "./config.js";

class SignupManager {
    constructor(apiBasePath) {
        this.apiBasePath = apiBasePath;
        this.selectedCountry = null;
        this.selectedProvince = null;
        this.selectedCity = null;
        this.otpModal = document.getElementById("otpModal");
        this.originalOtpContent = this.otpModal.querySelector(".otp-modal-content").innerHTML;

        this.countryContainer = document.getElementById("country-buttons");
        this.provinceContainer = document.getElementById("province-buttons");
        this.cityContainer = document.getElementById("city-buttons");

        console.log("signup.js loaded, initializing...");
        this.loadCountries();

        // ✅ Ensure password validation attaches after DOM is ready
        this.safeInitPasswordValidation();
    }

    safeInitPasswordValidation() {
        // Run once DOM is ready
        document.addEventListener("DOMContentLoaded", () => {
            this.initPasswordValidation();
        });

        // Backup: run again after a short delay (in case DOMContentLoaded already fired)
        setTimeout(() => this.initPasswordValidation(), 50);
    }

    initPasswordValidation() {
        const passwordInput = document.getElementById("signupPass");
        if (!passwordInput) return;

        let feedback = document.getElementById("passwordFeedback");
        if (!feedback) {
            feedback = document.createElement("div");
            feedback.id = "passwordFeedback";
            feedback.style.fontSize = "14px";
            feedback.style.marginTop = "5px";
            passwordInput.parentNode.appendChild(feedback);
        }

        passwordInput.addEventListener("input", () => {
            if (passwordInput.value.length === 0) {
                feedback.textContent = "";
            } else if (passwordInput.value.length < 6) {
                feedback.textContent = "❌ Password must be at least 6 characters";
                feedback.style.color = "red";
            } else {
                feedback.textContent = "✅ Strong enough";
                feedback.style.color = "green";
            }
        });
    }

    async loadCountries() {
        try {
            const res = await fetch(`${this.apiBasePath}/getCountries`);
            const countries = await res.json();
            this.renderButtons(this.countryContainer, countries, country => this.selectCountry(country));
        } catch (err) {
            console.error("Error fetching countries:", err);
        }
    }

    async selectCountry(country) {
        this.selectedCountry = country;
        this.selectedProvince = null;
        this.selectedCity = null;

        document.getElementById("selected-country").textContent = country.name;
        document.getElementById("selected-province").textContent = "";
        document.getElementById("selected-city").textContent = "";
        document.getElementById("postalCode").value = "";

        document.getElementById("country-container").style.display = "none";
        document.getElementById("province-container").style.display = "block";

        try {
            const res = await fetch(`${this.apiBasePath}/getProvince?country_id=${country.id}`);
            const provinces = await res.json();
            this.renderButtons(this.provinceContainer, provinces, province => this.selectProvince(province));
        } catch (err) {
            console.error("Error fetching provinces:", err);
        }
    }

    async selectProvince(province) {
        this.selectedProvince = province;
        this.selectedCity = null;

        document.getElementById("selected-province").textContent = province.name;
        document.getElementById("selected-city").textContent = "";
        document.getElementById("postalCode").value = "";

        document.getElementById("province-container").style.display = "none";
        document.getElementById("city-container").style.display = "block";

        try {
            const res = await fetch(`${this.apiBasePath}/getCities?province_id=${province.id}`);
            const cities = await res.json();
            this.renderButtons(this.cityContainer, cities, city => this.selectCity(city));
        } catch (err) {
            console.error("Error fetching cities:", err);
        }
    }

    selectCity(city) {
        this.selectedCity = city;
        document.getElementById("selected-city").textContent = city.name;
        document.getElementById("city-container").style.display = "none";
        document.getElementById("postalCode").value = city.postal_code || "";
    }

    renderButtons(container, items, onClick) {
        container.innerHTML = "";
        items.forEach(item => {
            const btn = document.createElement("button");
            btn.textContent = item.name;
            btn.onclick = () => onClick(item);
            container.appendChild(btn);
        });
    }

    submitSignup() {
        const password = document.getElementById('signupPass').value;
        if (password.length < 6) {
            alert("Password must be at least 6 characters long.");
            return;
        }

        const payload = {
            first_name: document.getElementById('firstName').value.trim(),
            middle_name: document.getElementById('middleName').value.trim(),
            last_name: document.getElementById('lastName').value.trim(),
            email: document.getElementById('signupEmail').value.trim(),
            password: password,
            phone: document.getElementById('signupPhone').value.trim(),
            street: document.getElementById('street').value.trim(),
            city: this.selectedCity?.name || '',
            province: this.selectedProvince?.name || '',
            postal_code: document.getElementById('postalCode').value.trim(),
            country: this.selectedCountry?.name || ''
        };

        signup(payload); // Assuming signup is globally available
    }

    goBack() {
        history.back();
    }
}

// Create singleton
export const signupManager = new SignupManager(API_BASE_PATH);

window.selectCountry = (country) => signupManager.selectCountry(country);
window.selectProvince = (province) => signupManager.selectProvince(province);
window.selectCity = (city) => signupManager.selectCity(city);
window.submitSignup = () => signupManager.submitSignup();
window.goBack = () => signupManager.goBack();
