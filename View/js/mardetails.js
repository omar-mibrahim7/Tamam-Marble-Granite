let count = 1;

const plus = document.querySelector(".plus");
const minus = document.querySelector(".minus");
const countEl = document.querySelector(".count");
const quantityInput = document.getElementById("quantityInput");

function syncQuantityInput() {
    if (quantityInput) quantityInput.value = count;
}

if(plus) plus.onclick = () => {
    count++;

    if(countEl){
        countEl.textContent = count;
    }

    syncQuantityInput();
};

if(minus) minus.onclick = () => {
    if(count > 1){
        count--;

        if(countEl){
            countEl.textContent = count;
        }

        syncQuantityInput();
    }
};

const heart = document.querySelector(".heart");
if(heart) heart.onclick = () => {
    heart.classList.toggle("active");
};
const bookQuantityInput = document.getElementById("bookQuantityInput");

function syncQuantityInput() {

    if (quantityInput) {
        quantityInput.value = count;
    }

    if (bookQuantityInput) {
        bookQuantityInput.value = count;
    }
}