document.querySelectorAll(".cat-btn").forEach( button => {
    button.addEventListener("click", () => {
        const subcat = button.nextElementSibling;


        if (subcat.style.display === "block") {
            subcat.style.display = "none" 
        } else {
            subcat.style.display = "block"
        };

    });
});