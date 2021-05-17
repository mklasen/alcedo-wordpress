<?php
namespace Deployer;

require 'recipe/common.php';


set( 'application', 'reserveerjewinkel' );
set( 'repository', 'git@github.com:conference7/reserveerjewinkel.git' );

// [Optional] Allocate tty for git clone. Default value is false.
set( 'git_tty', true );

// Shared files/dirs between deploys
set( 'shared_files', array() );
set( 'shared_dirs', array() );
set( 'writable_dirs', array() );

set( 'composer_options', 'install --optimize-autoloader' );

host( 'prod' )
	->stage( 'prod' )
	->set( 'branch', 'master' )
	->hostname( 'bijnoabers' )
	->set( 'deploy_path', '/home/master/sites/reserveerjewinkel/deploy' )
	->set( 'keep_releases', 5 );

// Symlinks
task(
	'deploy:wp_symlinks',
	function () {
		writeln( ' - Symlink media directories' );
		run( 'ln -nfs {{deploy_path}}/shared/uploads {{release_path}}/app/www/content/uploads' );

		writeln( ' - Copy wp-config.php' );
		run( '[ -f {{deploy_path}}/shared/wp-config.php ] && cp {{deploy_path}}/shared/wp-config.php {{release_path}}/app/www/wp-config.php || cp {{release_path}}/app/www/wp-config.php {{deploy_path}}/shared/wp-config.php' );

		writeln( ' - Environment symlink' );
		run( 'ln -nfs {{deploy_path}}/shared/.env {{release_path}}/.env' );
		
		writeln( ' - htaccess symlink' );
		run( 'ln -nfs {{deploy_path}}/shared/.htaccess {{release_path}}/app/www/.htaccess' );

		writeln( ' - Archive symlink' );
		run( 'ln -nfs /var/www/comwifi-medelana/archived-sites {{release_path}}/app/www/archive' );

		writeln( ' - USA symlink' );
		run( 'ln -nfs /var/www/comwifi-medelana/archived-sites/usa {{release_path}}/app/www/usa' );
	}
);

// Robots.txt - we want this on all environments except for production
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

task(
	'deploy:clear_cache',
	function() {
		// run( "wp eval \"do_action( 'warpdrive_cache_flush' );\" --path=/var/www/comwifi-medelana/wordpress/current/" );
		// run( "touch ~/wordpress/php-fpm.service" );
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
		'deploy:robots_txt',
		'deploy:clear_paths',
		'deploy:symlink',
		'deploy:clear_cache',
		'deploy:unlock',
		'cleanup',
		'success',
	)
);
// [Optional] If deploy fails automatically unlock.
after( 'deploy:failed', 'deploy:unlock' );
