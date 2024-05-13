<?php

// Chemin du fichier exporté et du fichier de sortie
$file_path = 'export.sql';
$output_file_path = 'inserts.sql';

// Ouvrir le fichier exporté en mode lecture
$file_handle = fopen($file_path, 'r');
if ($file_handle === false) {
    die("Impossible d'ouvrir le fichier exporté.");
}

// Ouvrir un nouveau fichier pour écrire les instructions INSERT INTO
$output_file_handle = fopen($output_file_path, 'w');
if ($output_file_handle === false) {
    die("Impossible de créer le fichier de sortie.");
}

// Parcourir chaque ligne du fichier exporté
while (($line = fgets($file_handle)) !== false) {
    // Ignorer les lignes vides ou les commentaires
    if (trim($line) === '' || strpos($line, '--') === 0) {
        continue;
    }
    // Séparer les valeurs par des tabulations
    $values = explode("\t", trim($line));
    // Échapper les caractères spéciaux dans les valeurs
    $escaped_values = array_map(function($value) {
        return "'" . str_replace("'", "''", $value) . "'";
    }, $values);
    // Générer l'instruction INSERT INTO correspondante
    $insert_statement = "INSERT INTO UsersFromYearsBefore (id, centre_emetteur_id, nom, prenom, genre, n_licence, adresse, zip, ville, pays, telephone, email, is_active, anniversaire, created_at, renouvellement_at, agree_terms, complement, region_id, is_imprimed, impression, imprimed_at, chaine) VALUES (" . implode(', ', $escaped_values) . ");\n";
    // Écrire l'instruction INSERT INTO dans le fichier de sortie
    fwrite($output_file_handle, $insert_statement);
}

// Fermer les fichiers
fclose($file_handle);
fclose($output_file_handle);

echo "Les instructions INSERT INTO ont été générées avec succès et écrites dans le fichier $output_file_path.";

?>
