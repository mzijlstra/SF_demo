<?php
if (!isset($hl)) {
    $hl = -1;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Car List</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css" integrity="sha384-9gVQ4dYFwwWSjIDZnLEWnxCjeSWFphJiwGPXr1jddIhOegiu1FwO5qRGvFXOdJZ4" crossorigin="anonymous">
        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js" integrity="sha384-cs/chFZiN24E4KMATLdqdvsezGxaGsi4hLGOzlXwp5UZB1LY//20VyM2taTB4QvJ" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js" integrity="sha384-uefMccjFJAIv6A+rW+L4AHf99KvxDjWSu1z9VI8SKNVmz4sk7buKt/6v9KI65qnm" crossorigin="anonymous"></script>
        <style>
            .hl {
                background-color: yellow;
            }
        </style>
    </head>
    <body>

        <div class="container">
            <a href='.'><button>Home</button></a>
            
            <?php if (isset($action)) : ?>
                <h2>Successfully <?= $action ?> a car</h2>
            <?php endif; ?>
                
            <h1>Car List</h1>

            <table class="table table-striped table-hover table-responsive table-sm ">
                <thead>
                    <tr>
                        <th>Make</th>
                        <th>Model</th>
                        <th>Year</th>
                        <th>Color</th>
                    </tr>
                </thead>
                <?php foreach ($cars as $car): ?>
                    <tr class='<?= $hl == $car['id'] ? "hl" : "" ?>'>
                        <td><?= $car['make'] ?></td>
                        <td><?= $car['model'] ?></td>
                        <td><?= $car['year'] ?></td>
                        <td><?= $car['color'] ?></td>
                        <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                            <th><a href='car/<?= $car['id'] ?>' ><button>edit</button></a></th>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$cars) : ?>
                    <tr>
                        <td colspan='5'> No cars in the database yet! </td>
                    </tr>
                <?php endif; ?>
            </table>

            <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                <a href='car/add'><button>Add a car</button></a>
            <?php endif; ?>
        </div>

    </body>
</html>
