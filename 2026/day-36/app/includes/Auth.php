<?php

class Auth {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function register($username, $email, $password) {
        try {
            $conn = $this->db->getConnection();

            // Check if email already exists
            $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $count = $stmt->fetchColumn();

            if ($count > 0) {
                return "Email already exists!";
            }

            // Validate inputs
            if (empty($username) || empty($email) || empty($password)) {
                return "Please fill in all fields!";
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return "Invalid email format!";
            }

            if (strlen($password) < 1) {
                return "Password cannot be empty!";
            }

            // Hash password and insert user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
            if ($stmt->execute([$username, $email, $hashed_password])) {
                return true;
            } else {
                return "Registration failed. Please try again.";
            }
        } catch (PDOException $e) {
            return "Database error: " . $e->getMessage();
        }
    }

    public function login($email, $password) {
        try {
            $conn = $this->db->getConnection();
            
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                return true;
            }
            
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function logout() {
        session_destroy();
        header('Location: login.php');
        exit();
    }

    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            try {
                $conn = $this->db->getConnection();
                $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                return $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                return null;
            }
        }
        return null;
    }

    public function isAdmin($user_id) {
        $query = "SELECT role FROM users WHERE user_id = ? AND role = 'admin'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id]);
        return $stmt->rowCount() > 0;
    }

    public function createAdmin($name, $email, $password) {
        try {
            // Check if email already exists
            $query = "SELECT user_id FROM users WHERE email = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                return ["success" => false, "message" => "Email already exists"];
            }

            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new admin
            $query = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$name, $email, $hashed_password]);
            
            return ["success" => true, "message" => "Admin account created successfully"];
        } catch(PDOException $e) {
            return ["success" => false, "message" => "Failed to create admin: " . $e->getMessage()];
        }
    }
} 