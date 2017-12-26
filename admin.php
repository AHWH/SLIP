<?php
    require 'php/data/model/FileRowError.php';
?>

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

        <div class="row">
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

        <?php
            if(isset($_SESSION['processAdmin'])) {
                if(isset($_SESSION['error'])) {
        ?>
        <div class="row mt-5 mb-5">
            <div id="amtinserted" class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="container align-items-center">
                            <div class="row justify-content-around">
                                <div class="col">
                                    <h6 class="text-danger"><?php print $_SESSION['error'] ?></h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
                    unset($_SESSION['error']);
                } else {
        ?>
        <div class="row mt-5">
            <div id="amtinserted" class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="container align-items-center">
                            <div class="row justify-content-around">
                                <?php
                                    if(isset($_SESSION['demographics.csv'])) {
                                        $count = $_SESSION['demographics.csv'];
                                ?>
                                <div class="col-4">
                                    <h5>Demographics.csv</h5>
                                    <h2><?php print $count ?></h2>
                                    <h6 class="text-muted">USERS INSERTED</h6>
                                </div>
                                <?php
                                        unset($_SESSION['demographics.csv']);
                                    }

                                    if(isset($_SESSION['location.csv'])) {
                                        $count = $_SESSION['location.csv'];
                                ?>
                                <div class="col-4">
                                    <h5>Location.csv</h5>
                                    <h2><?php print $count ?></h2>
                                    <h6 class="text-muted">LOCATION HISTORIES INSERTED</h6>
                                </div>
                                <?php
                                        unset($_SESSION['location.csv']);
                                    }

                                    if(isset($_SESSION['location-lookup.csv'])) {
                                        $count = $_SESSION['location-lookup.csv'];
                                ?>
                                <div class="col-4">
                                    <h5>Location-lookup.csv</h5>
                                    <h2><?php print $count ?></h2>
                                    <h6 class="text-muted">LOCATIONS INSERTED</h6>
                                </div>
                                <?php
                                        unset($_SESSION['location-lookup.csv']);
                                    }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-5">
            <div id="errors" role="tablist" class="col-12">
                <?php
                    $errorTableShown = FALSE;

                    if(isset($_SESSION['userErrors'])) {
                        $errors = $_SESSION['userErrors'];
                        if(!empty($errors)) {
                            //If demographics.csv has errors show card
                ?>
                <div class="card">
                    <div class="card-header" role="tab" id="demoErrorHeading">
                        <h4>
                            <a data-toggle="collapse" href="#demoError" aria-expanded="true" aria-controls="demoError">Demographics.csv</a>
                        </h4>
                    </div>
                    <div id="demoError" class="collapse <?php $errorTableShown ? print '': print 'show' ?>" role="tabpanel" aria-labelledby="demoErrorHeading"
                         data-parent="#errors">
                        <div class="card-body">
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th scope="col">No.</th>
                                        <th scope="col">Row No.</th>
                                        <th scope="col">Reason</th>
                                        <th scope="col">Cause</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        $count = 1;
                                        foreach ($errors as $error) {
                                            $individualErrors = $error->getErrors();

                                            foreach ($individualErrors as $reason => $cause) {
                                    ?>
                                    <tr>
                                        <td><?php print $count ?></td>
                                        <td><?php print $error->getLineNo() ?></td>
                                        <td><?php print $reason ?></td>
                                        <td><?php print $cause ?></td>
                                    </tr>
                                    <?php
                                            }
                                            $count++;
                                        }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php
                            $errorTableShown = TRUE;
                            unset($_SESSION['userErrors']);
                        }
                    }

                    if(isset($_SESSION['locationErrors'])) {
                        $errors = $_SESSION['locationErrors'];
                        if(!empty($errors)) {
                ?>
                <div class="card">
                    <div class="card-header" role="tab" id="locErrorHeading">
                        <h4>
                            <a data-toggle="collapse" href="#locError" aria-expanded="false" aria-controls="locError">Location-lookup.csv</a>
                        </h4>
                    </div>
                    <div id="locError" class="collapse <?php $errorTableShown ? print '': print 'show' ?>" role="tabpanel" aria-labelledby="locErrorHeading"
                         data-parent="#errors">
                        <div class="card-body">
                            <table class="table table-hover table-striped">
                                <thead>
                                <tr>
                                    <th scope="col">No.</th>
                                    <th scope="col">Row No.</th>
                                    <th scope="col">Reason</th>
                                    <th scope="col">Cause</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                    $count = 1;
                                    foreach ($errors as $error) {
                                        $individualErrors = $error->getErrors();

                                        foreach ($individualErrors as $reason => $cause) {
                                ?>
                                    <tr>
                                        <td><?php print $count ?></td>
                                        <td><?php print $error->getLineNo() ?></td>
                                        <td><?php print $reason ?></td>
                                        <td><?php print $cause ?></td>
                                    </tr>
                                <?php
                                    }
                                    $count++;
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php
                            $errorTableShown = TRUE;
                            unset($_SESSION['locErrors']);
                        }
                    }

                    if(isset($_SESSION['locHistErrors'])) {
                        $errors = $_SESSION['locHistErrors'];
                        if(!empty($errors)) {
                ?>
                <div class="card">
                    <div class="card-header" role="tab" id="locHistErrorHeading">
                        <h4>
                            <a data-toggle="collapse" href="#locHistError" aria-expanded="false" aria-controls="locHistError">Location.csv</a>
                        </h4>
                    </div>
                    <div id="locHistError" class="collapse <?php $errorTableShown ? print '': print 'show' ?>" role="tabpanel" aria-labelledby="locHistErrorHeading"
                         data-parent="#errors">
                        <div class="card-body">
                            <table class="table table-hover table-striped">
                                <thead>
                                <tr>
                                    <th scope="col">No.</th>
                                    <th scope="col">Row No.</th>
                                    <th scope="col">Reason</th>
                                    <th scope="col">Cause</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                    $count = 1;
                                    foreach ($errors as $error) {
                                        $individualErrors = $error->getErrors();

                                        foreach ($individualErrors as $reason => $cause) {
                                ?>
                                    <tr>
                                        <td><?php print $count ?></td>
                                        <td><?php print $error->getLineNo() ?></td>
                                        <td><?php print $reason ?></td>
                                        <td><?php print $cause ?></td>
                                    </tr>
                                <?php
                                        }
                                        $count++;
                                    }
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php
                            $errorTableShown = TRUE;
                            unset($_SESSION['locHistErrors']);
                        }
                    }
                ?>
            </div>
        </div>
    </div>

    <?php
                unset($_SESSION['processAdmin']);
            }
        }
    ?>
    <script src="assets/js/jquery.3.2.1.min.js"></script>
    <script src="assets/js/popper.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
</body>
</html>