<div class="container">
    <h1 class="heading-center">Login</h1>
    <form method="post" action="./server/requests.php">
        <div class="col-6 offset-sm-3 margin-bottom-15">
            <label for="username">Username</label>
            <input type="text" name="username" class="form-control" id="username" placeholder="Enter Your Username">
        </div>

        <div class="col-6 offset-sm-3 margin-bottom-15">
            <label for="emailaddress">Email address</label>
            <input type="email" name="email" class="form-control" id="emailaddress" placeholder="Enter Your Email Address">
        </div>

        <div class="col-6 offset-sm-3 margin-bottom-15">
            <label for="password">Password</label>
            <input type="password" name="password" class="form-control" id="password" placeholder="Enter Your password">
        </div>

        <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf_token'] ?>">
        <div class="col-6 offset-sm-3 d-flex justify-content-between align-items-center">
            <button type="submit" name="login" class="btn btn-primary">Login</button>
            <a href="?signup=true" class="text-decoration-none">Don't have an account? Signup</a>
        </div>
    </form>
</div>
