<?php

// Check for form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include ("../config.php");



    // Retrieve the email and password from POST data
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Check if email and password are not empty
    if (!empty($email) && !empty($password)) {
        // Prepare SQL statement to fetch the user by email
        $stmt = $pdo->prepare("SELECT * FROM ExplorersAndCreators WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if the user was found and the password matches
        if ($user && password_verify($password, $user['password'])) {
            session_start(); // Start the session
            // Set session variables
            $_SESSION['user_id'] = $user['idpk'];
            $_SESSION['ExplorerOrCreator'] = $user['ExplorerOrCreator'];

            // Set cookies for auto-login (expires in 10 years)
            setcookie('user_id', $user['idpk'], time() + (315360000), "/");
            setcookie('ExplorerOrCreator', $user['ExplorerOrCreator'], time() + (315360000), "/");


            // redirect
            echo "<div class=\"content\">";
            echo "<script>window.location.href = 'index.php';</script>";
            // echo "Welcome   : )";
            exit();
            echo "</div>";
        } else {
            // invalid login: incorrect email or password
            $error_message = "We are very sorry, but we weren't able to find that email or password. Please try again or create an account if you don't have one.";
        }
    } else {
        // error: Email or password was empty
        $error_message = "Please enter both email and password.";
    }
}






include ("header.php"); // Include the header
echo "<div class=\"content\">";
?>




<script>
    function submitForm() {
        document.getElementById('loginForm').submit(); // Submit the form
    }
</script>




<div class="login-container">
    <h1>COME ON BOARD</h1> <!-- Main heading -->

    <?php if (isset($error_message)): ?>
        <?= htmlspecialchars($error_message) ?> <!-- Display error message -->
        <br><br><br>
    <?php endif; ?>

    <div class="links"> <!-- Links for creating account -->
        <br>
        <a href="index.php?content=CreateAccount.php" class="mainbutton">CREATE AN ACCOUNT</a>

        <br><br><br><br>
        <!-- <h3> - - - - - OR - - - - - </h3> -->
        <h3 style="display: flex; align-items: center; text-align: center; margin: 20px 0;">
            <hr>
            <span style="padding: 0 20px; font-weight: bold;">OR</span>
            <hr>
        </h3>
        <br><br><br>
    </div>

    <form id="loginForm" action="" method="post"> <!-- Form for login -->
        <!-- <div class="steps"> -->
            <input type="email" id="email" name="email" placeholder="email" style="width: 300px;" required> <!-- Email input -->
            <!-- <label for="email">email</label> -->

            <br><br>
            <input type="password" id="password" name="password" placeholder="password" style="width: 300px;" required> <!-- Password input -->
            <!-- <label for="password">password</label> -->
        <!-- </div> -->
        
        <br><br>
        <a href="javascript:void(0);" class="mainbutton" onclick="submitForm()">LOG IN</a>
    </form>

    <div class="links"> <!-- Links for forgot password -->
        <br><br><br>
        <a href="index.php?content=ForgotPassword.php" style="opacity: 0.4;">FORGOT YOUR PASSWORD?</a>
    </div>
</div>









</div>