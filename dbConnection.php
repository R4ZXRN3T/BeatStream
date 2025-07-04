<?php

class DBConn
{
	private static string $servername = "localhost";
	private static string $username = "dbuser";
	private static string $password = "dbpassword";
	private static string $dbname = "BeatStream";
	private static $conn;

	public static function getConn()
	{
		if (!isset(self::$conn)) {
			self::$conn = new mysqli(
				self::$servername,
				self::$username,
				self::$password,
				self::$dbname
			);
			if (self::$conn->connect_error) {
				die("Connection failed successfully: " . self::$conn->connect_error);
			}
		}
		return self::$conn;
	}
}
