// checkout.js
import { renderCartFromServer, fetchCartItems } from './cart.js';
import { API_BASE_PATH } from './config.js';

// ✅ Helper to format currency with commas
function formatMoney(amount) {
    return amount.toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

class Checkout {
    constructor(apiBasePath) {
        this.apiBasePath = apiBasePath;
        this.cartSnapshot = [];
        this.paymentData = {};
    }

    async checkout() {
        try {
            await this.verifySession();
            const cartItems = await fetchCartItems();

            if (!cartItems.length) {
                alert("🛒 Cart is empty.");
                return;
            }

            this.cartSnapshot = cartItems;
            const total = cartItems.reduce((sum, i) => sum + parseFloat(i.price) * parseInt(i.quantity), 0);
            this.showPaymentModal(total);

        } catch (err) {
            alert("⚠️ " + err.message);
            window.location.href = '../login/login.html';
        }
    }

    async verifySession() {
        const res = await fetch(`${this.apiBasePath}/check_login`, { credentials: 'include' });
        if (res.status === 401) throw new Error("Not authorized");
        const data = await res.json();
        if (!data.status) throw new Error("Not logged in");
    }

    showPaymentModal(total) {
        const final = total; // ✅ no discount applied

        document.getElementById('paymentTotal').textContent = formatMoney(total);
        document.getElementById('paymentDiscount').textContent = formatMoney(0);
        document.getElementById('finalAmount').textContent = formatMoney(final);
        document.getElementById('cashInput').value = '';
        document.getElementById('paymentModal').style.display = 'flex';

        this.paymentData = { total, discount: 0, final };
    }

    closePaymentModal() {
        document.getElementById('paymentModal').style.display = 'none';
    }

    async processPayment() {
        const amt = parseFloat(document.getElementById('cashInput').value);
        const { total, discount, final } = this.paymentData;
        const type = document.getElementById('paymentType').value;

        if (isNaN(amt) || amt < final) {
            alert("❌ Not enough cash.");
            return;
        }

        const change = amt - final;

        try {
            const res = await fetch(`${this.apiBasePath}/payment`, {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    type,
                    amountPaid: amt,
                    total,
                    discount,
                    finalAmount: final
                })
            });

            const text = await res.text();

            let data;
            try {
                data = JSON.parse(text);
            } catch {
                data = { error: text || "Invalid JSON response from server" };
            }

            console.log(data);

            if (!res.ok) {
                throw new Error(data.error || data.message || "Payment failed");
            }

            alert(`✅ Paid! Change: ₱${formatMoney(change)}`);
            this.closePaymentModal();

            const items = this.cartSnapshot.map(item => ({
                name: item.name + (item.size_name ? ` (${item.size_name})` : ""),
                quantity: item.quantity,
                price: parseFloat(item.price),
                total: parseFloat(item.price) * item.quantity
            }));

            this.showReceipt({
                items,
                order_id: data.orderId,
                discount_type: "none",
                discount_amount: formatMoney(0),
                total: formatMoney(total),
                final: formatMoney(final),
                paid: formatMoney(amt),
                change: formatMoney(change),
                date: new Date().toLocaleString()
            });

            const updatedCart = await fetchCartItems();
            renderCartFromServer(updatedCart);

        } catch (err) {
            console.error("Payment error:", err);
            alert("❌ Payment failed:\n" + err.message.trim());
        }
    }

    showReceipt(data) {
        const receiptContainer = document.getElementById("receiptContainer");
        const receiptBox = document.getElementById("receiptBox");

        // Make the modal visible
        receiptContainer.style.display = "flex";

        // Build items HTML
        const itemsHTML = data.items.map(item => `
            <tr>
                <td>${item.name}</td>
                <td>${item.quantity}</td>
                <td>₱${formatMoney(item.price)}</td>
                <td>₱${formatMoney(item.total)}</td>
            </tr>
        `).join("");

        // Add close button inside the modal
        receiptBox.innerHTML = `
            <span class="modal-close" onclick="document.getElementById('receiptContainer').style.display='none'">&times;</span>
            <h2>☕ Starbucks Receipt</h2>
            <p><strong>Order ID:</strong> ${data.order_id}</p>

            <table border="1" cellpadding="5" cellspacing="0" style="width: 100%; margin-top: 10px;">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    ${itemsHTML}
                </tbody>
            </table>

            <p><strong>Total:</strong> ₱${data.total}</p>
            <p><strong>Discount:</strong> ₱${data.discount_amount} (${data.discount_type})</p>
            <p><strong>Final:</strong> ₱${data.final}</p>
            <p><strong>Paid:</strong> ₱${data.paid}</p>
            <p><strong>Change:</strong> ₱${data.change}</p>
            <p><strong>Date:</strong> ${data.date}</p>

            <button onclick="window.print()">🖨️ Print</button>
        `;
    }

}

// ===== Singleton Export =====
export const checkoutManager = new Checkout(API_BASE_PATH);

// Backwards compatibility for existing function calls
export function checkout() {
    checkoutManager.checkout();
}
export function showPaymentModal(total) {
    checkoutManager.showPaymentModal(total);
}
export function closePaymentModal() {
    checkoutManager.closePaymentModal();
}
export function processPayment() {
    checkoutManager.processPayment();
}
