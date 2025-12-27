<nav class="navbar navbar-expand-lg bg-body-tertiary navbar-light">
  <div class="container-fluid">
    <a class="navbar-brand" href="./">
      <img src="./public/logo.png" alt="Quesiono World Logo" class="websiteLogo">
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0 navbar-text d-flex flex-lg-row gap-5">
        <li class="nav-item">
          <a class="nav-link" href="?about=true">About Us</a>
        </li>
        <?php
        if (isset($_SESSION['user']['username'])) { ?>
          <li class="nav-item">
            <a class="nav-link" href="?askQuestion=true">Ask Here</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="?u-id=<?php echo $_SESSION['user']['user_id'] ?>">My QnA</a>
          </li>
        <?php }
        ?>
        <?php
        if (!isset($_SESSION['user']['username'])) { ?>
          <li class="nav-item">
            <a class="nav-link" href="?login=true">Login</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="?signup=true">Signup</a>
          </li>
        <?php }
        ?>
        <li class="nav-item">
          <a class="nav-link" href="?categories=true">
            Categories
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="?latest=true">
            Latest
          </a>
        </li>
      </ul>
      <div class="d-flex align-items-center ms-auto gap-2">
        <div class="dropdown user-dropdown">
          <button class="btn user-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="User Menu">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
              <circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2"/>
              <path d="M4 21c0-4 4-7 8-7s8 3 8 7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <?php if (isset($_SESSION['user']['username'])) { ?>
              <li><a class="dropdown-item" href="?profile=true">Profile</a></li>
              <li><a class="dropdown-item" href="?profile_edit=true">Edit</a></li>
              <li><a class="dropdown-item" href="?settings=true">Settings</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="./server/requests.php?logout=true">Logout</a></li>
            <?php } else { ?>
              <li><a class="dropdown-item" href="?login=true">Login</a></li>
              <li><a class="dropdown-item" href="?signup=true">Signup</a></li>
            <?php } ?>
          </ul>
        </div>
        <form class="search-wrap" action="" role="search">
          <span class="search-icon" aria-hidden="true">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
              <path d="M21 21l-4.3-4.3m1.8-5.2a7 7 0 11-14 0 7 7 0 0114 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </span>
          <input class="form-control search-input" name="search" type="search" placeholder="Search here!"/>
        </form>
      </div>
    </div>
  </div>
</nav>
