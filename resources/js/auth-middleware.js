(function () {
  try {
    const token = localStorage.getItem("token");
    const path = window.location.pathname;

    const guestPages = ["/login", "/register"];
    const isGuestPage = guestPages.includes(path);

    if (!token && !isGuestPage) {
      window.location.href = "/login";
      return;
    }

    if (token && isGuestPage) {
      window.location.href = "/home";
      return;
    }

  } catch (_) {
    // abaikan error
  }
})();
