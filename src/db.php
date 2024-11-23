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
            'username' => 'root',
            'password' => 'mypassword',
            'dbname' => 'comp3335_database'
        ],
        'patient' => [
            'host' => 'percona',
            'username' => 'root',
            'password' => 'mypassword',
            'dbname' => 'comp3335_database'
        ],
        'secretary' => [
            'host' => 'percona',
            'username' => 'root',
            'password' => 'mypassword',
            'dbname' => 'comp3335_database'
        ]
    ];

    return $credentials[$role] ?? $credentials['root'];
}