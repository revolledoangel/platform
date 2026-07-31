<?php
$conn = new mysqli('srv1013.hstgr.io', 'u961992735_plataforma', 'Peru+*963.', 'u961992735_plataforma', 3306);
if ($conn->connect_error) die('Error: ' . $conn->connect_error);

// Create channel_platform table
$conn->query("CREATE TABLE IF NOT EXISTS channel_platform (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    channel_id BIGINT UNSIGNED NOT NULL,
    platform_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_channel_platform (channel_id, platform_id)
)");
echo "Table created.\n";

// Mapping: platform_id => [channel_ids]
// Platform 11=Meta, 12=TikTok, 13=Google, 14=LinkedIn, 15=YouTube, 16=Programática, 17=SEO, 18=Spotify, 19=Bing, 20=CRO
// Channel: 1=TikTok Ads, 2=Meta Ads, 3=LinkedIn Ads, 4=X Ads, 5=Pinterest Ads, 6=YouTube Ads, 7=Google Search, 8=Microsoft Ads/Bing, 9=Google PMax, 10=Programática, 11=CTV, 12=SEO, 13=Email, 14=Social Orgánica, 15=Content, 16=WhatsApp/SMS, 17=Influencers, 18=Afiliados, 19=Google Display
$mapping = [
    11 => [2],          // Meta -> Meta Ads
    12 => [1],          // TikTok -> TikTok Ads
    13 => [7, 9, 19],   // Google -> Search, PMax, Display
    14 => [3],          // LinkedIn -> LinkedIn Ads
    15 => [6],          // YouTube -> YouTube Ads
    16 => [10, 11],     // Programática -> Progr. Display, CTV
    17 => [12, 15],     // SEO -> SEO, Marketing de Contenidos
    18 => [10],         // Spotify -> Programática
    19 => [8],          // Bing -> Microsoft Ads
    20 => [],           // CRO -> (none = show all)
];

$inserted = 0;
foreach ($mapping as $platform_id => $channels) {
    foreach ($channels as $channel_id) {
        $conn->query("INSERT IGNORE INTO channel_platform (channel_id, platform_id) VALUES ($channel_id, $platform_id)");
        $inserted += $conn->affected_rows;
    }
}
echo "Inserted $inserted rows.\n";
$conn->close();
echo "Done.\n";
