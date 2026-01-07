<?php
if (!isset($_SESSION['user']['username'])) {
    echo "<script>window.location.href='?login=true';</script>";
    exit();
}
?>

<div class="post-page-container mb-5">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 p-4" style="background: var(--gradient); color: var(--white);">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="display-6 fw-bold mb-2">Create a Rich Post</h1>
                        <p class="lead mb-0 opacity-90">Share your knowledge with structured templates, links, and more.</p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <a href="index.php" class="btn btn-outline-light rounded-pill px-4">
                            <i class="bi bi-arrow-left me-2"></i>Back to Home
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
                        <div class="template-card active p-3 rounded-4 border text-center cursor-pointer transition-all" data-template="article">
                            <i class="bi bi-journal-text fs-2 mb-2 d-block text-primary"></i>
                            <span class="fw-bold">Classic Article</span>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="template-card p-3 rounded-4 border text-center cursor-pointer transition-all" data-template="guide">
                            <i class="bi bi-list-ol fs-2 mb-2 d-block text-primary"></i>
                            <span class="fw-bold">Step-by-Step Guide</span>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="template-card p-3 rounded-4 border text-center cursor-pointer transition-all" data-template="technical">
                            <i class="bi bi-code-square fs-2 mb-2 d-block text-primary"></i>
                            <span class="fw-bold">Technical Post</span>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="template-card p-3 rounded-4 border text-center cursor-pointer transition-all" data-template="story">
                            <i class="bi bi-chat-quote fs-2 mb-2 d-block text-primary"></i>
                            <span class="fw-bold">Visual Story</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Editor Column -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                <form id="postForm" method="post" action="./server/requests.php">
                    <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="template" id="selectedTemplate" value="article">
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold" for="category">Category</label>
                        <p class="small text-muted mb-2">Select a category to help others find your post.</p>
                        <?php include("category.php"); ?>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold" for="postTitle">Title</label>
                        <input type="text" id="postTitle" class="form-control form-control-lg border-0 bg-light rounded-3" name="title" placeholder="Enter a catchy title..." required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold" for="postSubtitle">Subtitle (Optional)</label>
                        <input type="text" id="postSubtitle" class="form-control border-0 bg-light rounded-3" name="subtitle" placeholder="Add a brief overview...">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold" for="postContent">Content</label>
                        <p class="small text-muted mb-2">Use double newlines for paragraphs. You can use simple HTML like &lt;b&gt;, &lt;i&gt;, or &lt;a&gt; for links.</p>
                        <textarea id="postContent" class="form-control border-0 bg-light rounded-3" name="content" rows="12" placeholder="Write your amazing content here..." required></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Add Links</label>
                        <div id="linksContainer">
                            <div class="input-group mb-2">
                                <span class="input-group-text border-0 bg-light rounded-start-3"><i class="bi bi-link-45deg"></i></span>
                                <input type="text" name="link_texts[]" class="form-control border-0 bg-light post-link-text" placeholder="Link Text">
                                <input type="url" name="link_urls[]" class="form-control border-0 bg-light post-link-url" placeholder="URL (https://...)">
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill mt-2" id="addMoreLinks">
                            <i class="bi bi-plus-circle me-1"></i> Add Another Link
                        </button>
                    </div>

                    <div class="d-grid pt-3">
                        <button type="submit" name="createRichPost" class="btn btn-primary btn-lg rounded-pill fw-bold py-3 shadow-sm">
                            Publish Your Post
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Preview Column -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 p-0 overflow-hidden h-100 sticky-lg-top" style="top: 20px; max-height: calc(100vh - 40px);">
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
                    <div class="text-center text-muted py-5 mt-5">
                        <i class="bi bi-pencil-square display-1 opacity-25 mb-4 d-block"></i>
                        <h3>Your preview will appear here</h3>
                        <p>Start typing in the editor to see your post come to life.</p>
                    </div>
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
    const postTitle = document.getElementById('postTitle');
    const postSubtitle = document.getElementById('postSubtitle');
    const postContent = document.getElementById('postContent');
    const previewArea = document.getElementById('previewArea');
    const templateCards = document.querySelectorAll('.template-card');
    const selectedTemplateInput = document.getElementById('selectedTemplate');
    const addMoreLinks = document.getElementById('addMoreLinks');
    const linksContainer = document.getElementById('linksContainer');

    function updatePreview() {
        const title = postTitle.value || 'Your Title Here';
        const subtitle = postSubtitle.value || '';
        const content = postContent.value || 'Start writing your content...';
        const template = selectedTemplateInput.value;

        // Get links
        let linksHtml = '';
        const linkTexts = document.querySelectorAll('.post-link-text');
        const linkUrls = document.querySelectorAll('.post-link-url');
        
        linkTexts.forEach((text, index) => {
            const url = linkUrls[index].value;
            if (text.value && url) {
                linksHtml += `<a href="${url}" target="_blank" class="badge rounded-pill bg-primary me-2 mb-2 text-white"><i class="bi bi-link-45deg me-1"></i>${text.value}</a>`;
            }
        });

        const formattedContent = content.replace(/\n\n/g, '</p><p>').replace(/\n/g, '<br>');

        let previewHtml = '';
        switch(template) {
            case 'article':
                previewHtml = `
                    <div class="preview-article">
                        <h1 class="preview-title">${title}</h1>
                        ${subtitle ? `<p class="preview-subtitle">${subtitle}</p>` : ''}
                        <div class="preview-content"><p>${formattedContent}</p></div>
                        <div class="preview-links mt-4 d-flex flex-wrap">${linksHtml}</div>
                    </div>`;
                break;
            case 'guide':
                previewHtml = `
                    <div class="preview-guide">
                        <h1 class="preview-title">${title}</h1>
                        ${subtitle ? `<div class="preview-subtitle fw-bold"><i class="bi bi-info-circle me-2 text-primary"></i>${subtitle}</div>` : ''}
                        <div class="preview-content"><p>${formattedContent}</p></div>
                        <div class="preview-links mt-4 d-flex flex-wrap">${linksHtml}</div>
                    </div>`;
                break;
            case 'technical':
                previewHtml = `
                    <div class="preview-technical">
                        <h1 class="preview-title">> ${title}</h1>
                        ${subtitle ? `<div class="mb-3 text-muted fw-bold small uppercase">${subtitle}</div>` : ''}
                        <div class="preview-content">${formattedContent}</div>
                        <div class="preview-links mt-4 d-flex flex-wrap">${linksHtml}</div>
                    </div>`;
                break;
            case 'story':
                previewHtml = `
                    <div class="preview-story">
                        <h1 class="preview-title">${title}</h1>
                        <div class="preview-content">
                            ${subtitle ? `<p class="mb-5 text-primary fw-bold">${subtitle}</p>` : ''}
                            <p>${formattedContent}</p>
                        </div>
                        <div class="preview-links mt-5 d-flex flex-wrap">${linksHtml}</div>
                    </div>`;
                break;
        }

        previewArea.innerHTML = previewHtml;
    }

    // Event Listeners
    postTitle.addEventListener('input', updatePreview);
    postSubtitle.addEventListener('input', updatePreview);
    postContent.addEventListener('input', updatePreview);

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

    // Initial listener for the first link inputs
    document.querySelectorAll('.post-link-text, .post-link-url').forEach(input => {
        input.addEventListener('input', updatePreview);
    });

    // Initial preview update if any values exist
    if(postTitle.value || postContent.value) updatePreview();
});
</script>