let form = document.querySelector("form");
let email = document.querySelector("input[name='email']");
let btn = document.getElementById("btn");

form.addEventListener("submit", function(e){

    // validation
    if(email.value.trim() === ""){
        e.preventDefault();
        alert("Please enter your email");
        return;
    }

    // loading effect
    btn.classList.add("loading");
    btn.innerText = "Sending...";
    btn.disabled = true;
});