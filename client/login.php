<div class="auth-container">
    <div class="auth-header">
        <h1>Welcome Back</h1>
        <p>Login to your Quesiono account</p>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger mb-4">
            <?php 
                echo $_SESSION['error']; 
                unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <form method="post" action="./server/requests.php">
        <div class="form-group mb-4">
            <label class="form-label" for="emailaddress">Email Address</label>
            <input type="email" name="email" class="form-control" id="emailaddress" placeholder="name@example.com" required>
        </div>

        <div class="form-group mb-4">
            <label class="form-label" for="password">Password</label>
            <div class="input-group">
                <input type="password" name="password" class="form-control border-end-0" id="password" placeholder="Enter your password" required>
                <span class="input-group-text bg-white border-start-0 cursor-pointer" onclick="togglePassword('password', this)">
                    <i class="bi bi-eye"></i>
                </span>
            </div>
        </div>

        <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf_token'] ?>">
        
        <div class="mt-4">
            <button type="submit" name="login" class="btn btn-primary w-100 mb-3">Login</button>
            <div class="text-center">
                <a href="?signup=true" class="text-decoration-none small">Don't have an account? Signup</a>
            </div>
        </div>
    </form>
</div>

<script>
function togglePassword(inputId, iconElement) {
    const input = document.getElementById(inputId);
    const icon = iconElement.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
}
</script>

