<?
$aMenuLinks = Array(
	Array(
		"Мои заказы", 
		"/personal/orders/", 
		Array(), 
		Array(),
        "\$GLOBALS['USER']->IsAuthorized()"
	),
	Array(
		"Управление подписками", 
		"/personal/subscribe/", 
		Array(), 
		Array(),
        "\$GLOBALS['USER']->IsAuthorized()"
	),
	Array(
		"Персональные данные", 
		"/personal/",
		Array(), 
		Array(),
        "\$GLOBALS['USER']->IsAuthorized()"
	)
);
?>