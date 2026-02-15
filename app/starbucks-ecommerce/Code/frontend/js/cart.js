// cart.js
import { API_BASE_PATH , IMAGES_BASE_PATH} from './config.js';


class CartService {
    constructor(apiBasePath) {
        this.apiBasePath = apiBasePath;
    }

    async fetchCartItems() {
        const res = await fetch(`${this.apiBasePath}/cart`, { credentials: 'include' });
        if (!res.ok) throw new Error("Failed to load cart");
        return res.json(); // [{ item_id, name, price, quantity, ... }]
    }

    async deleteCartItem(itemId, sizeId = null) {
        if (!itemId) throw new Error("Item ID is missing");

        const query = sizeId !== null ? `?item_id=${itemId}&size_id=${sizeId}` : `?item_id=${itemId}`;

        const res = await fetch(`${this.apiBasePath}/cart${query}`, {
            method: 'DELETE',
            credentials: 'include'
        });

        if (!res.ok) throw new Error("Failed to delete cart item");
        return res.json();
    }

    async updateCartItemQuantity(itemId, sizeId, quantity) {
        const res = await fetch(`${this.apiBasePath}/cart`, {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ item_id: itemId, size_id: sizeId, quantity })
        });
        if (!res.ok) throw new Error("Failed to update cart item");
        return res.json();
    }

}


class CartUI {
    constructor(cartItemsContainerId, totalId, discountId) {
        this.container = document.getElementById(cartItemsContainerId);
        this.totalElem = document.querySelector(`.${totalId}`);
        this.discountElem = document.querySelector(`.${discountId}`);
        this.items = []; // store items here
    }

    render(items) {
        this.items = items;

        // Re-query DOM elements in case HTML was loaded dynamically
        this.container = document.getElementById('cart-container');
        this.totalElem = document.querySelector('.cartTotal');
        this.discountElem = document.querySelector('.cartDiscount');

        if (!this.container) {
            console.error("Cart container not found in DOM");
            return;
        }

        this.container.innerHTML = '';
        let total = 0;

        items.forEach((item, index) => {
            const lineTotal = item.quantity * parseFloat(item.price || 0);
            total += lineTotal;
            this.container.appendChild(this.createProductElement(item, index));
        });

        this.totalElem.textContent = total.toFixed(2);
        this.discountElem.textContent = '0.00';
    }


    createProductElement(item, index) {
        const div = document.createElement('div');
        div.className = 'prod';
        div.dataset.index = index; // keep index reference

        div.innerHTML = `
            <div class="image-checkbox">
                <div class="img">
                    <img src="${IMAGES_BASE_PATH}${item.image_url || ''}" alt="${item.name}">
                </div>
            </div>
            <div class="prod-info">
                <h2 class="prod-name">${item.name || 'Prod Name'}</h2>
                <div class="prod-att-con">
                    <ul>
                        <li class="prod-att">
                            <span>${item.size_name || ''}</span>
                        </li>
                    </ul>
                </div>
                <span class="prod-price">₱${parseFloat(item.price || 0).toFixed(2)}</span>
            </div>
            <div class="end-config">
                <div class="cross"></div>
                <div class="qty-config">
                    <button class="add-qty">+</button>
                    <span class="qty">${item.quantity || 1}</span>
                    <button class="minus-qty">-</button>        
                </div>
            </div>
            <div class="border-bot"></div>
        `;

        div.querySelector(".cross").addEventListener("click", async () => {
            try {
                console.log("Deleting item:", item.item_id); // Debug
                await cartService.deleteCartItem(item.item_id, item.size_id); // Send correct ID
                this.items = this.items.filter(i => i.item_id !== item.item_id); // Remove only this one
                this.render(this.items);
            } catch (err) {
                console.error("Delete failed:", err);
                alert("Failed to remove item.");
            }
        });

        //Increase quantity
        div.querySelector(".add-qty").addEventListener("click", async (e) => {
            e.preventDefault();
            const currentQty = this.items[index].quantity; // ✅ Always use latest
            console.log(currentQty);
            const newQty = currentQty + 1;
            await this.updateQuantity(index, newQty);
        });

        //Decrease quantity
        div.querySelector(".minus-qty").addEventListener("click", async (e) => {
            e.preventDefault();
            const currentQty = this.items[index].quantity; // ✅ Always use latest
            console.log(currentQty);
            const newQty = currentQty - 1;
            if (newQty >= 1) {
                await this.updateQuantity(index, newQty);   
            }
        });


        return div;
    }

    // 🔹 new method to update qty in data + re-render UI
    async updateQuantity(index, newQty) {
        console.log("quantity: ", newQty)
        if (newQty >= 1) {
            const item = this.items[index];
            try {
                await cartService.updateCartItemQuantity(item.item_id, item.size_id, newQty);
                this.items[index].quantity = newQty;
                this.render(this.items);
            } catch (err) {
                console.error("Failed to update quantity:", err);
                alert("Could not update quantity");
            }
        }
}


}


class CartController {
    constructor(service, ui) {
        this.service = service;
        this.ui = ui;
    }

    async loadCart() {
        try {
            const items = await this.service.fetchCartItems();
            this.ui.render(items);
            console.log(items)
        } catch (err) {
            
            console.error('Error loading cart:', err);
        }
    }

    updateQuantity(index, qty) {
        this.ui.updateQuantity(index, qty);
    }
}

// ===== Initialization =====
const cartService = new CartService(API_BASE_PATH);
const cartUI = new CartUI('cart-container', 'cartTotal', 'cartDiscount');
export const cartController = new CartController(cartService, cartUI);

// Keep original functions for compatibility
export function fetchCartItems() {
    return cartService.fetchCartItems();
}

export function renderCartFromServer(items) {
    cartUI.render(items);
};
