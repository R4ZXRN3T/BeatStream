<?php

class Utils
{
	public static function generateRandomString(int $length = 10, string $characterSet = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'): string
	{
		$chars = str_split($characterSet);
		return implode('', array_map(fn() => $chars[array_rand($chars)], range(1, $length)));
	}
}