import { cartController } from '../../js/cart.js';

document.addEventListener("DOMContentLoaded", () => {
    document.body.addEventListener("click", function(e) {
        if (e.target.classList.contains("add-qty") || e.target.classList.contains("minus-qty")) {
            const prodElem = e.target.closest(".prod");
            const qtyElem = prodElem.querySelector(".qty");
            const index = parseInt(prodElem.dataset.index, 10);
            
            let qty = parseInt(qtyElem.textContent, 10);

            if (e.target.classList.contains("add-qty")) {
                qty++;
            } else if (e.target.classList.contains("minus-qty") && qty > 1) {
                qty--;
            }

            // Update the cartController and re-render totals
            cartController.updateQuantity(index, qty);
        }
    });
});
