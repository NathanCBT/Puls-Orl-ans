<?php

 $cacheDir = __DIR__ . '/cache/';
 $cacheFile = $cacheDir . 'pmr_data.json';


 $query = '
    [out:json][timeout:60];
    area[name="Orléans"]->.searchArea;
    (
      way[highway~"footway|path"][surface~"paved|asphalt|concrete"](area.searchArea);
    );
    (._;>;); // Cette ligne magique récupère tous les nœuds (nodes) des chemins trouvés
    out body;
';

 $encodedQuery = urlencode($query);
 $overpassURL = "https://overpass-api.de/api/interpreter?data=" . $encodedQuery;

 $ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $overpassURL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_USERAGENT, 'OrleansSafePlace/1.0');
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
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
    $errorMessage = "Erreur HTTP : Le serveur Overpass a répondu avec le code " . $httpcode . ".";
    error_log($errorMessage);
    file_put_contents($cacheFile, json_encode(["error" => $errorMessage]));
    exit;
}

json_decode($response);
if (json_last_error() !== JSON_ERROR_NONE) {
    $errorMessage = "Erreur de décodage JSON : la réponse d'Overpass n'était pas du JSON valide.";
    error_log($errorMessage);
    error_log("Réponse brute d'Overpass : " . $response);
    file_put_contents($cacheFile, json_encode(["error" => $errorMessage]));
    exit;
}

 $data = json_decode($response, true);
if (!isset($data['elements'])) {
    error_log("Format inattendu des données PMR.");
    file_put_contents($cacheFile, json_encode(["error" => "Format de réponse de l'API PMR inattendu."]));
    exit;
}

 $results = [];
 $nodes = [];

foreach ($data['elements'] as $element) {
    if ($element['type'] === 'node') {
        $nodes[$element['id']] = ['lat' => $element['lat'], 'lon' => $element['lon']];
    }
}

foreach ($data['elements'] as $element) {
    if ($element['type'] === 'way') {
        $coordinates = [];
        foreach ($element['nodes'] as $nodeId) {
            if (isset($nodes[$nodeId])) {
                $coordinates[] = [$nodes[$nodeId]['lat'], $nodes[$nodeId]['lon']];
            }
        }
        if (count($coordinates) > 1) {
            $results[] = [
                "coords" => $coordinates,
                "tags" => $element['tags'] ?? []
            ];
        }
    }
}

file_put_contents($cacheFile, json_encode($results));
?>