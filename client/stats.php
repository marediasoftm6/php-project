<?php
$qCount = $conn->query("SELECT COUNT(*) as count FROM questions")->fetch_assoc()['count'];
$aCount = $conn->query("SELECT COUNT(*) as count FROM answers")->fetch_assoc()['count'];
$uCount = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$pCount = $conn->query("SELECT COUNT(*) as count FROM posts")->fetch_assoc()['count'];
?>
<div class="row mb-5">
    <div class="col-12">
        <div class="bg-white rounded-4 shadow-sm p-4 border-0">
            <div class="row text-center g-4">
                <div class="col-6 col-md-3 border-end-md border-light">
                    <div class="h3 fw-bold text-primary mb-1"><?php echo number_format($uCount); ?>+</div>
                    <div class="text-muted small text-uppercase fw-bold" style="font-size: 0.65rem; letter-spacing: 1px;">Members</div>
                </div>
                <div class="col-6 col-md-3 border-end-md border-light">
                    <div class="h3 fw-bold text-primary mb-1"><?php echo number_format($qCount); ?>+</div>
                    <div class="text-muted small text-uppercase fw-bold" style="font-size: 0.65rem; letter-spacing: 1px;">Questions</div>
                </div>
                <div class="col-6 col-md-3 border-end-md border-light">
                    <div class="h3 fw-bold text-primary mb-1"><?php echo number_format($aCount); ?>+</div>
                    <div class="text-muted small text-uppercase fw-bold" style="font-size: 0.65rem; letter-spacing: 1px;">Solutions</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="h3 fw-bold text-primary mb-1"><?php echo number_format($pCount); ?>+</div>
                    <div class="text-muted small text-uppercase fw-bold" style="font-size: 0.65rem; letter-spacing: 1px;">Articles</div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media (min-width: 768px) {
    .border-end-md {
        border-right: 1px solid var(--bs-border-color) !important;
    }
}
@media (max-width: 767.98px) {
    .border-end-md {
        border-right: none !important;
    }
}
</style>