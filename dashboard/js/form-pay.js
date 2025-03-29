document.addEventListener("DOMContentLoaded", () => {
  function validateForm() {
    const requiredFields = [
      "inputCPM",
      "inputImpressions",
      "inputAdLink",
      "inputCampaignDescription",
      "inputCampaignName",
      "inputCampaignStartDate",
      "inputdiff_per_hour",
    ];

    // Vérifie si tous les champs requis sont remplis
    const allRequiredFieldsFilled = requiredFields.every((fieldId) => {
      const field = document.getElementById(fieldId);
      return field && field.value.trim();
    });

    // Vérifie la présence de l'image obligatoire
    const logoFileInput = document.getElementById("inputLogoUpload");
    const logoUploaded = logoFileInput && logoFileInput.files.length > 0;

    // Vérifie la condition Discord ou Minecraft
    const discordCheckbox = document.getElementById("selectDiscord");
    const discordMessageInput = document.getElementById("inputDiscordMessage");
    const minecraftCheckbox = document.getElementById("selectMinecraft");
    const minecraftMessageInput = document.getElementById(
      "inputMinecraftMessage"
    );

    const discordValid =
      discordCheckbox.checked && discordMessageInput.value.trim();
    const minecraftValid =
      minecraftCheckbox.checked && minecraftMessageInput.value.trim();

    // Condition : si les deux sont cochés, les deux messages doivent être remplis
    const formIsValid =
      allRequiredFieldsFilled &&
      logoUploaded &&
      ((discordCheckbox.checked &&
        minecraftCheckbox.checked &&
        discordValid &&
        minecraftValid) ||
        (!discordCheckbox.checked && minecraftValid) ||
        (!minecraftCheckbox.checked && discordValid));

    // Active ou désactive le bouton "payer" en fonction de la validité
    const payButton = document.getElementById("pay-button");
    if (payButton) {
      payButton.disabled = !formIsValid;
    }
  }

  // Applique validateForm sur chaque changement dans les champs requis, les options, et l'upload de logo
  [
    "inputCPM",
    "inputImpressions",
    "inputAdLink",
    "inputCampaignDescription",
    "inputCampaignName",
    "inputCampaignStartDate",
    "inputdiff_per_hour",
    "selectDiscord",
    "inputDiscordMessage",
    "selectMinecraft",
    "inputMinecraftMessage",
    "inputLogoUpload",
  ].forEach((id) => {
    const element = document.getElementById(id);
    if (element) {
      element.addEventListener("input", validateForm);
      if (element.type === "checkbox") {
        element.addEventListener("change", validateForm);
      }
    }
  });

  // Affiche ou cache les champs de message selon la sélection Discord/Minecraft
  const discordCheckbox = document.getElementById("selectDiscord");
  if (discordCheckbox) {
    discordCheckbox.addEventListener("change", () => {
      const discordMessageContainer = document.getElementById(
        "discordMessageContainer"
      );
      discordMessageContainer.style.display = discordCheckbox.checked
        ? "block"
        : "none";
      validateForm();
    });
  }

  const minecraftCheckbox = document.getElementById("selectMinecraft");
  if (minecraftCheckbox) {
    minecraftCheckbox.addEventListener("change", () => {
      const minecraftMessageContainer = document.getElementById(
        "minecraftMessageContainer"
      );
      minecraftMessageContainer.style.display = minecraftCheckbox.checked
        ? "block"
        : "none";
      validateForm();
    });
  }

  // Fonction pour calculer le coût total
  function calculateTotalCost() {
    const cpm = parseFloat(document.getElementById("inputCPM").value) || 0;
    const impressions =
      parseInt(document.getElementById("inputImpressions").value, 10) || 0;
    const totalCostField = document.getElementById("totalCost");

    if (cpm > 0 && impressions > 0) {
      const cost = (cpm * impressions) / 1000;
      const totalCost = cost * 1.9; // Majoré de 40%
      totalCostField.value = totalCost.toFixed(2) + " €";
    } else {
      totalCostField.value = "Valeurs invalides";
    }
  }

  // Applique calculateTotalCost sur chaque changement de CPM ou impressions
  document
    .getElementById("inputCPM")
    .addEventListener("input", calculateTotalCost);
  document
    .getElementById("inputImpressions")
    .addEventListener("input", calculateTotalCost);

  // Gestion du paiement via Stripe
  const payButton = document.getElementById("pay-button");
  if (payButton) {
    payButton.addEventListener("click", async () => {
      if (payButton.disabled) return; // Empêche l'envoi si désactivé

      const formData = new FormData();
      formData.append(
        "cpm",
        parseFloat(document.getElementById("inputCPM").value)
      );
      formData.append(
        "impressions",
        parseInt(document.getElementById("inputImpressions").value, 10)
      );
      formData.append(
        "ad_link",
        document.getElementById("inputAdLink").value.trim()
      );
      formData.append(
        "campaign_description",
        document.getElementById("inputCampaignDescription").value.trim()
      );
      formData.append(
        "campaign_name",
        document.getElementById("inputCampaignName").value.trim()
      );
      formData.append(
        "campaign_start_date",
        document.getElementById("inputCampaignStartDate").value
      );
      formData.append(
        "diff_per_hour",
        document.getElementById("inputdiff_per_hour").value.trim()
      );

      const discordCheckbox = document.getElementById("selectDiscord");
      const discordMessageInput = document.getElementById(
        "inputDiscordMessage"
      );
      const minecraftCheckbox = document.getElementById("selectMinecraft");
      const minecraftMessageInput = document.getElementById(
        "inputMinecraftMessage"
      );

      formData.append(
        "discord",
        discordCheckbox && discordCheckbox.checked ? "yes" : "no"
      );
      if (
        discordCheckbox &&
        discordCheckbox.checked &&
        discordMessageInput.value.trim()
      ) {
        formData.append("discord_message", discordMessageInput.value.trim());
      }

      formData.append(
        "minecraft",
        minecraftCheckbox && minecraftCheckbox.checked ? "yes" : "no"
      );
      if (
        minecraftCheckbox &&
        minecraftCheckbox.checked &&
        minecraftMessageInput.value.trim()
      ) {
        formData.append(
          "minecraft_message",
          minecraftMessageInput.value.trim()
        );
      }

      const logoFile = document.getElementById("inputLogoUpload").files[0];
      if (logoFile) {
        const allowedTypes = ["image/jpeg", "image/png"];
        if (!allowedTypes.includes(logoFile.type)) {
          alert("Veuillez sélectionner un fichier PNG ou JPEG pour le logo.");
          return;
        }
        formData.append("logo_upload", logoFile);
      }

      try {
        const response = await fetch(
          "/dashboard/z-stripe/stripe-checkout.php",
          {
            method: "POST",
            body: formData,
          }
        );
        const result = await response.json();
        if (response.ok) {
          const stripe = Stripe(
            "pk_test_51OJi61DSit6yTJlDVAemzFyCqRL6puhUqnizsoprry8fegHUmFwDWHoGndzoiY7ONyA6nMunTqqC5vBVOMONRI2Y00lpG1wwoS"
          );
          await stripe.redirectToCheckout({ sessionId: result.sessionId });
        } else {
          console.error("Erreur:", result.error);
        }
      } catch (error) {
        console.error("Error:", error);
      }
    });
  }

  // Gestion des boutons pour la navigation entre étapes
  [
    ["nextButton1", "wizard1"],
    ["prevButton2", "wizard2"],
    ["nextButton2", "wizard2"],
    ["prevButton3", "wizard3"],
    ["nextButton3", "wizard3"],
  ].forEach(([buttonId, tabId]) => {
    const button = document.getElementById(buttonId);
    if (button) {
      button.addEventListener("click", () => {
        if (buttonId.startsWith("next")) showNextStep(tabId);
        else showPrevStep(tabId);
      });
    }
  });

  // Fonction pour afficher l’étape suivante
  function showNextStep(currentTab) {
    const currentTabElement = document.querySelector(`#${currentTab}-tab`);
    if (!currentTabElement) {
      console.warn(`Onglet actuel introuvable: ${currentTab}`);
      return;
    }

    const nextTab = currentTabElement.nextElementSibling;
    if (nextTab && nextTab.getAttribute("role") === "tab") {
      const tab = new bootstrap.Tab(nextTab);
      tab.show();
    } else {
      console.warn("Aucun onglet suivant trouvé.");
    }
  }

  // Fonction pour afficher l’étape précédente
  function showPrevStep(currentTab) {
    const currentTabElement = document.querySelector(`#${currentTab}-tab`);
    if (!currentTabElement) {
      console.warn(`Onglet actuel introuvable: ${currentTab}`);
      return;
    }

    const prevTab = currentTabElement.previousElementSibling;
    if (prevTab && prevTab.getAttribute("role") === "tab") {
      const tab = new bootstrap.Tab(prevTab);
      tab.show();
    } else {
      console.warn("Aucun onglet précédent trouvé.");
    }
  }

  // Valide et calcule au chargement de la page
  validateForm();
  calculateTotalCost();
});
