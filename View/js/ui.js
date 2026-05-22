function toggleMenu() {
  const menu = document.getElementById('menu');
  if (!menu) return;
  menu.style.left = menu.style.left === '0px' ? '-260px' : '0px';
}

function toggleSearch() {
  const input = document.getElementById('searchInput');
  if (!input) return;
  input.classList.toggle('active');
  if (input.classList.contains('active')) input.focus();
}

function showSuccessModal() {
  const modal = document.getElementById('booking-success-modal');
  if (modal) modal.classList.add('show');
}

function hideSuccessModal() {
  const modal = document.getElementById('booking-success-modal');
  if (modal) modal.classList.remove('show');
}

document.addEventListener('DOMContentLoaded', function () {
  const modal = document.getElementById('booking-success-modal');
  if (modal) {
    modal.addEventListener('click', function (event) {
      if (event.target === modal) hideSuccessModal();
    });
  }
});
