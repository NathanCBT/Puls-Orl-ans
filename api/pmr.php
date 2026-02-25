<?php
// fournit les données des chemins PMR
// lit les données depuis un fichier de cache local pour éviter des appels à l'API à chaque chargement

header('Content-Type: application/json'); 

 $cacheFile = __DIR__ . '/../cache/pmr_data.json'; 


if (!file_exists($cacheFile)) {
    http_response_code(503);
    echo json_encode(["error" => "Données PMR non disponibles. Veuillez réessayer plus tard."]);
    exit;
}

 $cachedData = file_get_contents($cacheFile);

// si la lecture échoue, on envoie un code d'erreur HTTP 500 (Internal Server Error)
if ($cachedData === false) {    
    http_response_code(500);
    echo json_encode(["error" => "Impossible de lire les données PMR en cache"]);
    exit; 
}


echo $cachedData;
?>