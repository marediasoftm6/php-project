<?php
// sidebar.php
include_once(__DIR__ . "/../common/db.php");
?>

<div class="sidebar-container">
    <!-- 1. Why Quesiono? (Static/Brand) -->
    <div class="sidebar-card mb-4">
        <h5 class="mb-4" style="font-weight: 700; color: var(--text);">Why Quesiono?</h5>
        <div class="mb-2">
            <div class="d-flex gap-3 align-items-center mb-3">
                <div class="bg-primary-light text-primary rounded-circle d-flex align-items-center justify-content-center" style="min-width: 40px; height: 40px;">
                    <i class="bi bi-lightning-charge fs-5"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0">Fast Answers</h6>
                    <p class="text-muted small mb-0">Quick community expert solutions.</p>
                </div>
            </div>
            <div class="d-flex gap-3 align-items-center mb-3">
                <div class="bg-primary-light text-primary rounded-circle d-flex align-items-center justify-content-center" style="min-width: 40px; height: 40px;">
                    <i class="bi bi-journal-check fs-5"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0">Rich Content</h6>
                    <p class="text-muted small mb-0">Deep guides and tech articles.</p>
                </div>
            </div>
            <div class="d-flex gap-3 align-items-center">
                <div class="bg-primary-light text-primary rounded-circle d-flex align-items-center justify-content-center" style="min-width: 40px; height: 40px;">
                    <i class="bi bi-people fs-5"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0">Expert Network</h6>
                    <p class="text-muted small mb-0">Connect with knowledgeable peers.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Trending Questions (Dynamic) -->
    <div class="sidebar-card mb-4">
        <h5 class="mb-4" style="font-weight: 700; color: var(--text);">Trending Now</h5>
        <div class="trending-list">
            <?php
            $trendingQuery = "SELECT q.id, q.title, q.slug, COUNT(a.id) as answer_count 
                              FROM questions q 
                              LEFT JOIN answers a ON a.question_id = q.id 
                              GROUP BY q.id, q.title, q.slug 
                              ORDER BY answer_count DESC, q.id DESC 
                              LIMIT 3";
            $trendingRes = $conn->query($trendingQuery);
            if ($trendingRes && $trendingRes->num_rows > 0):
                while($tq = $trendingRes->fetch_assoc()):
            ?>
                <div class="mb-3 pb-3 border-bottom border-light-subtle last-child-border-0">
                    <a href="<?php echo $tq['slug']; ?>" class="text-decoration-none text-dark fw-bold small d-block mb-1 line-clamp-2 hover-primary">
                        <?php echo htmlspecialchars($tq['title']); ?>
                    </a>
                    <div class="d-flex align-items-center gap-2 text-muted" style="font-size: 0.85rem;">
                        <span class="badge bg-primary-light text-primary border-0 rounded-pill px-2 py-2" style="font-size: 0.85rem;">
                            <i class="bi bi-chat-text me-1"></i><?php echo $tq['answer_count']; ?> answers
                        </span>
                    </div>
                </div>
            <?php 
                endwhile;
            else:
            ?>
                <p class="text-muted small">No trending questions yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- 3. Popular Categories (Dynamic) -->
    <div class="sidebar-card mb-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0" style="font-weight: 700; color: var(--text);">Categories</h5>
            <a href="categories" class="text-primary text-decoration-none small fw-bold">View all</a>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <?php 
            $catQuery = "SELECT c.id, c.name, c.slug, COUNT(q.id) as q_count 
                         FROM category c 
                         LEFT JOIN questions q ON q.category_id = c.id 
                         GROUP BY c.id, c.name, c.slug 
                         ORDER BY q_count DESC 
                         LIMIT 12";
            $catRes = $conn->query($catQuery);
            if ($catRes):
                foreach($catRes as $row){
                    $name = htmlspecialchars(ucfirst($row["name"]), ENT_QUOTES, 'UTF-8');
                    $slug = $row["slug"];
                    ?>
                    <a href="<?php echo $slug; ?>" class="badge-category text-decoration-none">
                        <?php echo $name; ?>
                    </a>
                    <?php
                }
            endif; ?>
        </div>
    </div>

    <!-- 4. Top Contributors (Dynamic) -->
    <div class="sidebar-card mb-4">
        <h5 class="mb-4" style="font-weight: 700; color: var(--text);">Top Contributors</h5>
        <div class="contributors-list">
            <?php
            $topUsersQuery = "SELECT u.id, u.username, COUNT(a.id) as answer_count 
                             FROM users u 
                             JOIN answers a ON a.user_id = u.id 
                             GROUP BY u.id 
                             ORDER BY answer_count DESC 
                             LIMIT 4";
            $topUsersRes = $conn->query($topUsersQuery);
            if ($topUsersRes && $topUsersRes->num_rows > 0):
                while($tu = $topUsersRes->fetch_assoc()):
                    $initial = strtoupper(substr($tu['username'], 0, 1));
            ?>
                <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom border-light-subtle last-child-border-0">
                    <div class="user-avatar-initial small" style="width: 55px; height: 55px; font-size: 1.5rem;">
                        <?php echo $initial; ?>
                    </div>
                    <div class="flex-grow-1">
                        <a href="<?php echo htmlspecialchars($tu['username']); ?>" class="text-decoration-none text-dark fw-bold d-block">
                            <?php echo htmlspecialchars(ucfirst($tu['username'])); ?>
                        </a>
                        <span class="text-muted" style="font-size: 1rem;"><?php echo $tu['answer_count']; ?> solutions provided</span>
                    </div>
                </div>
            <?php 
                endwhile;
            endif; ?>
        </div>
    </div>

    <!-- 5. Recent Posts (Dynamic) -->
    <div class="sidebar-card mb-4">
        <h5 class="mb-4" style="font-weight: 700; color: var(--text);">Recent Insights</h5>
        <div class="recent-posts-sidebar">
            <?php
            $postQuery = "SELECT p.id, p.title, p.slug, p.template, p.created_at FROM posts p ORDER BY p.id DESC LIMIT 3";
            $postRes = $conn->query($postQuery);
            if ($postRes && $postRes->num_rows > 0):
                while($p = $postRes->fetch_assoc()):
                    $icon = 'bi-journal-text';
                    if($p['template'] == 'guide') $icon = 'bi-list-ol';
                    else if($p['template'] == 'technical') $icon = 'bi-code-square';
                    else if($p['template'] == 'story') $icon = 'bi-chat-quote';
            ?>
                <div class="mb-3 pb-3 border-bottom border-light-subtle last-child-border-0">
                    <div class="d-flex gap-3 align-items-start">
                        <div class="bg-primary-light text-primary rounded p-2" style="min-width: 38px; text-align: center;">
                            <i class="bi <?php echo $icon; ?> fs-5"></i>
                        </div>
                        <div class="flex-grow-1">
                            <a href="<?php echo $p['slug']; ?>" class="text-decoration-none text-dark fw-bold small d-block mb-1 line-clamp-2 hover-primary">
                                <?php echo htmlspecialchars($p['title']); ?>
                            </a>
                            <span class="text-muted" style="font-size: 0.7rem;">
                                <i class="bi bi-calendar3 me-1"></i><?php echo date('M d, Y', strtotime($p['created_at'])); ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php 
                endwhile;
            endif; ?>
        </div>
        <a href="posts" class="text-primary text-decoration-none small fw-bold d-block mt-2">View all posts <i class="bi bi-arrow-right ms-1"></i></a>
    </div>

    <!-- 6. About Mini (Static) -->
    <div class="sidebar-card">
        <h5 class="mb-3" style="font-weight: 700; color: var(--text);">About Quesiono</h5>
        <p class="text-muted small mb-3">Empowering community through shared knowledge and expert solutions.</p>
        <div class="d-flex gap-2">
            <a href="about" class="btn btn-sm btn-primary-light text-primary px-3 py-2">Learn More</a>
            <a href="signup" class="btn btn-sm btn-primary-light text-primary px-3 py-2">Join Us</a>
        </div>
    </div>
</div>

<style>
.hover-primary:hover {
    color: var(--primary) !important;
}
.btn-primary-light {
    background-color: rgba(var(--primary-rgb), 0.1);
    border: none;
}
.btn-primary-light:hover {
    background-color: rgba(var(--primary-rgb), 0.2);
    color: var(--primary);
}
</style>
