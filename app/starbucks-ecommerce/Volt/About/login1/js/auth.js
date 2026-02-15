import { LOGIN_ENDPOINT, SIGNUP_ENDPOINT } from "./config.js";
import { loadComponent } from "./utils.js";

class AuthController {
  constructor() {
    this.errorEl = document.getElementById("errorMsg");
    this.rootEl = document.getElementById("component-root");
  }

  // 🔔 UI Helpers
  // 🔔 UI Helpers
  showError(msg) {
    let container = this.errorEl;

    // if #errorMsg doesn't exist, create it dynamically inside the form
    if (!container) {
      const form = document.querySelector("#component-root form") || this.rootEl;
      container = document.createElement("div");
      container.id = "errorMsg";
      container.style.color = "red";
      container.style.marginTop = "10px";
      container.style.fontWeight = "bold";
      form.prepend(container);
      this.errorEl = container;
    }

    container.textContent = msg;
  }


  showMessage(msg, color = "green") {
    if (this.errorEl) {
      this.errorEl.textContent = msg;
      this.errorEl.style.color = color;
    }
  }

  clearError() {
    if (this.errorEl) this.errorEl.textContent = "";
  }

  // 🏗 Load Forms
  async showForm(type) {
    if (!this.rootEl) return;

    this.rootEl.innerHTML = "";

    if (type === "login") {
      await loadComponent("components/login-form.html", "component-root");
    } else if (type === "signup") {
      await loadComponent("components/signup-form.html", "component-root");

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
    await loadComponent("components/start-screen.html", "component-root");
    this.clearError();
  }

  // 🔐 Authentication
  async login() {
    const email = document.getElementById("loginEmail")?.value.trim() || "";
    const password = document.getElementById("loginPass")?.value.trim() || "";

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
        localStorage.setItem("isLoggedIn", "true");
        localStorage.removeItem("isGuest");
        localStorage.setItem("loggedInUser", JSON.stringify({
          id: data.account_id,
          type: data.account_type
        }));

        alert("Login successful!");

        if (data.account_type?.toLowerCase() === "admin") {
          window.location.href = "../admin/panel/panel.html";
        } else {
          window.location.href = "../menu/menu.html";
        }
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

  // ✅ Password length validation
  if (!userData.password || userData.password.length < 6) {
    this.showError("Password must be at least 6 characters long.");
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
      // ✅ Special case: email already exists
      if (data.message?.toLowerCase().includes("email")) {
        this.showError("This email is already registered. Please log in.");
      } else {
        this.showError(data.message || "Signup failed.");
      }
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

  logout() {
    localStorage.removeItem("loggedInUser");
    localStorage.removeItem("isLoggedIn");
    localStorage.removeItem("isGuest");
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
