<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Top-k Popular Places</title>
    <link rel="stylesheet" href="../assets/css/normalize.css">
    <link rel="stylesheet" href="../assets/css/bootstrap-reboot.min.css">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/bootstrap-grid.min.css">
    <link rel="stylesheet" href="../assets/css/basicjet-common.css">
    <link rel="stylesheet" href="../assets/css/flatpickr.min.css">
    <script src="../assets/js/flatpickr.min.js"></script>
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
                        <a class="group-link" href="../admin.php">Bootstrap/Upload</a>
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
                        <a class="group-link active-link" href="">Popular Places</a>
                    </li>
                </ul>

            </nav>
        </div>

        <div class="row">
            <div class="col-12 footer">
                <a href="../logout.php">Logout</a>
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
                        <h4 class="card-title">Top-k Popular Places</h4>
                        <form id="topk-popularplaces-form" action="../php/topk/popularplaces/PopularPlacesController.php" method="post">
                            <label for="dateTime">Select a date and time</label>
                            <input id="dateTime" name="dateTime" type="text" class="form-control flatpickr flatpickr-input" required>
                            <label for="k">Select the result's range</label>
                            <select id="k" name="k" class="form-control">
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3" selected>3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="6">6</option>
                                <option value="7">7</option>
                                <option value="8">8</option>
                                <option value="9">9</option>
                                <option value="10">10</option>
                            </select>
                            <input type="submit" id="submit" name="submit" class="btn btn-primary" value="Get Top-k Popular Places"/>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    <?php
    if(isset($_SESSION['processed'])) {
        if (isset($_SESSION['error'])) {
            ?>
            <div class="row mt-5">
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
            <div class="row mt-5 mb-5">
                <div id="resultsTable" class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Last Search for: Top <?php print $_SESSION['k']?> Popular Places at <?php print ($_SESSION['searchTime'])->add(new DateInterval('PT1S'))->format('Y-m-d H:i:s') ?></h4>
                        </div>

                        <div class="card-body">
                            <?php
                            $results = $_SESSION['results'];
                            if(empty($results)) {
                                ?>
                                <h6 class="text-info">No Popular Places found</h6>
                                <?php
                            } else {
                                ?>
                                <table class="table table-hover table-striped">
                                    <thead>
                                    <tr>
                                        <th scope="col">Rank</th>
                                        <th scope="col">Semantic Place</th>
                                        <th scope="col">Number of People</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $k = $_SESSION['k'];
                                    $rank = 1;
                                    foreach ($results as $result => $places) {
                                        ksort($places);
                                        foreach ($places as $place) {
                                            ?>
                                            <tr>
                                                <td><?php print $rank ?></td>
                                                <td><?php print $place ?></td>
                                                <td><?php print $result ?></td>
                                            </tr>
                                            <?php
                                        }

                                        if(++$rank > $k) {
                                            break;
                                        }
                                    }
                                    ?>
                                    </tbody>
                                </table>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            unset($_SESSION['results']);
            unset($_SESSION['searchTime']);
            unset($_SESSION['k']);
        }
        unset($_SESSION['processed']);
    }
    ?>
    </div>

    <script>
        flatpickr("#dateTime", {
            allowInput: true,
            enableTime: true,
            enableSeconds: true,
            time_24hr: true
        })
    </script>
</body>
</html>