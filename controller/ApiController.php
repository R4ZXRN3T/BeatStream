<?php
require_once $GLOBALS['PROJECT_ROOT_DIR'] . "/dbConnection.php";
require_once $GLOBALS['PROJECT_ROOT_DIR'] . "/Utils.php";

class ApiController
{
	public static function createApiKey(int $userID, bool $canExpire = true, DateTime|null $expiresAt = null, bool $isAdmin = false): string
	{
		$conn = DBConn::getConn();

		// Loop until we hit a unique key (UNIQUE constraint protects us!)
		do {
			$apiKey = Utils::generateRandomString(32);
		} while (self::apiKeyExists($apiKey));

		$expires = $expiresAt?->format('Y-m-d H:i:s');

		$sql = "INSERT INTO api_key (apiKey, userID, canExpire, expirationDate, adminAccess) VALUES (?, ?, ?, ?, ?)";

		$stmt = $conn->prepare($sql);
		if (!$stmt) {
			throw new RuntimeException("Prepare failed: $conn->error");
		}

		// Bind – note the '?' for NULL will be handled automatically
		$canExpire = $canExpire ? 1 : 0;
		$adminAccess = $isAdmin ? 1 : 0;
		$stmt->bind_param('sissi', $apiKey, $userID, $canExpire, $expires, $adminAccess);

		if (!$stmt->execute()) {
			throw new RuntimeException("Execute failed: $stmt->error");
		}

		$stmt->close();
		return $apiKey;
	}

	private static function apiKeyExists(string $apiKey): bool
	{
		return ApiController::getApiKeyRow($apiKey) !== null;
	}

	private static function getApiKeyRow(string $apiKey): ?array
	{
		$stmt = DBConn::getConn()->prepare("SELECT canExpire, expirationDate FROM api_key WHERE apiKey = ?");
		$stmt->bind_param("s", $apiKey);
		$stmt->execute();
		$result = $stmt->get_result();
		$row = $result->fetch_assoc();
		$stmt->close();

		return $row ?: null;
	}

	public static function deleteApiKey(string $apiKey): bool
	{
		$conn = DBConn::getConn();
		$stmt = $conn->prepare("DELETE FROM api_key WHERE apiKey = ?");
		if (!$stmt) {
			throw new RuntimeException("Prepare failed: $conn->error");
		}

		$stmt->bind_param("s", $apiKey);
		if (!$stmt->execute()) {
			throw new RuntimeException("Execute failed: $stmt->error");
		}

		$deletedRows = $stmt->affected_rows;
		$stmt->close();

		return $deletedRows > 0;
	}

	public static function apiKeyBelongsToUser(string $apiKey, int $userID): bool
	{
		$stmt = DBConn::getConn()->prepare("SELECT 1 FROM api_key WHERE apiKey = ? AND userID = ?");
		if (!$stmt) {
			throw new RuntimeException("Prepare failed: " . DBConn::getConn()->error);
		}

		$stmt->bind_param("si", $apiKey, $userID);
		if (!$stmt->execute()) {
			throw new RuntimeException("Execute failed: " . $stmt->error);
		}

		$result = $stmt->get_result();
		$exists = $result->num_rows > 0;
		$stmt->close();

		return $exists;
	}

	public static function deleteUserApiKeys(int $userID): bool
	{
		$conn = DBConn::getConn();
		$stmt = $conn->prepare("DELETE FROM api_key WHERE userID = ?");
		if (!$stmt) {
			throw new RuntimeException("Prepare failed: $conn->error");
		}

		$stmt->bind_param("i", $userID);
		if (!$stmt->execute()) {
			throw new RuntimeException("Execute failed: $stmt->error");
		}

		$deletedRows = $stmt->affected_rows;
		$stmt->close();
		return $deletedRows > 0;
	}

	public static function isApiKeyValid(string $apiKey): bool
	{
		$row = ApiController::getApiKeyRow($apiKey);
		if (!$row) {
			return false;
		}

		if ((int)$row['canExpire'] === 0) {
			return true;
		}

		return strtotime($row['expirationDate']) > time();
	}

	public static function apiKeyStatus(string $apiKey): array
	{
		$row = ApiController::getApiKeyRow($apiKey);
		if (!$row) {
			return [
				"valid" => false,
				"status" => "API key is invalid",
				"adminAccess" => false
			];
		}

		$isValid = ((int)$row['canExpire'] === 0) || strtotime($row['expirationDate']) > time();
		return [
			"valid" => $isValid,
			"status" => $isValid ? "valid" : "API key is expired",
			"adminAccess" => (bool)$row['adminAccess']
		];
	}
}
