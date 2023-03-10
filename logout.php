<?php
include("header.php");
session_start();
session_unset();
session_destroy();
header("Location: index.php");
?>