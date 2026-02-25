<?php
//met la date de la dernière mise à jour des données en cache 

header('Content-Type: application/json'); 


 $cacheDir = __DIR__ . '/../cache/';
 $pollutionCacheFile = $cacheDir . 'pollution_data.json';
 $defibrillatorCacheFile = $cacheDir . 'defibrillator_data.json';
 $pmrCacheFile = $cacheDir . 'pmr_data.json'; 

 $lastUpdate = 0; 

if (file_exists($pollutionCacheFile)) {
    $pollutionTime = filemtime($pollutionCacheFile); 
    if ($pollutionTime > $lastUpdate) {
        $lastUpdate = $pollutionTime; 
    }
}


if (file_exists($defibrillatorCacheFile)) {
    $defibrillatorTime = filemtime($defibrillatorCacheFile);
    if ($defibrillatorTime > $lastUpdate) {
        $lastUpdate = $defibrillatorTime;
    }
}

if (file_exists($pmrCacheFile)) {
    $pmrTime = filemtime($pmrCacheFile);
    if ($pmrTime > $lastUpdate) {
        $lastUpdate = $pmrTime;
    }
}

echo json_encode(['last_update' => $lastUpdate]);