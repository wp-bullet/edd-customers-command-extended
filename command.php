<?php

// edd customers --delete --email=<email> --all-records

class EDD_Customers_Command_Extended extends WP_CLI_Command {
	/**
	 * Start menu import using WP-CLI.
	 *
	 * ## OPTIONS
	 *
	 * <file>
	 * : The exported menu JSON file.
	 *
	 * [--overwrite]
	 * : Overwrite the existent menus.
	 *
	 * ## EXAMPLES
	 *
	 *     # Import a menu.
	 *     $ wp menu import my-menu.json
	 *
	 *     # Import a menu with overriding the existent ones.
	 *     $ wp menu import my-menu.json --overwrite
	 */
	public function __invoke( $args, $assoc_args ) {
		var_dump($args);
	}
}

WP_CLI::add_command( 'roel customers' );
