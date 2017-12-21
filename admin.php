<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Tools</title>
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
                        <a class="group-link active-link" href="#">Bootstrap/Upload</a>
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
                        <a class="group-link" href="">Popular Places</a>
                    </li>
                </ul>

            </nav>
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

            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Bootstrap/Upload</h4>
                        <form id="bootstrap-form" action="php/admin/AdminController.php" method="post" enctype="multipart/form-data">
                            <label for="file">Choose a file to upload</label>
                            <small class="text-muted">Psst...We only accepts .zip file. Don't try to trick the system</small>
                            <input type="file" class="form-control-file" id="file" name="file" accept="application/zip" required/>
                            <div class="row">
                                <div class="col-4">
                                    <input type="submit" name="submit" class="btn btn-primary" value="Bootstrap"/>
                                    <input type="submit" name="submit" class="btn btn-info" value="Upload"/>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>