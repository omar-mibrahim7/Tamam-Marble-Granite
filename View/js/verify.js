// 🔥 move between inputs
function move(current, index){
    let inputs = document.querySelectorAll(".otp-inputs input");

    if(current.value.length === 1 && index < inputs.length){
        inputs[index].focus();
    }
}

// 🔥 collect code
function collectCode(){
    let inputs = document.querySelectorAll(".otp-inputs input");
    let code = "";

    inputs.forEach(input => {
        code += input.value;
    });

    document.getElementById("finalCode").value = code;
}