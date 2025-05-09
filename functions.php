<?php
require_once 'config.php';

// Database Connection
function getDbConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

// Verify FaucetPay Email
function verifyFaucetPayEmail($email, $currency = 'BTC') {
    $url = FAUCETPAY_API_URL . '/checkaddress';
    $data = [
        'api_key' => FAUCETPAY_API_KEY,
        'address' => $email,
        'currency' => $currency
    ];
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($response, true);
    return isset($result['status']) && $result['status'] == 200;
}

// Register User
function registerUser($email) {
    if (!verifyFaucetPayEmail($email)) {
        return ['success' => false, 'message' => 'Invalid FaucetPay email'];
    }
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $stmt->close();
        $conn->close();
        return ['success' => false, 'message' => 'Email already registered'];
    }
    $stmt = $conn->prepare("INSERT INTO users (email, balance, referrals) VALUES (?, 0, 0)");
    $stmt->bind_param("s", $email);
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $success ? ['success' => true, 'balance' => 0, 'referrals' => 0] : ['success' => false, 'message' => 'Registration failed'];
}

// Login User
function loginUser($email) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $stmt->close();
        $conn->close();
        return ['success' => true, 'balance' => $row['balance'], 'referrals' => $row['referrals']];
    }
    $stmt->close();
    $conn->close();
    return ['success' => false, 'message' => 'User not found'];
}

// Claim Faucet
function claimFaucet($email, $amount, $currency) {
    if (!in_array($currency, SUPPORTED_CURRENCIES) || $amount <= 0) {
        return ['success' => false, 'message' => 'Invalid currency or amount'];
    }
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT balance FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $newBalance = $row['balance'] + $amount;
        $stmt = $conn->prepare("UPDATE users SET balance = ? WHERE email = ?");
        $stmt->bind_param("ds", $newBalance, $email);
        $success = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $success ? ['success' => true, 'message' => 'Claim successful', 'newBalance' => $newBalance] : ['success' => false, 'message' => 'Claim failed'];
    }
    $stmt->close();
    $conn->close();
    return ['success' => false, 'message' => 'User not found'];
}

// Spin to Earn
function spinToEarn($email) {
    $rewards = [0.00001, 0.00005, 0.0001];
    $reward = $rewards[array_rand($rewards)];
    $currency = 'BTC';
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT balance FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $newBalance = $row['balance'] + $reward;
        $stmt = $conn->prepare("UPDATE users SET balance = ? WHERE email = ?");
        $stmt->bind_param("ds", $newBalance, $email);
        $success = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $success ? ['success' => true, 'reward' => $reward, 'currency' => $currency, 'newBalance' => $newBalance] : ['success' => false, 'message' => 'Spin failed'];
    }
    $stmt->close();
    $conn->close();
    return ['success' => false, 'message' => 'User not found'];
}

// Lottery
function buyLotteryTickets($email, $tickets) {
    $ticketPrice = 0.0001; // BTC per ticket
    $cost = $tickets * $ticketPrice;
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT balance FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        if ($row['balance'] < $cost) {
            $stmt->close();
            $conn->close();
            return ['success' => false, 'message' => 'Insufficient balance'];
        }
        $newBalance = $row['balance'] - $cost;
        $stmt = $conn->prepare("UPDATE users SET balance = ? WHERE email = ?");
        $stmt->bind_param("ds", $newBalance, $email);
        $success = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $success ? ['success' => true, 'message' => 'Tickets purchased', 'newBalance' => $newBalance] : ['success' => false, 'message' => 'Purchase failed'];
    }
    $stmt->close();
    $conn->close();
    return ['success' => false, 'message' => 'User not found'];
}

// Roll to Earn
function rollToEarn($email) {
    $rewards = [0.00002, 0.00006, 0.00012];
    $reward = $rewards[array_rand($rewards)];
    $currency = 'BTC';
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT balance FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $newBalance = $row['balance'] + $reward;
        $stmt = $conn->prepare("UPDATE users SET balance = ? WHERE email = ?");
        $stmt->bind_param("ds", $newBalance, $email);
        $success = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $success ? ['success' => true, 'reward' => $reward, 'currency' => $currency, 'newBalance' => $newBalance] : ['success' => false, 'message' => 'Roll failed'];
    }
    $stmt->close();
    $conn->close();
    return ['success' => false, 'message' => 'User not found'];
}

// Complete Task
function completeTask($email, $taskId) {
    $rewards = [1 => 0.0001, 2 => 0.00005]; // Task ID to reward mapping
    if (!isset($rewards[$taskId])) {
        return ['success' => false, 'message' => 'Invalid task'];
    }
    $reward = $rewards[$taskId];
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT balance FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $newBalance = $row['balance'] + $reward;
        $stmt = $conn->prepare("UPDATE users SET balance = ? WHERE email = ?");
        $stmt->bind_param("ds", $newBalance, $email);
        $success = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $success ? ['success' => true, 'message' => 'Task completed', 'newBalance' => $newBalance] : ['success' => false, 'message' => 'Task failed'];
    }
    $stmt->close();
    $conn->close();
    return ['success' => false, 'message' => 'User not found'];
}

// Withdraw
function withdraw($email, $amount, $currency) {
    if (!in_array($currency, SUPPORTED_CURRENCIES) || $amount <= 0) {
        return ['success' => false, 'message' => 'Invalid currency or amount'];
    }
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT balance FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        if ($row['balance'] < $amount) {
            $stmt->close();
            $conn->close();
            return ['success' => false, 'message' => 'Insufficient balance'];
        }
        // FaucetPay Withdrawal
        $url = FAUCETPAY_API_URL . '/send';
        $data = [
            'api_key' => FAUCETPAY_API_KEY,
            'amount' => $amount,
            'to' => $email,
            'currency' => $currency
        ];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($response, true);
        if (isset($result['status']) && $result['status'] == 200) {
            $newBalance = $row['balance'] - $amount;
            $stmt = $conn->prepare("UPDATE users SET balance = ? WHERE email = ?");
            $stmt->bind_param("ds", $newBalance, $email);
            $success = $stmt->execute();
            $stmt->close();
            $conn->close();
            return $success ? ['success' => true, 'message' => 'Withdrawal successful', 'newBalance' => $newBalance] : ['success' => false, 'message' => 'Withdrawal failed'];
        }
        $stmt->close();
        $conn->close();
        return ['success' => false, 'message' => 'FaucetPay withdrawal failed'];
    }
    $stmt->close();
    $conn->close();
    return ['success' => false, 'message' => 'User not found'];
}
?>
