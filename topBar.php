<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
	<div class="container-fluid">
		<!-- Account Info -->
		<a class="navbar-brand" href="/BeatStream/">
			<img src="/BeatStream/images/logo_white.webp" alt="BeatStream Logo" class="d-inline-block align-text-top"
				 style="width: 276px; height: 40px; object-fit: fill;">
		</a>
		<?php if (isset($_SESSION['account_loggedin']) && $_SESSION['account_loggedin'] === true): ?>
			<div class="dropdown ms-auto">
				<button class="btn d-flex align-items-center dropdown-toggle p-0 bg-transparent border-0"
						type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
					<div class="text-end">
						<div class="fw-bold text-white"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
						<div class="small text-white-50"><?php echo htmlspecialchars($_SESSION['email']); ?></div>
					</div>
					<img src="<?php echo $_SESSION['imagePath'] ? '/BeatStream/images/user/' . $_SESSION['imagePath'] : '/BeatStream/images/defaultUser.webp'; ?>"
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
</nav>