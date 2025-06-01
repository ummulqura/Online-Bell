<?php
header('Content-Type: application/json'); 
require 'db_config.php'; 

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $group_name = isset($_POST['group_name']) ? trim($_POST['group_name']) : '';

    if (empty($group_name)) {
        $response['message'] = 'Nama kelompok tidak boleh kosong.';
        echo json_encode($response);
        exit;
    }

    try {
        $stmt_round = $pdo->query("SELECT current_round_id FROM game_status ORDER BY status_id DESC LIMIT 1");
        $current_round = $stmt_round->fetch();
        
        if (!$current_round) {
            $response['message'] = 'Error: Status ronde tidak ditemukan.';
            echo json_encode($response);
            exit;
        }
        $round_id = $current_round['current_round_id'];
        $stmt_check = $pdo->prepare("SELECT COUNT(*) as count FROM bell_presses WHERE round_id = ? AND group_name = ?");
        $stmt_check->execute([$round_id, $group_name]);
        $existing_press = $stmt_check->fetch();

        if ($existing_press && $existing_press['count'] > 0) {
            $response['success'] = true; 
            $response['message'] = 'Anda sudah menekan bel di ronde ini.';
            echo json_encode($response);
            exit;
        }

        $stmt_insert = $pdo->prepare("INSERT INTO bell_presses (round_id, group_name) VALUES (?, ?)");
        if ($stmt_insert->execute([$round_id, $group_name])) {
            $response['success'] = true;
            $response['message'] = 'Bel berhasil dicatat!';
        } else {
            $response['message'] = 'Gagal menyimpan data penekanan bel.';
        }
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage(); 
    }
} else {
    $response['message'] = 'Metode request tidak valid.';
}

echo json_encode($response);
?>
