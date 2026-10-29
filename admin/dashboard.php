<?php
session_start();

if (!isset($_SESSION['id_user']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    header('Location: ../login.php');
    exit;
}

require_once '../includes/koneksi.php';
$nama = $_SESSION['nama_user'] ?? 'Admin';
$page_title = "Dashboard";
$active_page = "dashboard";

include 'includes/header.php';
include 'includes/sidebar.php';
?>