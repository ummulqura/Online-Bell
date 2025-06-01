<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bel Cerdas Cermat Online</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Bel Cerdas Cermat Online</h2>

        <div id="groupSetup">
            <h3>Masukkan Nama Kelompok Anda:</h3>
            <input type="text" id="groupNameInput" placeholder="Contoh: Tim Matematika">
            <button id="setGroupButton" class="control-buttons">Setel Nama & Siap</button>
        </div>

        <div id="gameArea" style="display: none;">
            <h3 id="currentGroupName">Kelompok: </h3>
            <button id="bellButton">TEKAN BEL!</button>
            <p id="messageArea" style="color: green; font-weight: bold;"></p>
        </div>

        <div id="statusArea">
            <h3>Urutan Penekan Bel (Ronde Ini):</h3>
            <ol id="orderList"></ol>
            <p id="timerMessage" style="font-style: italic;"></p>
        </div>
        
        <div class="control-buttons">
            <button id="resetButton">Reset Ronde (Juri)</button>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            let groupName = '';
            let pollingInterval;
            const POLLING_RATE_MS = 2000; 

            // --- Setup Kelompok ---
            $('#setGroupButton').on('click', function() {
                let inputName = $('#groupNameInput').val().trim();
                if (inputName === '') {
                    alert('Nama kelompok tidak boleh kosong!');
                    return;
                }
                groupName = inputName;
                $('#currentGroupName').text('Kelompok: ' + groupName);
                $('#groupSetup').hide();
                $('#gameArea').show();
                $('#bellButton').prop('disabled', false); 
                $('#messageArea').text('');
                startPolling(); 
            });

            // --- Tekan Bel ---
            $('#bellButton').on('click', function() {
                if (!groupName) {
                    alert('Silakan setel nama kelompok Anda terlebih dahulu.');
                    return;
                }
                $(this).prop('disabled', true); 
                $('#messageArea').text('Bel ditekan! Menunggu konfirmasi...');

                $.ajax({
                    url: 'tekan_bel.php',
                    type: 'POST',
                    data: { group_name: groupName },
                    dataType: 'json', 
                    success: function(response) {
                        if(response.success) {
                            $('#messageArea').text(response.message || 'Bel Anda telah dicatat!');
                        } else {
                            $('#messageArea').text('Error: ' + (response.message || 'Gagal mencatat bel.'));
                            $('#bellButton').prop('disabled', false); 
                        }
                        fetchStatus(); 
                    },
                    error: function(xhr, status, error) {
                        $('#messageArea').text('Terjadi kesalahan AJAX: ' + error);
                        $('#bellButton').prop('disabled', false); 
                    }
                });
            });

            // --- Polling Status Ronde ---
            function fetchStatus() {
                $.ajax({
                    url: 'status_ronde.php',
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        $('#orderList').empty(); 
                        if (response.presses && response.presses.length > 0) {
                            response.presses.forEach(function(press, index) {
                                $('#orderList').append('<li>' + (index + 1) + '. ' + press.group_name + ' (pada ' + press.press_time_formatted + ')</li>');
                            });
                        } else {
                            $('#orderList').append('<li>Belum ada yang menekan bel.</li>');
                        }
                        
                        let currentUserPressed = false;
                        if (groupName && response.presses) {
                            currentUserPressed = response.presses.some(p => p.group_name === groupName);
                        }
                        $('#bellButton').prop('disabled', currentUserPressed); 

                        if (response.round_id) {
                        }

                        $('#timerMessage').text('Update terakhir: ' + new Date().toLocaleTimeString());
                    },
                    error: function() {
                        $('#timerMessage').text('Gagal mengambil status.');
                    }
                });
            }

            function startPolling() {
                if (pollingInterval) clearInterval(pollingInterval); 
                fetchStatus(); 
                pollingInterval = setInterval(fetchStatus, POLLING_RATE_MS);
            }

            // --- Reset Ronde ---
            $('#resetButton').on('click', function() {
                if (!confirm('Anda yakin ingin mereset ronde untuk semua peserta?')) {
                    return;
                }
                $.ajax({
                    url: 'reset_ronde.php',
                    type: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        if(response.success) {
                            alert(response.message || 'Ronde berhasil direset!');
                            $('#orderList').empty().append('<li>Belum ada yang menekan bel.</li>');
                            $('#bellButton').prop('disabled', false);
                            $('#messageArea').text('');
                            fetchStatus(); 
                        } else {
                            alert('Error: ' + (response.message || 'Gagal mereset ronde.'));
                        }
                    },
                    error: function() {
                        alert('Terjadi kesalahan AJAX saat mereset.');
                    }
                });
            });
        });
    </script>
</body>
</html>
