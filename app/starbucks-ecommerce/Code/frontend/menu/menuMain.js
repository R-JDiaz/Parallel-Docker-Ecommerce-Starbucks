import '../login/js/auth.js';
import { API_BASE_PATH, IMAGES_BASE_PATH } from '../js/config.js';

class MenuManager {
  constructor() {
    this.searchInput = document.getElementById('searchInput');
    this.suggestionsBox = document.getElementById('suggestionsBox');
    this.itemList = document.getElementById('itemList');
    this.searchTimeout = null;

    this.currentCategoryId = 0;
    this.currentCategoryItems = []; // store currently loaded items
    this.initSearch();
    this.initGlobalClick();
    this.initLogout();
  }
async setCategory(categoryId) {
  this.currentCategoryId = categoryId; 
  console.log("Selected category:", categoryId);

  // Clear search input and suggestions when selecting a category
  this.searchInput.value = '';
  this.suggestionsBox.innerHTML = '';
  this.suggestionsBox.style.display = 'none';

  // Fetch items for this category
  try {
    const res = await fetch(`${API_BASE_PATH}/items?category_id=${categoryId}`, {
      credentials: 'include'
    });
    const json = await res.json();
    if (json.status) {
      this.currentCategoryItems = json.data; // store for search
      // Optionally, display them immediately
      // this.displayItemsForSearch(json.data);
    }
  } catch (err) {
    console.error('Error fetching category items:', err);
  }
}


  getCurrentCategoryId() {
    return this.currentCategoryId || 0;
  }

  initLogout() {
    window.logout = () => {
      localStorage.clear();
      fetch(`${API_BASE_PATH}/logout`, { credentials: 'include' })
        .then(() => window.location.href = '../../frontend/home/home.html');
    };
  }

  initSearch() {
    // Add click event to show all suggestions when clicking on search input
    this.searchInput?.addEventListener('click', () => this.showAllSuggestions());
    
    // Keep the existing input event for typing search
    this.searchInput?.addEventListener('input', () => this.handleSearchInput());
  }
async showAllSuggestions() {
  const catId = this.getCurrentCategoryId();
  let allItems = [];

  try {
    if (catId > 0) {
      // Inside a category
      if (catId === 3) {
        // Merchandise category
        const resMerch = await fetch(`${API_BASE_PATH}/merchandise`, { credentials: 'include' });
        const jsonMerch = await resMerch.json();
        if (jsonMerch.status && Array.isArray(jsonMerch.data)) {
          allItems = jsonMerch.data.map(item => ({ ...item, item_type: 'merchandise' }));
        }
      } else {
        // Regular category (Food/Beverages) — only fetch items for that category
        const resItems = await fetch(`${API_BASE_PATH}/items?category_id=${catId}`, { credentials: 'include' });
        const jsonItems = await resItems.json();
        if (jsonItems.status && Array.isArray(jsonItems.data)) {
          allItems = jsonItems.data.map(item => ({ ...item, item_type: 'starbucksitem' }));
        }
      }
    } else {
      // No category selected → fetch everything (starbucks items + merchandise)
      const resStarbucks = await fetch(`${API_BASE_PATH}/items`, { credentials: 'include' });
      const jsonStarbucks = await resStarbucks.json();
      if (jsonStarbucks.status && Array.isArray(jsonStarbucks.data)) {
        allItems = jsonStarbucks.data.map(item => ({ ...item, item_type: 'starbucksitem' }));
      }

      const resMerch = await fetch(`${API_BASE_PATH}/merchandise`, { credentials: 'include' });
      const jsonMerch = await resMerch.json();
      if (jsonMerch.status && Array.isArray(jsonMerch.data)) {
        const merchItems = jsonMerch.data.map(item => ({ ...item, item_type: 'merchandise' }));
        allItems = allItems.concat(merchItems);
      }
    }

    // Sort alphabetically
    allItems.sort((a, b) => a.name.localeCompare(b.name));

  } catch (err) {
    console.error('Error fetching items:', err);
  }

  // Display suggestions
  this.suggestionsBox.innerHTML = '';
  if (allItems.length > 0) {
    allItems.slice(0, 20).forEach(item => this.addSuggestion(item));
    this.suggestionsBox.style.display = 'block';
  } else {
    this.suggestionsBox.style.display = 'none';
  }
}


  initGlobalClick() {
    document.addEventListener('click', (event) => {
      if (!event.target.closest('.search-container')) {
        this.suggestionsBox.style.display = 'none';
      }
    });
  }

  handleSearchInput() {
    const query = this.searchInput.value.trim();
    if (query.length < 1) {
      // If empty, show all suggestions instead of hiding
      this.showAllSuggestions();
      return;
    }

    clearTimeout(this.searchTimeout);
    this.searchTimeout = setTimeout(() => this.performSearch(query), 300);
  }

// Replace the performSearch method in your MenuManager class
async performSearch(query) {
  this.suggestionsBox.innerHTML = '';

  const catId = this.getCurrentCategoryId();
  let dataToSearch = [];

  if (catId > 0) {
    if (catId === 3) {
      // Merchandise category - search merchandise using existing route
      try {
        const res = await fetch(`${API_BASE_PATH}/merchandise?action=search&query=${encodeURIComponent(query)}`, {
          credentials: 'include'
        });
        const json = await res.json();
        if (json.status && json.data.length > 0) {
          dataToSearch = json.data.map(item => ({
            ...item,
            item_type: 'merchandise'
          }));
        }
      } catch (err) {
        console.error('Merchandise search error:', err);
      }
    } else {
      // Regular items search with category filter
      try {
        const res = await fetch(`${API_BASE_PATH}/items?category_id=${catId}&query=${encodeURIComponent(query)}`, {
          credentials: 'include'
        });
        const json = await res.json();
        if (json.status && json.data.length > 0) {
          dataToSearch = json.data;
        }
      } catch (err) {
        console.error('Category search error:', err);
      }
    }
  } else {
    // Global search via backend
    try {
      const res = await fetch(`${API_BASE_PATH}/search?query=${encodeURIComponent(query)}`, {
        credentials: 'include'
      });
      const json = await res.json();
      if (json.status && json.data.length > 0) dataToSearch = json.data;
    } catch (err) {
      console.error('Search error:', err);
    }
  }

  // Optional: filter by query text in case backend returned extra results
  const filteredData = dataToSearch.filter(item => item.name.toLowerCase().includes(query.toLowerCase()));

  if (filteredData.length > 0) {
    filteredData.forEach(item => this.addSuggestion(item));
    this.suggestionsBox.style.display = 'block';
  } else {
    this.suggestionsBox.style.display = 'none';
  }
}
  // Display items and store them for search
  displayItemsForSearch(items) {
    this.currentCategoryItems = items; // store loaded items
    this.itemList.innerHTML = '';

    items.forEach(item => {
      const card = document.createElement('div');
      card.className = 'item-card';
      card.onclick = () => openModal(item);

      const imageUrl = item.image_url 
        ? `${IMAGES_BASE_PATH}${item.image_url}` 
        : `${IMAGES_BASE_PATH}ClassicCup.png`;

      card.innerHTML = `
        <img src="${imageUrl}" class="item-img" alt="${item.name}">
        <div class="item-name">${item.name}</div>
        <div class="item-description">${item.description || ''}</div>
        <div class="item-price">₱${parseFloat(item.price).toFixed(2)}</div>
      `;

      this.itemList.appendChild(card);
    });
  }

  resetCategory() {
  this.currentCategoryId = 0;
  this.currentCategoryItems = [];
  this.searchInput.value = '';             // Clear search input
  this.suggestionsBox.innerHTML = '';      // Clear suggestions
  this.suggestionsBox.style.display = 'none'; // Hide suggestions
  console.log("Category reset to all and search cleared");
}

  addSuggestion(item) {
    const div = document.createElement('div');
    div.textContent = item.name;
    div.onclick = () => {
      this.searchInput.value = item.name;
      this.suggestionsBox.style.display = 'none';
      this.displaySingleItem(item);
    };
    this.suggestionsBox.appendChild(div);
  }

  displaySingleItem(item) {
    this.itemList.innerHTML = '';

    const card = document.createElement('div');
    card.className = 'item-card';
    card.onclick = () => openModal(item);

    const imageUrl = item.image_url 
      ? `${IMAGES_BASE_PATH}${item.image_url}` 
      : `${IMAGES_BASE_PATH}ClassicCup.png`;

    card.innerHTML = `
      <img src="${imageUrl}" class="item-img" alt="${item.name}">
      <div class="item-name">${item.name}</div>
      <div class="item-description">${item.description || ''}</div>
      <div class="item-price">₱${parseFloat(item.price).toFixed(2)}</div>
    `;

    this.itemList.appendChild(card);
  }
}

// Initialize
window.menu = new MenuManager(); // expose instance globally