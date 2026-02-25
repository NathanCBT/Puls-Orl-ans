<?php
 $cacheDir = __DIR__ . '/cache/';
 $cacheFile = $cacheDir . 'defibrillator_data.json';

 $query = "[out:json][timeout:25];area[\"name\"=\"Orléans\"]->.a;(node[\"emergency\"=\"defibrillator\"](area.a););out body;";


 $encodedQuery = urlencode($query);
 $overpassURL = "https://overpass-api.de/api/interpreter?data=" . $encodedQuery;

 $ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $overpassURL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
curl_setopt($ch, CURLOPT_USERAGENT, 'OrleansSafePlace/1.0');
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); 
curl_setopt($ch, CURLOPT_TIMEOUT, 30); 
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

 $response = curl_exec($ch);
 $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
 $curl_error = curl_error($ch);

curl_close($ch);

if ($curl_error) {
    $errorMessage = "Erreur cURL : " . $curl_error . " (Code HTTP: " . $httpcode . ")";
    error_log($errorMessage);
    file_put_contents($cacheFile, json_encode(["error" => $errorMessage])); 
    exit;
}

if ($httpcode != 200) {
    $errorMessage = "Erreur HTTP : Le serveur a répondu avec le code " . $httpcode;
    error_log($errorMessage);
    file_put_contents($cacheFile, json_encode(["error" => $errorMessage]));
    exit;
}


 $data = json_decode($response, true);

if (!isset($data['elements'])) {
    error_log("Format inattendu des données de défibrillateurs.");
    file_put_contents($cacheFile, json_encode(["error" => "Format de réponse de l'API inattendu."]));
    exit;
}

 $results = [];
foreach ($data['elements'] as $element) {
    if (isset($element['lat']) && isset($element['lon'])) {
        $results[] = [
            "lat" => $element['lat'],
            "lng" => $element['lon'],
            "tags" => $element['tags'] ?? []
        ];
    }
}

// sauvegarde des données traitées dans le fichier de cache
file_put_contents($cacheFile, json_encode($results));
?>