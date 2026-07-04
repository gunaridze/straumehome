<?
if($INCLUDE_FROM_CACHE!='Y')return false;
$datecreate = '001729667616';
$dateexpire = '001732259616';
$ser_content = 'a:2:{s:7:"CONTENT";s:0:"";s:4:"VARS";a:2:{s:7:"results";a:8:{i:0;a:5:{s:5:"title";s:128:"Уровень безопасности административной группы не является повышенным";s:8:"critical";s:5:"HIGHT";s:6:"detail";s:182:"Пониженный уровень безопасности административной группы может значительно помочь злоумышленнику";s:14:"recommendation";s:337:"Ужесточить <a href="/bitrix/admin/group_edit.php?ID=1&tabControl_active_tab=edit2"  target="_blank">политики безопасности административной</a> группы или выбрать предопределенную настройку уровня безопасности "Повышенный".";s:15:"additional_info";s:0:"";}i:1;a:5:{s:5:"title";s:61:"Включен расширенный вывод ошибок";s:8:"critical";s:5:"HIGHT";s:6:"detail";s:126:"Расширенный вывод ошибок может раскрыть важную информацию о ресурсе";s:14:"recommendation";s:63:"Выключить в файле настроек .settings.php";s:15:"additional_info";s:0:"";}i:2;a:5:{s:5:"title";s:101:"Не удалось проверить доступность обновлений платформы";s:8:"critical";s:5:"HIGHT";s:6:"detail";s:193:"Возможно доступно обновление системы SiteUpdate или у вашей копии продукта истек период получения обновлений";s:14:"recommendation";s:143:"Подробнее на странице: <a href="/bitrix/admin/update_system.php" target="_blank">Обновление платформы</a>";s:15:"additional_info";s:0:"";}i:3;a:5:{s:5:"title";s:76:"Используется устаревшая версия модуля main";s:8:"critical";s:6:"MIDDLE";s:6:"detail";s:110:"Не рекомендуется использование устаревших версий модуля main";s:14:"recommendation";s:83:"Обновите модули main и security до последних версий";s:15:"additional_info";s:98:"Текущая версия: 22.100.0<br>Минимально рекомендуемая: 23.600.0";}i:4;a:5:{s:5:"title";s:69:"Включено использование расширения PHAR";s:8:"critical";s:6:"MIDDLE";s:6:"detail";s:171:"Использование расширения PHAR - небезопасно. <a href=\'https://blog.sonarsource.com/new-php-exploitation-technique\'>Подробнее</a>";s:14:"recommendation";s:101:"Отключите это расширение в конфигурационном файле php.ini";s:15:"additional_info";s:0:"";}i:5;a:5:{s:5:"title";s:68:"Разрешено чтение файлов по URL (URL wrappers)";s:8:"critical";s:6:"MIDDLE";s:6:"detail";s:256:"Если эта, сомнительная, возможность PHP не требуется - рекомендуется отключить, т.к. она может стать отправной точкой для различного типа атак";s:14:"recommendation";s:89:"Необходимо в настройках php указать:<br>allow_url_fopen = Off";s:15:"additional_info";s:0:"";}i:6;a:5:{s:5:"title";s:86:"Проверяемый сайт отвечает на хост по умолчанию";s:8:"critical";s:3:"LOW";s:6:"detail";s:356:"Существует целый класс атак, направленных на использование хоста по умолчанию. Самый простой пример - <a href="https://www.owasp.org/index.php/Content_Spoofing" target="_blank">content spoofing</a> с подделкой кешируемых ссылок/ресурсов.";s:14:"recommendation";s:504:"Если есть необходимость, чтобы сайт отвечал на запросы с произвольным хостом, нужно организовать редирект с "default host" на необходимый домен.<br>
Для этого достаточно включить соответствующее ограничение модуля проактивной защиты: <a href="/bitrix/admin/security_hosts.php" target="_blank">Хосты/домены</a>";s:15:"additional_info";s:2481:"Адрес: <a href="https://loriata.ru/bitrix/header.php?rnd=0.7448985506037245&t=T013" target="_blank">https://loriata.ru/bitrix/header.php?rnd=0.7448985506037245&t=T013</a><br>Запрос/Ответ: <pre>GET /bitrix/header.php?rnd=0.7448985506037245&amp;t=T013 HTTP/1.1
host: fake.lala.com
User-Agent: BitrixCloud BitrixSecurityScanner/Robin-Scooter rnd94
Accept: */*
Accept-Encoding: gzip, deflate

HTTP/1.1 200 OK
Server: nginx
Date: Tue, 25 Jun 2024 17:28:55 GMT
Content-Type: text/html; charset=UTF-8
Transfer-Encoding: chunked
Connection: keep-alive
Vary: HTTPS
P3P: policyref=&quot;/bitrix/p3p.xml&quot;, CP=&quot;NON DSP COR CUR ADM DEV PSA PSD OUR UNR BUS UNI COM NAV INT DEM STA&quot;
X-Powered-CMS: Bitrix Site Manager (80577a1704dd32c46c4502fa692330e5)
Set-Cookie: PHPSESSID=sgkQeKmU1c1HaTSxZYeI7NeRWDKAJUKG; path=/; HttpOnly
Expires: Thu, 19 Nov 1981 08:52:00 GMT
Cache-Control: no-store, no-cache, must-revalidate
Pragma: no-cache
X-Content-Type-Options: nosniff
X-Frame-Options: SAMEORIGIN
Content-Encoding: gzip

    &lt;!DOCTYPE html&gt;
&lt;html xml:lang=&quot;ru&quot; lang=&quot;ru&quot; class=&quot;no-js&quot;&gt;
    &lt;head&gt;
        
        &lt;meta content=&quot;user-scalable=no,initial-scale=1.0,maximum-scale=1.0,width=device-width&quot; name=&quot;viewport&quot;&gt;
        &lt;meta http-equiv=&quot;X-UA-Compatible&quot; content=&quot;IE=edge&quot;&gt;
        &lt;meta name=&quot;format-detection&quot; content=&quot;telephone=no&quot;&gt;
        &lt;meta name=&quot;format-detection&quot; content=&quot;address=no&quot;&gt;
        &lt;meta name=&quot;SKYPE_TOOLBAR&quot; content=&quot;SKYPE_TOOLBAR_PARSER_COMPATIBLE&quot;&gt;
        &lt;meta name=&quot;mobile-web-app-capable&quot; content=&quot;yes&quot;&gt;
        &lt;meta name=&quot;msthemecompatible&quot; content=&quot;no&quot;&gt;
        &lt;meta name=&quot;HandheldFriendly&quot; content=&quot;True&quot;&gt;
        &lt;link rel=&quot;apple-touch-icon&quot; sizes=&quot;57x57&quot; href=&quot;/apple-touch-icon-57x57.png&quot;&gt;
        &lt;link rel=&quot;apple-touch-icon&quot; sizes=&quot;60x60&quot; href=&quot;/apple-touch-icon-60x60.png&quot;&gt;
        &lt;link rel=&quot;apple-touch-icon&quot; sizes=&quot;72x72&quot; href=&quot;/apple-touch-icon-72x72.png&quot;&gt;
        &lt;link rel=&quot;apple-touch-icon&quot; sizes=&quot;76x76&quot; href=&quot;/apple-touch-icon-76x76.png&quot;&gt;
        &lt;link rel=&quot;apple-touch-icon&quot; sizes=&quot;114x11</pre>";}i:7;a:5:{s:5:"title";s:53:"Не используется HSTS заголовок";s:8:"critical";s:3:"LOW";s:6:"detail";s:147:"HSTS заголовок принудительно активирует защищённое соединение через протокол https";s:14:"recommendation";s:127:"Добавьте "Strict-Transport-Security: max-age=31536000" к заголовкам ответа вашего сервера";s:15:"additional_info";s:2644:"Адрес: <a href="https://loriata.ru/?rnd=0.1353462801800417&t=T032" target="_blank">https://loriata.ru/?rnd=0.1353462801800417&t=T032</a><br>Запрос/Ответ: <pre>GET /?rnd=0.1353462801800417&amp;t=T032 HTTP/1.1
Host: loriata.ru
User-Agent: BitrixCloud BitrixSecurityScanner/Robin-Scooter rnd22
Accept: */*
Accept-Encoding: gzip, deflate

HTTP/1.1 200 OK
Server: nginx
Date: Tue, 25 Jun 2024 17:28:54 GMT
Content-Type: text/html; charset=UTF-8
Transfer-Encoding: chunked
Connection: keep-alive
Vary: HTTPS
P3P: policyref=&quot;/bitrix/p3p.xml&quot;, CP=&quot;NON DSP COR CUR ADM DEV PSA PSD OUR UNR BUS UNI COM NAV INT DEM STA&quot;
X-Powered-CMS: Bitrix Site Manager (80577a1704dd32c46c4502fa692330e5)
Set-Cookie: PHPSESSID=0XdoZ9JfHt5NT7SEZk1bjHPCY6gMPknS; path=/; HttpOnly
Expires: Thu, 19 Nov 1981 08:52:00 GMT
Cache-Control: no-store, no-cache, must-revalidate
Pragma: no-cache
Set-Cookie: BITRIX_SM_IR_LOCATION=0000103664; expires=Tue, 25-Jun-2024 17:28:54 GMT; Max-Age=0; path=/; HttpOnly
Set-Cookie: BITRIX_SM_SALE_UID=1397868; expires=Fri, 20-Jun-2025 17:28:54 GMT; Max-Age=31104000; path=/
X-Content-Type-Options: nosniff
X-Frame-Options: SAMEORIGIN
Content-Encoding: gzip

    &lt;!DOCTYPE html&gt;
&lt;html xml:lang=&quot;ru&quot; lang=&quot;ru&quot; class=&quot;no-js&quot;&gt;
    &lt;head&gt;
        
        &lt;meta content=&quot;user-scalable=no,initial-scale=1.0,maximum-scale=1.0,width=device-width&quot; name=&quot;viewport&quot;&gt;
        &lt;meta http-equiv=&quot;X-UA-Compatible&quot; content=&quot;IE=edge&quot;&gt;
        &lt;meta name=&quot;format-detection&quot; content=&quot;telephone=no&quot;&gt;
        &lt;meta name=&quot;format-detection&quot; content=&quot;address=no&quot;&gt;
        &lt;meta name=&quot;SKYPE_TOOLBAR&quot; content=&quot;SKYPE_TOOLBAR_PARSER_COMPATIBLE&quot;&gt;
        &lt;meta name=&quot;mobile-web-app-capable&quot; content=&quot;yes&quot;&gt;
        &lt;meta name=&quot;msthemecompatible&quot; content=&quot;no&quot;&gt;
        &lt;meta name=&quot;HandheldFriendly&quot; content=&quot;True&quot;&gt;
        &lt;link rel=&quot;apple-touch-icon&quot; sizes=&quot;57x57&quot; href=&quot;/apple-touch-icon-57x57.png&quot;&gt;
        &lt;link rel=&quot;apple-touch-icon&quot; sizes=&quot;60x60&quot; href=&quot;/apple-touch-icon-60x60.png&quot;&gt;
        &lt;link rel=&quot;apple-touch-icon&quot; sizes=&quot;72x72&quot; href=&quot;/apple-touch-icon-72x72.png&quot;&gt;
        &lt;link rel=&quot;apple-touch-icon&quot; sizes=&quot;76x76&quot; href=&quot;/apple-touch-icon-76x76.png&quot;&gt;
        &lt;link rel=&quot;apple-touch-icon&quot; sizes=&quot;114x11</pre>";}}s:9:"test_date";O:25:"Bitrix\\Main\\Type\\DateTime":2:{s:8:"'.chr(0).'*'.chr(0).'value";O:8:"DateTime":3:{s:4:"date";s:26:"2024-06-25 20:29:46.000000";s:13:"timezone_type";i:3;s:8:"timezone";s:13:"Europe/Moscow";}s:18:"'.chr(0).'*'.chr(0).'userTimeEnabled";b:1;}}}';
return true;
?>