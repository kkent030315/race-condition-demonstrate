<?php

/**
 * MIT License
 *
 * Copyright (c) 2020 Kento Oki
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

error_reporting(0);

require_once 'util.php';
require_once 'database.php';
require_once 'uuid.php';
require_once 'session.php';

define('SALT', 'RjFsqz2BLrJSxSe5FKyE4P548MHd67Bt');

class BankServiceCore
{
    private $db;

    function __construct()
    {
        $this->db = new DatabaseProvider();
    }

    private function ProbeForParam($param)
    {
        return !empty($param);
    }

    public function IsLoggined()
    {
        SERVICE_SESSION\StartSessionIfNeeded();
        return isset($_SESSION['authed']) && $_SESSION['authed'] === true;
    }

    private function IsUserExists($username)
    {
        $username = $this->db->Escape($username);
        $query_result = $this->db->IssueQuery("SELECT `uid` FROM `customers` WHERE `username`='$username' LIMIT 1");

        $num_rows = mysqli_num_rows($query_result);

        if (!$query_result || $num_rows >= 1)
        {
            return true;
        }

        return false;
    }

    private function GetUserBalance($username)
    {
        $username = $this->db->Escape($username);
        $query_result = $this->db->IssueQuery(
            "SELECT `balance` FROM `customers` WHERE `username`='$username'"
        );

        if (!$query_result)
        {
            return 0;
        }

        $row = mysqli_fetch_row($query_result);
        
        return $row[0];
    }

    public function GetCurrentUser()
    {
        SERVICE_SESSION\StartSessionIfNeeded();
        return $_SESSION['username'];
    }

    public function GetCurrentUserBalance()
    {
        SERVICE_SESSION\StartSessionIfNeeded();

        if (!isset($_SESSION['username']))
        {
            return 0;
        }

        return $this->GetUserBalance($this->GetCurrentUser());
    }

    public function AuthenticateUser($username, $password)
    {
        if (!$this->ProbeForParam($username) ||
            !$this->ProbeForParam($password) ||
            strlen($username) > 20)
        {
            return false;
        }

        $password_hash = hash('sha256', $password.SALT);

        if (empty($password_hash))
        {
            return false;
        }

        $username = $this->db->Escape($username);
        $password_hash = $this->db->Escape($password_hash);

        $query_result = $this->db->IssueQuery("SELECT `uid`, `username`, `token` FROM `customers` 
        WHERE `username`='$username' AND `password`='$password_hash' LIMIT 1");

        if ($query_result === false)
        {
            return false;
        }

        $num_rows = mysqli_num_rows($query_result);

        if ($num_rows <= 0 || $num_rows > 1)
        {
            return false;
        }

        $row = mysqli_fetch_row($query_result);

        if (empty($row))
        {
            return false;
        }

        $uid = $row[0];
        $username_out = $row[1];
        $token = $row[2];

        SERVICE_SESSION\StartSessionIfNeeded();
        $_SESSION['uid'] = $uid;
        $_SESSION['username'] = $username_out;
        $_SESSION['authed'] = true;
        $_SESSION['token'] = $token;

        return true;
    }

    public function RegisterUser($username, $password)
    {
        if (!$this->ProbeForParam($username) ||
            !$this->ProbeForParam($password) ||
            strlen($username) > 20)
        {
            return false;
        }

        $password_hash = hash('sha256', $password.SALT);

        if (empty($password_hash))
        {
            return false;
        }

        $username = $this->db->Escape($username);
        $password_hash = $this->db->Escape($password_hash);
        $uid = $this->db->Escape(UuidV4Factory::generate());

        if ($this->IsUserExists($username))
        {
            return false;
        }

        $query_result = $this->db->IssueQuery("INSERT INTO `customers` 
        (`uid`, `username`, `password`, `balance`)
        VALUES 
        ('$uid', '$username', '$password_hash', '100')");

        if (!$query_result)
        {
            return false;
        }

        return true;
    }

    private function ModifyBalance($username, $amount, $increase)
    {
        if (!$this->ProbeForParam($username) || 
            strlen($username) > 20 ||
            $amount <= 0)
        {
            return false;
        }

        $username = $this->db->Escape($username);
        $amount = $this->db->Escape($amount);

        if ($increase)
        {
            $query_result = $this->db->IssueQuery("UPDATE `customers` SET `balance`=`balance`-$amount WHERE `username`='$username'");
        }
        else
        {
            $query_result = $this->db->IssueQuery("UPDATE `customers` SET `balance`=`balance`+$amount WHERE `username`='$username'");
        }

        if (!$query_result)
        {
            return false;
        }

        return true;
    }

    public function SendMoney($sender, $recipient, $amount)
    {
        if (!$this->ProbeForParam($sender) ||
            !$this->ProbeForParam($recipient) ||
            $amount <= 0 ||
            strlen($recipient) > 20 ||
            strlen($sender) > 20)
        {
            return false;
        }

        if (!$this->IsUserExists($sender) ||
            !$this->IsUserExists($recipient))
        {
            return false;
        }

        if ($this->GetCurrentUserBalance() < $amount)
        {
            return false;
        }

        $sender = $this->db->Escape($sender);
        $recipient = $this->db->Escape($recipient);

        if (!$this->ModifyBalance($sender, $amount, true))
        {
            return false;
        }

        if (!$this->ModifyBalance($recipient, $amount, false))
        {
            // must be revovered sender's balance
            return false;
        }

        return true;
    }

    public function MoneyTransferByToken($token, $recipient, $amount)
    {
        if (!$this->ProbeForParam($token) ||
            !$this->ProbeForParam($recipient) ||
            $amount <= 0)
        {
            return false;
        }

        $token = $this->db->Escape($token);

        $query_result = $this->db->IssueQuery(
            "SELECT `username` FROM `customers` WHERE `token`='$token' LIMIT 1"
        );

        if (!$query_result)
        {
            return false;
        }

        $row_count = mysqli_num_rows($query_result);

        if ($row_count <= 0 || $row_count > 1)
        {
            return false;
        }

        $row = mysqli_fetch_row($query_result);
        $token_username = $row[0];

        if (empty($token_username))
        {
            return false;
        }

        $token_user_balance = $this->GetUserBalance($token_username);

        if ($token_user_balance < $amount)
        {
            return false;
        }

        if (!$this->ModifyBalance($token_username, $amount, false))
        {
            return false;
        }

        if (!$this->ModifyBalance($recipient, $amount, true))
        {
            // sender's balance must be recovered
            return false;
        }

        return true;
    }

    public function GetCurrentUserToken()
    {
        SERVICE_SESSION\StartSessionIfNeeded();
        return $_SESSION['token'];
    }

    public function GenerateUserToken()
    {
        $current_user = $this->GetCurrentUser();

        if (empty($current_user))
        {
            return false;
        }

        $token = GenerateToken();

        if (empty($token))
        {
            return false;
        }

        $query_result = $this->db->IssueQuery(
            "UPDATE `customers` SET `token`='$token' WHERE `username`='$current_user' LIMIT 1"
        );

        if (!$query_result)
        {
            return false;
        }

        $_SESSION['token'] = $token;

        return $token;
    }
}

?>
