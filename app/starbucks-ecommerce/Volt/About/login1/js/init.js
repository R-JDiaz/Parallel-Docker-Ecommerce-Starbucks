// frontend/login/js/init.js
import { loadComponent } from "./utils.js";
import { login, signup } from "./auth.js";
import { API_BASE_PATH } from "../../js/config.js";

// ✅ Just use the imported loadComponent here
async function init() {
  await loadComponent("components/start-screen.html", "container");
}

init();
