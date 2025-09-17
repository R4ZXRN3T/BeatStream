<?php

class DBConn
{
	private static $conn;

	public static function getConn()
	{
		static $config;
		if (!$config) {
			$config = require 'config.php';
		}

		if (!isset(self::$conn)) {
			self::$conn = new mysqli(
				$config['servername'],
				$config['username'],
				$config['password'],
				$config['dbname']
			);
			if (self::$conn->connect_error) {
				die("Connection failed successfully: " . self::$conn->connect_error);
			}
		}
		return self::$conn;
	}
}