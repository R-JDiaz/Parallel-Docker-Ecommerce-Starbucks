// Payment Modal
const paymentModal = document.getElementById('paymentModal');
const receiptModal = document.getElementById('receiptContainer');

// Function to open modals
function openModal(modal) {
    modal.classList.add('active');
}

// Function to close modals
function closeModal(modal) {
    modal.classList.remove('active');
}

// Click outside to close
[paymentModal, receiptModal].forEach(modal => {
    modal.addEventListener('click', (e) => {
        if (e.target === modal) { // clicked on overlay
            closeModal(modal);
        }
    });
});

// Example usage for payment modal
function showPaymentModal() {
    openModal(paymentModal);
}

function closePaymentModal() {
    closeModal(paymentModal);
}

// Example usage for receipt
function showReceipt() {
    openModal(receiptModal);
}

function closeReceipt() {
    closeModal(receiptModal);
}
