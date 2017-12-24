<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SLOCA</title>
    <link rel="stylesheet" href="assets/css/normalize.css">
    <link rel="stylesheet" href="assets/css/bootstrap-reboot.min.css">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/bootstrap-grid.min.css">
    <link rel="stylesheet" href="assets/css/basicjet-common.css">
</head>
<body>
<div id="navbar" class="container-fluid">
    <div class="row align-items-center">
        <div class="col-12 header">
            <h2>SLOCA</h2>
        </div>
    </div>

    <div class="row">
        <nav class="navbar">
            <h3 class="leader-link">Admin</h3>
            <br/>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="group-link" href="admin.php">Bootstrap/Upload</a>
                </li>
            </ul>
            <h3 class="leader-link">Top-K</h3>
            <br/>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="group-link" href="">Companion</a>
                </li>
                <li class="nav-item">
                    <a class="group-link" href="">Next Place</a>
                </li>
                <li class="nav-item">
                    <a class="group-link" href="topk/popularplace.php">Popular Places</a>
                </li>
            </ul>

        </nav>
    </div>

    <div class="row">
        <div class="col-12 footer">
            <a href="logout.php">Logout</a>
        </div>
    </div>
</div>

<div id="content" class="container-fluid">
    <div class="row align-items-end">
        <div class="col-4 header">
            <h3>Welcome
                <?php
                    session_start();
                    echo $_SESSION['username'];
                ?>
            </h3>
        </div>
    </div>
</div>
</body>
</html>