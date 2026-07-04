<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

global $USER;
$USER->Authorize(1); // Автоматически авторизуем пользователя с ID 1 (главный админ)

$user = new CUser;
$fields = Array(
  "PASSWORD"          => "NewStraume2026!",
  "CONFIRM_PASSWORD"  => "NewStraume2026!",
);
if($user->Update(1, $fields)) {
    echo "Пароль успешно изменен на: NewStraume2026!";
} else {
    echo "Ошибка: ".$user->LAST_ERROR;
}
?>
