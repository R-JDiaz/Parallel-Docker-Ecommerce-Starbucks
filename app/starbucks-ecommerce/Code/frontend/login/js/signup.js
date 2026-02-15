// signup.js
import { API_BASE_PATH } from "./config.js";
import { showForm, signup as authSignup } from "./auth.js";

class SignupManager {
    constructor(apiBasePath) {
        this.apiBasePath = apiBasePath;
        this.selectedCountry = null;
        this.selectedProvince = null;
        this.selectedCity = null;

        this.countryContainer = document.getElementById("country-buttons");
        this.provinceContainer = document.getElementById("province-buttons");
        this.cityContainer = document.getElementById("city-buttons");

        this.signupBtn = document.getElementById("signupBtn");

        // Inputs
        this.firstName = document.getElementById("firstName");
        this.middleName = document.getElementById("middleName");
        this.lastName = document.getElementById("lastName");
        this.email = document.getElementById("signupEmail");
        this.password = document.getElementById("signupPass");
        this.phone = document.getElementById("signupPhone");

        // Add live validation
        this.addValidationListeners();

        // OTP modal elements (if OTP functionality is needed)
        this.otpModal = document.getElementById("otpModal");
        if (this.otpModal) {
            this.originalOtpContent = this.otpModal.querySelector(".otp-modal-content")?.innerHTML;
            this.bindOtpModalElements();
        }

        console.log("signup.js loaded, initializing...");
        this.loadCountries();
    }

    /* ================= Validators ================= */
    isValidName(name) {
        return /^[A-Za-z\s]+$/.test(name);
    }

    isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    isValidPhone(phone) {
        return /^(09\d{9}|\+63\d{10})$/.test(phone);
    }

    isValidPassword(password) {
        return password.length >= 6;
    }

    addValidationListeners() {
        const fields = [
            { input: this.firstName, validator: this.isValidName, errorId: "firstNameError", message: "Only letters and spaces allowed" },
            { input: this.middleName, validator: this.isValidName, errorId: "middleNameError", message: "Only letters and spaces allowed" },
            { input: this.lastName, validator: this.isValidName, errorId: "lastNameError", message: "Only letters and spaces allowed" },
            { input: this.email, validator: this.isValidEmail, errorId: "emailError", message: "Enter a valid email (gmail, yahoo, email)" },
            { input: this.phone, validator: this.isValidPhone, errorId: "phoneError", message: "Enter a valid PH number (09XXXXXXXXX or +63XXXXXXXXXX)" },
            { input: this.password, validator: this.isValidPassword, errorId: "passwordError", message: "Password must be at least 6 characters" }
        ];

        fields.forEach(({ input, validator, errorId, message }) => {
            let errorEl = document.getElementById(errorId);
            if (!errorEl) {
                errorEl = document.createElement("small");
                errorEl.id = errorId;
                errorEl.style.color = "red";
                input.insertAdjacentElement("afterend", errorEl);
            }

            input.addEventListener("input", () => {
                if (!validator.call(this, input.value.trim()) && input.value.trim() !== "") {
                    errorEl.textContent = message;
                } else {
                    errorEl.textContent = "";
                }
                this.toggleSignupButton();
            });
        });
    }

    toggleSignupButton() {
        const valid =
            this.isValidName(this.firstName.value.trim()) &&
            (this.middleName.value.trim() === "" || this.isValidName(this.middleName.value.trim())) &&
            this.isValidName(this.lastName.value.trim()) &&
            this.isValidEmail(this.email.value.trim()) &&
            this.isValidPhone(this.phone.value.trim()) &&
            this.isValidPassword(this.password.value.trim());

        this.signupBtn.disabled = !valid;
    }

    /* ================= Address Selection ================= */
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

    /* ================= Signup & OTP (Modified to use auth.js) ================= */
    async submitSignup() {
        if (this.signupBtn.disabled) return;

        this.payload = {
            first_name: this.firstName.value.trim(),
            middle_name: this.middleName.value.trim(),
            last_name: this.lastName.value.trim(),
            email: this.email.value.trim(),
            password: this.password.value,
            phone: this.phone.value.trim(),
            street: document.querySelectorAll("input#street")[1]?.value.trim() || "",
            city: this.selectedCity?.name || '',
            province: this.selectedProvince?.name || '',
            postal_code: document.getElementById('postalCode').value.trim(),
            country: this.selectedCountry?.name || ''
        };

        if (this.otpModal) {
            try {
                // 🔹 Show loading immediately
                this.showOtpLoadingOverlay();

                const res = await fetch(`${this.apiBasePath}/send-otp`, {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ email: this.payload.email })
                });

                const data = await res.json();

                if (data.success) {
                    // Wait 2s to simulate loading before OTP modal
                    setTimeout(() => {
                        this.hideOtpLoadingOverlay();
                        this.openOtpModal();
                    }, 2000);
                } else {
                    this.hideOtpLoadingOverlay();
                    alert("Failed to send OTP. Try again.");
                }
            } catch (err) {
                this.hideOtpLoadingOverlay();
                console.error("Signup error:", err);
                alert("Something went wrong. Please try again.");
            }
        } else {
            // Normal signup without OTP
            await authSignup(this.payload);
        }
    }

    async verifyOtp() {
        const otp = this.otpInput.value.trim();
        if (!otp || otp.length !== 6) {
            alert("Enter a valid 6-digit OTP");
            return;
        }

        try {
            console.log("Sending verification request:", {
                email: this.payload.email,
                otp: otp,
                user: this.payload
            });

            const verifyRes = await fetch(`${this.apiBasePath}/verify-otp`, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ email: this.payload.email, otp, user: this.payload })
            });
            
            const verifyData = await verifyRes.json();
            console.log("Verification response:", verifyData);

            if (verifyData.success) {
                this.otpModal.querySelector(".otp-modal-content").innerHTML = `
                    <div class="success-check">✔</div>
                    <h2>Signup Successful!</h2>
                    <p>Redirecting to login...</p>
                `;

                setTimeout(async () => {
                    this.closeOtpModal();
                    // Use auth.js showForm instead of manual redirect
                    await showForm("login");
                }, 1500);
            } else {
                alert(`Error: ${verifyData.message}`);
                console.error("Verification failed:", verifyData);
            }
        } catch (err) {
            console.error("Verify OTP error:", err);
            alert("Something went wrong during OTP verification.");
        }
    }
showOtpLoading() {
    if (!this.otpModal) return;
    this.otpModal.style.display = "flex";
    this.otpModal.querySelector(".otp-modal-content").innerHTML = `
        <div style="text-align:center; padding:20px;">
            <p>Loading...</p>
            <div class="spinner" style="margin-top:10px;">⏳</div>
        </div>
    `;
}
showOtpLoadingOverlay() {
    const overlay = document.getElementById("otpLoadingOverlay");
    if (overlay) overlay.style.display = "flex";
}

hideOtpLoadingOverlay() {
    const overlay = document.getElementById("otpLoadingOverlay");
    if (overlay) overlay.style.display = "none";
}


    /* ================= OTP Modal Functions ================= */
    openOtpModal() {
        if (!this.otpModal) return;
        this.restoreOtpModal();
        this.otpModal.style.display = "flex";
        this.startOtpCountdown();
    }

    closeOtpModal() {
        if (!this.otpModal) return;
        this.otpModal.style.display = "none";
        this.restoreOtpModal();
    }

    restoreOtpModal() {
        if (!this.otpModal || !this.originalOtpContent) return;
        this.otpModal.querySelector(".otp-modal-content").innerHTML = this.originalOtpContent;
        this.bindOtpModalElements();
    }

    bindOtpModalElements() {
        if (!this.otpModal) return;
        
        this.otpInput = document.getElementById("otpInput");
        this.verifyOtpBtn = document.getElementById("verifyOtpBtn");
        this.cancelOtpBtn = document.getElementById("cancelOtpBtn");
        this.resendOtpBtn = document.getElementById("resendOtpBtn");

        if (this.otpInput && this.verifyOtpBtn) {
            this.otpInput.addEventListener("input", () => {
                this.verifyOtpBtn.disabled = this.otpInput.value.trim().length !== 6;
            });
            this.verifyOtpBtn.disabled = true;
            this.verifyOtpBtn.addEventListener("click", () => this.verifyOtp());
        }
        
        if (this.cancelOtpBtn) {
            this.cancelOtpBtn.addEventListener("click", () => this.closeOtpModal());
        }
        
        if (this.resendOtpBtn) {
            this.resendOtpBtn.addEventListener("click", () => this.resendOtp());
        }
    }

    startOtpCountdown() {
        if (!this.resendOtpBtn) return;

        let timer = 30;
        this.resendOtpBtn.disabled = true;

        const countdown = setInterval(() => {
            this.resendOtpBtn.textContent = `Resend OTP (${timer})`;
            timer--;
            if (timer < 0) {
                clearInterval(countdown);
                this.resendOtpBtn.disabled = false;
                this.resendOtpBtn.textContent = "Resend OTP";
            }
        }, 1000);
    }

    resendOtp() {
        if (!this.payload || !this.payload.email) return;

        fetch(`${this.apiBasePath}/send-otp`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ email: this.payload.email })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
    this.showOtpLoadingOverlay();

    // Wait 2 seconds, then open OTP modal
    setTimeout(() => {
        this.hideOtpLoadingOverlay();
        this.openOtpModal();
    }, 2000); // adjust delay if needed
} else {
    alert("Failed to send OTP. Try again.");
}

        })
        .catch(err => {
            console.error("Resend OTP error:", err);
            alert("Something went wrong. Try again.");
        });
    }

    /* ================= Go Back ================= */
    goBack() {
        history.back();
    }
}

// Create singleton
export const signupManager = new SignupManager(API_BASE_PATH);

// Window bindings for compatibility with existing HTML event attributes
window.selectCountry = (country) => signupManager.selectCountry(country);
window.selectProvince = (province) => signupManager.selectProvince(province);
window.selectCity = (city) => signupManager.selectCity(city);
window.submitSignup = () => signupManager.submitSignup();
window.goBack = () => signupManager.goBack();