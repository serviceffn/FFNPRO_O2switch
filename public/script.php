<?php
// Paramètres de connexion à la base de données
$servername = "ly52647-001.eu.clouddb.ovh.net";
$username = "ffnpronet2015";
$password = "PN3PW6vZIs1vM9zA";
$dbname = "ffntest";
$port = 35146;

// Connexion à la base de données
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}
// Fonction pour remplacer les caractères spéciaux et les accents
function normalizeString($str) {
    $str = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
    $str = strtr($str, [
        'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e', // Remplacer les é par e, etc.
        // Ajoutez d'autres remplacements pour les autres caractères spéciaux ici si nécessaire
    ]);
    return mb_strtoupper($str); // Convertir en majuscules
}

// Requête pour récupérer toutes les adresses
$query = "SELECT adresse FROM Users";
$result = $conn->query($query);

// Tableau pour stocker les adresses normalisées
$normalized_addresses = array();

// Récupérer les résultats et les normaliser
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $adresse = $row['adresse'];
        if ($adresse !== null) {
            $normalized_address = normalizeString($adresse);
            $normalized_addresses[] = $normalized_address;
        }
    }
}

// Fermer la connexion
$conn->close();

// Afficher les adresses normalisées
foreach ($normalized_addresses as $address) {
    echo $address . "<br>";
}
?>