<div class="sidebar-card">
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
    <h5 class="mb-3" style="font-weight: 700; color: var(--text);">About Quesiono</h5>
    <p class="text-muted small mb-3">Quesiono is a community-driven Q&A platform where you can share knowledge and find answers to your questions.</p>
    <a href="?about=true" class="text-primary text-decoration-none small fw-bold">Learn more about us <i class="bi bi-arrow-right ms-1"></i></a>
</div>