<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Car Application</title>
    </head>
    <body>
        <h1>Welcome Shopper!</h1>
        <p><a href='car'>See a list of cars</a></p>
        <?php if (isset($_SESSION['admin'])) : ?>
            <p><a href='logout'>Admin logout</a></p>
        <?php else : ?>
            <p><a href='login'>Admin Login</a></p>
        <?php endif; ?>
    </body>
</html>
