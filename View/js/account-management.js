function attachSearchResults(inputId, pages) {
  const input = document.getElementById(inputId);
  if (!input) return;

let resultBox = document.getElementById("searchResults");
if (!resultBox) {
    resultBox = document.createElement("div");
    resultBox.id = "searchResults";
    document.body.appendChild(resultBox);
}

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

function initCodeInputs() {
  const inputs = Array.from(document.querySelectorAll('.code-input'));
  if (!inputs.length) return;

  inputs.forEach((input, index) => {
    input.addEventListener('input', (e) => {
      e.target.value = e.target.value.replace(/[^0-9]/g, '').slice(0, 1);
      if (e.target.value && index < inputs.length - 1) inputs[index + 1].focus();
    });

    input.addEventListener('keydown', (e) => {
      if (e.key === 'Backspace' && !e.target.value && index > 0) {
        inputs[index - 1].focus();
      }
    });
  });
}

function initPasswordToggles() {
  document.querySelectorAll('[data-toggle-password]').forEach((button) => {
    button.addEventListener('click', () => {
      const target = document.getElementById(button.dataset.togglePassword);
      if (!target) return;
      const isPassword = target.type === 'password';
      target.type = isPassword ? 'text' : 'password';
      button.innerHTML = `<i class="fa-solid ${isPassword ? 'fa-eye-slash' : 'fa-eye'}"></i>`;
    });
  });
}

function initDeleteAccountModal() {
  const modal = document.getElementById('delete-account-modal');
  const openButton = document.getElementById('open-delete-modal');
  const passwordStep = document.getElementById('delete-password-step');
  const confirmStep = document.getElementById('delete-confirm-step');
  const passwordInput = document.getElementById('deletePassword');
  const continueButton = document.getElementById('continue-delete');
  const backButton = document.getElementById('back-delete-password');

  if (!modal || !openButton || !passwordStep || !confirmStep || !passwordInput) return;

  const showPasswordStep = () => {
    passwordStep.hidden = false;
    confirmStep.hidden = true;
    passwordStep.classList.remove('is-hidden');
    confirmStep.classList.add('is-hidden');
  };

  const closeModal = () => {
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    modal.hidden = true;
    document.body.classList.remove('modal-open');
    showPasswordStep();
  };

  openButton.addEventListener('click', () => {
    modal.hidden = false;
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('modal-open');
    passwordInput.value = '';
    showPasswordStep();
    setTimeout(() => passwordInput.focus(), 50);
  });

  document.querySelectorAll('[data-close-delete-modal]').forEach((button) => {
    button.addEventListener('click', closeModal);
  });

  modal.addEventListener('click', (event) => {
    if (event.target === modal) closeModal();
  });

  if (continueButton) {
    continueButton.addEventListener('click', () => {
      if (!passwordInput.reportValidity()) return;
      passwordStep.hidden = true;
      confirmStep.hidden = false;
      passwordStep.classList.add('is-hidden');
      confirmStep.classList.remove('is-hidden');
    });
  }

  if (backButton) {
    backButton.addEventListener('click', showPasswordStep);
  }

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && modal.classList.contains('is-open')) {
      closeModal();
    }
  });
}

window.addEventListener('DOMContentLoaded', () => {
  attachSearchResults('searchInput', [
    { name: 'Profile', link: 'profile.php' },
    { name: 'Orders', link: 'orders.php' },
    { name: 'Favorites', link: 'favorites.php' },
    { name: 'Order Tracking', link: 'tracking.php' },
    { name: 'Account Management', link: 'account-management.php' },
    { name: 'Verification Code', link: 'verify-code.php' },
    { name: 'Reset Password', link: 'change-password.php' }
  ]);
  initCodeInputs();
  initPasswordToggles();
  initDeleteAccountModal();
});
