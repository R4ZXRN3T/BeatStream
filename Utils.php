<?php

class Utils
{
	public static function generateRandomString(int $length = 16, string $characterSet = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'): string
	{
		$chars = str_split($characterSet);
		return implode('', array_map(fn() => $chars[array_rand($chars)], range(1, $length)));
	}

	public static function hashPassword(string $password, string $salt, int $iterations = 200_000): string
	{
		for ($i = 0; $i < $iterations; $i++) $password = hash("sha256", $password . $salt);
		return $password;
	}
}