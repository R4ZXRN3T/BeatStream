<?php

declare(strict_types=1);

class Router
{
	private array $routes = [];

	public function get(string $pattern, string $endpoint): void
	{
		$this->add('GET', $pattern, $endpoint);
	}

	/**
	 * Register a route.
	 */
	public function add(string $method, string $pattern, string $endpoint): void
	{
		$this->routes[] = ['method' => strtoupper($method), 'pattern' => $pattern, 'endpoint' => $endpoint];
	}

	public function post(string $pattern, string $endpoint): void
	{
		$this->add('POST', $pattern, $endpoint);
	}

	public function put(string $pattern, string $endpoint): void
	{
		$this->add('PUT', $pattern, $endpoint);
	}

	public function patch(string $pattern, string $endpoint): void
	{
		$this->add('PATCH', $pattern, $endpoint);
	}

	public function delete(string $pattern, string $endpoint): void
	{
		$this->add('DELETE', $pattern, $endpoint);
	}

	/**
	 * Match the current request and execute the endpoint.
	 */
	public function dispatch(): void
	{
		$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

		$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

		// Remove /api/v1 from the path.
		$path = preg_replace('#^/api/v1#', '', $path);

		$path = '/' . trim($path ?? '', '/');

		foreach ($this->routes as $route) {
			if ($route['method'] !== $method) continue;

			$params = $this->match($route['pattern'], $path);

			if ($params === null) continue;

			$query = $_GET;

			$body = $this->getRequestBody();

			$endpoint = __DIR__ . '/endpoints/' . $route['endpoint'];

			if (!is_file($endpoint)) {
				$this->json(['error' => 'Endpoint not found',], 500);

				return;
			}

			// Variables available to the endpoint.
			require $endpoint;

			return;
		}

		$this->json(['error' => 'Endpoint not found',], 404);
	}

	/**
	 * Match a route pattern such as:
	 *
	 * /songs/{id}
	 * /albums/{id}/songs
	 */
	private function match(string $pattern, string $path): ?array
	{
		$parameterNames = [];

		$regex = preg_replace_callback('#\{([a-zA-Z_][a-zA-Z0-9_]*)}#', function ($matches) use (&$parameterNames) {
			$parameterNames[] = $matches[1];

			return '([0-9]+)';
		}, $pattern);

		$regex = '#^' . $regex . '/?$#';

		if (!preg_match($regex, $path, $matches)) return null;

		array_shift($matches);

		$params = [];

		foreach ($parameterNames as $index => $name) $params[$name] = (int)$matches[$index];

		return $params;
	}

	/**
	 * Parse JSON request body.
	 */
	private function getRequestBody(): ?array
	{
		$contentType = $_SERVER['CONTENT_TYPE'] ?? '';

		if (!str_contains(strtolower($contentType), 'application/json')) return null;

		$contents = file_get_contents('php://input');

		if ($contents === false || $contents === '') return null;

		$body = json_decode($contents, true);

		if (!is_array($body)) {
			$this->json(['error' => 'Invalid JSON body',], 400);
			exit();
		}

		return $body;
	}

	/**
	 * JSON response helper.
	 */
	private function json(array $data, int $status = 200): void
	{
		http_response_code($status);

		header('Content-Type: application/json');

		echo json_encode($data, JSON_UNESCAPED_SLASHES);
	}
}
