function validateForm() {
    let name = document.getElementById("name").value.trim();
    let email = document.getElementById("email").value.trim();
    let message = document.getElementById("message").value.trim();
    let valid = true;

    if (name === "" || email === "" || message === "") {
        alert("Minden mezőt ki kell tölteni!");
        valid = false;
    } else if (!email.includes("@")) {
        alert("Érvénytelen e-mail cím!");
        valid = false;
    }

    return valid;
}
