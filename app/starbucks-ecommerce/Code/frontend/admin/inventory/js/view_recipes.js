class RecipeManager {
    constructor() {
        if (!window.API_BASE_PATH) throw new Error("API_BASE_PATH is not defined.");
        this.API_BASE_PATH = window.API_BASE_PATH.replace(/\/+$/, '');
        this.recipesContainer = document.getElementById('recipesContainer');
        this.btnBack = document.getElementById('btnBack');
        this.searchInput = document.getElementById('searchInput');
        this.searchTimeout = null;
        this.currentEditingRecipe = null;
        this.availableIngredients = [];
        this.currentRecipeIngredients = [];
    }

    escapeHtml(str = "") {
        return String(str)
            .replaceAll("&", "&amp;")
            .replaceAll("<", "&lt;")
            .replaceAll(">", "&gt;")
            .replaceAll('"', "&quot;")
            .replaceAll("'", "&#039;");
    }

    showMessage(text, type = "error") {
        const msg = document.createElement("div");
        msg.className = `alert ${type}`;
        msg.textContent = text;
        document.body.appendChild(msg);
        setTimeout(() => msg.remove(), 3000);
    }

    async fetchRecipes() {
        this.recipesContainer.innerHTML = `<div class="loading"><div class="loading-spinner"></div><p>Loading recipes...</p></div>`;
        try {
            const res = await fetch(`${this.API_BASE_PATH}/recipes`, { credentials: 'include' });
            const data = await res.json();
            if (!data.status || !Array.isArray(data.data)) {
                this.recipesContainer.innerHTML = `<div class="recipes-empty">${data.message || 'No recipes found'}</div>`;
                return;
            }
            this.renderRecipes(data.data);
        } catch {
            this.recipesContainer.innerHTML = `<div class="recipes-empty">Error loading recipes</div>`;
        }
    }

    async searchRecipes(query) {
        if (!query || query.trim().length < 1) return this.fetchRecipes();

        this.recipesContainer.innerHTML = `<div class="loading"><div class="loading-spinner"></div><p>Searching recipes...</p></div>`;
        try {
            const res = await fetch(`${this.API_BASE_PATH}/recipes?action=search&query=${encodeURIComponent(query.trim())}`, { credentials: 'include' });
            const data = await res.json();
            if (!data.status || !Array.isArray(data.data) || data.data.length === 0) {
                this.recipesContainer.innerHTML = `<div class="recipes-empty">${data.message || `No recipes found containing "${this.escapeHtml(query)}"`}</div>`;
                return;
            }
            this.renderRecipes(data.data);
        } catch {
            this.recipesContainer.innerHTML = `<div class="recipes-empty">Error searching recipes</div>`;
        }
    }

    renderRecipes(recipes) {
        if (!recipes || recipes.length === 0) {
            this.recipesContainer.innerHTML = `<div class="recipes-empty">No recipes available.</div>`;
            return;
        }

        this.recipesContainer.innerHTML = `
            <div class="recipes-grid">
                ${recipes.map(recipe => {
                    const recipeId = recipe.id;
                    return `
                        <div class="recipe-card">
                            <div class="recipe-header">
                                <h3 class="recipe-title">${this.escapeHtml(recipe.item_name || recipe.name)}</h3>
                                <span class="recipe-category">Recipe</span>
                            </div>
                            <div class="recipe-info">
                                <div class="recipe-detail">
                                    <div class="recipe-detail-label">Ingredients</div>
                                    <div class="recipe-detail-value">${recipe.ingredients?.length || 0}</div>
                                </div>
                                <div class="recipe-detail"><div class="recipe-detail-label">Type</div><div class="recipe-detail-value">Food Item</div></div>
                                <div class="recipe-detail"><div class="recipe-detail-label">Status</div><div class="recipe-detail-value">Available</div></div>
                            </div>
                            ${recipe.description ? `<div class="recipe-description">${this.escapeHtml(recipe.description)}</div>` : ''}
                            <div class="ingredients-section">
                                <div class="section-title">Ingredients</div>
                                <ul class="ingredients-list">
                                    ${recipe.ingredients && recipe.ingredients.length > 0
                                        ? recipe.ingredients.map(ing => `<li class="ingredient-item">${this.escapeHtml(ing.name)}: ${ing.quantity || 'As needed'} ${this.escapeHtml(ing.unit || '')}</li>`).join('')
                                        : '<li class="ingredient-item">No ingredients listed</li>'}
                                </ul>
                            </div>
                            <div class="recipe-actions">
                                <button class="btn-edit-recipe" data-recipe-id="${recipeId}">Edit Recipe</button>
                                <button class="btn-delete-recipe" data-recipe-id="${recipeId}">Delete Recipe</button>
                            </div>
                        </div>
                    `;
                }).join('')}
            </div>
        `;
    }

    bindEvents() {
        this.btnBack.addEventListener('click', () => window.location.href = '../inventory.html');
        if (this.searchInput) {
            this.searchInput.addEventListener('input', () => {
                clearTimeout(this.searchTimeout);
                this.searchTimeout = setTimeout(() => this.searchRecipes(this.searchInput.value.trim()), 300);
            });
            this.searchInput.addEventListener('keydown', (e) => { if (e.key === 'Escape') { this.searchInput.value = ''; this.fetchRecipes(); } });
        }

        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('btn-edit-recipe')) this.editRecipe(e.target.dataset.recipeId);
            if (e.target.classList.contains('btn-delete-recipe')) this.deleteRecipe(e.target.dataset.recipeId);
        });
    }

    async editRecipe(recipeId) {
        if (!recipeId) { this.showMessage('Invalid recipe ID', 'error'); return; }
        this.currentEditingRecipe = recipeId;
        await Promise.all([this.loadAvailableIngredients(), this.loadRecipeIngredients(recipeId)]);
        this.showEditModal();
    }

    async loadRecipeIngredients(recipeId) {
        try {
            const res = await fetch(`${this.API_BASE_PATH}/ingredients?action=getForItem&item_id=${recipeId}`, { credentials: 'include' });
            const data = await res.json();
            this.currentRecipeIngredients = data.status ? data.data : [];
        } catch { this.currentRecipeIngredients = []; }
    }

    async loadAvailableIngredients() {
        try {
            const res = await fetch(`${this.API_BASE_PATH}/ingredients?action=getAll`, { credentials: 'include' });
            const data = await res.json();
            this.availableIngredients = data.status ? data.data : [];
        } catch { this.availableIngredients = []; this.showMessage('Error loading ingredients', 'error'); }
    }

    showEditModal() {
        const modalHtml = `
            <div id="editRecipeModal" class="edit-modal-overlay">
                <div class="edit-modal-content">
                    <div class="edit-modal-header">
                        <h2>Edit Recipe Ingredients</h2>
                        <button class="edit-modal-close" onclick="document.getElementById('editRecipeModal').remove()">×</button>
                    </div>
                    <div class="edit-modal-body">
                        <div class="current-ingredients-section">
                            <h3>Current Ingredients</h3>
                            <div id="currentIngredientsList">${this.renderCurrentIngredients()}</div>
                        </div>
                        <div class="add-ingredients-section">
                            <h3>Add New Ingredient</h3>
                            <div class="add-ingredient-form">
                                <select id="newIngredientSelect"><option value="">Select Ingredient</option>${this.renderAvailableIngredientsOptions()}</select>
                                <input type="number" id="newIngredientQuantity" placeholder="Quantity" step="0.01" min="0">
                                <input type="text" id="newIngredientUnit" placeholder="Unit (g, ml, pcs, etc.)">
                                <button id="addIngredientBtn" class="btn-add-ingredient">Add Ingredient</button>
                            </div>
                        </div>
                    </div>
                    <div class="edit-modal-footer">
                        <button class="btn-cancel" onclick="document.getElementById('editRecipeModal').remove()">Cancel</button>
                        <button class="btn-save" onclick="recipeManager.closeEditModal()">Done</button>
                    </div>
                </div>
            </div>`;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        this.bindEditModalEvents();
    }

    renderCurrentIngredients() {
        if (!this.currentRecipeIngredients.length) return '<p class="no-ingredients">No ingredients found</p>';
        return this.currentRecipeIngredients.map(ing => `
            <div class="current-ingredient-item" data-ingredient-id="${ing.item_ingredient_id}">
                <span class="ingredient-name">${this.escapeHtml(ing.ingredient_name)}</span>
                <div class="ingredient-controls">
                    <input type="number" class="ingredient-quantity" value="${ing.quantity_value}" step="0.01" min="0">
                    <input type="text" class="ingredient-unit" value="${this.escapeHtml(ing.quantity_unit)}">
                    <button class="btn-update-ingredient" data-id="${ing.item_ingredient_id}">Update</button>
                    <button class="btn-remove-ingredient" data-id="${ing.item_ingredient_id}">Remove</button>
                </div>
            </div>`).join('');
    }

    renderAvailableIngredientsOptions() {
        const usedIds = this.currentRecipeIngredients.map(ing => ing.ingredient_id);
        return this.availableIngredients.filter(ing => !usedIds.includes(ing.id))
            .map(ing => `<option value="${ing.id}" data-unit="${ing.stock_unit || ''}">${this.escapeHtml(ing.name)}</option>`).join('');
    }

    bindEditModalEvents() {
        document.getElementById('addIngredientBtn').addEventListener('click', () => this.addNewIngredient());
        document.getElementById('newIngredientSelect').addEventListener('change', (e) => document.getElementById('newIngredientUnit').value = e.target.selectedOptions[0].dataset.unit || '');
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('btn-update-ingredient')) this.updateIngredient(e.target.dataset.id);
            if (e.target.classList.contains('btn-remove-ingredient')) this.removeIngredient(e.target.dataset.id);
        });
    }

    async addNewIngredient() {
        const ingredientId = document.getElementById('newIngredientSelect').value;
        const quantity = parseFloat(document.getElementById('newIngredientQuantity').value);
        const unit = document.getElementById('newIngredientUnit').value.trim();
        if (!ingredientId || !quantity || quantity <= 0 || !unit) { this.showMessage('Please fill all fields with valid values', 'error'); return; }
        if (!this.currentEditingRecipe) { this.showMessage('No recipe selected for editing', 'error'); return; }

        try {
            const res = await fetch(`${this.API_BASE_PATH}/ingredients?action=addItemIngredient`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ item_id: this.currentEditingRecipe, ingredient_id: parseInt(ingredientId), quantity_value: quantity, quantity_unit: unit })
            });
            const data = await res.json();
            if (data.status) {
                this.showMessage('Ingredient added successfully!', 'success');
                await this.loadRecipeIngredients(this.currentEditingRecipe);
                document.getElementById('currentIngredientsList').innerHTML = this.renderCurrentIngredients();
                document.getElementById('newIngredientSelect').innerHTML = '<option value="">Select Ingredient</option>' + this.renderAvailableIngredientsOptions();
                document.getElementById('newIngredientSelect').value = '';
                document.getElementById('newIngredientQuantity').value = '';
                document.getElementById('newIngredientUnit').value = '';
            } else this.showMessage(data.message || 'Failed to add ingredient', 'error');
        } catch { this.showMessage('Error adding ingredient', 'error'); }
    }

    async updateIngredient(id) {
        const item = document.querySelector(`[data-ingredient-id="${id}"]`);
        const quantity = parseFloat(item.querySelector('.ingredient-quantity').value);
        const unit = item.querySelector('.ingredient-unit').value.trim();
        if (!quantity || quantity <= 0 || !unit) { this.showMessage('Please enter valid quantity and unit', 'error'); return; }
        try {
            const res = await fetch(`${this.API_BASE_PATH}/ingredients?action=updateItemIngredient`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ item_ingredient_id: parseInt(id), quantity_value: quantity, quantity_unit: unit })
            });
            const data = await res.json();
            this.showMessage(data.status ? 'Ingredient updated successfully!' : (data.message || 'Failed to update ingredient'), data.status ? 'success' : 'error');
        } catch { this.showMessage('Error updating ingredient', 'error'); }
    }

    async removeIngredient(id) {
        if (!confirm('Are you sure you want to remove this ingredient?')) return;
        try {
            const res = await fetch(`${this.API_BASE_PATH}/ingredients?action=removeItemIngredient`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ item_ingredient_id: parseInt(id) })
            });
            const data = await res.json();
            if (data.status) {
                this.showMessage('Ingredient removed successfully!', 'success');
                await this.loadRecipeIngredients(this.currentEditingRecipe);
                document.getElementById('currentIngredientsList').innerHTML = this.renderCurrentIngredients();
                document.getElementById('newIngredientSelect').innerHTML = '<option value="">Select Ingredient</option>' + this.renderAvailableIngredientsOptions();
            } else this.showMessage(data.message || 'Failed to remove ingredient', 'error');
        } catch { this.showMessage('Error removing ingredient', 'error'); }
    }

    closeEditModal() {
        document.getElementById('editRecipeModal').remove();
        this.searchInput?.value.trim() ? this.searchRecipes(this.searchInput.value.trim()) : this.fetchRecipes();
    }

    async deleteRecipe(id) {
        if (!confirm('Are you sure you want to delete this recipe?')) return;
        try {
            const res = await fetch(`${this.API_BASE_PATH}/recipes?id=${id}`, { method: 'DELETE', credentials: 'include' });
            const data = await res.json();
            if (data.status) {
                this.showMessage('Recipe deleted successfully!', 'success');
                this.searchInput?.value.trim() ? this.searchRecipes(this.searchInput.value.trim()) : this.fetchRecipes();
            } else this.showMessage(data.message || 'Failed to delete recipe', 'error');
        } catch { this.showMessage('Error deleting recipe', 'error'); }
    }

    async init() { this.bindEvents(); await this.fetchRecipes(); }
}

document.addEventListener("DOMContentLoaded", () => { window.recipeManager = new RecipeManager(); recipeManager.init(); });
