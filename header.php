<?php
/**
 * Tank Storage and Logistics (Amber Shipping International S.A.) - Header
 *
 * Set $pageTitle and $currentPage BEFORE including this file, e.g.
 *   $pageTitle = 'About Us'; $currentPage = 'about';
 *
 * NOTE: this file deliberately leaves <header class="site-header"> OPEN.
 * Each page closes it with </header> after adding its own slider (home)
 * or title bar (inner pages), because the template styles them as one block.
 */
if (!isset($pageTitle)) $pageTitle = '';
if (!isset($currentPage)) $currentPage = '';
?>
<!doctype html>
<html class="no-js" lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="x-ua-compatible" content="ie=edge">
		<title><?php echo $pageTitle ? $pageTitle . ' | ' : ''; ?>'s-Heerenberg Logistics B.V. - Tank Storage & Logistics</title>
		<meta name="description" content="'s-Heerenberg Logistics B.V. - secure bulk liquid tank storage, terminal facilities, port logistics and global supply chain solutions.">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<!-- Favicon -->
		<link rel="shortcut icon" type="image/x-icon" href="images/fevicon.png">
		<link rel="icon" type="image/png" sizes="32x32" href="images/favicon.png">
		<link rel="apple-touch-icon" sizes="180x180" href="images/fevicon.png">
		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="css/bootstrap.min.css">
		<!-- Fontawesome -->
		<link rel="stylesheet" href="css/fontawesome.css">
		<!-- Flaticon -->
		<link rel="stylesheet" href="css/flaticon.css">
		<!-- Base Icons -->
		<link rel="stylesheet" href="css/pbminfotech-base-icons.css">
		<!-- Themify Icons -->
		<link rel="stylesheet" href="css/themify-icons.css">
		<!-- Swiper -->
		<link rel="stylesheet" href="css/swiper.min.css">
		<!-- Magnific -->
		<link rel="stylesheet" href="css/magnific-popup.css">
		<!-- AOS -->
		<link rel="stylesheet" href="css/aos.css">
		<!-- Shortcode CSS -->
		<link rel="stylesheet" href="css/shortcode.css?v=<?php echo @filemtime(__DIR__ . '/css/shortcode.css') ?: time(); ?>">
		<!-- Base CSS -->
		<link rel="stylesheet" href="css/base.css?v=<?php echo @filemtime(__DIR__ . '/css/base.css') ?: time(); ?>">
		<!-- Style CSS -->
		<link rel="stylesheet" href="css/style.css?v=<?php echo @filemtime(__DIR__ . '/css/style.css') ?: time(); ?>">
		<!-- Responsive CSS -->
		<link rel="stylesheet" href="css/responsive.css?v=<?php echo @filemtime(__DIR__ . '/css/responsive.css') ?: time(); ?>">
	</head>
	<body>

	<!-- page wrapper -->
	<div class="page-wrapper" id="page">

		<!-- Header Main Area (closed by each page) -->
		<header class="site-header header-style-1">
			<div class="pbmit-header-overlay">
				<div class="pbmit-main-header-area">
					<div class="container-fluid">
						<div class="pbmit-header-content d-flex justify-content-between align-items-center">
							<div class="pbmit-logo-menuarea d-flex justify-content-between align-items-center">
								<div class="site-branding">
									<h1 class="site-title">
										<a href="index.php">
											<img class="logo-img" src="images/logo.png?v=<?php echo @filemtime(__DIR__ . '/images/logo.png') ?: 1; ?>" alt="'s-Heerenberg Logistics B.V.">
										</a>
									</h1>
								</div>
								<div class="site-navigation">
									<nav class="main-menu navbar-expand-xl navbar-light">
										<div class="navbar-header">
											<!-- Toggle Button -->
											<button class="navbar-toggler" type="button">
												<i class="pbmit-base-icon-menu-1"></i>
											</button>
										</div>
										<div class="pbmit-mobile-menu-bg"></div>
										<div class="collapse navbar-collapse clearfix" id="pbmit-menu">
											<div class="pbmit-menu-wrap">
												<span class="closepanel">
													<svg class="qodef-svg--close qodef-m" xmlns="http://www.w3.org/2000/svg" width="20.163" height="20.163" viewBox="0 0 26.163 26.163">
														<rect width="36" height="1" transform="translate(0.707) rotate(45)"></rect>
														<rect width="36" height="1" transform="translate(0 25.456) rotate(-45)"></rect>
													</svg>
												</span>
												<ul class="navigation clearfix">
													<li class="<?php echo $currentPage == 'home' ? 'active' : ''; ?>"><a href="index.php">Home</a></li>
													<li class="<?php echo $currentPage == 'about' ? 'active' : ''; ?>"><a href="about.php">About Us</a></li>
													<li class="<?php echo $currentPage == 'services' ? 'active' : ''; ?>"><a href="services.php">Services</a></li>
													<li class="<?php echo in_array($currentPage, ['portfolio', 'gallery']) ? 'active' : ''; ?>"><a href="portfolio.php">Our Gallery</a></li>
													<li class="<?php echo $currentPage == 'contact' ? 'active' : ''; ?>"><a href="contact.php">Contact Us</a></li>
												</ul>
											</div>
										</div>
									</nav>
								</div>
							</div>
							<div class="pbmit-right-box d-flex align-items-center">
								<div class="social-links-wrapper">
									<ul class="pbmit-social-links">
										<li class="pbmit-social-li pbmit-social-facebook"><a title="Facebook" href="#" target="_blank"><span><i class="pbmit-base-icon-facebook-f"></i></span></a></li>
										<li class="pbmit-social-li pbmit-social-twitter"><a title="Twitter" href="#" target="_blank"><span><i class="pbmit-base-icon-twitter-2"></i></span></a></li>
										<li class="pbmit-social-li pbmit-social-linkedin"><a title="LinkedIn" href="#" target="_blank"><span><i class="pbmit-base-icon-linkedin-in"></i></span></a></li>
										<li class="pbmit-social-li pbmit-social-instagram"><a title="Instagram" href="#" target="_blank"><span><i class="pbmit-base-icon-instagram"></i></span></a></li>
									</ul>
								</div>
								<div class="pbmit-header-search-btn">
									<a href="#" title="Search">
										<i class="pbmit-base-icon-search-1"></i>
									</a>
								</div>
								<div class="pbmit-header-button2">
									<a class="pbmit-btn pbmit-btn-white" href="contact.php">
										<span class="pbmit-button-content-wrapper">
											<span class="pbmit-button-text">Contact Us</span>
										</span>
									</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>