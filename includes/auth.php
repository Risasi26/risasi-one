<?php
// Start session for login and page protection
session_start();

// Redirect to login if user is not authenticated
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Helper to display flash messages stored in session
function flash($name = '', $message = '', $class = 'success') {
    if (!empty($name)) {
        if (!empty($message)) {
            $_SESSION[$name] = $message;
            $_SESSION[$name . '_class'] = $class;
        } elseif (!empty($_SESSION[$name])) {
            echo '<div class="alert ' . htmlspecialchars($_SESSION[$name . '_class']) . '">'
                . htmlspecialchars($_SESSION[$name]) . '</div>';
            unset($_SESSION[$name]);
            unset($_SESSION[$name . '_class']);
        }
    }
}

// Escape output for HTML
function e($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
