<?php
include("./common/db.php");

if (!isset($_GET['post-id'])) {
    echo "<script>window.location.href='index.php';</script>";
    exit();
}

$post_id = (int)$_GET['post-id'];

// Fetch post with user info and category
$stmt = $conn->prepare("
    SELECT p.*, u.username, c.name as category_name 
    FROM posts p 
    JOIN users u ON p.user_id = u.id 
    LEFT JOIN category c ON p.category_id = c.id 
    WHERE p.id = ?
");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if (!$post) {
    echo "<div class='container py-5 text-center'><h2>Post not found</h2><a href='index.php' class='btn btn-primary mt-3'>Back to Home</a></div>";
    exit();
}

// Update views count
$conn->query("UPDATE posts SET views_count = views_count + 1 WHERE id = $post_id");

$links = json_decode($post['links'], true) ?: [];
$template = $post['template'] ?: 'article';
$formattedContent = nl2br($post['content']);
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Template Rendering -->
            <div class="post-detail-wrapper bg-white shadow-sm rounded-4 overflow-hidden">
                <?php if ($template == 'article'): ?>
                    <div class="p-4 p-md-5 preview-article">
                        <div class="mb-4">
                            <?php if ($post['category_name']): ?>
                                <span class="badge bg-primary-light text-primary mb-2"><?php echo htmlspecialchars($post['category_name']); ?></span>
                            <?php endif; ?>
                            <h1 class="preview-title display-4 fw-bold"><?php echo htmlspecialchars($post['title']); ?></h1>
                            <?php if ($post['subtitle']): ?>
                                <p class="preview-subtitle lead"><?php echo htmlspecialchars($post['subtitle']); ?></p>
                            <?php endif; ?>
                            <div class="d-flex align-items-center mt-4 text-muted small">
                                <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                    <?php echo strtoupper(substr($post['username'], 0, 1)); ?>
                                </div>
                                <span>By <strong><?php echo htmlspecialchars($post['username']); ?></strong></span>
                                <span class="mx-2">•</span>
                                <span><?php echo date('M d, Y', strtotime($post['created_at'])); ?></span>
                                <span class="mx-2">•</span>
                                <span><i class="bi bi-eye me-1"></i><?php echo $post['views_count']; ?> views</span>
                            </div>
                        </div>
                        <hr class="my-5 opacity-10">
                        <div class="preview-content">
                            <?php echo $formattedContent; ?>
                        </div>
                    </div>

                <?php elseif ($template == 'guide'): ?>
                    <div class="p-4 p-md-5 preview-guide">
                        <div class="text-center mb-5">
                            <h1 class="preview-title"><?php echo htmlspecialchars($post['title']); ?></h1>
                            <?php if ($post['subtitle']): ?>
                                <div class="preview-subtitle d-inline-block"><?php echo htmlspecialchars($post['subtitle']); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="preview-content">
                            <?php echo $formattedContent; ?>
                        </div>
                    </div>

                <?php elseif ($template == 'technical'): ?>
                    <div class="preview-technical">
                        <div class="p-4 p-md-5 bg-dark text-white rounded-top-4">
                            <h1 class="preview-title mb-0">> <?php echo htmlspecialchars($post['title']); ?></h1>
                            <div class="mt-3 opacity-75 small">
                                Created by @<?php echo htmlspecialchars($post['username']); ?> on <?php echo date('Y-m-d', strtotime($post['created_at'])); ?>
                            </div>
                        </div>
                        <div class="p-4 p-md-5">
                            <?php if ($post['subtitle']): ?>
                                <div class="mb-4 text-primary fw-bold text-uppercase small"><?php echo htmlspecialchars($post['subtitle']); ?></div>
                            <?php endif; ?>
                            <div class="preview-content">
                                <?php echo $formattedContent; ?>
                            </div>
                        </div>
                    </div>

                <?php elseif ($template == 'story'): ?>
                    <div class="p-4 p-md-5 preview-story">
                        <h1 class="preview-title"><?php echo htmlspecialchars($post['title']); ?></h1>
                        <div class="preview-content">
                            <?php if ($post['subtitle']): ?>
                                <p class="mb-5 text-primary fw-bold h4"><?php echo htmlspecialchars($post['subtitle']); ?></p>
                            <?php endif; ?>
                            <?php echo $formattedContent; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Shared Footer for Links -->
                <?php if (!empty($links)): ?>
                    <div class="p-4 p-md-5 pt-0">
                        <div class="preview-links border-top pt-4">
                            <h5 class="mb-3">Related Links</h5>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($links as $link): ?>
                                    <a href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank" class="btn btn-outline-primary rounded-pill btn-sm px-3">
                                        <i class="bi bi-link-45deg me-1"></i><?php echo htmlspecialchars($link['text']); ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mt-4 text-center">
                <a href="?all-posts=true" class="btn btn-link text-decoration-none text-muted">
                    <i class="bi bi-arrow-left me-2"></i>Back to All Posts
                </a>
            </div>
        </div>
    </div>
</div>

<style>
/* Ensure detail page matches preview styles */
.preview-article .preview-content { line-height: 1.8; font-size: 1.15rem; color: #333; }
.preview-guide .preview-content p { position: relative; padding-left: 2rem; margin-bottom: 1.5rem; }
.preview-guide .preview-content p::before { content: '✓'; position: absolute; left: 0; color: var(--primary); font-weight: bold; }
.preview-technical .preview-content { font-family: 'Inter', sans-serif; background: #fdfdfd; padding: 2rem; border-radius: 0.5rem; }
.preview-story .preview-content { text-align: center; max-width: 90%; margin: 0 auto; font-size: 1.25rem; line-height: 2; }
.bg-primary-light { background-color: rgba(var(--primary-rgb), 0.1); }
</style>