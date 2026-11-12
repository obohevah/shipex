<?php
http_response_code(404);
$pageTitle = 'Page Not Found';
$currentPage = '404';
include 'header.php';
?>
			<!-- Title Bar -->
			<div class="pbmit-title-bar-wrapper" style="background-image: url('images/bg/titlebar-services.jpg?v=<?php echo @filemtime(__DIR__ . '/images/bg/titlebar-services.jpg') ?: 3; ?>');">
				<div class="container">
					<div class="pbmit-title-bar-content">
						<div class="pbmit-title-bar-content-inner">
							<div class="pbmit-tbar">
								<div class="pbmit-tbar-inner container">
									<h1 class="pbmit-tbar-title">404 &ndash; Page Not Found</h1>
								</div>
							</div>
							<div class="pbmit-breadcrumb">
								<div class="pbmit-breadcrumb-inner">
									<span><a title="Home"
									href="index.php" class="home"><span>Home</span></a></span>
									<span class="sep"><i class="pbmit-base-icon-angle-right"></i></span>
									<span><span class="post-root post post-post current-item">404 Error</span></span>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- Title Bar End-->
		</header>
		<!-- Header Main Area End Here -->

		<!-- page content -->
		<div class="page-content">

		<!-- 404 Error Section -->
		<section class="section-lg text-center" style="padding: 100px 0;">
			<div class="container">
				<div class="row justify-content-center">
					<div class="col-lg-8">
						<div class="error-404-box" style="background: #ffffff; border-radius: 24px; padding: 60px 40px; box-shadow: 0 20px 60px rgba(0, 17, 46, 0.08); border: 1px solid #e2e8f0;">
							<div style="font-size: 96px; font-weight: 800; color: #d4a024; line-height: 1; margin-bottom: 20px; letter-spacing: -2px;">404</div>
							<h2 style="font-size: 32px; font-weight: 700; color: #00112e; margin-bottom: 16px;">Berth or Terminal Location Not Found</h2>
							<p style="font-size: 16px; color: #64748b; line-height: 28px; max-width: 580px; margin: 0 auto 35px auto;">
								The page or facility resource you are looking for may have been moved, renamed, or is temporarily unavailable. Please navigate back to our main operations or contact our dispatch team.
							</p>
							<div style="display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
								<a class="pbmit-btn pbmit-btn-gold" href="index.php">
									<span class="pbmit-button-content-wrapper">
										<span class="pbmit-button-text">Return to Home</span>
									</span>
								</a>
								<a class="pbmit-btn" href="contact.php" style="background: #00112e; color: #ffffff;">
									<span class="pbmit-button-content-wrapper">
										<span class="pbmit-button-text">Contact Operations</span>
									</span>
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
		<!-- 404 Error Section End -->

		</div>
		<!-- page content End -->

<?php include 'footer.php'; ?>
