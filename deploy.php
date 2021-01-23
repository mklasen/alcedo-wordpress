<?php
namespace Deployer;

require 'recipe/common.php';

set( 'application', 'wptest' );
set( 'repository', 'git@github.com:usehonorato/wordpress.git' );
set( 'composer_options', 'install --optimize-autoloader' );

// Staging staging
host( 'staging' )
	->stage( 'staging' )
	->set( 'branch', 'staging' )
	->hostname( 'kingfisher' )
	->set( 'deploy_path', '~/sites/timeline' )
	->set( 'keep_releases', 3 );

// Production host
host( 'prod' )
	->stage( 'prod' )
	->set( 'branch', 'master' )
	->hostname( '_hostname_or_ip_' )
	->set( 'deploy_path', '_path_to_deploy_folder_' )
	->set( 'keep_releases', 5 );

// Symlinks
task(
	'deploy:wp_symlinks',
	function () {
		writeln( ' - Symlink media directories' );
		run( 'ln -nfs {{deploy_path}}/shared/uploads {{release_path}}/app/www/content/uploads' );

		writeln( ' - Symlink htaccess' );
		run( 'ln -nfs {{deploy_path}}/shared/.htaccess {{release_path}}/app/www/.htaccess' );

		writeln( ' - Copy wp-config.php' );
		run( '[ -f {{deploy_path}}/shared/wp-config.php ] && cp {{deploy_path}}/shared/wp-config.php {{release_path}}/app/www/wp-config.php || cp {{release_path}}/app/www/wp-config.php {{deploy_path}}/shared/wp-config.php' );

		writeln( ' - Environment symlink' );
		run( 'ln -nfs {{deploy_path}}/shared/.env {{release_path}}/.env' );

		writeln( ' - Debug symlink' );
		run( 'touch {{deploy_path}}/shared/debug.log && ln -nfs {{deploy_path}}/shared/debug.log {{release_path}}/app/www/content/debug.log' );
	}
);

// Restart PHP-FPM
task(
	'deploy:restart_php-fpm',
	function() {
		writeln( ' - Restart PHP-FPM' );
		run( '[ -f ~/tools/cloudways-manage-server/restart-php-fpm.php ] && php ~/tools/cloudways-manage-server/restart-php-fpm.php || echo "restart script not found."' );
	}
);

// Robots.txt for staging environments
task(
	'deploy:robots_txt',
	function () {
		$stage = null;
		if ( input()->hasArgument( 'stage' ) ) {
			$stage = input()->getArgument( 'stage' );
		}
		if ( 'prod' === $stage ) {
			$robots_txt = '';
		} else {
			$robots_txt = implode(
				"\n",
				array(
					'# This is a staging site. Do not index.',
					'User-agent: *',
					'Disallow: /',
				)
			);
		}
		writeln( 'Uploading robots.txt' );
		run( "echo '$robots_txt ' > {{release_path}}/app/www/robots.txt" );
	}
);

// Must use plugins.
task(
	'deploy:mu-plugins',
	function () {
		$stage = null;
		if ( input()->hasArgument( 'stage' ) ) {
			$stage = input()->getArgument( 'stage' );
		}
		if ( 'prod' === $stage ) {
			// Don't do this for production
		} else {
			writeln( ' - Symlink mu-plugins' );
			run( 'ln -nfs {{deploy_path}}/shared/mu-plugins {{release_path}}/app/www/content/mu-plugins' );
		}
	}
);

// Tasks that run on deployment
task(
	'deploy',
	array(
		'deploy:info',
		'deploy:prepare',
		'deploy:lock',
		'deploy:release',
		'deploy:update_code',
		'deploy:shared',
		'deploy:writable',
		'deploy:vendors',
		'deploy:wp_symlinks',
		'deploy:mu-plugins',
		'deploy:robots_txt',
		'deploy:clear_paths',
		'deploy:symlink',
		'deploy:restart_php-fpm',
		'deploy:unlock',
		'cleanup',
		'success',
	)
);

// [Optional] If deploy fails automatically unlock.
after( 'deploy:failed', 'deploy:unlock' );
