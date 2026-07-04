<?php

namespace Imedia\Main\Service;

class TelegramBot
{
	private static $instance;
	private $token = '5050838574:AAEywnkpITBG9c6se4bZoEx0vtUQwbhN_Bs';
	private $chat = '-604792378';

	private function __construct($chat = false)
	{
		if ($chat) {
			$this->chat = $chat;
		}
	}

	public static function send($text = '', $chat = false)
	{
		$that = new self($chat);

		$text = '<pre>' . strip_tags($text) . '</pre>';
		$entityBody = [
			'chat_id' => $that->chat,
			'text' => $text,
			'parse_mode' => 'HTML'
		];

		$httpClient = new \Bitrix\Main\Web\HttpClient();
		$httpClient->waitResponse(true);
		$httpClient->setTimeout(10);
		$httpClient->setStreamTimeout(10);
		$httpClient->setHeader('Content-Type', 'application/json', true);
		$httpClient->query('POST', 'https://api.telegram.org/bot' . $that->token . '/sendMessage', json_encode($entityBody));
	}
}