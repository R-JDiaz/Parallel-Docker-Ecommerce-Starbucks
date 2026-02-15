// Make sure config.js is loaded before this script in HTML
if (!window.API_BASE_PATH) {
    throw new Error("API_BASE_PATH is not defined. Make sure config.js is loaded first.");
}

const API = `${window.API_BASE_PATH.replace(/\/+$/, '')}/accounts`;

async function loadUsers() {
    try {
        const res = await fetch(`${API}?action=list`);
        const response = await res.json();

        const tbody = document.querySelector("#userTable tbody");
        tbody.innerHTML = "";

        if (!response.success || !Array.isArray(response.data) || response.data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="4">No users found</td></tr>`;
            return;
        }

        response.data.forEach(user => {
            const tr = document.createElement("tr");

            // Determine button text and action based on current status
            const isBlocked = user.status === 'blocked';
            const blockText = isBlocked ? 'Unblock' : 'Block';
            const action = isBlocked ? 'unblock' : 'block';

            tr.innerHTML = `
                <td>${user.email}</td>
                <td>${user.status}</td>
                <td>${user.created_at}</td>
                <td>
                    <button onclick="toggleBlock(${user.id}, '${action}')">${blockText}</button>
                    <button onclick="deleteUser(${user.id})">Delete</button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    } catch (err) {
        console.error("Failed to load users:", err);
        const tbody = document.querySelector("#userTable tbody");
        tbody.innerHTML = `<tr><td colspan="4">Error loading users</td></tr>`;
    }
}

// Toggle block/unblock user
window.toggleBlock = async (id, action) => {
    try {
        const res = await fetch(`${API}?action=${action}`, {
            method: 'POST',
            body: new URLSearchParams({ id })
        });
        const data = await res.json();
        if (!data.success) {
            alert(data.message || `Failed to ${action} user`);
        }
        await loadUsers();
    } catch (err) {
        console.error(`Error ${action}ing user:`, err);
        alert(`Error ${action}ing user`);
    }
}

// Delete user
window.deleteUser = async (id) => {
    try {
        const res = await fetch(`${API}?action=delete`, {
            method: 'POST',
            body: new URLSearchParams({ id })
        });
        const data = await res.json();
        if (!data.success) {
            alert(data.message || "Failed to delete user");
        }
        await loadUsers();
    } catch (err) {
        console.error("Error deleting user:", err);
        alert("Error deleting user");
    }
}

// Load users on page load
document.addEventListener("DOMContentLoaded", loadUsers);
