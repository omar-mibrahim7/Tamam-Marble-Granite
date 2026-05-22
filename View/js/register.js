function toggle1(){
    let p = document.getElementById("pass1");
    p.type = (p.type === "password") ? "text" : "password";
}

function toggle2(){
    let p = document.getElementById("pass2");
    p.type = (p.type === "password") ? "text" : "password";
}
document.querySelector('form').addEventListener('submit', function(e) {
    const name     = document.querySelector('input[name="name"]').value.trim();
    const phone    = document.querySelector('input[name="phone"]').value.trim();
    const email    = document.querySelector('input[name="email"]').value.trim();
    const password = document.querySelector('input[name="password"]').value.trim();
    const confirm  = document.querySelector('input[name="confirm"]').value.trim();
    const type     = document.querySelector('select[name="type"]').value;

    // إخفاء أي error قديم
    clearError();

    if (!name || !phone || !email || !password || !confirm) {
        e.preventDefault();
        showError('Please fill in all required fields.');
        return;
    }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        e.preventDefault();
        showError('Please enter a valid email address.');
        return;
    }

    const phoneRegex = /^[0-9]{11}$/;
    if (!phoneRegex.test(phone)) {
        e.preventDefault();
        showError('Please enter a valid phone number.');
        return;
    }

    if (password.length < 8) {
        e.preventDefault();
        showError('Password must be at least 8 characters.');
        return;
    }

    if (password !== confirm) {
        e.preventDefault();
        showError('Passwords do not match.');
        return;
    }

    if (!type) {
        e.preventDefault();
        showError('Please choose an account type.');
        return;
    }
});

function showError(message) {
    clearError();
    const box = document.createElement('div');
    box.id = 'js-error';
    box.className = 'form-message error';
    box.innerHTML = `
        <i class="fa-solid fa-circle-exclamation"></i>
        <div>
            <strong>Account was not created</strong>
            <span>${message}</span>
        </div>
    `;
    const form = document.querySelector('form');
    form.parentNode.insertBefore(box, form);
}

function clearError() {
    const old = document.getElementById('js-error');
    if (old) old.remove();
}

function toggle1() {
    const input = document.getElementById('pass1');
    input.type = input.type === 'password' ? 'text' : 'password';
}

function toggle2() {
    const input = document.getElementById('pass2');
    input.type = input.type === 'password' ? 'text' : 'password';
}