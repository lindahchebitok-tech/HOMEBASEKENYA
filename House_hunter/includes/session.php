<?php

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1); // Use only with HTTPS
ini_set('session.use_strict_mode', 1);

function secure_session_start() {
    session_start();
    
    if (!isset($_SESSION['last_regeneration'])) {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    } else {
        $interval = 60 * 30; // 30 minutes
        if (time() - $_SESSION['last_regeneration'] >= $interval) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }
}


function is_logged_in() {
    return isset($_SESSION['user_id']);
}


function is_landlord() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'landlord';
}

function is_tenant() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'tenant';
}

function is_admin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}
?>