<?php

namespace boonebgorges\WPCLIGitHelper;

use Bit3\GitPhp\GitRepository;
use \WP_CLI_Command;
use \WP_CLI;

class Command extends WP_CLI_Command {
	public function __invoke( $args, $assoc_args ) {
		$command = isset( $args[0] ) ? $args[0] : '';

		if ( ! in_array( $command, array( 'plugin', 'theme' ) ) ) {
			WP_CLI::error( '\'wp gh\' can only be run with \'plugin\' or \'theme\' commands.' );
		}

		$action = isset( $args[1] ) ? $args[1] : '';

		$dir = defined( 'WP_CLI_GIT_HELPER_REPO_ROOT' ) ? WP_CLI_GIT_HELPER_REPO_ROOT : ABSPATH;

		if ( ! in_array( $action, array( 'update', 'install' ) ) ) {
			WP_CLI::error( '\'wp gh ' . $command . '\' can only be run with \'update\' or \'install\' commands.' );
		}

		// Convert 'all' to a list to make formatting easier.
		if ( isset( $assoc_args['all'] ) ) {
			// @todo
		} else {
			$asset_names = array_slice( $args, 2 );
		}

		// Grab a list of existing assets.
		$old_assets = $this->get_assets( $asset_names, $command );

		WP_CLI::run_command( $args, $assoc_args );

		// Verify that the install/update worked.
		$new_assets = $this->get_assets( $asset_names, $command );

		// Set up values that vary between plugins and themes.
		if ( 'plugin' === $command ) {
			$dir_base = WP_CONTENT_DIR . '/plugins/';
			$message_formats = array(
				'install' => "Install plugin: %s.\n\nName: %s\nVersion: %s",
				'update'  => "Update plugin: %s.\n\nName: %s\nNew version: %s\nPrevious version: %s",
			);

		} elseif ( 'theme' === $command ) {
			$dir_base = WP_CONTENT_DIR . '/themes/';
			$message_formats = array(
				'install' => "Install theme: %s.\n\nName: %s\nVersion: %s",
				'update'  => "Update theme: %s.\n\nName: %s\nNew version: %s\nPrevious version: %s",
			);
		}

		// Perform git actions.
		// @todo 'delete' will require different logic.
		$repo = new GitRepository( $dir );

		foreach ( $new_assets as $new_asset_name => $new_asset ) {
			if ( ! file_exists( $dir_base . $new_asset_name ) ) {
				continue;
			}

			$repo->add()->execute( $dir_base . $new_asset_name );

			// @todo override message generation with assoc_arg or with config.yml
			$message_args = array(
				$new_asset_name,
				$new_asset['Name'],
				$new_asset['Version'],
			);

			if ( 'update' === $action ) {
				$message_args[] = $old_assets[ $new_asset_name ]['Version'];
			}

			$message = vsprintf( $message_formats[ $action ], $message_args );

			$repo->commit()->message( $message )->execute();
		}
	}

	protected function get_assets( $asset_names, $type ) {
		$assets = array();
		foreach ( $asset_names as $asset_name ) {
			$asset = $this->get_asset_details( $asset_name, $type );
			if ( $asset ) {
				$assets[ $asset_name ] = $asset;
			}
		}

		return $assets;
	}

	protected function get_asset_details( $asset_name, $type ) {
		if ( 'plugin' === $type ) {
			return $this->get_plugin_details( $asset_name );
		} elseif ( 'theme' === $type ) {
			return $this->get_theme_details( $asset_name );
		}

		return false;
	}

	private function get_plugin_details( $plugin_name ) {
		foreach ( get_plugins() as $file => $_ ) {
			if ( "$plugin_name.php" === $file ||
				( dirname( $file ) === $plugin_name && '.' !== $plugin_name ) ) {
				$_['file'] = $file;
				return $_;
			}
		}
	}

	private function get_theme_details( $theme_name ) {
		$_theme = wp_get_theme( $theme_name );

		if ( ! $_theme->exists() ) {
			return false;
		}

		// Format like a theme, because barf.
		$theme = array(
			'Name' => $_theme->get( 'Name' ),
			'Version' => $_theme->get( 'Version' ),
		);

		return $theme;
	}
}
