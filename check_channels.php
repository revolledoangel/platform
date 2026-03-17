<?php
$conn = new mysqli('srv1013.hstgr.io', 'u961992735_plataforma', 'Peru+*963.', 'u961992735_plataforma', 3306);
if ($conn->connect_error) die('Error: ' . $conn->connect_error);

// Tables with "channel" in name
$r = $conn->query('SHOW TABLES LIKE "%channel%"');
echo "=== Tables with 'channel' ===\n";
while ($row = $r->fetch_row()) echo $row[0] . "\n";

// channels table structure
$r2 = $conn->query('DESCRIBE channels');
echo "\n=== channels table ===\n";
if ($r2) while ($row = $r2->fetch_assoc()) echo json_encode($row) . "\n";

// sample channels
$r3 = $conn->query('SELECT * FROM channels LIMIT 10');
echo "\n=== Sample channels ===\n";
if ($r3) while ($row = $r3->fetch_assoc()) echo json_encode($row) . "\n";

$conn->close();
