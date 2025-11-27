function formatIDR(value) {
  if (value === null || value === undefined) return "Rp 0";

  let str = value.toString();

  let numberString = str.replace(/[^\d]/g, "");

  if (numberString === "") return "Rp 0";

  let number = parseInt(numberString, 10);

  return "Rp " + number.toLocaleString("id-ID");
}


function unformatIDR(formatted) {
  if (!formatted) return 0;
  return parseInt(formatted.replace(/[^\d]/g, ""), 10);
}

function setBladewindInputError(fieldName, message) {
  const errorElement = document.getElementById(`error-${fieldName}`);

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