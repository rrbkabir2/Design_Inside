let scrollContainer = document.querySelector(".gallary");
let backBtn = document.querySelector(".backBtn");
let nextBtn = document.querySelector(".nextBtn");

scrollContainer.addEventListener("wheel", (evt) => {
    evt.preventDefault();
    // scrollContainer.scrollLeft += evt.deltaX || evt.deltaY;
    scrollContainer.scrollLeft += evt.deltaX;
    scrollContainer.style.scrollBehavior = "auto";
});

nextBtn.addEventListener("click", () => {
    scrollContainer.style.scrollBehavior = "smooth";
    scrollContainer.scrollLeft += 900;
});

backBtn.addEventListener("click", () => {
    scrollContainer.style.scrollBehavior = "smooth";
    scrollContainer.scrollLeft -= 900;
});

// Responsive section

function toggleMenu() {
    document.querySelector('.menus').classList.toggle('active');
}


// responsive scroll horizontal code
scrollContainer.addEventListener("wheel", (evt) => {
    if (window.innerWidth <= 768) return;
    evt.preventDefault();
    scrollContainer.scrollLeft += evt.deltaX;
});
