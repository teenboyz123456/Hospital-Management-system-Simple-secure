<?php
$conn = mysqli_connect("localhost", "root", "", "hospital_management_system");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>