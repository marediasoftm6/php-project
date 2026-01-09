<div class="row g-4">
    <!-- Main Content Column -->
    <div class="col-12 col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 style="font-weight: 800; color: var(--text);">Categories</h2>
        </div>

        <?php
        include("./common/db.php");
        $canManage = isset($_SESSION['user']['user_id']);
        
        if ($canManage && isset($_GET['edit-c'])) {
            $editId = (int)$_GET['edit-c'];
            $one = $conn->prepare("select id, name from category where id=?");
            $one->bind_param("i", $editId);
            $one->execute();
            $oneRes = $one->get_result();
            if ($oneRes->num_rows === 1) {
                $oneRow = $oneRes->fetch_assoc();
        ?>
                <div class="auth-container mb-5" style="max-width: 500px; margin: 0 auto 3rem;">
                    <h4 class="mb-3">Edit Category</h4>
                    <form method="post" action="./server/requests.php">
                        <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="category_id" value="<?php echo (int)$oneRow['id'] ?>">
                        <div class="form-group mb-3">
                            <label class="form-label" for="edit_cat_name">Category Name</label>
                            <input type="text" id="edit_cat_name" class="form-control" name="name" value="<?php echo htmlspecialchars($oneRow['name'], ENT_QUOTES, 'UTF-8') ?>" required>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" name="editCategory" class="btn btn-primary">Save Changes</button>
                            <a href="categories" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
        <?php
            }
        }
        ?>

        <div class="row row-cols-1 row-cols-md-2 g-4">
            <?php
            $stmt = $conn->prepare("select c.id, c.name, c.slug, count(q.id) as cnt from category c left join questions q on q.category_id=c.id group by c.id, c.name, c.slug order by c.name asc");
            $stmt->execute();
            $result = $stmt->get_result();
            
            foreach ($result as $row) {
                $name = htmlspecialchars(ucfirst($row["name"]), ENT_QUOTES, 'UTF-8');
                $id = (int)$row["id"];
                $slug = $row["slug"];
                $cnt = (int)$row["cnt"];
                $deleteLink = "./server/requests.php?deleteCategory=" . $id . "&csrf=" . urlencode($_SESSION['csrf_token']);
                $editLink = "categories?edit-c=$id";
                ?>
                <div class="col">
                    <div class="profile-sidebar-card h-100 d-flex flex-column justify-content-between p-4">
                        <div>
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h4 class="mb-0">
                                    <a href="<?php echo $slug; ?>" class="text-decoration-none" style="color: var(--text); font-weight: 700;">
                                        <?php echo $name; ?>
                                    </a>
                                </h4>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-link text-muted p-0" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical fs-5"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                        <?php if ($canManage) { ?>
                                            <li><a class="dropdown-item py-2" href="<?php echo $editLink; ?>">
                                                <i class="bi bi-pencil me-2"></i>Edit
                                            </a></li>
                                            <li><a class="dropdown-item py-2 text-danger" href="<?php echo $deleteLink; ?>" onclick="return confirm('Delete this category?')">
                                                <i class="bi bi-trash me-2"></i>Delete
                                            </a></li>
                                        <?php } else { ?>
                                            <li><a class="dropdown-item py-2" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#loginModal">
                                                <i class="bi bi-box-arrow-in-right me-2"></i>Login to Edit
                                            </a></li>
                                        <?php } ?>
                                    </ul>
                                </div>
                            </div>
                            <p class="text-muted small mb-4">Discover questions and share knowledge in the <?php echo $name; ?> community.</p>
                        </div>
                        <div class="mt-auto d-flex align-items-center justify-content-between">
                            <span class="badge-category"><?php echo $cnt; ?> Questions</span>
                            <a href="<?php echo $slug; ?>" class="btn btn-sm btn-outline-primary py-1 px-3">Explore</a>
                        </div>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
    </div>

    <!-- Sidebar Column -->
    <div class="col-12 col-lg-4">
        <?php include('sidebar.php'); ?>
    </div>
</div>