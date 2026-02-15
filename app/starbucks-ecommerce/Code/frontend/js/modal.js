import { getSizes, ensureGuestToken } from './session.js';
import { API_BASE_PATH } from './config.js';

class Modal {
    constructor(apiBasePath) {
        this.apiBasePath = apiBasePath;
        this.currentItem = null;

        this.modalElement = document.getElementById('itemModal');
        this.nameElement = document.getElementById('modalItemName');
        this.quantityInput = document.getElementById('modalQuantity');
        this.sizeSelect = document.getElementById('modalSize');
    }

    open(item) {
        this.currentItem = item;
        this.nameElement.textContent = item.name;
        this.quantityInput.value = 1;

        this.populateSizes(item.id);
        this.show();
    }

    close() {
        this.hide();
    }

    async populateSizes(itemId) {
        this.sizeSelect.innerHTML = '';
        try {
            const itemType = this.currentItem.category_id === 3 || this.currentItem.item_type === 'merchandise'
                ? 'merchandise'
                : 'starbucksitem';

            const res = await fetch(`${this.apiBasePath}/sizes?item_id=${itemId}&item_type=${itemType}`, {
                credentials: 'include'
            });
            if (!res.ok) throw new Error('Failed to fetch sizes');

            const response = await res.json();
            if (response.status && response.data && response.data.length > 0) {
                response.data.forEach(size => {
                    const opt = document.createElement('option');
                    opt.value = size.id;
                    opt.textContent = `${size.name} (+₱${parseFloat(size.price_modifier).toFixed(2)})`;
                    opt.dataset.modifier = size.price_modifier;
                    this.sizeSelect.appendChild(opt);
                });
            } else {
                const opt = document.createElement('option');
                opt.value = '';
                opt.textContent = 'Default Size';
                opt.dataset.modifier = '0.00';
                this.sizeSelect.appendChild(opt);
            }
        } catch (err) {
            console.error('Failed to fetch item sizes:', err);
            const opt = document.createElement('option');
            opt.value = '';
            opt.textContent = 'Default Size';
            opt.dataset.modifier = '0.00';
            this.sizeSelect.appendChild(opt);
        }
    }

    async initGuestIfNeeded() {
        const guestToken = ensureGuestToken();
        try {
            const res = await fetch(`${this.apiBasePath}/init_guest`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ guest_token: guestToken })
            });
            if (!res.ok) console.warn('Failed to register guest token:', await res.text());
        } catch (err) {
            console.warn('Could not initialize guest token:', err);
        }
        return guestToken;
    }

    async addToCart() {
        const userData = JSON.parse(localStorage.getItem("loggedInUser") || "{}");
        if (userData.type && userData.type.toLowerCase() === "admin") {
            alert("❌ Admins cannot add items to the cart.");
            this.close();
            return;
        }

        const qty = parseInt(this.quantityInput.value, 10);
        if (qty < 1) return;

        const selectedOption = this.sizeSelect.options[this.sizeSelect.selectedIndex];
        const sizeId = selectedOption.value || null;
        const mod = parseFloat(selectedOption.dataset.modifier || '0.00');
        const unitPrice = parseFloat(this.currentItem.price) + mod;
        const guestToken = await this.initGuestIfNeeded();

        const itemType = this.currentItem.category_id === 3 || this.currentItem.item_type === 'merchandise'
            ? 'merchandise'
            : 'starbucksitem';

        const payload = {
            item_id: this.currentItem.id,
            item_type: itemType,
            quantity: qty,
            guest_token: guestToken
        };
        if (sizeId) payload.size_id = sizeId;

        try {
            // 🔹 Step 1: Check ingredients
            const checkRes = await fetch(`${this.apiBasePath}/check_ingredients`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({
                    item_id: payload.item_id,
                    item_type: payload.item_type,
                    quantity: payload.quantity,
                    size_id: payload.size_id || null
                })
            });

            let checkData = {};
            try { checkData = await checkRes.json(); } catch { console.warn('No JSON returned from /check_ingredients'); }

            if (!checkRes.ok || !checkData.status) {
                alert(`❌ Cannot add to cart: ${checkData.message || 'Not enough ingredients'}`);
                return;
            }

            // 🔹 Step 2: Add to cart
            const res = await fetch(`${this.apiBasePath}/cart`, {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            let data = {};
            try { data = await res.json(); } catch { console.warn('No JSON returned from /cart'); }

            if (!res.ok) throw new Error(data.error || data.message || res.statusText);

            alert(`✅ Added ${this.currentItem.name} ×${qty} to your cart.`);
        } catch (err) {
            console.error("Cart sync failed:", err);
            alert(`❌ Could not add to cart: ${err.message}`);
        }

        this.close();
    }

    show() { this.modalElement.style.display = 'flex'; }
    hide() { this.modalElement.style.display = 'none'; }
}

// ===== Singleton Export =====
export const modal = new Modal(API_BASE_PATH);

// Legacy functions
export function openModal(item) { modal.open(item); }
export function closeModal() { modal.close(); }
export async function addToCart() { await modal.addToCart(); }
