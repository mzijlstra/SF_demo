<!DOCTYPE html>
<!--
    Created on : Aug 29, 2014, 13:00:01 PM
    Author     : mzijlstra 
-->
<html>
    <head>
        <title>Car App Login</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width">
        <link rel="stylesheet" href="login.css" type="text/css" />

    </head>
    <body>
        <div class="central">
            <h1>Login: </h1>
            <div class="container">
                <form action="login" method="post">
                    <?php if (isset($_SESSION['error'])) : ?>
                        <span class="error"><?= $_SESSION['error'] ?></span>
                        <br />
                        <?php unset($_SESSION['error']) ?>
                    <?php endif; ?>
                    <input type="text" name="user" placeholder="Username" />
                    <br />
                    <input type="password" name="pass" placeholder="Password" />
                    <br />
                    <input type="submit" value="submit" />
                </form>
            </div>
        </div>
    </body>
</html>