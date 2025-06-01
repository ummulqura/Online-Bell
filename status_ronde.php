<?php
header('Content-Type: application/json');
require 'db_config.php';

$response = [
    'success' => false,
    'round_id' => null,
    'presses' => [],
    'message' => ''
];

try {
    $stmt_round = $pdo->query("SELECT current_round_id FROM game_status ORDER BY status_id DESC LIMIT 1");
    $current_round_data = $stmt_round->fetch();

    if (!$current_round_data) {
        $response['message'] = 'Status ronde tidak ditemukan.';
        echo json_encode($response);
        exit;
    }
    $current_round_id = $current_round_data['current_round_id'];
    $response['round_id'] = (int)$current_round_id;
    $stmt_presses = $pdo->prepare("
        SELECT group_name, press_timestamp 
        FROM bell_presses 
        WHERE round_id = ? 
        ORDER BY press_timestamp ASC
    ");
    $stmt_presses->execute([$current_round_id]);
    $presses_data = $stmt_presses->fetchAll();

    $formatted_presses = [];
    if ($presses_data) {
        foreach ($presses_data as $press) {
            $timestamp = new DateTime($press['press_timestamp']);
            $formatted_presses[] = [
                'group_name' => $press['group_name'],
                'press_time_formatted' => $timestamp->format('H:i:s.u') 
            ];
        }
    }

    $response['success'] = true;
    $response['presses'] = $formatted_presses;

} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);
?>
