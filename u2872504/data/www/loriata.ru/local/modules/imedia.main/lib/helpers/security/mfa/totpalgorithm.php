<?php
namespace Imedia\Main\Helpers\Security\Mfa;

use Bitrix\Main\Security;


class TotpAlgorithm extends Security\Mfa\TotpAlgorithm
{
    protected $digits = 4;
}