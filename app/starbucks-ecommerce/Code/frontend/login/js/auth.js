import { LOGIN_ENDPOINT, SIGNUP_ENDPOINT } from "./config.js";
import { loadComponent } from "./utils.js";

class AuthController {
  constructor() {
    this.errorEl = document.getElementById("errorMsg");
    this.rootEl = document.getElementById("con1");
  }

  // 🔔 UI Helpers
  showError(msg) {
    this.showAlert(msg, "red");
  }

  showMessage(msg, color = "green") {
    this.showAlert(msg, color);
  }

  showAlert(msg, color) {
    // Use existing container or create one
    let container = this.errorEl;

    if (!container) {
      const form = document.querySelector("#con1 form") || this.rootEl;
      container = document.createElement("div");
      container.id = "errorMsg";
      container.style.margin = "10px 0";
      container.style.padding = "10px";
      container.style.borderRadius = "5px";
      container.style.fontWeight = "bold";
      container.style.textAlign = "center";
      container.style.width = "100%";
      container.style.transition = "all 0.3s ease";
      form.prepend(container);
      this.errorEl = container;
    }

    container.textContent = msg;
    container.style.color = color;
    container.style.backgroundColor = color === "red" ? "#ffe5e5" : "#e5ffe5";
    container.style.border = `1px solid ${color === "red" ? "#ff4d4d" : "#4dff4d"}`;

    // Auto-hide after 3 seconds
    clearTimeout(container.hideTimeout);
    container.hideTimeout = setTimeout(() => {
      container.textContent = "";
      container.style.backgroundColor = "transparent";
      container.style.border = "none";
    }, 3000);
  }

  clearError() {
    if (this.errorEl) this.errorEl.textContent = "";
  }

  // 🏗 Load Forms
  async showForm(type) {
    if (!this.rootEl) return;

    this.rootEl.innerHTML = "";

    if (type === "login") {
      await loadComponent("components/login-form.html", "con1");
    } else if (type === "signup") {
      await loadComponent("components/signup-form.html", "con1");

      const existingScript = document.getElementById('signup-script');
      if (existingScript) existingScript.remove();

      const script = document.createElement('script');
      script.type = 'module';
      script.id = 'signup-script';
      script.src = './js/signup.js';
      document.body.appendChild(script);
    }

    this.clearError();
  }

  async goBack() {
    if (!this.rootEl) return;
    this.rootEl.innerHTML = "";
    await loadComponent("components/start-screen.html", "con1");
    this.clearError();
  }

  // 🔐 Authentication
  async login() {
    const email = document.getElementById("email")?.value.trim() || "";
    const password = document.getElementById("password")?.value.trim() || "";

    if (!email || !password) {
      this.showError("Email and password are required.");
      return;
    }

    try {
      const res = await fetch(LOGIN_ENDPOINT, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        credentials: "include",
        body: JSON.stringify({ email, password })
      });

      const data = await res.json();

      if (data.success) {
        this.showMessage("Login successful!");

        localStorage.setItem("isLoggedIn", "true");
        localStorage.removeItem("isGuest");
        localStorage.setItem("loggedInUser", JSON.stringify({
          id: data.account_id,
          type: data.account_type
        }));

        setTimeout(() => {
          if (data.account_type?.toLowerCase() === "admin") {
            window.location.href = "../admin/panel/panel.html";
          } else {
            window.location.href = "../menu/menu.html";
          }
        }, 800); // slight delay to see message
      } else {
        this.showError(data.message || "Login failed.");
      }
    } catch (err) {
      console.error("Login error:", err);
      this.showError("Server error. Please try again.");
    }
  }

  async signup(userData) {
    if (!userData) {
      this.showError("Invalid signup data.");
      return;
    }

    try {
      const res = await fetch(SIGNUP_ENDPOINT, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(userData)
      });

      const data = await res.json();

      if (data.success) {
        await this.showForm("login");
        this.showMessage("Signup successful! Please log in.");
      } else {
        this.showError(data.message || "Signup failed.");
      }
    } catch (err) {
      console.error("Signup error:", err);
      this.showError("Server error during signup.");
    }
  }

  continueAsGuest() {
    localStorage.setItem("isGuest", "true");
    localStorage.removeItem("isLoggedIn");
    localStorage.removeItem("loggedInUser");

    setTimeout(() => {
      console.log("✅ Guest mode enabled");
      window.location.href = "../menu/menu.html";
    }, 500);
  }

 async logout() {
  // clear frontend storage
  localStorage.removeItem("loggedInUser");
  localStorage.removeItem("isLoggedIn");
  localStorage.removeItem("isGuest");

  try {
    // clear backend session
    await fetch(`${API_BASE_PATH}/logout`, {
      method: "POST",
      credentials: "include"
    });
  } catch (err) {
    console.warn("Logout request failed:", err);
  }

  // redirect to login
  window.location.href = "../../frontend/login/login.html";
}

  isLoggedIn() {
    return localStorage.getItem("loggedInUser") !== null;
  }
}

// Instantiate controller
const authController = new AuthController();

// ✅ Export functions for frontend buttons and other modules
export const login = () => authController.login();
export const signup = (userData) => authController.signup(userData);
export const showForm = (type) => authController.showForm(type);
export const goBack = () => authController.goBack();
export const continueWithoutAccount = () => authController.continueAsGuest();
export const logout = () => authController.logout();

// Optional: attach to window for legacy button handlers
window.login = login;
window.signup = signup;
window.showForm = showForm;
window.goBack = goBack;
window.continueWithoutAccount = continueWithoutAccount;
window.logout = logout;
