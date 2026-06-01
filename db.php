<?php
$con = mysqli_connect('MySQL-8.4', 'root', '', 'konferencia');
if (!$con)
    die('������ ����������� � ���� ������: ' . mysqli_connect_error());
mysqli_set_charset($con, 'utf8mb4');
?>