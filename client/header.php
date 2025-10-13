<nav class="navbar navbar-expand-lg bg-body-tertiary">
  <div class="container-fluid">
    <a class="navbar-brand" href="./">
      <img src="./public/logo.jpg" alt="Discuss World Logo" class="websiteLogo">
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav mx-auto mb-2 mb-lg-0 navbar-text d-flex flex-row gap-5">
        <li class="nav-item">
          <a class="nav-link" href="#">About US</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#">
            Categories
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#">My QnA</a>
        </li>
        <?php
        if (isset($_SESSION['user']['username'])) { ?>
          <li class="nav-item">
            <a class="nav-link" href="./server/requests.php?logout=true">Logout</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="?askQuestion=true">Ask Here</a>
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
      </ul>
    </div>
  </div>
</nav>