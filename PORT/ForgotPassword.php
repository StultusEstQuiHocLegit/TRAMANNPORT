<?php

// Check for form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize email input
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);

    // Check if the email is valid
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Prepare the SQL statement to find the user by email
        $stmt = $pdo->prepare("SELECT * FROM ExplorersAndCreators WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        // Check if a user with that email exists
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Generate a random password
            $newPassword = bin2hex(random_bytes(10)); // Generates a random 20-character password

            // Hash the new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Update the password in the database
            $updateStmt = $pdo->prepare("UPDATE ExplorersAndCreators SET password = :password WHERE email = :email");
            $updateStmt->bindParam(':password', $hashedPassword);
            $updateStmt->bindParam(':email', $email);
            $updateStmt->execute();

            // Send an email to the user with the new password
            $subject = "TRAMANN PORT - new password";
            $message = "Hi : )<br><br>Your new password is: $newPassword<br><br>Always at your service,<br>TRAMANN PROJECTS";
            $headers = "From: hi@tramann-projects.com\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

            if (mail($email, $subject, $message, $headers)) {
                $success_message = "A new password has been sent to your email. Please check your inbox (and spam folder) and enter your new password at <a href=\"index.php?content=login.php\">LOGIN</a>.";
            } else {
                $error_message = "There was an error sending the email. Please try again later. If the problem persists, please call us so we can fix it.";
            }
        } else {
            $error_message = "The email couldn't be found, please try again, minding the spelling.";
        }
    } else {
        $error_message = "Please enter a valid email address.";
    }
}
?>

<script>
function submitForm() {
    document.getElementById('forgotPasswordForm').submit(); // Submit the form
}
</script>

<div class="login-container">
    <h1>🧠 FORGOT PASSWORD</h1> <!-- Main heading -->

    <?php if (isset($error_message)): ?>
        <?= htmlspecialchars($error_message) ?> <!-- Display error message -->
        <br><br>
    <?php elseif (isset($success_message)): ?>
        <?= htmlspecialchars($success_message) ?> <!-- Display success message -->
        <br><br>
    <?php endif; ?>

    <form id="forgotPasswordForm" action="" method="post"> <!-- Form -->
        <input type="email" id="email" name="email" placeholder="email" style="width: 300px;" required> <!-- Email input -->
        <br><br>
        <a href="javascript:void(0);" class="mainbutton" onclick="submitForm()">✉️ GET NEW PASSWORD</a>
    </form>

    <div class="links"> <!-- Links for creating account and forgot password -->
        <br><br><br><br><br><a href="index.php?content=login.php">🗝️ BACK TO LOGIN</a>
    </div>
</div>

