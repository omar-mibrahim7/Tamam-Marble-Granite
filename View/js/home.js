document.addEventListener("DOMContentLoaded", () => {

const slider = document.querySelector(".best-selling-wrapper");
const leftBtn = document.querySelector(".arrow.left");
const rightBtn = document.querySelector(".arrow.right");
const dots = document.querySelectorAll(".dots span");

// مقدار الـ scroll عند كل ضغطة
const scrollAmount = 300;

// زرار اليمين
if (rightBtn) {
  rightBtn.addEventListener("click", () => {
    slider.scrollBy({ left: scrollAmount, behavior: "smooth" });
    updateDots();
  });
}

// زرار الشمال
if (leftBtn) {
  leftBtn.addEventListener("click", () => {
    slider.scrollBy({ left: -scrollAmount, behavior: "smooth" });
    updateDots();
  });
}

// تحديث الـ dots
function updateDots() {
  setTimeout(() => {
    const maxScroll = slider.scrollWidth - slider.clientWidth;
    const scrolled = slider.scrollLeft;

    dots.forEach((dot, i) => {
      dot.classList.remove("active");
    });

    if (scrolled < maxScroll / 2) {
      dots[0]?.classList.add("active");
    } else {
      dots[1]?.classList.add("active");
    }
  }, 300);
}

// الكود القديم بتاع الـ drag (ابقيه زي ما هو)
if (slider) {
  let isDown = false;
  let startX;
  let scrollLeft;

  slider.addEventListener("mousedown", (e) => {
    isDown = true;
    startX = e.pageX - slider.offsetLeft;
    scrollLeft = slider.scrollLeft;
  });

  slider.addEventListener("mouseleave", () => isDown = false);
  slider.addEventListener("mouseup", () => isDown = false);

  slider.addEventListener("mousemove", (e) => {
    if (!isDown) return;
    e.preventDefault();
    const x = e.pageX - slider.offsetLeft;
    const walk = (x - startX) * 2;
    slider.scrollLeft = scrollLeft - walk;
  });

  // تحديث الـ dots عند الـ scroll اليدوي
  slider.addEventListener("scroll", updateDots);
}
});
