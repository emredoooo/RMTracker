<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
// Optionally load user data into variables for easy access
$user_id = $_SESSION['user_id'];
$user_nik = $_SESSION['nik'];
$user_nama = $_SESSION['nama'];
$user_role = $_SESSION['role'];
$user_unit = $_SESSION['unit_asal'];
?>
