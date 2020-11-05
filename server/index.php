<!--
    MIT License

    Copyright (c) 2020 Kento Oki

    Permission is hereby granted, free of charge, to any person obtaining a copy
    of this software and associated documentation files (the "Software"), to deal
    in the Software without restriction, including without limitation the rights
    to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
    copies of the Software, and to permit persons to whom the Software is
    furnished to do so, subject to the following conditions:

    The above copyright notice and this permission notice shall be included in all
    copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
    FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
    AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
    LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
    OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
    SOFTWARE.
-->
<?php

error_reporting(E_ALL);

require_once 'app/session.php';
require_once 'app/service.php';

SERVICE_SESSION\StartSessionIfNeeded();
$service = new BankServiceCore();
$is_loggined = $service->IsLoggined();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Race Conditions</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,600;0,700;0,800;1,300;1,400;1,600;1,700;1,800&display=swap" rel="stylesheet">
    <script src="assets/js/jquery-3.5.1.min.js"></script>
    <script src="assets/js/app.js"></script>
</head>
<body>
    <div class="wrapper">
        <?php if ($is_loggined === false): ?>
        <div class="login-wrapper">
            <div class="block">
                <p class="block-header">ABC Bank</p>
                <input id="username" type="text" placeholder="username" autocomplete="off">
                <input id="password" type="text" placeholder="password" autocomplete="off">
                <button id="button-login">LOGIN</button>
                <button id="button-register">REGISTER</button>
            </div>
        </div>
        <?php else: ?>
        <?php
        $uid = $_SESSION['uid'];
        $username = $service->GetCurrentUser();
        $balance = $service->GetCurrentUserBalance();
        $token = $service->GetCurrentUserToken();
        ?>
        <div class="dashboard-wrapper">
            <button id="button-logout">LOGOUT</button>
            <div class="block">
                <p class="block-header">Hello, <?php echo($username); ?></p>
                <table>
                    <tr>
                        <th>Balance</td><td>$<?php echo(number_format($balance)); ?></td>
                    </tr>
                </table>
                <div class="send-wrapper">
                    <button id="button-send">SEND</button>
                    <input id="amount" type="number" value="1" min="1">
                    <input id="recipient" type="text" placeholder="recipient">
                </div>
                <div class="token-wrapper">
                    <button id="button-gentoken">GENERATE</button>
                    <input id="token" type="text" value="<?php echo($token); ?>">
                </div>
            </div>
        </div>
        <?php endif ?>
    </div>
</body>
</html>