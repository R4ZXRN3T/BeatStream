<head>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
	<title></title></head>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
	<div class="container-fluid">
		<!-- Account Info -->
		<a class="navbar-brand" href="/BeatStream/">
			<img src="/BeatStream/images/logo_white.webp" alt="BeatStream Logo" class="d-inline-block align-text-top"
				 style="width: 276px; height: 40px; object-fit: fill;">
		</a>

		<div class="ms-auto d-flex align-items-center">
			<button id="darkModeToggle" class="btn btn-secondary me-2" title="Toggle Dark Mode" style="background-color: transparent; border: none;">
				<i class="bi bi-moon fs-4"></i>
			</button>
			<?php if (isset($_SESSION['account_loggedin']) && $_SESSION['account_loggedin'] === true): ?>
				<div class="dropdown ms-auto">
					<button class="btn d-flex align-items-center dropdown-toggle p-0 bg-transparent border-0"
							type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
						<div class="text-end">
							<div class="fw-bold text-white"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
							<div class="small text-white-50"><?php echo htmlspecialchars($_SESSION['email']); ?></div>
						</div>
						<img src="<?php echo $_SESSION['imageName'] ? '/BeatStream/images/user/' . $_SESSION['imageName'] : '/BeatStream/images/defaultUser.webp'; ?>"
							 alt="Profile" class="rounded-circle me-2"
							 style="width:40px; height:40px; object-fit:cover; margin-left: 15px; margin-right: 15px;">
					</button>
					<ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
						<li><a class="dropdown-item" href="/BeatStream/account/profile.php">View Profile</a></li>
						<li>
							<hr class="dropdown-divider">
						</li>
						<li><a class="dropdown-item text-danger" href="/BeatStream/account/logout.php">Log Out</a></li>
					</ul>
				</div>
			<?php else: ?>
				<div class="ms-auto d-flex">
					<a href="/BeatStream/account/login.php" class="btn btn-outline-light me-2">Login</a>
					<a href="/BeatStream/account/signup.php" class="btn btn-primary">Sign Up</a>
				</div>
			<?php endif; ?>
		</div>
	</div>
	<script>
		const toggle = document.getElementById('darkModeToggle');
		const body = document.body;
		// Load preference
		if (localStorage.getItem('darkMode') === 'enabled') {
			body.classList.add('dark-mode');
		}
		toggle.onclick = function () {
			body.classList.toggle('dark-mode');
			if (body.classList.contains('dark-mode')) {
				localStorage.setItem('darkMode', 'enabled');
			} else {
				localStorage.setItem('darkMode', 'disabled');
			}
		}
	</script>
</nav>