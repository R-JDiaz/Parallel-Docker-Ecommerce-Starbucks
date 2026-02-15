// history.js
import { API_BASE_PATH, IMAGES_BASE_PATH } from './config.js';
import { checkoutManager} from './payment.js';

function formatMoney(amount) {
    return amount.toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
export class HistoryService {
    constructor(apiBasePath) {
        this.apiBasePath = apiBasePath;
    }

    async fetchHistory() {
        const response = await fetch(`${this.apiBasePath}/history`, { credentials: 'include' });
        if (!response.ok) throw new Error('Failed to fetch history data');
        return response.json();
    }
}

export class HistoryUI {
    constructor(containerId) {
        this.container = document.getElementById(containerId);
    }

    renderNoHistory() {
        this.container.innerHTML = "<p>No order history available.</p>";
    }

    renderError() {
        this.container.innerHTML = "<p>Failed to load order history.</p>";
    }

    renderHistory(history) {
        this.container.innerHTML = ''; // Clear old data

        history.forEach(receipt => {
            const div = document.createElement("div");
            div.classList.add("receipt-box");

            // Render items properly with IMAGES_BASE_PATH
            const itemBadges = receipt.items.map(item => {
                const imgPath = item.image_url 
                    ? `${IMAGES_BASE_PATH}/${item.image_url}` 
                    : null;

                return `
                    <div class="receipt-item">
                        ${imgPath ? `<img src="${imgPath}" alt="${item.name}" class="item-img">` : ""}
                        <span>${item.qty} × ${item.name} - ₱${formatMoney(item.price)}</span>
                    </div>
                `;
            }).join('');

            div.innerHTML = `
                <h3>Receipt ID: ${receipt.id}</h3>
                <p><strong>Date:</strong> ${receipt.date}</p>
                <div><strong>Items:</strong><br>${itemBadges}</div>
                <p><strong>Total:</strong> ₱${formatMoney(receipt.total)}</p>
                <button class="view-receipt-btn">View Receipt</button>
                <hr>
            `;

            // Add event listener to View Receipt button
            const btn = div.querySelector(".view-receipt-btn");
            btn.addEventListener("click", () => {
                const items = receipt.items.map(item => ({
                    name: item.name + (item.size_name ? ` (${item.size_name})` : ""),
                    quantity: item.qty,
                    price: parseFloat(item.price),
                    total: parseFloat(item.price) * item.qty
                }));

                checkoutManager.showReceipt({
                    items,
                    order_id: receipt.id,
                    discount_type: receipt.discount_type || "none",
                    discount_amount: formatMoney(receipt.discount_amount || 0),
                    total: formatMoney(receipt.total),
                    final: formatMoney(receipt.final_amount || receipt.total),
                    paid: formatMoney(receipt.paid || receipt.total),
                    change: formatMoney(receipt.change || 0),
                    date: receipt.date
                });
            });

            this.container.appendChild(div);
        });
    }
}


export class HistoryController {
    constructor(service, ui) {
        this.service = service;
        this.ui = ui;
    }

    async init() {
        try {
            const data = await this.service.fetchHistory();

            if (!data.status || !Array.isArray(data.history) || data.history.length === 0) {
                this.ui.renderNoHistory();
                return;
            }

            this.ui.renderHistory(data.history);
        } catch (error) {
            console.error("Error fetching history:", error);
            this.ui.renderError();
        }
    }
}

