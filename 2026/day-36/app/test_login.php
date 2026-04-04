<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

echo "<h2>Login System Test</h2>";
echo "<pre>";

// Test 1: Database Connection
try {
    require_once 'config/database.php';
    $db = new Database();
    $conn = $db->getConnection();
    echo "✅ Database connection successful\n";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit;
}

// Test 2: Verify Admin User
try {
    $stmt = $conn->prepare("SELECT user_id, name, email, role, password FROM users WHERE email = ?");
    $stmt->execute(['admin@example.com']);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "✅ Admin user found\n";
        echo "   Email: " . $user['email'] . "\n";
        echo "   Name: " . $user['name'] . "\n";
        echo "   Role: " . $user['role'] . "\n";
        echo "   Password hash length: " . strlen($user['password']) . "\n";
        
        // Test password verification
        if (password_verify('admin123', $user['password'])) {
            echo "✅ Password verification successful\n";
        } else {
            echo "❌ Password verification failed\n";
        }
    } else {
        echo "❌ Admin user not found\n";
    }
} catch (Exception $e) {
    echo "❌ User verification failed: " . $e->getMessage() . "\n";
}

// Test 3: Test Login API
$postData = http_build_query([
    'email' => 'admin@example.com',
    'password' => 'admin123'
]);

$opts = [
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/x-www-form-urlencoded\r\n" .
                   "Cookie: " . session_name() . "=" . session_id() . "\r\n",
        'content' => $postData
    ]
];

$context = stream_context_create($opts);
$response = file_get_contents('http://localhost/Abc/api/login.php', false, $context);

echo "\nAPI Test Results:\n";
echo "POST Data: " . $postData . "\n";
echo "Response: " . $response . "\n";

// Test 4: Verify Session After Login
echo "\nSession Test:\n";
if (isset($_SESSION['user_id'])) {
    echo "✅ Session created successfully\n";
    echo "   User ID: " . $_SESSION['user_id'] . "\n";
    echo "   Name: " . $_SESSION['user_name'] . "\n";
    echo "   Role: " . $_SESSION['user_role'] . "\n";
} else {
    echo "❌ Session not created\n";
}

echo "</pre>";

// Add buttons for testing
echo '<div style="margin: 20px 0;">';
echo '<a href="login.php" style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; margin-right: 10px;">Try Login Page</a>';
echo '<a href="reset_admin.php" style="display: inline-block; padding: 10px 20px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px;">Reset Admin Account</a>';
echo '</div>';

// Add debugging information
echo "<h3>Current Session Data:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h3>Cookie Information:</h3>";
echo "<pre>";
print_r($_COOKIE);
echo "</pre>";
?>
