<?php
header('Content-Type: application/json');
require 'db_config.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
    try {
        $stmt_current_round = $pdo->query("SELECT current_round_id FROM game_status ORDER BY status_id DESC LIMIT 1");
        $round_data = $stmt_current_round->fetch();

        if (!$round_data) {
            $response['message'] = 'Gagal mendapatkan status ronde saat ini.';
            echo json_encode($response);
            exit;
        }
        
        $new_round_id = $round_data['current_round_id'] + 1;
        $stmt_update = $pdo->prepare("UPDATE game_status SET current_round_id = ? ORDER BY status_id DESC LIMIT 1");

        if ($stmt_update->execute([$new_round_id])) {
            $response['success'] = true;
            $response['new_round_id'] = $new_round_id;
            $response['message'] = 'Ronde berhasil direset. Ronde baru: ' . $new_round_id;
        } else {
            $response['message'] = 'Gagal memperbarui status ronde.';
        }

    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Metode request tidak valid.';
}

echo json_encode($response);
?>
