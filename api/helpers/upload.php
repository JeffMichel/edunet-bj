<?php
// Gestion upload fichiers

function upload_file($file, $category) {
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['success' => false, 'message' => 'Paramètres invalides.'];
    }

    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            return ['success' => false, 'message' => 'Aucun fichier envoyé.'];
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return ['success' => false, 'message' => 'Taille du fichier dépassée.'];
        default:
            return ['success' => false, 'message' => 'Erreur inconnue lors du téléversement.'];
    }

    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'Taille maximale du fichier dépassée (5 Mo).'];
    }

    // Définir les extensions et types mime autorisés
    $allowed_types = [];
    if ($category === 'courses' || $category === 'assignments') {
        $allowed_types = [
            'pdf' => 'application/pdf'
        ];
    } elseif ($category === 'avatars') {
        $allowed_types = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif'
        ];
    } else {
        return ['success' => false, 'message' => 'Catégorie de téléversement invalide.'];
    }

    // Vérifier le type MIME réel du fichier
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime_type = $finfo->file($file['tmp_name']);
    $ext = array_search($mime_type, $allowed_types, true);

    if ($ext === false) {
        return ['success' => false, 'message' => 'Format de fichier non autorisé. PDF uniquement pour les cours/devoirs, JPG/PNG/GIF pour l\'avatar.'];
    }

    // Générer un nom unique
    $filename = sprintf('%s_%s.%s', uniqid($category . '_', true), time(), $ext);
    
    // Déterminer le chemin cible
    $target_dir = UPLOAD_DIR . $category . '/';
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $target_filepath = $target_dir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $target_filepath)) {
        return ['success' => false, 'message' => 'Impossible de sauvegarder le fichier.'];
    }

    // Retourner l'URL relative à enregistrer en BDD
    $relative_url = 'uploads/' . $category . '/' . $filename;
    return [
        'success' => true,
        'url' => $relative_url,
        'filename' => $filename
    ];
}
