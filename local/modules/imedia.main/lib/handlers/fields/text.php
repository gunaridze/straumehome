<?php
namespace Imedia\Main\Handlers\Fields;

use Bitrix\Main\Localization\Loc;

class Text
{
	public static function GetUserTypeDescription()
	{
		return [
			'CLASS_NAME' => static::class,
			'BASE_TYPE' => 'string',
			'USER_TYPE_ID' => 'editor',
			'DESCRIPTION' => Loc::getMessage('IMEDIA_FIELD_TEXT_DESCRIPTION')
		];
	}

	public static function GetDBColumnType()
	{
		return 'text';
	}

	public static function GetEditFormHTML($arUserField, $arHtmlControl)
	{
		ob_start();
		\CFileMan::AddHTMLEditorFrame(
			$arHtmlControl['NAME'],
			$arHtmlControl['VALUE'],
			false,
			'html',
			['height' => 450, 'width' => '100%'],
			'N',
			0,
			'',
			'',
			false,
			true,
			false,
			[
				'hideTypeSelector' => true
			]
		);
		$result = ob_get_contents();
		ob_end_clean();

		return $result;
	}
}