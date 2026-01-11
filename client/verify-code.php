<div class="row justify-content-center py-5">
    <div class="col-12 col-md-8 col-lg-5">
        <div class="profile-card-modern p-5">
            <div class="text-center mb-4">
                <div class="bg-primary-light text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                    <i class="bi bi-shield-check" style="font-size: 2.5rem;"></i>
                </div>
                <h2 class="fw-bold" style="color: var(--text);">Enter Verification Code</h2>
                <p class="text-muted">Enter the 6-digit code sent to your email address to verify your account.</p>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger border-0 small mb-4">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['notice'])): ?>
                <div class="alert alert-info border-0 small mb-4">
                    <?php echo $_SESSION['notice']; unset($_SESSION['notice']); ?>
                </div>
            <?php endif; ?>

            <form action="./server/requests.php" method="post">
                <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="form-group mb-4">
                    <label class="form-label fw-bold small text-uppercase" style="letter-spacing: 1px;">Email Address</label>
                    <input type="email" name="email" class="form-control form-control-lg" 
                           value="<?php 
                                if(isset($_SESSION['user']['email'])) echo htmlspecialchars($_SESSION['user']['email']);
                                elseif(isset($_SESSION['temp_verify_user']['email'])) echo htmlspecialchars($_SESSION['temp_verify_user']['email']);
                           ?>" 
                           placeholder="Enter your email" required>
                </div>

                <div class="form-group mb-4">
                    <label class="form-label fw-bold small text-uppercase" style="letter-spacing: 1px;">6-Digit Code</label>
                    <input type="text" name="code" class="form-control form-control-lg text-center fw-bold" 
                           maxlength="6" placeholder="000000" style="letter-spacing: 10px; font-size: 1.5rem;" required>
                </div>

                <div class="d-grid">
                    <button type="submit" name="verifyCode" class="btn btn-primary btn-lg rounded-pill fw-bold">Verify Account</button>
                </div>
            </form>

            <div class="text-center mt-4">
                <p class="text-muted small">Didn't receive the code? 
                    <form action="./server/requests.php" method="post" class="d-inline">
                        <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <button type="submit" name="resendCode" class="btn btn-link p-0 m-0 align-baseline text-primary text-decoration-none fw-bold">Resend Code</button>
                    </form>
                    <span class="mx-1">or</span>
                    <a href="signup" class="text-primary text-decoration-none fw-bold">Try Signing up again</a>
                </p>
            </div>
        </div>
    </div>
</div>

<style>
.bg-primary-light {
    background-color: rgba(13, 110, 253, 0.1);
}
</style>