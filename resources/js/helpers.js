function formatIDR(value) {
  // hilangkan semua selain angka
  let numberString = value.replace(/[^\d]/g, "");

  // hilangkan nol di depan (kecuali jika angka memang "0")
  numberString = numberString.replace(/^0+/, "");

  if (numberString.length === 0) return "";

  let formatted = numberString.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
  return "Rp " + formatted;
}

function unformatIDR(formatted) {
  if (!formatted) return 0;
  return parseInt(formatted.replace(/[^\d]/g, ""), 10);
}

function setBladewindInputError(fieldName, message) {
  const errorElement = document.getElementById(`error-${fieldName}`);

  // reset error message 
  // resetBladewindInputError();

  // set new error message
  if (errorElement) {
    errorElement.textContent = message;
  }
}

function resetBladewindInputError() {
  document.querySelectorAll('.alert-error').forEach(el => el.textContent = '');
}

window.formatIDR = formatIDR;
window.unformatIDR = unformatIDR;
window.setBladewindInputError = setBladewindInputError;
window.resetBladewindInputError = resetBladewindInputError;