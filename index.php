<?php
// =================================== DETECT APPLICATION RUN MODE =====================================================
$runMode = 'FIRST_LOAD';

if (isset($_POST['ClearStorageButton'])) {
    $runMode = 'STORAGE_INIT';
}

if (isset($_POST['MainSubmitButton'])) {
    $runMode = 'MEMBER_ADD';
}
// =================================== INIT THE STORAGE AND SOME COMMON VARS ===========================================
session_start();

if ($runMode === 'STORAGE_INIT') {
    unset($_SESSION['membersDatabase']);
}

if (!isset($_SESSION['membersDatabase'])) {
    $_SESSION['membersDatabase'] = [];
}

$notificationBlockClass = 'Hidden';
$notificationMessage = '';
$logLine = date("Y.m.d G:i:s") . ', From: ' . $_SERVER['REMOTE_ADDR'] . ', RunMode: ' . $runMode;

if ($runMode === 'MEMBER_ADD') {
    // =============================== CHECK INPUT DATA ================================================================
    $userName = $_POST['user_name'];
    $userMail = $_POST['user_mail'];

    $isUserNameValid = isNameValid($userName);
    $isUserEmailValid = isEmailValid($userMail);
    $isUserAlreadyExists = isset($_SESSION['membersDatabase'][$userMail]);

    if (!$isUserNameValid) {
        $notificationBlockClass = 'Error';
        $notificationMessage .= 'User name is not valid. Only latin letters, spaces and dot (.) are allowed!<br>';
    }

    if (!$isUserEmailValid) {
        $notificationBlockClass = 'Error';
        $notificationMessage .= 'Provided email address is not valid.<br>';
    }

    if ($isUserAlreadyExists) {
        $notificationBlockClass = 'Error';
        $notificationMessage .= 'User already exists. We don\'t allow multiple registration on one email address.<br>';
    }

    // =============================== CREATE USER RECORD ==============================================================
    if (!$isUserAlreadyExists && $isUserNameValid && $isUserEmailValid) {
        $newMemberID = count($_SESSION['membersDatabase']) + 1;

        $_SESSION['membersDatabase'][$userMail] = [ // Emails should be unique! So let's use emails as array keys, and uniqueness will be ensured automatically :)
            'memberID' => $newMemberID,
            'userName' => $userName,
            'registrationDate' => date("Y.m.d"),
        ];

        $logLine .= ', Member added: ' . 'ID=' . $newMemberID . ', Name=' . $userName . ', Email=' . $userMail;
    }
}
// =================================== WRITE LOG =======================================================================
$writeResult = file_put_contents('log.txt',  $logLine . PHP_EOL, FILE_APPEND);

if (!$writeResult) {
    $notificationBlockClass = 'Error';
    $notificationMessage .= 'Can\'t create file log.txt. Try to create it manually and make it writeable.<br>';
}
// =================================== VALIDATION FUNCTIONS ============================================================
function isEmailValid($email) :bool {
    if (empty ($email)) {
        return false;
    }

    [$userName, $mailDomain] = explode('@', $email);

    if (empty($userName) || empty($mailDomain)) {
        return false;
    }

    $isSyntaxOk = (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
    $isMXRecordExists = checkdnsrr($mailDomain, 'MX');

    return ($isSyntaxOk && $isMXRecordExists);
}

function isNameValid($fullName) :bool {
    return (bool) preg_match('/[a-z\s\.]+/i', $fullName);
}
// =====================================================================================================================
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <style>
        h3, h4 {
            text-align: center;
        }
        form {
            /* Расположим форму по центру страницы */
            margin: 0 auto;
            width: 400px;
            /* Определим контур формы */
            padding: 1em;
            border: 1px solid #CCC;
            border-radius: 1em;
        }

        ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        form li + li {
            margin-top: 1em;
        }

        label {
            /* Определим размер и выравнивание */
            display: inline-block;
            width: 90px;
            text-align: right;
        }

        input {
            /* Убедимся, что все поля имеют одинаковые настройки шрифта
               По умолчанию в textarea используется моноширинный шрифт */
            font: 1em sans-serif;

            /* Определим размер полей */
            width: 300px;
            box-sizing: border-box;

            /* Стилизуем границы полей */
            border: 1px solid #999;
        }

        input:focus {
            /* Дополнительная подсветка для элементов в фокусе */
            border-color: #000;
        }

        .button {
            display: inline-block;
        }

        button {
            width: 120px;
        }

        table {
            width: 80%;
            border-collapse: collapse;
            margin: 0 auto;
        }

        table, th, td {
            border: 1px solid black;
        }

        div.Hidden {
            display: none;
            background-color: #FFFFFF;
            width: 80%;
            margin: 10px auto;
            padding: 10px;
        }

        div.Error {
            display: block;
            background-color: #FF0000;
            width: 80%;
            margin: 10px auto;
            padding: 10px;
        }
    </style>
    <title>Welcome To The Club!</title>
</head>
<body>

<h3>Welcome To The Club!</h3>

<form action="" method="post">
    <ul>
        <li>
            <label for="name">Name:</label>
            <input type="text" id="name" name="user_name" placeholder="Input your name here">
        </li>
        <li>
            <label for="mail">E-mail:</label>
            <input type="email" id="mail" name="user_mail" placeholder="Email will be validated">
        </li>
        <li class="button">
            <button type="submit" name="MainSubmitButton">Add</button>
        </li>
        <li class="button">
            <button type="reset">Reset</button>
        </li>
        <li class="button">
            <button type="submit" name="ClearStorageButton">Clear storage</button>
        </li>
    </ul>
</form>

<div class="<?= $notificationBlockClass ?>">
    <p><?= $notificationMessage ?></p>
</div>

<h4>Members</h4>
<table>
    <tr>
        <th>#</th>
        <th>Name</th>
        <th>Email</th>
        <th>Registration Date</th>
    </tr>
    <?php foreach ($_SESSION['membersDatabase'] as $email => $userRecord) { ?>
        <tr>
            <td><?= $userRecord['memberID'] ?></td>
            <td><?= $userRecord['userName'] ?></td>
            <td><?= $email ?></td>
            <td><?= $userRecord['registrationDate'] ?></td>
        </tr>
    <?php } ?>
</table>
</body>