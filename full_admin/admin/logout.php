<?php
session_start();

/* Clear all session data */
$_SESSION = [];

/* Destroy the session */
session_destroy();

/* Remove session cookie (important) */
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 3600,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

/* Redirect to index.php */
header("Location: ../index.php");
exit;
