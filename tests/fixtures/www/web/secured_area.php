<!DOCTYPE html>

<html>
    <head>
        <title>Secured Area</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>
        <h1>Secured Area</h1>

        <?php

            session_start();

            if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
                die ("Access denied");
            }

        ?>

        <div>Access granted</div>
    </body>
</html>
