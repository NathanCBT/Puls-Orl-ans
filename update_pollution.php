<?php
// mise à jour des données de la qualité de l'air
// interroge l'API d'Orléans Métropole et stocke les données par commune en cache

 $cacheDir = __DIR__ . '/cache/';
 $cacheFile = $cacheDir . 'pollution_data.json';

 $url = "https://data.orleans-metropole.fr/api/records/1.0/search/?dataset=om-santepublique-qualiteair-3j";


 $response = @file_get_contents($url); 

if ($response === false) {
    error_log("Impossible de récupérer les données de pollution depuis l'API Orléans Métropole.");
    file_put_contents($cacheFile, json_encode(["error" => "Impossible de récupérer les données"]));
    exit;
}

 $data = json_decode($response, true);

if (!isset($data['records'])) {
    error_log("Format inattendu des données de pollution : la clé 'records' est manquante.");
    file_put_contents($cacheFile, json_encode(["error" => "Format de réponse de l'API inattendu."]));
    exit;
}

 $results = [];
foreach ($data['records'] as $record) {
    $fields = $record['fields'];

    if (isset($fields['geo_point']) && is_array($fields['geo_point']) && count($fields['geo_point']) == 2) {
        $results[] = [
            "commune" => $fields['nom_commune'] ?? "Commune inconnue",
            "indice" => $fields['code_qual'] ?? null,
            "qualite" => $fields['lib_qual'] ?? "inconnue",
            "couleur" => $fields['coul_qual'] ?? '#999', 
            "lat" => $fields['geo_point'][0],
            "lng" => $fields['geo_point'][1],
            "date" => $fields['date_ech'] ?? null
        ];
    }
}

file_put_contents($cacheFile, json_encode($results));
?>