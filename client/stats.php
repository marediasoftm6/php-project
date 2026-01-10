<?php
include_once("./common/badge_helper.php");
ensure_default_badges($conn); // Ensure badges exist for stats

$qCount = $conn->query("SELECT COUNT(*) as count FROM questions")->fetch_assoc()['count'];
$aCount = $conn->query("SELECT COUNT(*) as count FROM answers")->fetch_assoc()['count'];
$uCount = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$pCount = $conn->query("SELECT COUNT(*) as count FROM posts")->fetch_assoc()['count'];
$bCount = $conn->query("SELECT COUNT(*) as count FROM user_badges")->fetch_assoc()['count'];
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

<div class="row g-4 mb-5 d-none">
    <!-- Top Badges -->
    <div class="col-lg-6">
        <div class="bg-white rounded-4 shadow-sm p-4 border-0 h-100">
            <h5 class="fw-bold mb-4 d-flex align-items-center">
                <i class="bi bi-award text-primary me-2"></i> Popular Badges
            </h5>
            <div class="badge-stats-list">
                <?php
                $topBadges = $conn->query("
                    SELECT b.name, b.icon, b.description, COUNT(ub.badge_id) as count 
                    FROM badges b 
                    LEFT JOIN user_badges ub ON b.id = ub.badge_id 
                    GROUP BY b.id 
                    ORDER BY count DESC
                ");
                while ($badge = $topBadges->fetch_assoc()) {
                    $percentage = $uCount > 0 ? round(($badge['count'] / $uCount) * 100, 1) : 0;
                ?>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary-light text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <i class="bi <?php echo $badge['icon']; ?> fs-5"></i>
                                </div>
                                <div>
                                    <div class="fw-bold"><?php echo $badge['name']; ?></div>
                                    <div class="text-muted small"><?php echo $badge['description']; ?></div>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-primary"><?php echo number_format($badge['count']); ?></div>
                                <div class="text-muted" style="font-size: 0.75rem;"><?php echo $percentage; ?>% of members</div>
                            </div>
                        </div>
                        <div class="progress" style="height: 6px; border-radius: 10px; background-color: var(--primary-light);">
                            <div class="progress-bar rounded-pill" role="progressbar" style="width: <?php echo $percentage; ?>%;" aria-valuenow="<?php echo $percentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                <?php
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Recent Achievement -->
    <div class="col-lg-6">
        <div class="bg-white rounded-4 shadow-sm p-4 border-0 h-100">
            <h5 class="fw-bold mb-4 d-flex align-items-center">
                <i class="bi bi-clock-history text-primary me-2"></i> Recent Achievements
            </h5>
            <div class="recent-badges">
                <?php
                // Get 5 most recent badge awards
                $recentAwards = $conn->query("
                    SELECT u.username, u.profile_pic, b.name as badge_name, b.icon
                    FROM user_badges ub 
                    JOIN users u ON ub.user_id = u.id 
                    JOIN badges b ON ub.badge_id = b.id 
                    ORDER BY ub.user_id DESC -- Assuming higher ID is more recent if awarded_at is missing
                    LIMIT 5
                ");

                if ($recentAwards->num_rows > 0) {
                    while ($award = $recentAwards->fetch_assoc()) {
                        $username = htmlspecialchars($award['username']);
                        $profilePic = $award['profile_pic'];
                        $initial = strtoupper(substr($username, 0, 1));
                ?>
                        <div class="d-flex align-items-center p-3 mb-3 border rounded-4 hover-bg-light transition-all">
                            <div class="user-avatar-initial user-avatar-md me-3">
                                <?php if ($profilePic): ?>
                                    <img src="<?php echo htmlspecialchars($profilePic); ?>" alt="<?php echo $username; ?>">
                                <?php else: ?>
                                    <?php echo $initial; ?>
                                <?php endif; ?>
                            </div>
                            <div class="flex-grow-1">
                                <div class="small">
                                    <strong><?php echo htmlspecialchars($award['username']); ?></strong> earned the
                                </div>
                                <div class="fw-bold text-primary d-flex align-items-center">
                                    <i class="bi <?php echo $award['icon']; ?> me-1 small"></i>
                                    <?php echo $award['badge_name']; ?>
                                </div>
                            </div>
                            <div class="text-muted small">
                                <i class="bi bi-check-circle-fill text-success"></i>
                            </div>
                        </div>
                <?php
                    }
                } else {
                    echo '<div class="text-center py-5 text-muted">
                        <i class="bi bi-award fs-1 opacity-25 d-block mb-3"></i>
                        No achievements recorded yet.
                    </div>';
                }
                ?>
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