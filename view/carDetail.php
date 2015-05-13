<!DOCTYPE html>
<?php
$action = "Update";
if (!isset($car)) {
    $car = false;
    $action = "Add";
}
?>
<html>
    <head>
        <meta charset="UTF-8">
        <title><?= $action ?> a Car</title>
    </head>
    <body>
        <h1><?= $action ?> a Car:</h1>
        <form action='<?= $car ? $car['id'] : "add" ?>' method='post'>
            <span class='tag'></span>
            <input type='text' placeholder="Car Maker" name='make' 
                   value='<?= $car ? $car['make'] : "" ?>'/><br />    
            <span class='tag'></span>
            <input type='text' placeholder="Car Model" name='model' 
                   value='<?= $car ? $car['model'] : "" ?>'/><br />                       
            <span class='tag'></span>
            <input type='text' placeholder="Year Made" name='year' 
                   value='<?= $car ? $car['year'] : "" ?>'/><br />                       
            <span class='tag'></span>
            <input type='text' placeholder="Car Color" name='color' 
                   value='<?= $car ? $car['color'] : "" ?>'/><br />                       
            <input type='submit' value='<?= $action ?> Car'/>
        </form>
        <?php if ($action == "Update"): ?>
            <form action='<?= $car['id'] ?>/del' method='post'>
                <input type="submit" value='Delete This Car' />
            </form>
        <?php endif; ?>
    </body>
</html>
