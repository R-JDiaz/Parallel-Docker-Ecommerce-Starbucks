import { initCartPage } from "../js/cartMain.js";
import { API_BASE_PATH } from '../../js/config.js';
import { HistoryService, HistoryUI, HistoryController } from '../../js/history.js';

const cartBtn = document.getElementById("cart-btn");
const hisBtn = document.getElementById("history-btn");

async function loadCart(htmlPath) {
    await loadHtml(htmlPath);
    await initCartPage(); 
    await import("./end-config.js");
    changeTitle("My Cart");
}

function changeTitle(title) {
    document.getElementById("con-title").textContent = title;
}

cartBtn.addEventListener("click", async () => {
    loadCart("./components/cartContents.html")
});


hisBtn.addEventListener("click", async () => {
    changeTitle("My Order History");
    await loadHtml("./components/history.html");

    // Initialize history after HTML is in the DOM
    const historyService = new HistoryService(API_BASE_PATH);
    const historyUI = new HistoryUI("history-container");
    const historyController = new HistoryController(historyService, historyUI);

    historyController.init();

    await import("../../js/history.js");
    
});

async function loadHtml(htmlPath) {
    try {
        const response = await fetch(htmlPath);
        const html = await response.text();
        document.getElementById("cart-contents").innerHTML = html;
    } catch (error) {
        console.error("Html Failed to Load:", error);
    }
}


loadCart("./components/cartContents.html");