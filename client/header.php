<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once(__DIR__ . "/../common/db.php");
?>
<nav class="navbar navbar-expand-lg sticky-top navbar-light shadow-sm">
  <div class="container">
    <a class="navbar-brand" href="./">
      <img src="./public/transparent-logo.png" alt="Quesiono World Logo" class="websiteLogo">
    </a>
    <button class="navbar-toggler border-0 ms-auto p-1 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0 navbar-text d-flex flex-lg-row gap-lg-4 align-items-lg-center">
        <?php
        $current_page = basename($_SERVER['PHP_SELF']);
        $is_home = !isset($_GET['about']) && !isset($_GET['askQuestion']) && !isset($_GET['categories']) && !isset($_GET['latest']) && !isset($_GET['profile']) && !isset($_GET['u-id']) && !isset($_GET['q-id']);
        ?>
        <li class="nav-item">
          <a class="nav-link <?php echo isset($_GET['categories']) ? 'active' : ''; ?>" href="categories">Categories</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo isset($_GET['latest']) ? 'active' : ''; ?>" href="latest">Latest</a>
        </li>
        <?php if (isset($_SESSION['user']['username'])) { ?>
          <li class="nav-item">
            <a class="nav-link <?php echo isset($_GET['askQuestion']) ? 'active' : ''; ?>" href="ask-question">Ask Question</a>
          </li>
        <?php } ?>
        <li class="nav-item dropdown explore-dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Explore
          </a>
          <ul class="dropdown-menu shadow-lg border-0 mt-2">
            <li><a class="dropdown-item py-2 <?php echo isset($_GET['about']) ? 'active' : ''; ?>" href="about">
              About
            </a></li>
            <?php if (isset($_SESSION['user']['username'])) { ?>
              <li><a class="dropdown-item py-2 <?php echo isset($_GET['u-id']) && $_GET['u-id'] == $_SESSION['user']['user_id'] ? 'active' : ''; ?>" href="<?php echo $_SESSION['user']['username'] ?>">
                My Q&A
              </a></li>
            <?php } ?>
          </ul>
        </li>

        <li class="nav-item dropdown explore-dropdown">
          <a class="nav-link dropdown-toggle <?php echo (isset($_GET['post']) || isset($_GET['all-posts']) || isset($_GET['my-posts'])) ? 'active' : ''; ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Posts
          </a>
          <ul class="dropdown-menu shadow-lg border-0 mt-2">
            <?php if (isset($_SESSION['user']['username'])) { ?>
              <li><a class="dropdown-item py-2 <?php echo isset($_GET['post']) ? 'active' : ''; ?>" href="create-post">
                Add Post
              </a></li>
            <?php } ?>
            <li><a class="dropdown-item py-2 <?php echo isset($_GET['all-posts']) ? 'active' : ''; ?>" href="posts">
              All Posts
            </a></li>
            <?php if (isset($_SESSION['user']['username'])) { ?>
              <li><a class="dropdown-item py-2 <?php echo isset($_GET['my-posts']) ? 'active' : ''; ?>" href="my-posts">
                My Posts
              </a></li>
            <?php } ?>
          </ul>
        </li>
      </ul>
      <div class="d-flex align-items-center gap-3">
        <form class="search-wrap" action="" role="search">
          <span class="search-icon" aria-hidden="true">
          </span>
          <input class="form-control search-input" name="search" type="search" placeholder="Search questions..."/>
        </form>

        <?php if (isset($_SESSION['user']['username'])): 
          $uid = $_SESSION['user']['user_id'];
          $notifCountStmt = $conn->prepare("SELECT COUNT(*) as cnt FROM notifications WHERE user_id = ? AND is_read = 0");
          $notifCountStmt->bind_param("i", $uid);
          $notifCountStmt->execute();
          $unreadCount = $notifCountStmt->get_result()->fetch_assoc()['cnt'] ?? 0;
        ?>
          <div class="dropdown notification-bell-wrap me-1">
            <div class="p-2 text-muted rounded-circle hover-bg-light" data-bs-toggle="dropdown" aria-expanded="false" style="transition: all 0.2s; cursor: pointer;">
              <i class="bi bi-bell fs-5"></i>
              <?php if ($unreadCount > 0): ?>
                <span class="notification-badge"><?php echo $unreadCount; ?></span>
              <?php endif; ?>
            </div>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2 p-0" style="width: 320px; max-height: 480px; overflow: hidden; border-radius: 12px;">
              <li class="px-3 py-3 border-bottom d-flex justify-content-between align-items-center bg-white sticky-top">
                <span class="fw-bold text-dark">Notifications</span>
                <?php if ($unreadCount > 0): ?>
                  <a href="./server/requests.php?markRead=all" class="text-decoration-none small fw-semibold">Mark all as read</a>
                <?php endif; ?>
              </li>
              <div style="max-height: 350px; overflow-y: auto;">
                <?php
                $notifStmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY id DESC LIMIT 15");
                $notifStmt->bind_param("i", $uid);
                $notifStmt->execute();
                $notifs = $notifStmt->get_result();
                if ($notifs->num_rows === 0): ?>
                  <li class="px-3 py-5 text-center text-muted">
                    <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                      <i class="bi bi-bell-slash fs-3"></i>
                    </div>
                    <p class="small mb-0 fw-medium">No notifications yet</p>
                    <p class="text-muted" style="font-size: 11px;">We'll notify you when something happens.</p>
                  </li>
                <?php else: 
                  while ($n = $notifs->fetch_assoc()): 
                    $icon = 'bi-info-circle';
                    $iconColor = 'text-primary';
                    if ($n['type'] === 'follow') { $icon = 'bi-person-plus'; $iconColor = 'text-success'; }
                  ?>
                    <li class="px-3 py-3 border-bottom notification-item <?php echo $n['is_read'] ? '' : 'unread'; ?>" style="transition: all 0.2s;">
                      <div class="d-flex gap-3">
                        <div class="notif-icon-wrap <?php echo $iconColor; ?> bg-light rounded-circle d-flex align-items-center justify-content-center" style="min-width: 35px; height: 35px;">
                          <i class="bi <?php echo $icon; ?> fs-6"></i>
                        </div>
                        <div class="flex-grow-1">
                          <div class="small text-dark mb-1"><?php echo $n['message']; ?></div>
                          <div class="text-muted d-flex align-items-center justify-content-between" style="font-size: 11px;">
                            <span><?php echo date('M d, H:i', strtotime($n['created_at'])); ?></span>
                            <?php if (!$n['is_read']): ?>
                              <span class="unread-dot"></span>
                            <?php endif; ?>
                          </div>
                        </div>
                      </div>
                    </li>
                  <?php endwhile;
                endif; ?>
              </div>
            </ul>
          </div>
        <?php endif; ?>

        <div class="dropdown user-dropdown">
          <button class="btn user-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <div class="user-avatar-small">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2"/>
                <path d="M4 21c0-4 4-7 8-7s8 3 8 7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              </svg>
            </div>
            <span class="d-none d-sm-inline"><?php echo isset($_SESSION['user']['username']) ? htmlspecialchars($_SESSION['user']['username']) : 'Account'; ?></span>
          </button>
          <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2">
            <?php if (isset($_SESSION['user']['username'])) { ?>
              <li><a class="dropdown-item py-2" href="<?php echo $_SESSION['user']['username'] ?>">
                Profile
              </a></li>
              <li><a class="dropdown-item py-2" href="profile-edit">
                Edit Profile
              </a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item py-2 text-danger" href="./server/requests.php?logout=true">
                Logout
              </a></li>
            <?php } else { ?>
              <li><a class="dropdown-item py-2" href="login">Login</a></li>
              <li><a class="dropdown-item py-2" href="signup">Signup</a></li>
            <?php } ?>
          </ul>
        </div>
      </div>
    </div>
  </div>
</nav>
