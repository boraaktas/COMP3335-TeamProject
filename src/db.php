<?php
function getDatabaseCredentials($role) {
    $credentials = [
        'root' => [
            'host' => 'percona',
            'username' => 'root',
            'password' => 'mypassword',
            'dbname' => 'comp3335_database'
        ],
        'lab_staff' => [
            'host' => 'percona',
            'username' => 'lab_staff_user',
            'password' => 'lab_staff_password',
            'dbname' => 'comp3335_database'
        ],
        'patient' => [
            'host' => 'percona',
            'username' => 'patient_user',
            'password' => 'patient_password',
            'dbname' => 'comp3335_database'
        ],
        'secretary' => [
            'host' => 'percona',
            'username' => 'secretary_user',
            'password' => 'secretary_password',
            'dbname' => 'comp3335_database'
        ]
    ];

    return $credentials[$role] ?? $credentials['root'];
}