<?php
// cette partie s'exécute avant d'envoyer l'HTML au navigateur pour s'assurer que les données dans le dossier /cache sont à jour
// si un fichier de cache est manquant ou trop ancien (plus de 30 minutes) alors le script de mise à jour correspondant est exécuté pour le rafraîchir

 $cacheDir = __DIR__ . '/cache/';
 $cacheTime = 1800;

 $pollutionCacheFile = $cacheDir . 'pollution_data.json';
 $defibrillatorCacheFile = $cacheDir . 'defibrillator_data.json';
 $pmrCacheFile = $cacheDir . 'pmr_data.json';

if (!file_exists($pollutionCacheFile) || (time() - filemtime($pollutionCacheFile) > $cacheTime)) {
    // si le fichier n'existe pas ou est trop ancien on lance le script de mise à jour
    if (file_exists(__DIR__ . '/update_pollution.php')) {
        include_once __DIR__ . '/update_pollution.php';
    } else {
        file_put_contents($pollutionCacheFile, json_encode([]));
        error_log("Script de mise à jour de la pollution non trouvé.");
    }
}

if (!file_exists($defibrillatorCacheFile) || (time() - filemtime($defibrillatorCacheFile) > $cacheTime)) {
    if (file_exists(__DIR__ . '/update_defibrillators.php')) {
        include_once __DIR__ . '/update_defibrillators.php';
    } else {
        file_put_contents($defibrillatorCacheFile, json_encode([]));
        error_log("Script de mise à jour des défibrillateurs non trouvé.");
    }
}

if (!file_exists($pmrCacheFile) || (time() - filemtime($pmrCacheFile) > $cacheTime)) {
    if (file_exists(__DIR__ . '/update_pmr.php')) {
        include_once __DIR__ . '/update_pmr.php';
    } else {
        file_put_contents($pmrCacheFile, json_encode([]));
        error_log("Script de mise à jour des PMR non trouvé.");
    }
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="assets/logo2.svg" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plus'Orléans</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <header>
        <div class="logo">
            <a href="#accueil">
                <img id="logo1" src="assets/logo1.svg" alt="logo" />
            </a>
        </div>
        <div class="header-content">
            <a href="#accueil">Accueil</a>
            <a href="#a-propos">À propos</a>
            <a href="#contact">Contact</a>
            <div class="burger-menu" id="burger-menu">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </header>

    <main class="container">
        <section id="accueil">
            <div class="controls mobile-hidden" id="controls">
                <div class="btn-group">
                    <button id="btn-defibrillators" class="control-btn" data-target="defibrillators">
                        <i class="fa-solid fa-heart-pulse"></i> Défibrillateurs
                    </button>
                    <button id="btn-air-quality" class="control-btn" data-target="air-quality">
                        <i class="fa-solid fa-wind"></i> Qualité de l'air
                    </button>
                    <button id="btn-pmr" class="control-btn" data-target="pmr">
                        <i class="fa-solid fa-wheelchair"></i> Voies PMR
                    </button>
                </div>
                <div class="legend" id="air-quality-legend" style="display: none;">
                    <div class="legend-item">
                        <div class="legend-color" style="background-color: #4ff0e5;"></div>
                        <span>Bon</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background-color: #50ccaa;"></div>
                        <span>Moyen</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background-color: #f0e641;"></div>
                        <span>Dégradé</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background-color: #ff5050;"></div>
                        <span>Mauvais</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background-color: #960032;"></div>
                        <span>Très mauvais</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background-color: #7d2181;"></div>
                        <span>Extrêmement mauvais</span>
                    </div>
                </div>
            </div>
            <div class="loading" id="loading">
                <i class="fa-solid fa-spinner fa-spin"></i> Chargement des données...
            </div>
            </div>

            <div id="map"></div>

            <div class="info-panel">
                <div class="last-update" id="last-update">
                    Dernière mise à jour: <span id="update-time">Inconnue</span>
                </div>
            </div>
        </section>


        <section id="a-propos">
            <div class="about">
                <h2>Pourquoi Puls'Orléans ?</h2>
                <p>Puls'Orléans est un projet que j’ai réalisé en dehors du temps de ma formation de Bachelor
                    Développeur
                    Full-Stack.
                    Ce projet m’a permis de découvrir la manipulation d’APIs et d'améliorer mes compétences dans la
                    conception
                    d’interfaces pour agréables et utiles pour les utilisateurs.
                </p>
            </div>
        </section>
        <section id="contact">
            <div class="form-part" id="contact-form">
                <h2>Vous voulez faire évoluer le projet ?</h2>
                <ul>
                    <li>
                        <p>Vous pouvez remplir le formulaire</p>
                    </li>
                    <li>
                        <p>Soumettre une de vos idées</p>
                    </li>
                    <li>
                        <p>Envoyer le formulaire avec votre adresse e-mail</p>
                    </li>
                </ul>

                <div class="form-feedback" id="form-feedback">
                    <?php
                    if (isset($_SESSION['form_message'])) {
                        echo $_SESSION['form_message'];
                        // supprime le message de la session pour qu'il n'apparaisse qu'une fois
                        unset($_SESSION['form_message']);
                    }
                    ?>
                </div>

                <form action="form.php" method="post">
                    <ul>
                        <li>
                            <label for="name">Nom&nbsp;:</label>
                            <input type="text" id="name" name="user_name" required />
                        </li>
                        <li>
                            <label for="mail">E-mail&nbsp;:</label>
                            <input type="email" id="mail" name="user_mail" required />
                        </li>
                        <li>
                            <label for="msg">Message&nbsp;:</label>
                            <textarea id="msg" name="user_message" required></textarea>
                        </li>
                    </ul>
                    <button type="submit" name="envoyer" class="submit-btn">Envoyer</button>
                </form>
            </div>
        </section>
    </main>

    <footer>
        <div class="footer-content">
            <p>&copy; 2026 NATHAN COBAT. Tous droits réservés.</p>
            <div class="footer-social">
                <a href="https://github.com/NathanCBT" target="_blank" aria-label="GitHub"><i
                        class="fa-brands fa-github"></i></a>
                <a href="https://www.linkedin.com/in/nathan-cobat-8b54a2386/" target="_blank" aria-label="LinkedIn"><i
                        class="fa-brands fa-linkedin"></i></a>
            </div>
        </div>
    </footer>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="js/map.js"></script>
</body>

</html>