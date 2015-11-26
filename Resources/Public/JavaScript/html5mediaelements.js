(function ($, undefined) {
	"use strict";

	$('.html5mediaelement').each(function () {
		var $this = $(this),
			pluginPath = $(this).find('object').attr('data');

		pluginPath = pluginPath.split('/');
		pluginPath.pop();
		pluginPath = pluginPath.join('/') + '/';

		$this.mediaelementplayer({
			pluginPath: pluginPath
		});
	});
}(jQuery));