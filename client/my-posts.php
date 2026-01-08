<?php
include("./common/db.php");

if (!isset($_SESSION['user']['user_id'])) {
    echo "<script>window.location.href='index.php';</script>";
    exit();
}

$uid = $_SESSION['user']['user_id'];

// Fetch my posts
$stmt = $conn->prepare("
    SELECT p.*, u.username 
    FROM posts p 
    JOIN users u ON p.user_id = u.id 
    WHERE p.user_id = ?
    ORDER BY p.id DESC
");
$stmt->bind_param("i", $uid);
$stmt->execute();
$postsRes = $stmt->get_result();
?>

<div class="row g-4">
    <div class="col-12 col-lg-8">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-4 mb-5">
                <div class="text-center text-md-start">
                    <h1 class="fw-bold text-dark display-5 display-md-4">My Posts</h1>
                    <p class="text-muted mb-0">Manage and view all the rich content you've published.</p>
                </div>
                <div class="text-center text-md-end">
                    <a href="?post=true" class="btn btn-primary rounded-pill px-4 py-2 py-md-3 shadow-sm w-100 w-md-auto">
                        <i class="bi bi-plus-circle me-2"></i>Create New Post
                    </a>
                </div>
            </div>

            <div class="row g-4">
                <?php if ($postsRes->num_rows === 0): ?>
                    <div class="col-12 text-center py-5">
                        <i class="bi bi-journal-plus display-1 text-muted opacity-25"></i>
                        <h3 class="mt-3 text-muted">You haven't posted anything yet</h3>
                        <p>Start sharing your knowledge by creating your first post.</p>
                        <a href="?post=true" class="btn btn-primary mt-3">Create First Post</a>
                    </div>
                <?php else: ?>
                    <?php while ($p = $postsRes->fetch_assoc()): 
                        $templateIcon = 'bi-journal-text';
                        if($p['template'] == 'guide') $templateIcon = 'bi-list-ol';
                        else if($p['template'] == 'technical') $templateIcon = 'bi-code-square';
                        else if($p['template'] == 'story') $templateIcon = 'bi-chat-quote';
                    ?>
                        <div class="col-md-6">
                            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden transition-all post-card">
                                <div class="card-body p-4 d-flex flex-column">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-primary-light text-primary rounded-circle p-2 me-2">
                                            <i class="bi <?php echo $templateIcon; ?> fs-5"></i>
                                        </div>
                                        <span class="text-uppercase small fw-bold text-muted"><?php echo $p['template']; ?></span>
                                    </div>
                                    
                                    <h4 class="card-title fw-bold mb-2">
                                        <a href="?post-id=<?php echo $p['id']; ?>" class="text-decoration-none text-dark">
                                            <?php echo htmlspecialchars($p['title']); ?>
                                        </a>
                                    </h4>
                                    
                                    <?php if ($p['subtitle']): ?>
                                        <p class="card-subtitle text-muted mb-3 small line-clamp-2"><?php echo htmlspecialchars($p['subtitle']); ?></p>
                                    <?php endif; ?>

                                    <p class="card-text text-muted flex-grow-1 small">
                                        <?php echo htmlspecialchars(substr(strip_tags($p['content']), 0, 100)) . (strlen(strip_tags($p['content'])) > 100 ? '...' : ''); ?>
                                    </p>

                                    <div class="border-top pt-3 mt-3 d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <span class="small text-muted"><i class="bi bi-calendar3 me-1"></i><?php echo date('M d, Y', strtotime($p['created_at'])); ?></span>
                                        </div>
                                        <div class="d-flex align-items-center gap-3">
                                            <span class="small text-muted"><i class="bi bi-eye me-1"></i><?php echo $p['views_count']; ?></span>
                                            <a href="?post-id=<?php echo $p['id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill">View</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </div>
        <!-- Sidebar Column -->
        <div class="col-12 col-lg-4 mt-4 mt-lg-0">
            <?php include('sidebar.php'); ?>
        </div>
    </div>

<style>
.post-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
.bg-primary-light { background-color: rgba(var(--primary-rgb), 0.1); }
</style>