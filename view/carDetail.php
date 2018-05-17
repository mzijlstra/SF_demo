<?php 
$action = "Add";
if (isset($car) && isset($car['id'])) {
    $action = "Update";
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title><?= $action ?> Car</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css" integrity="sha384-9gVQ4dYFwwWSjIDZnLEWnxCjeSWFphJiwGPXr1jddIhOegiu1FwO5qRGvFXOdJZ4" crossorigin="anonymous">
        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js" integrity="sha384-cs/chFZiN24E4KMATLdqdvsezGxaGsi4hLGOzlXwp5UZB1LY//20VyM2taTB4QvJ" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js" integrity="sha384-uefMccjFJAIv6A+rW+L4AHf99KvxDjWSu1z9VI8SKNVmz4sk7buKt/6v9KI65qnm" crossorigin="anonymous"></script>
        <style>
            span.tag {
                width: 100px;
            }
        </style>
    </head>
    <body>
        <h1><?= $action ?> Car:</h1>
        <form action='<?= $car ? $car['id'] : "add" ?>' method='post'>
            <span class='tag'>Make</span>
            <input type='text' placeholder="Car Maker" name='make' 
                   value='<?= $car ? $car['make'] : "" ?>'/><br />    
            <span class='tag'>Model</span>
            <input type='text' placeholder="Car Model" name='model' 
                   value='<?= $car ? $car['model'] : "" ?>'/><br />                       
            <span class='tag'>Year</span>
            <input type='text' placeholder="Year Made" name='year' 
                   value='<?= $car ? $car['year'] : "" ?>'/><br />                       
            <span class='tag'>Color</span>
            <input type='text' placeholder="Car Color" name='color' 
                   value='<?= $car ? $car['color'] : "" ?>'/><br />                       
            <span class='tag'>Type</span>
            <select name="type">
                <?php foreach ($types as $type): ?>
                    <option value="<?= $type['id'] ?>"><?= $type['name'] ?></option>
                <?php endforeach; ?>
            </select></br>
            <input type='submit' value='<?= $action ?> Car'/>
        </form>
        <?php if ($action == "Update"): ?>
            <form action='<?= $car['id'] ?>/del' method='post'>
                <input type="submit" value='Delete This Car' />
            </form>
        <?php endif; ?>
    </body>
</html>
