<div class="row g-4">
    <!-- Main Content Column -->
    <div class="col-12 col-lg-8">
        <?php if (!is_verified_user($conn)): ?>
            <div class="alert alert-warning border-0 shadow-sm mb-4 p-4 d-flex align-items-center">
                <div class="me-3">
                    <i class="bi bi-exclamation-triangle-fill fs-2"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-1">Account Verification Required</h5>
                    <p class="mb-2 small">You must verify your email address before you can ask public questions. This helps us maintain a high-quality community.</p>
                    <a href="verify-code" class="btn btn-warning btn-sm fw-bold rounded-pill px-4">Verify My Account</a>
                </div>
            </div>
        <?php endif; ?>
        <div class="auth-container auth-container-wide p-4 p-md-5 m-0 <?php echo !is_verified_user($conn) ? 'opacity-75 pointer-events-none' : ''; ?>">
            <div class="text-center mb-5">
                <h2 class="display-6 fw-bold text-dark">Ask a Public Question</h2>
                <p class="text-muted lead">Be specific and imagine you’re asking a question to another person.</p>
            </div>

            <form method="post" action="./server/requests.php">
                <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf_token'] ?>">

                <div class="row g-4">
                    <div class="col-12">
                        <div class="form-group mb-4">
                            <label class="form-label fs-5 fw-bold" for="question_title">Title</label>
                            <p class="small text-muted mb-2">Be specific and imagine you’re asking a question to another person.</p>
                            <input type="text" id="question_title" class="form-control form-control-lg" name="title" placeholder="e.g. Is there an R function for finding the index of an element in a vector?" required <?php echo !is_verified_user($conn) ? 'disabled' : ''; ?>>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-group mb-4">
                            <label class="form-label fs-5 fw-bold" for="question_desc">Description</label>
                            <p class="small text-muted mb-2">Include all the information someone would need to answer your question.</p>
                            <textarea id="question_desc" class="form-control" name="description" rows="8" placeholder="Details about your problem..." required <?php echo !is_verified_user($conn) ? 'disabled' : ''; ?>></textarea>
                        </div>
                    </div>

                    <div class="col-12 col-md-8 col-lg-6">
                        <div class="form-group mb-4">
                            <label class="form-label fs-5 fw-bold" for="category_id">Category</label>
                            <p class="small text-muted mb-2">Add a category to help people find your question.</p>
                            <?php if (is_verified_user($conn)): ?>
                                <?php include("category.php"); ?>
                            <?php else: ?>
                                <select class="form-control" disabled>
                                    <option>Select a category</option>
                                </select>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-12">
                        <hr class="my-4 opacity-10">
                        <div class="d-flex flex-column flex-md-row gap-4 align-items-center">
                            <button type="submit" name="askQuestion" class="btn btn-primary btn-lg px-5 fw-bold" <?php echo !is_verified_user($conn) ? 'disabled' : ''; ?>>Post Your Question</button>
                            <a href="index.php" class="text-decoration-none text-muted fw-bold hover-primary transition-all">Discard draft</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Sidebar Column -->
    <div class="col-12 col-lg-4">
        <?php include('sidebar.php'); ?>
    </div>
</div>