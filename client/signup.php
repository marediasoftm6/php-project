<div class="auth-container-two-column">
    <!-- Info Side -->
    <div class="auth-info-side">
        <a href="./">
            <img src="./public/transparent-logo.png" alt="Quesiono Logo" class="websiteLogo">
        </a>
        <h2>Join Our Community!</h2>
        <p>Your journey to knowledge begins here. Create an account to ask questions, share answers, and connect with people who share your interests.</p>
        <div class="mt-4 opacity-75 small">
            <p><i class="bi bi-check2-circle me-2"></i> Earn badges and recognition</p>
            <p><i class="bi bi-check2-circle me-2"></i> Personalize your feed</p>
            <p><i class="bi bi-check2-circle me-2"></i> Stay updated with latest trends</p>
        </div>
    </div>

    <!-- Form Side -->
    <div class="auth-form-side">
        <div class="auth-header text-start mb-4">
            <h1>Create Account</h1>
            <p>Join the Quesiono community today</p>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger mb-4">
                <?php 
                    echo $_SESSION['error']; 
                    unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <form method="post" action="./server/requests.php" id="signupForm">
            <div class="form-group mb-3">
                <label class="form-label" for="username">Username</label>
                <input type="text" name="username" class="form-control" id="username" placeholder="Choose a username" required>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" for="emailaddress">Email Address</label>
                <input type="email" name="email" class="form-control" id="emailaddress" placeholder="name@example.com" required>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" for="gender">Gender</label>
                <select name="gender" class="form-select" id="gender" required>
                    <option value="" disabled selected>Select your gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" for="password">Password</label>
                <div class="input-group">
                    <input type="password" name="password" class="form-control border-end-0" id="password" placeholder="Create a strong password" required>
                    <span class="input-group-text bg-white border-start-0 cursor-pointer" onclick="togglePassword('password', this)">
                        <i class="bi bi-eye"></i>
                    </span>
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" for="confirm_password">Confirm Password</label>
                <div class="input-group">
                    <input type="password" name="confirm_password" class="form-control border-end-0" id="confirm_password" placeholder="Confirm your password" required>
                    <span class="input-group-text bg-white border-start-0 cursor-pointer" onclick="togglePassword('confirm_password', this)">
                        <i class="bi bi-eye"></i>
                    </span>
                </div>
            </div>

            <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf_token'] ?>">
            
            <div class="mt-4">
                <button type="submit" name="signup" class="btn btn-primary w-100 mb-3">Sign Up</button>
                <div class="text-center">
                    <a href="login" class="text-decoration-none small">Already have an account? Login</a>
                </div>
            </div>
        </form>
    </div>
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

document.getElementById('signupForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirm = document.getElementById('confirm_password').value;
    if (password !== confirm) {
        e.preventDefault();
        alert('Passwords do not match!');
    }
});
</script>