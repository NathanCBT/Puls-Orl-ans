🗺️Puls’Orléans

Application web cartographique dédiée à l’accessibilité, la santé publique et à l’information citoyenne à Orléans 
(dans les cas d’urgences).

Ce projet centralise des données publiques locales sur une carte interactive afin d’améliorer l’accès à l’information 
urbaine (en temps réel).


🎯Contexte et ambition

Les données publiques existent, mais elles sont souvent dispersées et peu lisibles pour le citoyen.

Puls’Orléans vise à :
•	Rendre l’information urbaine claire et exploitable
•	Favoriser l’inclusion (personnes à mobilité réduite)
•	Faciliter l’accès aux équipements de santé (défibrillateurs)
•	Sensibiliser à la qualité de l’air


🚀 Fonctionnalités

🫀 Défibrillateurs
  •	Récupération dynamique via l’API Overpass (OpenStreetMap)
  •	Affichage sous forme d’icônes personnalisées
  •	Popups informatives avec liens vers l’itinéraire 

📍Localisation de l’utilisateur 
  •	Utilise la Geolocation API du navigateur pour obtenir la position de l’utilisateur
  
🌫️ Qualité de l’air (Indice ATMO)
  •	Appel à l’API Open Data d’Orléans Métropole
  •	Exploitation du dataset om-santepublique-qualiteair-3j
  •	Affichage de l’indice ATMO
  •	Coloration dynamique selon le niveau de qualité
  
♿ Accessibilité PMR
  •	Points d’accès affichables dynamiquement
  •	Logique de toggle (affichage / masquage)
  
🅿️ Évolutions prévues
  •	Localisation des parkings publics
  •	Affichage des places PMR
  •	Filtrage par date (J0, J+1, J+2)
  •	Légende interactive ATMO


🛠️ Stack technique

Frontend
  •	HTML5
  •	CSS3
  •	JavaScript 
  •	Leaflet (cartographie interactive)
  •	Font Awesome (icônes)
  
Backend
  •	PHP (traitement API & transformation JSON)
  •	Appels API via cURL / file_get_contents
  •	Formatage des réponses pour consommation frontend
  
APIs utilisées
  •	Open Data Orléans Métropole
  •	Overpass API (OpenStreetMap)
  
Le backend PHP agit comme proxy :
  •	sécurisation
  •	normalisation des données
  •	adaptation au format cartographique

📈 Compétences mobilisées
  •	Intégration d’API REST
  •	Manipulation de JSON
  •	Traitement backend PHP
  •	Cartographie interactive
  •	Structuration d’un projet web
  •	UX orientée accessibilité
  •	Gestion d’erreurs API

🎓 Objectif pédagogique

Ce projet m’a permis de :
  •	Comprendre l’architecture client / serveur
  •	Travailler avec des données publiques réelles
  •	Manipuler des flux API externes
  •	Structurer un projet full-stack simple
  •	Concevoir une interface orientée utilité publique

🔮 Perspectives d’amélioration
  •	Mise d’un cronjob pour mettre à jour les données
  •	Refactorisation en architecture MVC
  •	Déploiement sur serveur distant
  •	Responsive mobile avancé


