<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
$action = $_GET['action'] ?? '';
$tab = $_GET['tab'] ?? 'faucet';

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $action = $_POST['action'];
    $email = $_POST['email'] ?? '';
    $amount = $_POST['amount'] ?? 0;
    $currency = $_POST['currency'] ?? 'BTC';
    $tickets = $_POST['tickets'] ?? 0;
    $taskId = $_POST['taskId'] ?? 0;

    switch ($action) {
        case 'login':
            $result = loginUser($email);
            if ($result['success']) {
                $_SESSION['user'] = ['email' => $email, 'balance' => $result['balance'], 'referrals' => $result['referrals']];
            }
            echo json_encode($result);
            exit;
        case 'register':
            $result = registerUser($email);
            if ($result['success']) {
                $_SESSION['user'] = ['email' => $email, 'balance' => $result['balance'], 'referrals' => $result['referrals']];
            }
            echo json_encode($result);
            exit;
        case 'claim':
            $result = claimFaucet($email, $amount, $currency);
            if ($result['success']) {
                $_SESSION['user']['balance'] = $result['newBalance'];
            }
            echo json_encode($result);
            exit;
        case 'spin':
            $result = spinToEarn($email);
            if ($result['success']) {
                $_SESSION['user']['balance'] = $result['newBalance'];
            }
            echo json_encode($result);
            exit;
        case 'lottery':
            $result = buyLotteryTickets($email, $tickets);
            if ($result['success']) {
                $_SESSION['user']['balance'] = $result['newBalance'];
            }
            echo json_encode($result);
            exit;
        case 'roll':
            $result = rollToEarn($email);
            if ($result['success']) {
                $_SESSION['user']['balance'] = $result['newBalance'];
            }
            echo json_encode($result);
            exit;
        case 'task':
            $result = completeTask($email, $taskId);
            if ($result['success']) {
                $_SESSION['user']['balance'] = $result['newBalance'];
            }
            echo json_encode($result);
            exit;
        case 'withdraw':
            $result = withdraw($email, $amount, $currency);
            if ($result['success']) {
                $_SESSION['user']['balance'] = $result['newBalance'];
            }
            echo json_encode($result);
            exit;
    }
    exit;
}

// Logout
if ($action === 'logout') {
    session_destroy();
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crypto Faucet</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .spinning { animation: spin 2s linear infinite; }
        @keyframes spin { 100% { transform: rotate(360deg); } }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Navbar -->
    <nav class="bg-blue-600 p-4">
        <div class="max-w-7xl mx-auto flex flex-wrap justify-between items-center">
            <h1 class="text-white text-2xl font-bold">Crypto Faucet</h1>
            <div class="flex space-x-4 flex-wrap">
                <?php $tabs = ['Faucet', 'Spin', 'Lottery', 'Roll', 'Tasks', 'Referrals', 'Withdraw']; ?>
                <?php foreach ($tabs as $t): ?>
                    <a href="?tab=<?= strtolower($t) ?>" class="text-white px-3 py-2 rounded-md <?= $tab === strtolower($t) ? 'bg-blue-800' : '' ?>"><?= $t ?></a>
                <?php endforeach; ?>
                <?php if ($user): ?>
                    <a href="?action=logout" class="text-white px-3 py-2 rounded-md bg-red-600">Logout</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto p-4">
        <?php if (!$user): ?>
            <!-- Auth Section -->
            <div class="max-w-md mx-auto mt-10 p-6 bg-white rounded-lg shadow-lg">
                <h2 class="text-2xl font-bold mb-4">Login / Register</h2>
                <input type="email" id="email" placeholder="FaucetPay Email" class="w-full p-2 mb-4 border rounded">
                <div class="flex space-x-4">
                    <button onclick="auth('login')" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Login</button>
                    <button onclick="auth('register')" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Register</button>
                </div>
            </div>
        <?php else: ?>
            <p class="text-center text-lg">Welcome, <?= htmlspecialchars($user['email']) ?></p>
            <p class="text-center text-lg">Balance: <?= number_format($user['balance'], 8) ?> BTC</p>

            <?php if ($tab === 'faucet'): ?>
                <!-- Faucet Section -->
                <div class="max-w-md mx-auto mt-10 p-6 bg-white rounded-lg shadow-lg">
                    <h2 class="text-2xl font-bold mb-4">Faucet Claim</h2>
                    <select id="currency" class="w-full p-2 mb-4 border rounded">
                        <?php foreach (SUPPORTED_CURRENCIES as $c): ?>
                            <option value="<?= $c ?>"><?= $c ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" id="amount" placeholder="Amount to claim" class="w-full p-2 mb-4 border rounded">
                    <button onclick="claim()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Claim</button>
                </div>
            <?php elseif ($tab === 'spin'): ?>
                <!-- Spin Section -->
                <div class="max-w-md mx-auto mt-10 p-6 bg-white rounded-lg shadow-lg">
                    <h2 class="text-2xl font-bold mb-4">Spin to Earn</h2>
                    <button id="spinBtn" onclick="spin()" class="w-full p-2 rounded bg-blue-600 text-white hover:bg-blue-700">Spin Now</button>
                </div>
            <?php elseif ($tab === 'lottery'): ?>
                <!-- Lottery Section -->
                <div class="max-w-md mx-auto mt-10 p-6 bg-white rounded-lg shadow-lg">
                    <h2 class="text-2xl font-bold mb-4">Lottery</h2>
                    <input type="number" id="tickets" placeholder="Number of tickets" class="w-full p-2 mb-4 border rounded">
                    <button onclick="buyTickets()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Buy Tickets</button>
                </div>
            <?php elseif ($tab === 'roll'): ?>
                <!-- Roll Section -->
                <div class="max-w-md mx-auto mt-10 p-6 bg-white rounded-lg shadow-lg">
                    <h2 class="text-2xl font-bold mb-4">Roll to Earn</h2>
                    <button id="rollBtn" onclick="roll()" class="w-full p-2 rounded bg-blue-600 text-white hover:bg-blue-700">Roll Now</button>
                </div>
            <?php elseif ($tab === 'tasks'): ?>
                <!-- Tasks Section -->
                <div class="max-w-md mx-auto mt-10 p-6 bg-white rounded-lg shadow-lg">
                    <h2 class="text-2xl font-bold mb-4">Tasks</h2>
                    <?php
                    $tasks = [
                        ['id' => 1, 'description' => 'Complete a survey', 'reward' => '0.0001 BTC'],
                        ['id' => 2, 'description' => 'Watch an ad', 'reward' => '0.00005 BTC'],
                    ];
                    ?>
                    <?php foreach ($tasks as $task): ?>
                        <div class="mb-4 p-4 border rounded">
                            <p><?= htmlspecialchars($task['description']) ?></p>
                            <p>Reward: <?= $task['reward'] ?></p>
                            <button onclick="completeTask(<?= $task['id'] ?>)" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 mt-2">Complete</button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php elseif ($tab === 'referrals'): ?>
                <!-- Referrals Section -->
                <div class="max-w-md mx-auto mt-10 p-6 bg-white rounded-lg shadow-lg">
                    <h2 class="text-2xl font-bold mb-4">Referrals (30% Commission)</h2>
                    <p>Invite friends and earn 30% of their earnings!</p>
                    <input type="text" value="<?= SITE_URL . '/?ref=' . urlencode($user['email']) ?>" readonly class="w-full p-2 mb-4 border rounded">
                    <p>Your Referrals: <?= $user['referrals'] ?></p>
                </div>
            <?php elseif ($tab === 'withdraw'): ?>
                <!-- Withdraw Section -->
                <div class="max-w-md mx-auto mt-10 p-6 bg-white rounded-lg shadow-lg">
                    <h2 class="text-2xl font-bold mb-4">Withdraw</h2>
                    <select id="currency" class="w-full p-2 mb-4 border rounded">
                        <?php foreach (SUPPORTED_CURRENCIES as $c): ?>
                            <option value="<?= $c ?>"><?= $c ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" id="amount" placeholder="Amount to withdraw" class="w-full p-2 mb-4 border rounded">
                    <button onclick="withdraw()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Withdraw</button>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- JavaScript -->
    <script>
        function auth(type) {
            const email = $('#email').val();
            if (!email) return alert('Email required');
            $.post('', { action: type, email }, (data) => {
                alert(data.message);
                if (data.success) location.reload();
            });
        }

        function claim() {
            const amount = $('#amount').val();
            const currency = $('#currency').val();
            const email = '<?= $user['email'] ?? '' ?>';
            if (!amount || amount <= 0) return alert('Invalid amount');
            $.post('', { action: 'claim', email, amount, currency }, (data) => {
                alert(data.message);
                if (data.success) location.reload();
            });
        }

        function spin() {
            const btn = $('#spinBtn');
            btn.addClass('spinning').text('Spinning...').prop('disabled', true);
            const email = '<?= $user['email'] ?? '' ?>';
            $.post('', { action: 'spin', email }, (data) => {
                setTimeout(() => {
                    btn.removeClass('spinning').text('Spin Now').prop('disabled', false);
                    alert(data.success ? `You won ${data.reward} ${data.currency}!` : data.message);
                    if (data.success) location.reload();
                }, 2000);
            });
        }

        function buyTickets() {
            const tickets = $('#tickets').val();
            const email = '<?= $user['email'] ?? '' ?>';
            if (!tickets || tickets <= 0) return alert('Invalid number of tickets');
            $.post('', { action: 'lottery', email, tickets }, (data) => {
                alert(data.message);
                if (data.success) location.reload();
            });
        }

        function roll() {
            const btn = $('#rollBtn');
            btn.addClass('spinning').text('Rolling...').prop('disabled', true);
            const email = '<?= $user['email'] ?? '' ?>';
            $.post('', { action: 'roll', email }, (data) => {
                setTimeout(() => {
                    btn.removeClass('spinning').text('Roll Now').prop('disabled', false);
                    alert(data.success ? `You won ${data.reward} ${data.currency}!` : data.message);
                    if (data.success) location.reload();
                }, 2000);
            });
        }

        function completeTask(taskId) {
            const email = '<?= $user['email'] ?? '' ?>';
            $.post('', { action: 'task', email, taskId }, (data) => {
                alert(data.message);
                if (data.success) location.reload();
            });
        }

        function withdraw() {
            const amount = $('#amount').val();
            const currency = $('#currency').val();
            const email = '<?= $user['email'] ?? '' ?>';
            if (!amount || amount <= 0) return alert('Invalid amount');
            $.post('', { action: 'withdraw', email, amount, currency }, (data) => {
                alert(data.message);
                if (data.success) location.reload();
            });
        }
    </script>
</body>
</html>
