function validateLogin() {
  let email = document.getElementById("email").value;
  let password = document.getElementById("password").value;

  if (email == "" || password == "") {
    alert("Semua field wajib diisi");
    return false;
  }

  return true;
}
