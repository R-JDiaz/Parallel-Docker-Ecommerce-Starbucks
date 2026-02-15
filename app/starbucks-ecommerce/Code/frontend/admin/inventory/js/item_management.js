class ItemManager {
  constructor() {
    if (!window.API_BASE_PATH) {
      throw new Error("API_BASE_PATH is not defined. Ensure config.js is loaded before item_management.js");
    }
    this.basePath = window.API_BASE_PATH.replace(/\/+$/, '');
    this.API_ITEMS = `${this.basePath}/items`;
    this.API_MERCH = `${this.basePath}/merchandise`;

    // Item refs
    this.categorySelect = document.getElementById("categorySelect");
    this.subcategorySelect = document.getElementById("subcategorySelect");
    this.itemTableBody = document.querySelector("#itemTable tbody");
    this.addItemForm = document.getElementById("addItemForm");
    this.searchInput = document.getElementById("searchInput");
    this.suggestionsBox = document.getElementById("suggestionsBox");

    // Merchandise refs
    this.merchSubcategorySelect = document.getElementById("merchSubcategorySelect");
    this.merchTableBody = document.querySelector("#merchTable tbody");
    this.addMerchForm = document.getElementById("addMerchForm");
    this.merchSearchInput = document.getElementById("merchSearchInput");

    this.searchTimeout = null;
    this.merchSearchTimeout = null;
    // Merchandise internal state
    this.merchCategoryId = null;
    this._allCategories = [];
  }
  formatMoney(value) {
  if (isNaN(value)) return "₱0.00";
  return "₱" + Number(value).toLocaleString("en-PH", { 
    minimumFractionDigits: 2, 
    maximumFractionDigits: 2 
  });
}


  async loadMerchCategories() {
    try {
      const res = await fetch(`${this.basePath}/categories`, { credentials: 'include' });
      const result = await res.json();
      if (!result.status || !Array.isArray(result.data)) throw new Error("Invalid categories data");
      this._allCategories = result.data;
      // Find and set Merchandise category ID
      const merchCat = this._allCategories.find(c => c.name === 'Merchandise');
      if (merchCat) {
        this.merchCategoryId = String(merchCat.id);
      } else {
        console.error('Merchandise category not found in database');
        this.merchCategoryId = null;
      }
    } catch (err) {
      console.error("Error loading merchandise categories:", err);
    }
  }

  async loadMerchSubcategories(categoryId) {
    try {
      const res = await fetch(`${this.basePath}/subcategories?category_id=${encodeURIComponent(categoryId)}`, { credentials: 'include' });
      const result = await res.json();
      if (!result || result.status === false || !Array.isArray(result.data)) throw new Error('Invalid subcategories data');
      if (this.merchSubcategorySelect) {
        this.merchSubcategorySelect.innerHTML = result.data.map(sc => `<option value="${sc.id}">${this.escapeHtml(sc.name)}</option>`).join("");
      }
    } catch (err) {
      console.error("Error loading merchandise subcategories:", err);
      this.merchSubcategorySelect.innerHTML = `<option value="0">--</option>`;
    }
  }

  /*********************
   * Initialization
   *********************/
  async init() {
    await this.loadCategories();
    const firstCat = this.categorySelect.options[0]?.value;
    if (firstCat) await this.loadSubcategories(firstCat);
    await this.loadItems();

    // Merchandise init
    await this.loadMerchCategories();

    // Load merchandise first to infer its category
    await this.loadMerchandise();

    // Load merchandise subcategories using the fixed Merchandise category
    if (this.merchCategoryId) {
      await this.loadMerchSubcategories(this.merchCategoryId);
    }

    this.bindEvents();
  }

  /*********************
   * Helpers
   *********************/
  escapeHtml(str = "") {
    return String(str)
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#039;");
  }

  showMessage(text, type = "success") {
    let msg = document.createElement("div");
    msg.className = `alert ${type}`;
    msg.textContent = text;
    document.body.appendChild(msg);
    setTimeout(() => msg.remove(), 3000);
  }

  /*********************
   * Loaders (Categories / Subcategories / Items / Sizes)
   *********************/
  async loadCategories() {
    try {
      const res = await fetch(`${this.basePath}/categories`, { credentials: 'include' });
      const result = await res.json();
      if (!result.status || !Array.isArray(result.data)) throw new Error("Invalid categories data");
      // Filter out Merchandise category for items - only show Food and Beverages
      const itemCategories = result.data.filter(cat => cat.name !== 'Merchandise');
      this.categorySelect.innerHTML = itemCategories.map(cat => `<option value="${cat.id}">${this.escapeHtml(cat.name)}</option>`).join("");
    } catch (err) {
      console.error("Error loading categories:", err);
    }
  }

  async loadSubcategories(categoryId) {
    try {
      const res = await fetch(`${this.basePath}/subcategories?category_id=${encodeURIComponent(categoryId)}`, { credentials: 'include' });
      const result = await res.json();
      if (!result || result.status === false || !Array.isArray(result.data)) throw new Error('Invalid subcategories data');
      this.subcategorySelect.innerHTML = result.data.map(sc => `<option value="${sc.id}">${this.escapeHtml(sc.name)}</option>`).join("");
    } catch (err) {
      console.error("Error loading subcategories:", err);
      this.subcategorySelect.innerHTML = `<option value="0">--</option>`;
    }
  }

  async loadItems() {
    try {
      const res = await fetch(`${this.API_ITEMS}?action=getAll`, { credentials: 'include' });
      const result = await res.json();
      if (!result.status || !Array.isArray(result.data)) throw new Error("Invalid items data");
      this.renderItems(result.data);
    } catch (err) {
      console.error("Error loading items:", err);
    }
  }

  /*********************
   * Rendering
   *********************/
  renderItems(items) {
    this.itemTableBody.innerHTML = items.map(item => `
      <tr data-id="${item.id}">
        <td><input value="${this.escapeHtml(item.name || '')}" class="edit-name"></td>
   <td>
  <input type="text" value="${this.formatMoney(item.price ?? 0)}" 
         class="edit-price" data-raw="${item.price ?? 0}">
</td>

        <td>${this.escapeHtml(item.category_name || '')}</td>
        <td>${this.escapeHtml(item.subcategory_name || '')}</td>
        <td><textarea class="edit-desc">${this.escapeHtml(item.description || "")}</textarea></td>
        <td>
          <button class="btnUpdate">Update</button>
          <button class="btnDelete">Delete</button>
        </td>
      </tr>
    `).join("");
  }

  // Merchandise: loaders and renderers
  async loadMerchandise() {
    try {
      const res = await fetch(`${this.API_MERCH}`, { credentials: 'include' });
      const result = await res.json();
      if (!result.status || !Array.isArray(result.data)) throw new Error("Invalid merchandise data");
      this._lastMerchandise = result.data;
      this.renderMerchandise(result.data);
    } catch (err) {
      console.error("Error loading merchandise:", err);
      if (this.merchTableBody) this.merchTableBody.innerHTML = `<tr><td colspan="6">Failed to load merchandise</td></tr>`;
    }
  }

  renderMerchandise(items) {
    if (!this.merchTableBody) return;
    this.merchTableBody.innerHTML = items.map(item => `
      <tr data-id="${item.id}">
        <td><input value="${this.escapeHtml(item.name || '')}" class="m-edit-name"></td>
       <td>
  <input type="text" value="${this.formatMoney(item.price ?? 0)}" 
         class="m-edit-price" data-raw="${item.price ?? 0}">
</td>

        <td>${this.escapeHtml(item.category_name || '')}</td>
        <td>${this.escapeHtml(item.subcategory_name || '')}</td>
        <td><textarea class="m-edit-desc">${this.escapeHtml(item.description || "")}</textarea></td>
        <td>
          <button class="m-btnUpdate">Update</button>
          <button class="m-btnDelete">Delete</button>
        </td>
      </tr>
    `).join("");
  }

  // Merchandise: CRUD helpers
  async addMerchandise(data) {
    try {
      const res = await fetch(`${this.API_MERCH}?action=add`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(data),
        credentials: 'include'
      });
      const result = await res.json();
      if (result.status) {
        this.showMessage("Merchandise added successfully!", "success");
        this.addMerchForm?.reset();
        this.loadMerchandise();
      } else {
        this.showMessage(result.message || "Failed to add merchandise", "error");
      }
    } catch (err) {
      console.error("Error adding merchandise:", err);
      this.showMessage("Network error adding merchandise", "error");
    }
  }

  async updateMerchandise(data) {
    try {
      const res = await fetch(`${this.API_MERCH}?action=update`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(data),
        credentials: 'include'
      });
      const result = await res.json();
      if (result.status) {
        this.showMessage("Merchandise updated successfully!", "success");
        this.loadMerchandise();
      } else {
        this.showMessage(result.message || "Failed to update merchandise", "error");
      }
    } catch (err) {
      console.error("Error updating merchandise:", err);
      this.showMessage("Error updating merchandise", "error");
    }
  }

  async deleteMerchandise(id) {
    try {
      const res = await fetch(
        `${this.API_MERCH}?action=delete&id=${encodeURIComponent(id)}`,
        { method: "DELETE", credentials: 'include' }
      );
      const result = await res.json();
      if (result.status) {
        this.showMessage("Merchandise deleted successfully!", "success");
        this.loadMerchandise();
      } else {
        this.showMessage(result.message || "Failed to delete merchandise", "error");
      }
    } catch (err) {
      console.error("Error deleting merchandise:", err);
      this.showMessage("Error deleting merchandise", "error");
    }
  }

  async searchMerchandise(query) {
    try {
      const res = await fetch(`${this.API_MERCH}?action=search&query=${encodeURIComponent(query)}`, { credentials: 'include' });
      const response = await res.json();
      if (response.status && Array.isArray(response.data) && response.data.length > 0) {
        this.renderMerchandise(response.data);
      } else {
        this.merchTableBody.innerHTML = `<tr><td colspan="6">No results found</td></tr>`;
      }
    } catch (err) {
      console.error('Merchandise search error:', err);
    }
  }

  /*********************
   * CRUD / Stock API calls
   *********************/
  async addItem(data) {
    try {
      const res = await fetch(`${this.API_ITEMS}?action=add`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(data),
        credentials: 'include'
      });
      
      // Check if response is ok first
      if (!res.ok) {
        console.error(`HTTP Error: ${res.status} ${res.statusText}`);
        this.showMessage(`Server error: ${res.status}`, "error");
        return;
      }

      // Check if response has content before parsing JSON
      const responseText = await res.text();
      if (!responseText) {
        console.error("Empty response from server");
        this.showMessage("Empty response from server", "error");
        return;
      }

      let result;
      try {
        result = JSON.parse(responseText);
      } catch (parseErr) {
        console.error("Invalid JSON response:", responseText);
        this.showMessage("Invalid server response", "error");
        return;
      }

      if (result.status) {
        this.showMessage("Item added successfully!", "success");
        this.addItemForm.reset();
        this.loadItems();
      } else {
        this.showMessage(result.message || "Failed to add item", "error");
      }
    } catch (err) {
      console.error("Error adding item:", err);
      this.showMessage("Network error adding item", "error");
    }
  }

  async updateItem(data) {
    try {
      const res = await fetch(`${this.API_ITEMS}?action=update`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(data),
        credentials: 'include'
      });

      if (!res.ok) {
        console.error(`HTTP Error: ${res.status}`);
        this.showMessage(`Server error: ${res.status}`, "error");
        return;
      }

      const text = await res.text();
      if (!text) {
        console.error("Empty response from server");
        this.showMessage("Empty server response", "error");
        return;
      }

      let result;
      try {
        result = JSON.parse(text);
      } catch (e) {
        console.error("Invalid JSON:", text);
        this.showMessage("Invalid server response", "error");
        return;
      }

      if (result.status) {
        this.showMessage("Item updated successfully!", "success");
        this.loadItems();
      } else {
        this.showMessage(result.message || "Failed to update item", "error");
      }

    } catch (err) {
      console.error("Error updating item:", err);
      this.showMessage("Network error updating item", "error");
    }
  }


  async deleteItem(id) {
    try {
      const res = await fetch(
        `${this.API_ITEMS}?action=delete&id=${encodeURIComponent(id)}`,
        { method: "DELETE", credentials: 'include' }
      );
      const result = await res.json();
      if (result.status) {
        this.showMessage("Item deleted successfully!", "success");
        this.loadItems();
      } else {
        this.showMessage(result.message || "Failed to delete item", "error");
      }
    } catch (err) {
      console.error("Error deleting item:", err);
      this.showMessage("Error deleting item", "error");
    }
  }

  async searchInventory(query) {
    try {
      const res = await fetch(`${this.API_ITEMS}?action=searchInventory&query=${encodeURIComponent(query)}`, {
        credentials: 'include'
      });
      const response = await res.json();
      if (response.status && Array.isArray(response.data) && response.data.length > 0) {
        this.renderItems(response.data);
      } else {
        this.itemTableBody.innerHTML = `<tr><td colspan="6">No results found</td></tr>`;
      }
    } catch (err) {
      console.error('Search error:', err);
    }
  }

  /*********************
   * Events binding
   *********************/
  bindEvents() {
    this.categorySelect.addEventListener("change", (e) => {
      this.loadSubcategories(e.target.value);
    });

    this.addItemForm.addEventListener("submit", (e) => {
      e.preventDefault();
      const name = document.getElementById("itemName").value.trim();
      const price = parseFloat(document.getElementById("itemPrice").value);
      if (!/^[A-Za-z\s\-]+$/.test(name)) {
        this.showMessage("Item name must only contain letters, spaces, or dashes.", "error");
        return;
      }
      if (isNaN(price) || price < 0) {
        this.showMessage("Price must be a positive number!", "error");
        return;
      }

      const newItem = {
        name,
        price,
        category_id: parseInt(this.categorySelect.value || 0, 10),
        subcategory_id: parseInt(this.subcategorySelect.value || 0, 10),
        description: document.getElementById("itemDescription").value,
        image_url: document.getElementById("itemImageUrl").value || null
      };

      this.addItem(newItem);
    });

    // Table-level delegation (update / delete / add stock)
    document.querySelector("#itemTable").addEventListener("click", (e) => {
      const row = e.target.closest("tr");
      if (!row) return;
      const id = row.dataset.id;

    if (e.target.classList.contains("btnUpdate")) {
  const updated = {
    id,
    name: row.querySelector(".edit-name").value,
    price: parseFloat(row.querySelector(".edit-price").dataset.raw || 0),
    description: row.querySelector(".edit-desc").value
  };
  this.updateItem(updated);
  return;
}


      if (e.target.classList.contains("btnDelete")) {
        if (confirm("Delete this item?")) {
          this.deleteItem(id);
        }
        return;
      }

    });

    // search input
    this.searchInput?.addEventListener('input', () => {
      const query = this.searchInput.value.trim();
      if (query.length < 1) {
        this.loadItems();
        return;
      }
      clearTimeout(this.searchTimeout);
      this.searchTimeout = setTimeout(() => {
        this.searchInventory(query);
      }, 300);
    });

    // Add merchandise form
    this.addMerchForm?.addEventListener("submit", (e) => {
      e.preventDefault();
      const name = document.getElementById("merchName").value.trim();
      const price = parseFloat(document.getElementById("merchPrice").value);
      if (!/^[A-Za-z\s\-]+$/.test(name)) {
        this.showMessage("Merchandise name must only contain letters, spaces, or dashes.", "error");
        return;
      }
      if (isNaN(price) || price < 0) {
        this.showMessage("Price must be a positive number!", "error");
        return;
      }
      if (!this.merchCategoryId) {
        this.showMessage("Merchandise category is not set.", "error");
        return;
      }
      const newMerch = {
        name,
        price,
        category_id: parseInt(this.merchCategoryId, 10),
        subcategory_id: parseInt(this.merchSubcategorySelect.value || 0, 10),
        description: document.getElementById("merchDescription").value,
        image_url: document.getElementById("merchImageUrl").value || null
      };
      this.addMerchandise(newMerch);
    });

    // Merchandise table events
    document.querySelector("#merchTable")?.addEventListener("click", (e) => {
      const row = e.target.closest("tr");
      if (!row) return;
      const id = row.dataset.id;

   if (e.target.classList.contains("m-btnUpdate")) {
  const updated = {
    id,
    name: row.querySelector(".m-edit-name").value,
    price: parseFloat(row.querySelector(".m-edit-price").dataset.raw || 0),
    description: row.querySelector(".m-edit-desc").value
  };
  this.updateMerchandise(updated);
  return;
}


      if (e.target.classList.contains("m-btnDelete")) {
        if (confirm("Delete this merchandise?")) {
          this.deleteMerchandise(id);
        }
        return;
      }
    });

    // Merchandise search input
    this.merchSearchInput?.addEventListener('input', () => {
      const query = this.merchSearchInput.value.trim();
      if (query.length < 1) {
        this.loadMerchandise();
        return;
      }
      clearTimeout(this.merchSearchTimeout);
      this.merchSearchTimeout = setTimeout(() => {
        this.searchMerchandise(query);
      }, 300);
    });


    // back button
    document.getElementById("btnBack").addEventListener("click", () => {
      window.location.href = "../inventory.html";
    });
// Format price fields on blur (items)
this.itemTableBody.addEventListener("blur", (e) => {
  if (e.target.classList.contains("edit-price")) {
    const raw = parseFloat(e.target.value.replace(/[^\d.-]/g, "")) || 0;
    e.target.dataset.raw = raw;
    e.target.value = this.formatMoney(raw);
  }
}, true);

// Format price fields on blur (merchandise)
this.merchTableBody.addEventListener("blur", (e) => {
  if (e.target.classList.contains("m-edit-price")) {
    const raw = parseFloat(e.target.value.replace(/[^\d.-]/g, "")) || 0;
    e.target.dataset.raw = raw;
    e.target.value = this.formatMoney(raw);
  }
}, true);

  }


  
}

document.addEventListener("DOMContentLoaded", () => {
  const manager = new ItemManager();
  manager.init();
});
