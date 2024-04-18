<?php
// Paramètres de connexion à la base de données
$servername = "ly52647-001.eu.clouddb.ovh.net";
$username = "ffnpronet2015";
$password = "PN3PW6vZIs1vM9zA";
$dbname = "ffntest";

// Connexion à la base de données
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

// Requête pour récupérer toutes les adresses
$query = "SELECT adresse FROM Users";
$result = $conn->query($query);

// Tableau pour stocker les adresses normalisées
$normalized_addresses = array();

// Fonction pour remplacer les accents et caractères spéciaux
function normalizeString($str) {
    return mb_strtoupper(iconv('UTF-8', 'ASCII//TRANSLIT', $str));
}

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
