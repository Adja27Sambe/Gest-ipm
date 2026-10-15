<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pilote de Sauvegarde Cloud par défaut
    |--------------------------------------------------------------------------
    |
    | Options : 'drive' (Google Drive API), 'vault' (Coffre-fort Cloud Local Mirror), 's3'
    |
    */
    'default' => env('CLOUD_BACKUP_DRIVER', 'drive'),

    /*
    |--------------------------------------------------------------------------
    | Synchronisation automatique
    |--------------------------------------------------------------------------
    |
    | Si activé, chaque document ou média uploadé est immédiatement sauvegardé
    | sur le Cloud Drive.
    |
    */
    'auto_backup' => env('CLOUD_AUTO_BACKUP', true),

    /*
    |--------------------------------------------------------------------------
    | Configuration Google Drive
    |--------------------------------------------------------------------------
    |
    | Paramètres de connexion à l'API Google Drive v3 (OAuth2 ou Service Account)
    |
    */
    'google_drive' => [
        'folder_id'      => env('GOOGLE_DRIVE_FOLDER_ID'),
        'client_id'      => env('GOOGLE_DRIVE_CLIENT_ID'),
        'client_secret'  => env('GOOGLE_DRIVE_CLIENT_SECRET'),
        'refresh_token'  => env('GOOGLE_DRIVE_REFRESH_TOKEN'),
        'service_account'=> env('GOOGLE_DRIVE_SERVICE_ACCOUNT_JSON'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Disque Miroir Cloud Local (Coffre-fort de secours)
    |--------------------------------------------------------------------------
    */
    'vault_disk' => 'cloud',

    /*
    |--------------------------------------------------------------------------
    | Paramètres d'Optimisation des Médias
    |--------------------------------------------------------------------------
    |
    | Compression d'images, redimensionnement intelligent, déduplication
    |
    */
    'optimization' => [
        'deduplication'       => true,
        'compress_images'     => true,
        'max_image_width'     => 1920,
        'max_image_height'    => 1920,
        'image_quality'       => 85,
        'generate_thumbnails' => true,
        'thumbnail_size'      => 320,
    ],
];
