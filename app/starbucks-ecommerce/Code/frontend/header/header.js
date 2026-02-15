// header.js - Modularized Header Component Entry Point
import { HeaderComponent } from './headerComponent.js';

// Initialize and render header when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    const header = new HeaderComponent();
    header.render();
});


