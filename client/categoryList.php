<div class="sidebar-card">
    <h5 class="mb-4" style="font-weight: 700; color: var(--text);">Why Quesiono?</h5>
    <div class="mb-4">
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

<div class="sidebar-card mt-4">
    <h5 class="mb-4" style="font-weight: 700; color: var(--text);">Popular Categories</h5>
    <div class="d-flex flex-wrap gap-2">
        <?php 
        include("./common/db.php");
        $query = "SELECT c.id, c.name, COUNT(q.id) as q_count FROM category c LEFT JOIN questions q ON q.category_id = c.id GROUP BY c.id, c.name ORDER BY q_count DESC LIMIT 15";
        $result = $conn->query($query);
        foreach($result as $row){
            $name = htmlspecialchars(ucfirst($row["name"]), ENT_QUOTES, 'UTF-8');
            $id = $row["id"];
            ?>
            <a href="?c-id=<?php echo $id; ?>" class="badge-category text-decoration-none">
                <?php echo $name; ?>
            </a>
            <?php
        }?>
    </div>
</div>

<div class="sidebar-card mt-4">
    <h5 class="mb-4" style="font-weight: 700; color: var(--text);">Recent Insights</h5>
    <div class="recent-posts-sidebar">
        <?php
        $postQuery = "SELECT p.id, p.title, p.template, p.created_at FROM posts p ORDER BY p.id DESC LIMIT 3";
        $postRes = $conn->query($postQuery);
        if ($postRes->num_rows > 0):
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
                    <div>
                        <a href="?post-id=<?php echo $p['id']; ?>" class="text-decoration-none text-dark fw-bold small d-block mb-1 line-clamp-2">
                            <?php echo htmlspecialchars($p['title']); ?>
                        </a>
                        <span class="text-muted" style="font-size: 0.75rem;">
                            <i class="bi bi-calendar3 me-1"></i><?php echo date('M d, Y', strtotime($p['created_at'])); ?>
                        </span>
                    </div>
                </div>
            </div>
        <?php 
            endwhile;
        else:
        ?>
            <p class="text-muted small">No articles published yet.</p>
        <?php endif; ?>
    </div>
    <a href="?all-posts=true" class="text-primary text-decoration-none small fw-bold d-block mt-2">View all posts <i class="bi bi-arrow-right ms-1"></i></a>
</div>

<div class="sidebar-card mt-4">
    <h5 class="mb-3" style="font-weight: 700; color: var(--text);">About Quesiono</h5>
    <p class="text-muted small mb-3">Quesiono is a community-driven Q&A platform where you can share knowledge and find answers to your questions.</p>
    <a href="?about=true" class="text-primary text-decoration-none small fw-bold">Learn more about us <i class="bi bi-arrow-right ms-1"></i></a>
</div>