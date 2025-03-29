document.addEventListener("DOMContentLoaded", function () {
  const cookieConsent = document.getElementById("cookieConsent");
  const acceptCookies = document.getElementById("acceptCookies");

  if (!localStorage.getItem("cookiesAccepted")) {
    cookieConsent.style.display = "flex";
    setTimeout(() => {
      cookieConsent.classList.remove("hidden");
    }, 10); // Délais pour permettre à l'animation d'entrée de se jouer
  }

  acceptCookies.addEventListener("click", function () {
    localStorage.setItem("cookiesAccepted", "true");
    cookieConsent.classList.add("hidden");
    setTimeout(() => {
      cookieConsent.style.display = "none";
    }, 500); // Durée de l'animation de sortie
  });
});
