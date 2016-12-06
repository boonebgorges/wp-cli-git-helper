<?php

if ( defined( 'WP_CLI' ) ) {
	require 'vendor/autoload.php';
	WP_CLI::add_command( 'gh', '\boonebgorges\WPCLIGitHelper\Command' );
}
