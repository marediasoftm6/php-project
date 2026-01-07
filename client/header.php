<nav class="navbar navbar-expand-lg sticky-top navbar-light shadow-sm">
  <div class="container">
    <a class="navbar-brand" href="./">
      <img src="./public/transparent-logo.png" alt="Quesiono World Logo" class="websiteLogo">
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav mb-2 mb-lg-0 navbar-text d-flex flex-lg-row gap-lg-4 align-items-center">
        <?php
        $current_page = basename($_SERVER['PHP_SELF']);
        $is_home = !isset($_GET['about']) && !isset($_GET['askQuestion']) && !isset($_GET['categories']) && !isset($_GET['latest']) && !isset($_GET['profile']) && !isset($_GET['u-id']) && !isset($_GET['q-id']);
        ?>
        <li class="nav-item">
          <a class="nav-link <?php echo isset($_GET['categories']) ? 'active' : ''; ?>" href="?categories=true">Categories</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo isset($_GET['latest']) ? 'active' : ''; ?>" href="?latest=true">Latest</a>
        </li>
        <?php if (isset($_SESSION['user']['username'])) { ?>
          <li class="nav-item">
            <a class="nav-link <?php echo isset($_GET['askQuestion']) ? 'active' : ''; ?>" href="?askQuestion=true">Ask Question</a>
          </li>
        <?php } ?>
        <li class="nav-item dropdown explore-dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Explore
          </a>
          <ul class="dropdown-menu shadow-lg border-0 mt-2">
            <li><a class="dropdown-item py-2 <?php echo isset($_GET['about']) ? 'active' : ''; ?>" href="?about=true">
              <i class="bi bi-info-circle me-2"></i>About
            </a></li>
            <?php if (isset($_SESSION['user']['username'])) { ?>
              <li><a class="dropdown-item py-2 <?php echo isset($_GET['u-id']) && $_GET['u-id'] == $_SESSION['user']['user_id'] ? 'active' : ''; ?>" href="?u-id=<?php echo $_SESSION['user']['user_id'] ?>">
                <i class="bi bi-chat-dots me-2"></i>My Q&A
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
              <li><a class="dropdown-item py-2 <?php echo isset($_GET['post']) ? 'active' : ''; ?>" href="?post=true">
                <i class="bi bi-plus-circle me-2"></i>Add Post
              </a></li>
            <?php } ?>
            <li><a class="dropdown-item py-2 <?php echo isset($_GET['all-posts']) ? 'active' : ''; ?>" href="?all-posts=true">
              <i class="bi bi-grid-3x3-gap me-2"></i>All Posts
            </a></li>
            <?php if (isset($_SESSION['user']['username'])) { ?>
              <li><a class="dropdown-item py-2 <?php echo isset($_GET['my-posts']) ? 'active' : ''; ?>" href="?my-posts=true">
                <i class="bi bi-person-badge me-2"></i>My Posts
              </a></li>
            <?php } ?>
          </ul>
        </li>
      </ul>
      <div class="d-flex align-items-center gap-3">
        <form class="search-wrap d-none d-lg-block" action="" role="search">
          <span class="search-icon" aria-hidden="true">
          </span>
          <input class="form-control search-input" name="search" type="search" placeholder="Search questions..."/>
        </form>
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
              <li class="px-3 py-2 d-lg-none border-bottom mb-2">
                <form class="search-wrap w-100" action="" role="search">
                  <input class="form-control search-input w-100" name="search" type="search" placeholder="Search..."/>
                </form>
              </li>
              <li><a class="dropdown-item py-2" href="?profile=true">
                <i class="bi bi-person me-2"></i>Profile
              </a></li>
              <li><a class="dropdown-item py-2" href="?profile_edit=true">
                <i class="bi bi-pencil me-2"></i>Edit Profile
              </a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item py-2 text-danger" href="./server/requests.php?logout=true">
                <i class="bi bi-box-arrow-right me-2"></i>Logout
              </a></li>
            <?php } else { ?>
              <li><a class="dropdown-item py-2" href="?login=true">Login</a></li>
              <li><a class="dropdown-item py-2" href="?signup=true">Signup</a></li>
            <?php } ?>
          </ul>
        </div>
      </div>
    </div>
  </div>
</nav>
