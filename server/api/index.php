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

require_once '../app/session.php';
require_once '../app/service.php';

function result_json($data_array)
{
    echo(json_encode($data_array));
}

function result_error($status_code, $error_message, $content_array = null)
{
    $data_array = array(
        "status" => $status_code,
        "error" => $error_message,
        "content" => $content_array
    );

    result_json($data_array);
}

function result_success($message = '', $content_array = null)
{
    result_error(1, $message, $content_array);
}

//
// api handlers
//

$action_list = ['login', 'register', 'logout', 'send', 'gentoken', 'transfer'];

if (!$_POST)
{
    result_error(0, 'invalid method');
    exit();
}

$action = filter_input(INPUT_POST, 'a');
$service = new BankServiceCore();

switch($action)
{
    case $action_list[0]: // login
        $username = filter_input(INPUT_POST, 'u');
        $password = filter_input(INPUT_POST, 'p');

        $auth_result = $service->AuthenticateUser($username, $password);

        if ($auth_result === true)
        {
            result_success('success');
            exit();
        }
        else if ($auth_result === false)
        {
            result_error(0, 'invalid username or password');
            exit();
        }

        result_error(0, 'authentication failure');
        exit();
    case $action_list[1]: // register
        $username = filter_input(INPUT_POST, 'u');
        $password = filter_input(INPUT_POST, 'p');

        $register_result = $service->RegisterUser($username, $password);

        if ($register_result === true)
        {
            result_success('success');
            exit();
        }
        else if ($register_result === false)
        {
            result_error(0, 'registration failure');
            exit();
        }

        result_error(0, 'registration failure with unknown error');
        exit();
    case $action_list[2]: // logout
        SERVICE_SESSION\DisposeSessionForcibly();
        result_success('success');
        exit();
    case $action_list[3]: // send
        $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_INT);
        $recipient = filter_input(INPUT_POST, 'recipient');

        SERVICE_SESSION\StartSessionIfNeeded();
        $sender = $_SESSION['username'];

        $send_result = $service->SendMoney($sender, $recipient, $amount);

        if ($send_result === true)
        {
            result_success('success');
            exit();
        }
        else if ($send_result === false)
        {
            result_error(0, 'send failure');
            exit();
        }

        result_error(0, 'send failure with unknown error');
        exit();
    case $action_list[4]: // gentoken
        SERVICE_SESSION\StartSessionIfNeeded();
        $gentoken_result = $service->GenerateUserToken();

        if ($gentoken_result === false)
        {
            result_error(0, 'token generation failure');
            exit();
        }
        else
        {
            result_success('success');
            exit();
        }

        result_error(0, 'token generation failure with unknown error');
        exit();
    case $action_list[5]: // transfer
        if (!isset($_SERVER['HTTP_X_ABCBANK_TOKEN']) ||
            empty($_SERVER['HTTP_X_ABCBANK_TOKEN']))
        {
            result_error(0, 'token must not be empty');
            exit();
        }

        $recipient = filter_input(INPUT_POST, 'recipient');
        $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_INT);

        $user_token = $_SERVER['HTTP_X_ABCBANK_TOKEN'];
        $transfer_result = $service->MoneyTransferByToken($user_token, $recipient, $amount);

        if ($transfer_result === false)
        {
            result_error(0, 'transfer failure');
            exit();
        }
        else if ($transfer_result === true)
        {
            result_success(1, 'success');
            exit();
        }

        result_error(0, 'transfer failure with unknown error');
        exit();
    default:
        result_error(0, 'invalid action');
        exit();
}

?>