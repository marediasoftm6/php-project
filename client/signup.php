<div class="auth-container">
    <div class="auth-header">
        <h1>Create Account</h1>
        <p>Join the Quesiono community today</p>
    </div>
    <form method="post" action="./server/requests.php">
        <div class="form-group">
            <label class="form-label" for="username">Username</label>
            <input type="text" name="username" class="form-control" id="username" placeholder="Choose a username" required>
        </div>

        <div class="form-group">
            <label class="form-label" for="emailaddress">Email Address</label>
            <input type="email" name="email" class="form-control" id="emailaddress" placeholder="name@example.com" required>
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Password</label>
            <input type="password" name="password" class="form-control" id="password" placeholder="Create a strong password" required>
        </div>

        <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf_token'] ?>">
        
        <div class="mt-4">
            <button type="submit" name="signup" class="btn btn-primary w-100 mb-3">Sign Up</button>
            <div class="text-center">
                <a href="?login=true" class="text-decoration-none small">Already have an account? Login</a>
            </div>
        </div>
    </form>
</div>
 
