<?php

class Utils
{
	public static function generateRandomString(int $length = 10, string $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!?,.:;()<>$#&*+-/=@%'): string
	{
		$charactersLength = strlen($characters);
		$randomString = '';

		for ($i = 0; $i < $length; $i++) {
			try {
				$randomString .= $characters[random_int(0, $charactersLength - 1)];
			} catch (Exception) {
				return '';
			}
		}
		return $randomString;
	}
}
