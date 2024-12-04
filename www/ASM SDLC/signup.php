<?php
include('dbconnect.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role']; // Get role value from the form

    // Check if the password confirmation matches
    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Check if the email already exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        if ($stmt->rowCount() > 0) {
            $error = "Email is already registered.";
        } else {
            // Insert the new user into the database
            $stmt = $conn->prepare("
                INSERT INTO users (username, email, phone, password, role) 
                VALUES (:username, :email, :phone, :password, :role)
            ");
            $stmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':phone' => $phone,
                ':password' => $hashed_password,
                ':role' => $role
            ]);

            // Log the user in automatically
            $_SESSION['user_id'] = $conn->lastInsertId();
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;

            // Redirect based on role
            if ($role === 'admin') {
                header('Location: admin.php');
            } elseif ($role === 'customer') {
                header('Location: buy.php');
            }
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="signup.css">
</head>
<body>
    <div class="signup-container">
        <h2>Sign Up</h2>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="POST" action="signup.php">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" placeholder="Enter your username" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" placeholder="Enter your email" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone Number:</label>
                <input type="text" name="phone" id="phone" placeholder="Enter your phone number">
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" placeholder="Enter your password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm your password" required>
            </div>
            <div class="form-group">
                <label for="role">Select Role:</label>
                <select name="role" id="role" required>
                    <option value="customer">Customer</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <button type="submit" class="btn">Sign Up</button>
        </form>
    </div>
</body>
</html>
