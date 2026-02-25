document.addEventListener("DOMContentLoaded", function () {
  const map = L.map("map").setView([47.9029, 1.9093], 12); // carte centrée sur Orléans
  let pmrLayer = null;
  let airQualityMarkers = [];
  let defibrillatorMarkers = [];
  let activeLayer = null;
  let userLocationMarker = null;

  L.tileLayer(
    "https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png",
    {
      attribution: "© OpenStreetMap contributors © CARTO",
      subdomains: "abcd",
      maxZoom: 19,
    },
  ).addTo(map);

  // menu burger mobil
  const burgerMenu = document.getElementById("burger-menu");
  const controls = document.getElementById("controls");

  burgerMenu.addEventListener("click", () => {
    controls.classList.toggle("mobile-hidden");
    controls.classList.toggle("mobile-visible");
  });

  const controlButtons = document.querySelectorAll(".control-btn");
  controlButtons.forEach((button) => {
    button.addEventListener("click", () => {
      const target = button.dataset.target;
      toggleLayer(target, button);

      if (controls.classList.contains("mobile-visible")) {
        controls.classList.remove("mobile-visible");
        controls.classList.add("mobile-hidden");
      }
    });
  });

  function toggleLayer(layerName, buttonElement) {
    if (buttonElement.classList.contains("active")) {
      buttonElement.classList.remove("active");
      if (layerName === "air-quality") {
        document.getElementById("air-quality-legend").style.display = "none";
      }
      removeLayer(layerName);
      activeLayer = null;
      return;
    }

    if (activeLayer) {
      const oldButton = document.querySelector(
        `.control-btn[data-target="${activeLayer}"]`,
      );
      if (oldButton) oldButton.classList.remove("active");
      if (activeLayer === "air-quality") {
        document.getElementById("air-quality-legend").style.display = "none";
      }
      removeLayer(activeLayer);
    }

    buttonElement.classList.add("active");
    activeLayer = layerName;

    if (layerName === "air-quality") {
      document.getElementById("air-quality-legend").style.display = "flex";
    }

    loadLayer(layerName);
  }

  function removeLayer(layerName) {
    if (layerName === "defibrillators") {
      defibrillatorMarkers.forEach((marker) => map.removeLayer(marker));
    } else if (layerName === "air-quality") {
      airQualityMarkers.forEach((marker) => map.removeLayer(marker));
    } else if (layerName === "pmr" && pmrLayer) {
      map.removeLayer(pmrLayer);
    }
  }

  function loadLayer(layerName) {
    document.getElementById("loading").style.display = "block"; //indicateur de chargement
    let apiUrl = `api/${layerName}.php`;

    if (layerName === "air-quality") {
      apiUrl = "api/pollution_atmo.php";
    }

    fetch(apiUrl)
      .then((res) => res.json())
      .then((data) => {
        if (data.error) {
          throw new Error(data.error);
        }

        try {
          displayData(data, layerName);
        } catch (renderingError) {
          console.error(`Erreur de rendu (${layerName}):`, renderingError);
          document.getElementById("loading").innerHTML =
            `<i class="fa-solid fa-exclamation-triangle"></i> Erreur lors de l'affichage des données.`;
        }
      })
      .catch((err) => {
        console.error(`Erreur (${layerName}):`, err);
        document.getElementById("loading").innerHTML =
          `<i class="fa-solid fa-exclamation-triangle"></i> Erreur lors du chargement.`;
      })
      .finally(() => {
        document.getElementById("loading").style.display = "none";
      });
  }

  function formatDefibrillatorInfo(tags) {
    if (!tags)
      return {
        address: "Non disponible",
        indoor: "Non spécifié",
        level: "Non spécifié",
        opening_hours: "Non spécifiés",
      };

    const info = {
      address: "Non disponible",
      indoor: "Non spécifié",
      level: "Non spécifié",
      opening_hours: "Non spécifiés",
    };

    if (tags.indoor) {
      info.indoor =
        tags.indoor === "yes"
          ? "Accès intérieur"
          : tags.indoor === "no"
            ? "Accès extérieur"
            : tags.indoor;
    }

    if (tags.level) {
      info.level = `Étage ${tags.level}`;
    }

    if (tags.opening_hours) {
      info.opening_hours = tags.opening_hours;
    }

    const addr_parts = [
      tags["addr:housenumber"],
      tags["addr:street"],
      tags["addr:postcode"] ? ` ${tags["addr:postcode"]}` : "",
      tags["addr:city"] ? ` ${tags["addr:city"]}` : "",
    ]
      .filter(Boolean)
      .join(" ");

    if (addr_parts) {
      info.address = addr_parts;
    }

    return info;
  }

  function displayData(data, layerName) {
    if (layerName === "defibrillators") {
      defibrillatorMarkers = [];
      data.forEach((item) => {
        if (!item.lat || !item.lng) return;

        const icon = L.divIcon({
          html: `<i class="fa-solid fa-heart-pulse" style="color:#e74c3c;font-size:20px;"></i>`,
          iconSize: [30, 30],
          className: "defibrillator-icon",
        });

        const info = formatDefibrillatorInfo(item.tags);

        //popup info défibrillateurs
        const popupContent = `
          <div style="min-width:200px;">
            <strong>Défibrillateur</strong><br>
            <strong>Localisation :</strong> ${info.address}<br>
            <strong>Accès :</strong> ${info.indoor}<br>
            <strong>Étage :</strong> ${info.level}<br>
            <strong>Horaires :</strong> ${info.opening_hours}<br>
            <hr style="margin: 5px 0;">
            <a href="https://www.google.com/maps/dir/?api=1&destination=${item.lat},${item.lng}&travelmode=driving" target="_blank" style="display:inline-block; background-color: #007BFF; color: white; padding: 8px 12px; text-decoration: none; border-radius: 4px; font-weight: bold; text-align: center;">
              <i class="fa-solid fa-location-dot"></i> Y aller
            </a>
          </div>
        `;

        const marker = L.marker([item.lat, item.lng], { icon }).bindPopup(
          popupContent,
        );
        defibrillatorMarkers.push(marker);
        marker.addTo(map);
      });
    } else if (layerName === "air-quality") {
      airQualityMarkers = [];
      data.forEach((item) => {
        if (!item.lat || !item.lng) return;
        const marker = L.circleMarker([item.lat, item.lng], {
          radius: 12,
          fillColor: item.couleur || "#999",
          color: "#000",
          weight: 1,
          fillOpacity: 0.8,
        }).bindPopup(
          `<div style="text-align: center;">
            <strong>${item.commune}</strong><br>
            <div style="font-size: 24px; font-weight: bold; color: ${item.couleur};">${item.indice ?? "N/A"}</div>
            Qualité : ${item.qualite}<br>
            Date : ${item.date ? new Date(item.date).toLocaleDateString("fr-FR") : "N/A"}
          </div>`,
        );
        airQualityMarkers.push(marker);
        marker.addTo(map);
      });
    } else if (layerName === "pmr") {
      if (pmrLayer) map.removeLayer(pmrLayer);
      pmrLayer = L.layerGroup();
      data.forEach((item) => {
        if (item.coords && item.coords.length > 1) {
          const polyline = L.polyline(item.coords, {
            color: "#3498db",
            weight: 5,
            opacity: 0.7,
          });
          polyline.bindPopup("<strong>Chemin accessible</strong>");
          polyline.addTo(pmrLayer);
        }
      });
      pmrLayer.addTo(map);
      updatePmrStyle();
    }
  }

  function updatePmrStyle() {
    if (!pmrLayer) return;
    const currentZoom = map.getZoom();
    let style = { color: "#3498db", weight: 2, opacity: 0.4 };
    if (currentZoom >= 15)
      style = { color: "#0066cc", weight: 5, opacity: 0.8 };
    else if (currentZoom >= 13)
      style = { color: "#0080d5", weight: 4, opacity: 0.6 };

    pmrLayer.eachLayer((layer) => {
      if (layer instanceof L.Polyline) layer.setStyle(style);
    });
  }

  map.on("zoomend", updatePmrStyle);

  //localisation de l'utilisateur

  function onLocationFound(position) {
    const lat = position.coords.latitude;
    const lng = position.coords.longitude;
    const accuracy = position.coords.accuracy;

    console.log(
      `Position utilisateur trouvée : Lat=${lat}, Lng=${lng}, Précision=${accuracy}m`,
    );

    const userIcon = L.divIcon({
      html: '<i class="fa-solid fa-user" style="color: #3498db;"></i>', // Icône bleue
      iconSize: [20, 20],
      className: "user-location-icon",
    });

    userLocationMarker = L.marker([lat, lng], { icon: userIcon })
      .addTo(map)
      .bindPopup("Vous êtes ici !");

    L.circle([lat, lng], {
      radius: accuracy,
      color: "#3498db",
      fillColor: "#3498db",
      fillOpacity: 0.15,
    }).addTo(map);

    // centre la carte sur la position de l'utilisateur
    map.setView([lat, lng], 15);
  }

  function onLocationError(error) {
    let message = "Erreur de localisation : ";
    switch (error.code) {
      case error.PERMISSION_DENIED:
        message += "Vous avez refusé l'accès à votre localisation.";
        break;
      case error.POSITION_UNAVAILABLE:
        message += "Les informations de localisation ne sont pas disponibles.";
        break;
      case error.TIMEOUT:
        message += "La demande de localisation a expiré.";
        break;
      case error.UNKNOWN_ERROR:
        message += "Une erreur inconnue est survenue.";
        break;
    }

    console.error(message);
    document.getElementById("update-time").textContent = message;
  }

  function loadDefaultLayer() {
    console.log("Chargement de la couche par défaut (défibrillateurs).");
    const defaultButton = document.getElementById("btn-defibrillators");
    toggleLayer("defibrillators", defaultButton);
  }

  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      (position) => {
        console.log("Géolocalisation réussie.");
        onLocationFound(position);
        loadDefaultLayer();
      },
      (error) => {
        console.error("Erreur de géolocalisation :", error.message);
        onLocationError(error);
        loadDefaultLayer();
      },
      {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 0,
      },
    );
  } else {
    console.error(
      "La géolocalisation n'est pas supportée par votre navigateur.",
    );
    document.getElementById("update-time").textContent =
      "Géolocalisation non supportée.";
    loadDefaultLayer();
  }

  // récupère et affiche l'heure de la dernière mise à jour des données
  fetch("api/last_update.php")
    .then((res) => res.json())
    .then((data) => {
      if (data.last_update) {
        if (
          !document.getElementById("update-time").textContent.includes("Erreur")
        ) {
          document.getElementById("update-time").textContent = new Date(
            data.last_update * 1000,
          ).toLocaleString("fr-FR");
        }
      }
    })
    .catch((err) =>
      console.error(
        "Erreur lors de la récupération de l'heure de mise à jour:",
        err,
      ),
    );
});
