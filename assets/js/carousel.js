const track = document.getElementById("carouselTrack");
const nextBtn = document.getElementById("nextBtn");
const prevBtn = document.getElementById("prevBtn");

let position = 0;
let speed = 15;

let isDragging = false;
let startX = 0;
let startPosition = 0;


// ==============================
// AVTOMATİK HƏRƏKƏT
// ==============================

function autoScroll() {

    if (!isDragging) {

        position -= speed;

        if (Math.abs(position) >= track.scrollWidth / 2) {
            position = 0;
        }

        track.style.transform = `translateX(${position}px)`;
    }

    requestAnimationFrame(autoScroll);
}

autoScroll();


// ==============================
// SAĞ DÜYMƏ
// ==============================

nextBtn.addEventListener("click", function () {

    position -= 300;

    track.style.transform = `translateX(${position}px)`;

});


// ==============================
// SOL DÜYMƏ
// ==============================

prevBtn.addEventListener("click", function () {

    position += 300;

    if (position > 0) {
        position = 0;
    }

    track.style.transform = `translateX(${position}px)`;

});


// ==============================
// MOUSE İLƏ SÜRÜŞDÜRMƏ
// ==============================

track.addEventListener("mousedown", function (e) {

    isDragging = true;

    startX = e.clientX;

    startPosition = position;

});

document.addEventListener("mousemove", function (e) {

    if (!isDragging) return;

    const difference = e.clientX - startX;

    position = startPosition + difference;

    track.style.transform = `translateX(${position}px)`;

});

document.addEventListener("mouseup", function () {

    isDragging = false;

});