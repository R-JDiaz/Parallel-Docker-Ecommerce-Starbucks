// session.js
import { API_BASE_PATH } from './config.js';

class SessionManager {
    constructor(apiBasePath) {
        this.apiBasePath = apiBasePath;
        this.sizes = [];
    }

    async loadSizes() {
        try {
            const res = await fetch(`${this.apiBasePath}/sizes`, { credentials: 'include' });
            this.sizes = await res.json();
        } catch (err) {
            console.error('Could not load sizes:', err);
        }
    }

   async checkLoginOnLoad() {
  if (!localStorage.getItem("isLoggedIn") && !localStorage.getItem("isGuest")) {
      // Generate a guest token for uniqueness
      this.ensureGuestToken();

      // Just mark as guest, no backend call needed
      localStorage.setItem("isGuest", "true");
      localStorage.removeItem("isLoggedIn");
      localStorage.removeItem("loggedInUser");

      console.log("✅ Guest mode enabled automatically");
  }
}




    getSizes() {
        return this.sizes;
    }

    ensureGuestToken() {
        let token = localStorage.getItem("guestToken");
        if (!token) {
            token = crypto.randomUUID(); // Or your own generator
            localStorage.setItem("guestToken", token);
        }
        return token;
    }
}

// Singleton instance
export const sessionManager = new SessionManager(API_BASE_PATH);

// Backwards compatibility
export const loadSizes = () => sessionManager.loadSizes();
export const checkLoginOnLoad = () => sessionManager.checkLoginOnLoad();
export const getSizes = () => sessionManager.getSizes();
export const ensureGuestToken = () => sessionManager.ensureGuestToken();
// Run on load to ensure default visitors are guests
window.addEventListener("DOMContentLoaded", () => {
  sessionManager.checkLoginOnLoad();
});

