<?php if (!isset($hl)) { $hl = -1; } ?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Car List</title>
        <style>
            h2 {
              margin-left: 2em;  
            }
            table {
                border-collapse: collapse;
            }
            tr.hl {
                background-color: yellow;
            }
            td {
                border: 1px solid black;
            }
        </style>
    </head>
    <body>
        <?php if(isset($action)) : ?>
            <h2>Successfully <?= $action ?> a car</h2>
        <?php endif; ?>

            <a href='.'><button>Home</button></a>
        <h1>Car List</h1>
        
        <table>
            <tr>
                <th>Make</th>
                <th>Model</th>
                <th>Year</th>
                <th>Color</th>
            </tr>
        <?php foreach ($cars as $car): ?>
            <tr class='<?= $hl == $car['id'] ? "hl" : "" ?>'>
                <td><?= $car['make'] ?></td>
                <td><?= $car['model'] ?></td>
                <td><?= $car['year'] ?></td>
                <td><?= $car['color'] ?></td>
                <?php if (isset($_SESSION['admin'])): ?>
                <th><a href='car/<?= $car['id']?>' ><button>edit</button></a></th>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        <?php if (!$cars) : ?>
            <tr>
                <td colspan='5'> No cars in the database yet! </td>
            </tr>
        <?php endif; ?>
        </table>
        
        <?php if (isset($_SESSION['admin'])): ?>
        <a href='car/add'><button>Add a car</button></a>
        <?php endif; ?>
     
    </body>
</html>
