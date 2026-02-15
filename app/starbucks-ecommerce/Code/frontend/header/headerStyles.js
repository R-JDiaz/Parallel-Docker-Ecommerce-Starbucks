export const headerCSS = `
<style>
    /* ===== HEADER NAVIGATION BAR ===== */
    #head-nav {
        display: flex;                 /* Align items in a row */
        align-items: center;           /* Vertically center items */
        justify-content: space-between;/* Space between logo, nav, and icons */
        width: 100%;
        padding: 1rem 2rem;
        background-color: var(--main-color-darkgreen);
        box-shadow: 0 3px 8px rgba(0, 54, 31, 0.9);
        position: sticky;              /* Sticks to top when scrolling */
        top: 0;
        z-index: 1000;                 /* Stays above other elements */
        gap: 1.5rem;
        box-sizing: border-box;
        margin: 0;
        list-style: none;
    }

    /* ===== LOGO ===== */
    .logo-container {
        flex-shrink: 0;                /* Prevents shrinking */
    }
    .logo {
        height: 4rem;                  /* Fixed size for clarity */
        width: 4rem;
        filter: invert(1) brightness(1000%); /* Makes logo white */
        cursor: pointer;
        user-select: none;
        display: block;
    }

    /* ===== NAVIGATION LINKS ===== */
    .nav-list {
        flex: 1;                       /* Take available space */
        display: flex;
        justify-content: center;       /* Center the nav links */
    }
    .nav-list nav {
        background-color: white;
        padding: 0.6rem 1.2rem;
        border-radius: 25px;
        display: flex;
        gap: 0.5rem;
    }
    .nav-list nav a {
        font-size: 0.9rem;
        text-transform: uppercase;
        font-weight: 700;
        text-decoration: none;
        color: var(--main-color-darkgreen);
        padding: 0.6rem 1.2rem;
        border-radius: 20px;
        transition: all 0.3s ease;
        white-space: nowrap;          /* Prevent text wrapping */
    }
    .nav-list nav a:hover {
        background-color: var(--main-color-darkgreen);
        color: white;
        transform: translateY(-2px);
    }

    /* ===== ICONS AND BUTTONS ===== */
    .icon-list-container {
        flex-shrink: 0;
    }
    #icon-list {
        display: flex;
        align-items: center;
        gap: 1rem;
        list-style: none;
        margin: 0;
        padding: 0;
    }
    .icon {
        filter: invert(1) brightness(1000%);
        height: 1.8rem;
        width: 1.8rem;
        cursor: pointer;
        transition: all 0.3s ease;
        border-radius: 50%;
        object-fit: cover;
    }
    .icon:hover {
        filter: brightness(0.8) invert(1);
        transform: scale(1.1);
    }
    #icon-list button {
        border-radius: 25px;
        background-color: rgb(240, 240, 240);
        padding: 0.5rem 1.5rem;
        font-weight: 700;
        font-size: 0.9rem;
        color: var(--main-color-darkgreen);
        text-transform: uppercase;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        white-space: nowrap;
    }
    #icon-list button:hover {
        background-color: var(--main-color-lightgreen);
        color: #f0f4ef;
        transform: translateY(-2px);
    }

    /* ===== HEADER RESPONSIVE ===== */
    /* Tablet adjustments */
    @media (max-width: 1024px) {
    #head-nav {
    padding: 0.8rem 1rem;
    gap: 1rem;
    }

    .logo {
    height: 3rem;
    width: 3rem;
    }

    .nav-list nav a {
    font-size: 0.85rem;
    padding: 0.5rem 0.9rem;
    }

    #icon-list button {
    padding: 0.4rem 1rem;
    font-size: 0.85rem;
    }
    }
    @media (max-width: 600px) {
    #head-nav {
        height: auto;
        display: flex;
        flex-direction: column;
        list-style: none;
    }
    }


    
    /* ===== PROFILE MODAL ===== */
    .modal {
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background: rgba(0,0,0,0.5);
        display: none;                 /* Hidden by default */
        justify-content: center;
        align-items: center;
        z-index: 1000;
        overflow-y: auto;
    }
    .modal-content {
        background: #fff;
        padding: 25px 30px;
        border-radius: 12px;
        width: 90%; max-width: 500px; max-height: 80vh;
        box-shadow: 0 15px 35px rgba(0,0,0,0.3);
        position: relative;
        font-family: 'Inter', sans-serif;
        overflow-y: auto;
        animation: modalSlideIn 0.3s ease-out;
    }
    .close {
        position: absolute;
        top: 15px; right: 20px;
        font-size: 28px;
        cursor: pointer;
        color: #666;
        background: rgba(255,255,255,0.9);
        border-radius: 50%;
        width: 30px; height: 30px;
        display: flex; align-items: center; justify-content: center;
    }
    .close:hover {
        color: #000;
        background: rgba(255,255,255,1);
    }

    /* ===== MODAL FORM ELEMENTS ===== */
    #profile-modal h2 { text-align: center; margin-bottom: 20px; color: #006241; font-size: 1.8rem; }
    #profile-modal img#profile-image {
        display: block; margin: 0 auto 15px;
        width: 100px; height: 100px;
        border-radius: 50%; object-fit: cover;
        border: 3px solid #006241;
    }
    #profile-image-url, #profile-form input, #profile-form select {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #ccc;
        border-radius: 8px;
        margin-bottom: 15px;
        font-size: 14px;
        box-sizing: border-box;
    }
    #open-image-picker {
        width: 100%;
        padding: 12px;
        background: #006241;
        color: #fff;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        margin-bottom: 20px;
        font-size: 15px;
        font-weight: 600;
        transition: background-color 0.3s ease;
    }
    #open-image-picker:hover { background: #004f1a; transform: translateY(-2px); }
    #profile-form label { display: block; margin: 15px 0 5px; font-weight: 600; color: #333; font-size: 14px; }
    #profile-form h3 { margin: 25px 0 12px; color: #006241; border-bottom: 2px solid #006241; padding-bottom: 8px; font-size: 16px; }
    #profile-form button[type="submit"] {
        width: 100%; padding: 15px;
        background: #006241; color: #fff;
        border: none; border-radius: 10px;
        font-size: 16px; font-weight: 600;
        cursor: pointer; margin-top: 25px;
        transition: all 0.3s ease;
    }
    #profile-form button[type="submit"]:hover {
        background: #004f1a;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 98, 65, 0.3);
    }
    .ingredient-item.highlight {
  background-color: yellow;
  transition: background-color 0.5s ease;
}


/* 🔹 General Modal Overlay */
.modal {
    display: none; /* Hidden by default */
    position: fixed;
    z-index: 1000; /* Make sure it overlays above everything */
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto; /* Enable scroll if content is too tall */
    background-color: rgba(0, 0, 0, 0.6); /* Semi-transparent black */
}

/* 🔹 Shared Modal Box Style */
.modal-content {
    background-color: #fff;
    margin: 8% auto;
    padding: 20px 25px;
    border-radius: 10px;
    width: 400px;
    max-width: 90%;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    animation: slideDown 0.3s ease-out;
    font-family: Arial, sans-serif;
}

/* 🔹 Close Button (X) */
.close {
    color: #666;
    float: right;
    font-size: 24px;
    font-weight: bold;
    cursor: pointer;
}
.close:hover {
    color: #000;
}

/* 🔹 Notification Modal Specific */
#notification-modal h2 {
    font-size: 20px;
    margin-bottom: 15px;
    border-bottom: 2px solid #ddd;
    padding-bottom: 8px;
}

/* 🔹 Notification List */
#notification-list {
    list-style: none;
    padding: 0;
    margin: 0;
    max-height: 300px; /* Scrollable if too many notifications */
    overflow-y: auto;
}

/* 🔹 Each Notification Item */
#notification-list li {
    padding: 12px 10px;
    margin-bottom: 8px;
    background: #f9f9f9;
    border-radius: 6px;
    font-size: 14px;
    border-left: 4px solid #00754a; /* Starbucks green accent */
    transition: background 0.2s;
}
#notification-list li:hover {
    background: #f1f1f1;
}

/* 🔹 Animation */
@keyframes slideDown {
    from { transform: translateY(-50px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}


    /* ===== SCROLLBAR STYLE ===== */
    .modal-content::-webkit-scrollbar { width: 8px; }
    .modal-content::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 4px; }
    .modal-content::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 4px; }
    .modal-content::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }

    /* ===== MODAL ANIMATION ===== */
    @keyframes modalSlideIn {
        from { opacity: 0; transform: translateY(-50px) scale(0.95); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }
</style>
`;
