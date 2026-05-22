function attachSearchResults(inputId, pages) {
  const input = document.getElementById(inputId);
  if (!input) return;

  const resultBox = document.createElement("div");
  resultBox.id = "searchResults";
  document.body.appendChild(resultBox);

  input.addEventListener("input", function () {
    const value = this.value.toLowerCase().trim();
    resultBox.innerHTML = "";

    if (!value) {
      resultBox.style.display = "none";
      return;
    }

    const results = pages.filter((page) => page.name.toLowerCase().includes(value));

    if (!results.length) {
      const item = document.createElement("div");
      item.innerText = "No results found";
      item.style.color = "gray";
      item.style.cursor = "default";
      item.style.textAlign = "center";
      resultBox.appendChild(item);
      resultBox.style.display = "block";
      return;
    }

    results.forEach((page) => {
      const item = document.createElement("div");
      item.innerText = page.name;
      item.onclick = () => {
        window.location.href = page.link;
      };
      resultBox.appendChild(item);
    });

    resultBox.style.display = "block";
  });

  document.addEventListener("click", function (e) {
    if (!input.contains(e.target) && !resultBox.contains(e.target)) {
      resultBox.style.display = "none";
    }
  });
}

function toggleMenu() {
  const menu = document.getElementById("menu");
  if (!menu) return;
  menu.style.left = menu.style.left === "0px" ? "-260px" : "0px";
}

function toggleSearch() {
  const input = document.getElementById("searchInput");
  if (!input) return;
  input.classList.toggle("active");
  if (input.classList.contains("active")) input.focus();
}

function showSuccessModal() {
  const modal = document.getElementById("booking-success-modal");
  if (modal) modal.classList.add("show");
}

function hideSuccessModal() {
  const modal = document.getElementById("booking-success-modal");
  if (modal) modal.classList.remove("show");
}
window.addEventListener("DOMContentLoaded", () => {
  const modal = document.getElementById("booking-success-modal");
  if (modal) {
    modal.addEventListener("click", function (event) {
      if (event.target === modal) hideSuccessModal();
    });
  }
});
  const modal = document.getElementById("booking-success-modal");
  if (modal) {
    modal.addEventListener("click", function (event) {
      if (event.target === modal) hideSuccessModal();
    });
  }
window.addEventListener("DOMContentLoaded", () => {
  const modal = document.getElementById("booking-success-modal");
  if (modal) {
    modal.addEventListener("click", function (event) {
      if (event.target === modal) hideSuccessModal();
    });
  }

  // Booking Form Validation
  const bookingForm = document.querySelector('.book-main-grid');
  if (bookingForm) {
    bookingForm.addEventListener('submit', function(e) {
      const fullName = bookingForm.querySelector('input[name="full_name"]').value.trim();
      const phone = bookingForm.querySelector('input[name="phone"]').value.trim();
      const whatsapp = bookingForm.querySelector('input[name="whatsapp_number"]').value.trim();
      const city = bookingForm.querySelector('input[name="city"]').value.trim();
      const area = bookingForm.querySelector('input[name="area"]').value.trim();

      clearBookingError();

      if (!fullName || !phone || !whatsapp || !city || !area) {
        e.preventDefault();
        showBookingError('Please fill in all required fields.');
        return;
      }

      const phoneRegex = /^[0-9]{11}$/;
      if (!phoneRegex.test(phone)) {
        e.preventDefault();
        showBookingError('Phone number must be 11 digits.');
        return;
      }

      if (!phoneRegex.test(whatsapp)) {
        e.preventDefault();
        showBookingError('WhatsApp number must be 11 digits.');
        return;
      }

      // Dimensions validation
      const lengthInputs = bookingForm.querySelectorAll('input[name^="length"]');
      const widthInputs = bookingForm.querySelectorAll('input[name^="width"]');
      const numRegex = /^\d+(\.\d+)?$/;

      for (let input of lengthInputs) {
        if (input.value.trim() !== '' && !numRegex.test(input.value.trim())) {
          e.preventDefault();
          showBookingError('Length must be a valid number.');
          return;
        }
      }

      for (let input of widthInputs) {
        if (input.value.trim() !== '' && !numRegex.test(input.value.trim())) {
          e.preventDefault();
          showBookingError('Width must be a valid number.');
          return;
        }
      }
    });
  }
});

function showBookingError(message) {
  clearBookingError();
  const box = document.createElement('div');
  box.id = 'booking-error';
  box.style.cssText = 'background:#fff1f1; border:1px solid #e8b9b9; color:#8e1f1f; padding:12px 16px; border-radius:12px; font-size:13px; margin-bottom:15px; display:flex; align-items:center; gap:8px;';
  box.innerHTML = `<i class="fa-solid fa-circle-exclamation"></i> ${message}`;
  const intro = document.querySelector('.book-intro');
  if (intro) intro.after(box);
}

function clearBookingError() {
  const old = document.getElementById('booking-error');
  if (old) old.remove();
}

function toggleMenu() {
  const menu = document.getElementById("menu");
  if (!menu) return;
  menu.style.left = menu.style.left === "0px" ? "-260px" : "0px";
}

function toggleSearch() {
  const input = document.getElementById("searchInput");
  if (!input) return;
  input.classList.toggle("active");
  if (input.classList.contains("active")) input.focus();
}

function showSuccessModal() {
  const modal = document.getElementById("booking-success-modal");
  if (modal) modal.classList.add("show");
}

function hideSuccessModal() {
  const modal = document.getElementById("booking-success-modal");
  if (modal) modal.classList.remove("show");
}