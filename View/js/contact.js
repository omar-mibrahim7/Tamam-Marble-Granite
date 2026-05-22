window.addEventListener("DOMContentLoaded", () => {

  const contactForm = document.querySelector('.contact-form');

  if(contactForm){

    contactForm.addEventListener('submit', function(e){

      const fullName = contactForm.querySelector('input[name="full_name"]').value.trim();

      const phone = contactForm.querySelector('input[name="phone"]').value.trim();

      const subject = contactForm.querySelector('input[name="subject"]').value.trim();

      const message = contactForm.querySelector('textarea[name="message"]').value.trim();

      clearContactError();

      /* =========================
         EMPTY VALIDATION
      ========================= */

      if(!fullName || !phone || !subject || !message){

        e.preventDefault();

        showContactError('Please fill in all required fields.');

        return;
      }

      /* =========================
         NAME VALIDATION
      ========================= */

      if(fullName.length < 3){

        e.preventDefault();

        showContactError('Full name must be at least 3 characters.');

        return;
      }

      /* =========================
         PHONE VALIDATION
      ========================= */

      const phoneRegex = /^[0-9]{11}$/;

      if(!phoneRegex.test(phone)){

        e.preventDefault();

        showContactError('Phone number must be exactly 11 digits.');

        return;
      }

      /* =========================
         SUBJECT VALIDATION
      ========================= */

      if(subject.length < 4){

        e.preventDefault();

        showContactError('Subject must be at least 4 characters.');

        return;
      }

      /* =========================
         MESSAGE VALIDATION
      ========================= */

      if(message.length < 10){

        e.preventDefault();

        showContactError('Message must be at least 10 characters.');

        return;
      }

    });

  }

});

/* =========================
   ERROR BOX
========================= */

function showContactError(message){

  clearContactError();

  const box = document.createElement('div');

  box.id = 'contact-error';

  box.style.cssText = `
    background:#fff1f1;
    border:1px solid #f0b7b7;
    color:#9f1d1d;
    padding:14px 18px;
    border-radius:16px;
    font-size:14px;
    margin-bottom:22px;
    display:flex;
    align-items:center;
    gap:10px;
    font-weight:600;
    box-shadow:0 6px 18px rgba(0,0,0,.05);
  `;

  box.innerHTML = `
    <i class="fa-solid fa-circle-exclamation"></i>
    ${message}
  `;

  const header = document.querySelector('.contact-header');

  if(header){

    header.after(box);

  }

}

/* =========================
   CLEAR ERROR
========================= */

function clearContactError(){

  const old = document.getElementById('contact-error');

  if(old){

    old.remove();

  }

}

/* =========================
   POPUP
========================= */

function showPopup(){

  document.getElementById("popup").style.display = "flex";

}

function closePopup(){

  document.getElementById("popup").style.display = "none";

}

/* =========================
   SUBMIT
========================= */

function submitForm(){

  document.querySelector('.contact-form').requestSubmit();

}