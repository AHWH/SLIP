<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SLOCA (Project SLIP)</title>
    <link rel="stylesheet" href="assets/css/normalize.css">
    <link rel="stylesheet" href="assets/css/bootstrap-reboot.min.css">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/bootstrap-grid.min.css">
    <link rel="stylesheet" href="assets/css/basicjet-login.css">
</head>
<body>
    <div class="jumbotron jumbotron-fluid">
        <div id="LoginContainer" class="container">
            <div id="titlerow" class="row center">
                <div class="col">
                    <h1>Sign in to SLOCA</h1>
                </div>
            </div>
            <div id="subtitlerow" class="row center">
                <div class="col">
                    <h5 class="text-muted">Enter your details below</h5>
                </div>
            </div>
            <div id="loginform">
                <form action="php/login/LoginHandler.php" method="post">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" name="username" id="username" class="form-control" placeholder="abc.xyz.2014" required/>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password" class="form-control" placeholder="Enter your Password" required/>
                    </div>
                    <?php
                        session_start();
                        if(isset($_SESSION['invalidLogin'])) {
                    ?>
                    <small class="text-danger">Invalid Login ID/Password. Please try again.</small>
                    <?php
                            unset($_SESSION['invalidLogin']);
                        }
                    ?>
                    <button class="btn btn-primary loginbtn" type="submit">Login</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>