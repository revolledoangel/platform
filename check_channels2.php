<?php
$conn = new mysqli('srv1013.hstgr.io', 'u961992735_plataforma', 'Peru+*963.', 'u961992735_plataforma', 3306);
if ($conn->connect_error) die('Error: ' . $conn->connect_error);
$r = $conn->query('SELECT id, name FROM channels ORDER BY id');
while ($row = $r->fetch_assoc()) echo $row['id'] . " | " . $row['name'] . "\n";
$r2 = $conn->query('SELECT id, name FROM platforms ORDER BY id');
echo "\n--- Platforms ---\n";
while ($row = $r2->fetch_assoc()) echo $row['id'] . " | " . $row['name'] . "\n";
$conn->close();
