

class InventoryAPI {
  constructor() {
    if (!window.API_BASE_PATH) {
      throw new Error("API_BASE_PATH is not defined. Make sure config.js is loaded first.");
    }
    this.API = `${window.API_BASE_PATH.replace(/\/+$/, '')}/inventory`;
  }

  async getSetting() {
    try {
      const res = await fetch(this.API);
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      return res.json();
    } catch (err) {
      console.error('getSetting error', err);
      return { status: false, error: err.message };
    }
  }

  async setThresholds(ingredient, stock, updated_by) {
    const ing = parseInt(ingredient, 10);
    const s = parseInt(stock, 10);

    if ([ing, s].some(v => Number.isNaN(v) || v < 0)) {
      return { status: false, error: 'Invalid threshold values' };
    }

    try {
      const res = await fetch(this.API, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          ingredient_threshold: ing,
          stock_threshold: s,
          updated_by: updated_by ? parseInt(updated_by, 10) : null
        })
      });
      if (!res.ok) {
        const txt = await res.text();
        throw new Error(`HTTP ${res.status}: ${txt}`);
      }
      return res.json();
    } catch (err) {
      console.error('setThresholds error', err);
      return { status: false, error: err.message };
    }
  }

  async getLowStock(type) {
    try {
      const res = await fetch(`${this.API}?action=low-stock&type=${encodeURIComponent(type)}`);
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      return res.json();
    } catch (err) {
      console.error('getLowStock error', err);
      return { status: false, error: err.message, data: [] };
    }
  }
}


class InventoryUI {
  constructor(api) {
    this.api = api;
    this.btnSet = document.getElementById('btnSet');
    this.ingInput = document.getElementById('ingredientThreshold');
    this.stockInput = document.getElementById('stockThreshold');

    this.lowStockLists = {
      ingredient: document.getElementById('lowStockIngredients'),
      stock: document.getElementById('lowStockStocks')
    };

    this.loggedInUser = JSON.parse(localStorage.getItem("loggedInUser") || "{}");

    this.init();
  }

  async init() {
    await this.loadSetting();
    await this.renderAllLowStock();
    this.bindEvents();
  }

  bindEvents() {
    if (this.btnSet) {
      this.btnSet.addEventListener('click', async () => {
        const ingT   = this.ingInput?.value.trim() === "" ? 0 : this.ingInput.value;
        const stockT = this.stockInput?.value.trim() === "" ? 0 : this.stockInput.value;

        const u = this.loggedInUser.id || null;

        this.btnSet.disabled = true;
        this.btnSet.textContent = 'Saving...';

        const result = await this.api.setThresholds(ingT, stockT, u);

        this.btnSet.disabled = false;
        this.btnSet.textContent = 'Set Thresholds';

        if (result.status) {
          alert('Thresholds updated');
          await this.renderAllLowStock();
        } else {
          alert('Error: ' + (result.error || JSON.stringify(result)));
        }
      });
    }
  }

  async loadSetting() {
    const s = await this.api.getSetting();
    if (s && s.status) {
      if (this.ingInput) this.ingInput.value = s.data.ingredient_threshold ?? 0;
      if (this.stockInput) this.stockInput.value = s.data.stock_threshold ?? 0;
    } else {
      console.warn('Could not fetch setting', s.error);
    }
  }

  async renderAllLowStock() {
    await this.renderLowStock('ingredient');
    await this.renderLowStock('stock');
  }

async renderLowStock(type) {
  const list = this.lowStockLists[type];
  if (!list) return;

  list.innerHTML = '<li>Loading...</li>';
  const j = await this.api.getLowStock(type);
  list.innerHTML = '';

  if (!j || !j.data || j.data.length === 0) {
    list.innerHTML = `<li>No low-stock ${type}s (or threshold = 0)</li>`;
    return;
  }

  j.data.forEach(it => {
    const li = document.createElement('li');

    if (type === 'stock' && it.image_url) {
      // prepend image path from config.js
      const fullImgUrl = `${window.IMAGES_BASE_PATH}${it.image_url}`;

      li.innerHTML = `
        <div class="stock-item">
          <img src="${fullImgUrl}" alt="${it.name}" class="stock-img" />
          <div class="stock-info">
            <strong>${it.name}</strong><br>
            Qty: ${it.quantity}
          </div>
        </div>
      `;
    } else {
      li.textContent = `${it.name} — qty: ${it.quantity}`;
    }

    list.appendChild(li);
  });
}

}



// ===== Initialization =====
document.addEventListener("DOMContentLoaded", () => {
  const inventoryAPI = new InventoryAPI();
  new InventoryUI(inventoryAPI);
});
