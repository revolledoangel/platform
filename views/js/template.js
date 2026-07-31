// Sidebar Menu
$('.sidebar-menu').tree();


(function () {
	const $logo = $('.main-header .logo');
	const $toggle = $('.main-header .sidebar-toggle');

	if (!$logo.length || !$toggle.length) {
		return;
	}

	const sidebarTransitionMs = 360;
	const logoShowDurationMs = 360;
	let logoAnimationInProgress = false;

	$toggle.on('click.logoHeaderAnimation', function () {
		if (logoAnimationInProgress) {
			return;
		}

		logoAnimationInProgress = true;
		const isCurrentlyCollapsed = $('body').hasClass('sidebar-collapse');

		if (isCurrentlyCollapsed) {
			// Expand: mini disappears instantly, lg appears with animation
			$logo
				.removeClass('logo-show-anim logo-force-lg')
				.addClass('logo-hidden');

			window.setTimeout(function () {
				$logo
					.removeClass('logo-hidden')
					.addClass('logo-show-anim');

				window.setTimeout(function () {
					$logo.removeClass('logo-show-anim');
					logoAnimationInProgress = false;
				}, logoShowDurationMs);
			}, 40);
		} else {
			// Collapse: keep lg visible during hide, swap to mini only after hide ends
			$logo
				.removeClass('logo-show-anim logo-hidden')
				.addClass('logo-force-lg')
				.addClass('logo-hide-anim');

			window.setTimeout(function () {
				$logo
					.removeClass('logo-hide-anim logo-force-lg')
					.addClass('logo-hidden');

				window.setTimeout(function () {
					$logo
						.removeClass('logo-hidden')
						.addClass('logo-show-anim');

					window.setTimeout(function () {
						$logo.removeClass('logo-show-anim');
						logoAnimationInProgress = false;
					}, logoShowDurationMs);
				}, 40);
			}, sidebarTransitionMs);
		}
	});
})();


$(".tablas").DataTable();

$('.select2').select2();
