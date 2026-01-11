<?php
include("./common/db.php");

if (!isset($_SESSION['user']['username'])) {
    echo "<script>window.location.href='login';</script>";
    exit();
}

if (!isset($_GET['post-id'])) {
    echo "<script>window.location.href='index.php';</script>";
    exit();
}

$post_id = (int)$_GET['post-id'];
$user_id = $_SESSION['user']['user_id'];

// Fetch post and verify ownership
$stmt = $conn->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $post_id, $user_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if (!$post) {
    echo "<div class='container py-5 text-center'><h2>Post not found or access denied.</h2><a href='index.php' class='btn btn-primary mt-3'>Back to Home</a></div>";
    exit();
}

$links = json_decode($post['links'], true) ?: [];
$currentTemplate = $post['template'] ?: 'article';
$categoryId = $post['category_id'];
?>

<!-- Quill Editor Dependencies -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>

<div class="post-page-container mb-5">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 p-4" style="background: var(--gradient); color: var(--white);">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="display-6 fw-bold mb-2">Edit Your Post</h1>
                        <p class="lead mb-0 opacity-90">Update your content and keep your readers engaged.</p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <a href="post-details?post-id=<?php echo $post_id; ?>" class="btn btn-outline-light rounded-pill px-4">
                            <i class="bi bi-x-circle me-2"></i>Cancel Edit
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Template Selector -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <label class="form-label fs-5 fw-bold mb-3 px-2">Choose a Template</label>
                <div class="row g-3" id="templateSelector">
                    <div class="col-6 col-md-3">
                        <div class="template-card <?php echo $currentTemplate == 'article' ? 'active' : ''; ?> p-3 rounded-4 border text-center cursor-pointer transition-all" data-template="article">
                            <i class="bi bi-journal-text fs-2 mb-2 d-block text-primary"></i>
                            <span class="fw-bold">Classic Article</span>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="template-card <?php echo $currentTemplate == 'guide' ? 'active' : ''; ?> p-3 rounded-4 border text-center cursor-pointer transition-all" data-template="guide">
                            <i class="bi bi-list-ol fs-2 mb-2 d-block text-primary"></i>
                            <span class="fw-bold">Step-by-Step Guide</span>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="template-card <?php echo $currentTemplate == 'technical' ? 'active' : ''; ?> p-3 rounded-4 border text-center cursor-pointer transition-all" data-template="technical">
                            <i class="bi bi-code-square fs-2 mb-2 d-block text-primary"></i>
                            <span class="fw-bold">Technical Post</span>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="template-card <?php echo $currentTemplate == 'story' ? 'active' : ''; ?> p-3 rounded-4 border text-center cursor-pointer transition-all" data-template="story">
                            <i class="bi bi-chat-quote fs-2 mb-2 d-block text-primary"></i>
                            <span class="fw-bold">Visual Story</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 <?php echo !is_verified_user($conn) ? 'opacity-75 pointer-events-none' : ''; ?>">
        <!-- Editor Column -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                <form id="postForm" method="post" action="./server/requests.php">
                    <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
                    <input type="hidden" name="template" id="selectedTemplate" value="<?php echo $currentTemplate; ?>">
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold" for="category">Category</label>
                        <p class="small text-muted mb-2">Select a category to help others find your post.</p>
                        <?php if (is_verified_user($conn)): ?>
                            <?php 
                            // Pre-select category logic for category.php
                            $_GET['pre_select_category'] = $categoryId;
                            include("category.php"); 
                            ?>
                        <?php else: ?>
                            <select class="form-control" disabled><option>Select a category</option></select>
                        <?php endif; ?>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold" for="postTitle">Title</label>
                        <input type="text" id="postTitle" class="form-control form-control-lg border-0 bg-light rounded-3" name="title" placeholder="Enter a catchy title..." required value="<?php echo htmlspecialchars($post['title']); ?>" <?php echo !is_verified_user($conn) ? 'disabled' : ''; ?>>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold" for="postSubtitle">Subtitle (Optional)</label>
                        <input type="text" id="postSubtitle" class="form-control border-0 bg-light rounded-3" name="subtitle" placeholder="Add a brief overview..." value="<?php echo htmlspecialchars($post['subtitle']); ?>" <?php echo !is_verified_user($conn) ? 'disabled' : ''; ?>>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold" for="postContent">Content</label>
                        <p class="small text-muted mb-2">Write your post with rich formatting, lists, and links.</p>
                        <div class="rich-editor-wrapper bg-light rounded-3 overflow-hidden">
                            <div id="editor-container" style="height: 400px; border: none !important;"></div>
                        </div>
                        <input type="hidden" name="content" id="postContent">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Add Links</label>
                        <div id="linksContainer">
                            <?php if (empty($links)): ?>
                                <div class="input-group mb-2">
                                    <span class="input-group-text border-0 bg-light rounded-start-3"><i class="bi bi-link-45deg"></i></span>
                                    <input type="text" name="link_texts[]" class="form-control border-0 bg-light post-link-text" placeholder="Link Text">
                                    <input type="url" name="link_urls[]" class="form-control border-0 bg-light post-link-url" placeholder="URL (https://...)">
                                </div>
                            <?php else: ?>
                                <?php foreach ($links as $index => $link): ?>
                                    <div class="input-group mb-2">
                                        <span class="input-group-text border-0 bg-light rounded-start-3"><i class="bi bi-link-45deg"></i></span>
                                        <input type="text" name="link_texts[]" class="form-control border-0 bg-light post-link-text" placeholder="Link Text" value="<?php echo htmlspecialchars($link['text']); ?>">
                                        <input type="url" name="link_urls[]" class="form-control border-0 bg-light post-link-url" placeholder="URL (https://...)" value="<?php echo htmlspecialchars($link['url']); ?>">
                                        <?php if ($index > 0): ?>
                                            <button type="button" class="btn btn-light border-0 remove-link"><i class="bi bi-trash text-danger"></i></button>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill mt-2" id="addMoreLinks">
                            <i class="bi bi-plus-circle me-1"></i> Add Another Link
                        </button>
                    </div>

                    <div class="d-grid pt-3">
                        <button type="submit" name="updateRichPost" class="btn btn-primary btn-lg rounded-pill fw-bold py-3 shadow-sm">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Preview Column -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 p-0 overflow-hidden h-100">
                <div class="bg-dark py-2 px-3 d-flex align-items-center justify-content-between">
                    <span class="text-white small fw-bold"><i class="bi bi-eye me-2"></i>Live Preview</span>
                    <div class="d-flex gap-1">
                        <span class="bg-secondary rounded-circle" style="width: 10px; height: 10px; opacity: 0.5;"></span>
                        <span class="bg-secondary rounded-circle" style="width: 10px; height: 10px; opacity: 0.5;"></span>
                        <span class="bg-secondary rounded-circle" style="width: 10px; height: 10px; opacity: 0.5;"></span>
                    </div>
                </div>
                <div class="preview-box p-4 p-md-5 overflow-auto" id="previewArea" style="background: #fff; height: 100%;">
                    <!-- Dynamic Content Loaded Here -->
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.template-card {
    border-color: var(--border) !important;
    background: var(--white);
}
.template-card:hover {
    border-color: var(--primary) !important;
    background: rgba(var(--primary-rgb), 0.02);
}
.template-card.active {
    border-color: var(--primary) !important;
    background: rgba(var(--primary-rgb), 0.05);
    box-shadow: 0 0 0 1px var(--primary);
}

/* Template Specific Styles for Preview */
.preview-article .preview-title { font-family: 'Playfair Display', serif; font-size: 2.5rem; color: var(--secondary); margin-bottom: 1rem; }
.preview-article .preview-subtitle { font-style: italic; color: var(--text-muted); font-size: 1.2rem; border-left: 4px solid var(--primary); padding-left: 1rem; margin-bottom: 2rem; }
.preview-article .preview-content { line-height: 1.8; font-size: 1.1rem; color: #444; }

.preview-guide .preview-title { font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: var(--primary); margin-bottom: 1.5rem; }
.preview-guide .preview-subtitle { background: #f8f9fa; padding: 1rem; border-radius: 0.5rem; margin-bottom: 2rem; }
.preview-guide .preview-content p { position: relative; padding-left: 2rem; }
.preview-guide .preview-content p::before { content: '✓'; position: absolute; left: 0; color: var(--primary); font-weight: bold; }

.preview-technical .preview-title { font-family: 'Courier New', Courier, monospace; background: #2d3436; color: #fff; padding: 1.5rem; border-radius: 0.5rem; margin-bottom: 2rem; }
.preview-technical .preview-content { font-family: 'Inter', sans-serif; background: #fafafa; padding: 2rem; border: 1px solid #eee; border-radius: 0.5rem; }
.preview-technical code { background: #eee; padding: 0.2rem 0.4rem; border-radius: 3px; }

.preview-story .preview-title { text-align: center; font-size: 3rem; font-weight: 300; margin-bottom: 3rem; }
.preview-story .preview-content { text-align: center; max-width: 90%; margin: 0 auto; font-size: 1.25rem; line-height: 2; }
.preview-story .preview-links { justify-content: center; }

.preview-links .badge { padding: 0.6rem 1.2rem; font-size: 0.9rem; text-decoration: none; transition: all 0.2s; }
.preview-links .badge:hover { transform: translateY(-2px); opacity: 0.9; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Quill Editor
    const quill = new Quill('#editor-container', {
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                ['link', 'blockquote', 'code-block'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'script': 'sub'}, { 'script': 'super' }],
                [{ 'align': [] }],
                ['image', 'video', 'table'],
                ['clean']
            ]
        },
        placeholder: 'Write your amazing content here...',
        theme: 'snow'
    });

    // Set initial content
    const initialContent = `<?php echo addslashes($post['content']); ?>`;
    quill.root.innerHTML = initialContent;

    const postTitle = document.getElementById('postTitle');
    const postSubtitle = document.getElementById('postSubtitle');
    const postContentHidden = document.getElementById('postContent');
    const previewArea = document.getElementById('previewArea');
    const templateCards = document.querySelectorAll('.template-card');
    const selectedTemplateInput = document.getElementById('selectedTemplate');
    const addMoreLinks = document.getElementById('addMoreLinks');
    const linksContainer = document.getElementById('linksContainer');

    function updatePreview() {
        const title = postTitle.value || 'Your Title Here';
        const subtitle = postSubtitle.value || '';
        const content = quill.root.innerHTML || 'Start writing your content...';
        const template = selectedTemplateInput.value;

        // Sync Quill content to hidden input
        postContentHidden.value = content;

        // Get links
        let linksHtml = '';
        const linkTexts = document.querySelectorAll('.post-link-text');
        const linkUrls = document.querySelectorAll('.post-link-url');
        
        linksHtml += '<div class="preview-links d-flex flex-wrap gap-2 mt-4">';
        linkTexts.forEach((el, i) => {
            const text = el.value;
            const url = linkUrls[i].value;
            if (text && url) {
                linksHtml += `<a href="${url}" target="_blank" class="badge bg-primary-light text-primary border-0 rounded-pill">${text}</a>`;
            }
        });
        linksHtml += '</div>';

        let templateHtml = '';
        if (template === 'article') {
            templateHtml = `
                <div class="preview-article">
                    <h1 class="preview-title">${title}</h1>
                    ${subtitle ? `<p class="preview-subtitle">${subtitle}</p>` : ''}
                    <div class="preview-content ql-editor p-0 mt-4">${content}</div>
                    ${linksHtml}
                </div>
            `;
        } else if (template === 'guide') {
            templateHtml = `
                <div class="preview-guide">
                    <h1 class="preview-title text-center">${title}</h1>
                    ${subtitle ? `<div class="preview-subtitle text-center mb-4">${subtitle}</div>` : ''}
                    <div class="preview-content ql-editor p-0">${content}</div>
                    ${linksHtml}
                </div>
            `;
        } else if (template === 'technical') {
            templateHtml = `
                <div class="preview-technical">
                    <h1 class="preview-title">> ${title}</h1>
                    <div class="preview-content">
                        ${subtitle ? `<div class="mb-3 fw-bold">${subtitle}</div>` : ''}
                        <div class="ql-editor p-0">${content}</div>
                    </div>
                    ${linksHtml}
                </div>
            `;
        } else if (template === 'story') {
            templateHtml = `
                <div class="preview-story">
                    <h1 class="preview-title">${title}</h1>
                    <div class="preview-content">
                        ${subtitle ? `<p class="lead mb-4">${subtitle}</p>` : ''}
                        <div class="ql-editor p-0">${content}</div>
                    </div>
                    ${linksHtml}
                </div>
            `;
        }

        previewArea.innerHTML = templateHtml;
    }

    // Update on changes
    postTitle.addEventListener('input', updatePreview);
    postSubtitle.addEventListener('input', updatePreview);
    quill.on('text-change', updatePreview);

    templateCards.forEach(card => {
        card.addEventListener('click', function() {
            templateCards.forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            selectedTemplateInput.value = this.dataset.template;
            updatePreview();
        });
    });

    addMoreLinks.addEventListener('click', function() {
        const newLinkRow = document.createElement('div');
        newLinkRow.className = 'input-group mb-2';
        newLinkRow.innerHTML = `
            <span class="input-group-text border-0 bg-light rounded-start-3"><i class="bi bi-link-45deg"></i></span>
            <input type="text" name="link_texts[]" class="form-control border-0 bg-light post-link-text" placeholder="Link Text">
            <input type="url" name="link_urls[]" class="form-control border-0 bg-light post-link-url" placeholder="URL (https://...)">
            <button type="button" class="btn btn-light border-0 remove-link"><i class="bi bi-trash text-danger"></i></button>
        `;
        linksContainer.appendChild(newLinkRow);

        // Add events to new inputs
        newLinkRow.querySelectorAll('input').forEach(input => input.addEventListener('input', updatePreview));
        newLinkRow.querySelector('.remove-link').addEventListener('click', function() {
            newLinkRow.remove();
            updatePreview();
        });
    });

    // Add remove functionality to existing links
    document.querySelectorAll('.remove-link').forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.input-group').remove();
            updatePreview();
        });
    });

    // Initial listener for the first link inputs
    document.querySelectorAll('.post-link-text, .post-link-url').forEach(input => {
        input.addEventListener('input', updatePreview);
    });

    // Initial preview update
    updatePreview();
});
</script>
