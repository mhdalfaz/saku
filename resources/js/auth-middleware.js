(async function () {
  try {
    const token = localStorage.getItem("token");
    const path = window.location.pathname;

    const guestPages = ["/login", "/register"];
    const isGuestPage = guestPages.includes(path);

    // --- Jika tidak ada token dan bukan halaman guest →
    if (!token && !isGuestPage) {
      window.location.href = "/login";
      return;
    }

    // --- Jika punya token, cek apakah VALID ---
    if (token) {
      const check = await fetch("/api/me", {
        headers: {
          "Authorization": "Bearer " + token
        }
      });

      // status 401 = token expired / invalid
      if (check.status === 401) {
        alert("Sesi Anda telah berakhir, silakan login kembali.");
        localStorage.removeItem("token");
        window.location.href = "/login";
        return;
      }
    }

    // --- Jika sudah login tapi masuk halaman login/register →
    if (token && isGuestPage) {
      window.location.href = "/home";
      return;
    }

  } catch (_) {
    // abaikan error
  }
})();
