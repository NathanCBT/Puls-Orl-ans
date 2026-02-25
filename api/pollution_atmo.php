<?php
header('Content-Type: application/json');

 $cacheFile = __DIR__ . '/../cache/pollution_data.json';

// vérification de l'existence du fichier de cache
if (!file_exists($cacheFile)) {
    http_response_code(503);
    echo json_encode(["error" => "Données non disponibles. Veuillez réessayer plus tard."]);
    exit;
}

// lecture des données depuis le cache
 $cachedData = file_get_contents($cacheFile);

if ($cachedData === false) {
    http_response_code(500);
    echo json_encode(["error" => "Impossible de lire les données en cache"]);
    exit;
}

echo $cachedData;
?>