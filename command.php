<?php

/**
 * Extend the original "edd customers" command by adding functionality
 * to delete a customer with: "edd customers --delete=<id_or_email>" command.
 *
 * If the "--delete" argument doesn't exists then it will run the original
 * EDD CLI functionality.
 *
 * @since 0.1.0
 */
class EDD_Customers_Command_Extended extends WP_CLI_Command {
	/**
	 * Get the customers currently on your EDD site. Can also be used to create or delete customers records.
	 *
	 * ## OPTIONS
	 *
	 * --id=<customer_id>: A specific customer ID to retrieve
	 * --email=<customer_email>: The email address of the customer to retrieve
	 * --create=<number>: The number of arbitrary customers to create. Leave as 1 or blank to create a customer with a speciific email
	 * --delete=<id_or_email>: Delete a customer by ID or email.
	 *
	 * ## EXAMPLES
	 *
	 * wp edd customers --id=103
	 * wp edd customers --email=john@test.com
	 * wp edd customers --create=1 --email=john@test.com
	 * wp edd customers --create=1 --email=john@test.com --name="John Doe"
	 * wp edd customers --create=1 --email=john@test.com --name="John Doe" user_id=1
	 * wp edd customers --create=1000
	 *
	 * # Delete a customer by ID.
	 * wp edd customers --delete=1
	 *
	 * # Delete a customer by email.
	 * wp edd customers --delete=wpbullet@gmail.com
	 *
	 * # Delete a customber with all records.
	 * wp edd customers --delete=1 --all-records
	 *
	 * # Delete a customer without confirmation.
	 * wp edd customers --delete=1 --yes
	 */
	public function __invoke( $args, $assoc_args ) {
		if ( ! class_exists( 'Easy_Digital_Downloads' ) || ! function_exists( 'EDD' ) ) {
			WP_CLI::error( 'Easy Digital Downloads plugin is not installed.' );
			return;
		}

		if ( isset( $assoc_args['delete'] ) ) {
			$this->delete_customer( $assoc_args );
			return;
		}

		if ( ! class_exists( 'EDD_CLI' ) ) {
			WP_CLI::error( 'EDD CLI class does not exists.' );
			return;
		}

		// Run normal EDD customer commands.
		$edd = new EDD_CLI();
		$edd->customers( $args, $assoc_args );
	}

	/**
	 * Delete an EDD Customer.
	 *
	 * We setup multiple validations before delete an EDD Customer.
	 *
	 * 1. Check the "$id_or_email" variable is not false.
	 * 2. Check if "$id_or_email" variable has a valid value.
	 * 3. Check if "$id_or_email" belongs to a valid EDD Customer.
	 *
	 * After those validations a confirmation message will show to ask to
	 * continue the current operation. (The user can always override this by passing "--yes") argument.
	 *
	 * At the end, the EDD Customer will be deleted and its payments ids will be
	 * deleted if the user pass "--all-records" argument or update to 0 if doesn't specify.
	 *
	 * @since  0.1.0
	 * @access private
	 *
	 * @param  array   $assoc_args   The current CLI command arguments.
	 */
	private function delete_customer( $assoc_args ) {
		if ( empty( $assoc_args ) ) {
			WP_CLI::error( 'There is nothing to process for the current arguments.' );
			return;
		}

		$id_or_email = $assoc_args['delete'];

		if ( ! $id_or_email ) {
			WP_CLI::error( 'You need to provide the user ID or email.' );
			return;
		}

		if ( ! $this->is_valid_id_or_email( $id_or_email ) ) {
			WP_CLI::error( 'The user ID or email is not valid. Please enter a valid value.' );
			return;
		}

		$customer = new EDD_Customer( $id_or_email );

		if ( 0 === $customer->id ) {
			WP_CLI::error( 'The EDD Customer you are trying to delete does not exists.' );
			return;
		}

		WP_CLI::confirm( 'Are you sure you want to delete the EDD Customer: ' . $id_or_email . '?', $assoc_args );

		$payments_array = explode( ',', $customer->payment_ids );
		$result         = EDD()->customers->delete( $id_or_email );

		if ( ! $result ) {
			WP_CLI::error( 'Something went wrong when trying to delete the EDD Customer.' );
			return;
		}

		WP_CLI::success( 'The EDD Customer: ' . $id_or_email . ' was deleted from EDD database.' );

		if ( ! isset( $assoc_args['all-records'] ) ) {
			$this->update_payment_meta( $payments_array );
			return;
		}

		WP_CLI::warning( 'Removing associated payments and records for EDD Customer: ' . $id_or_email . '...' );
		$this->delete_payment_meta( $payments_array );
		WP_CLI::success( 'All associated payments and records were deleted for EDD Customer: ' . $id_or_email . '.' );
	}

	/**
	 * Delete EDD Customer payments ids.
	 *
	 * This action only executes when the customer was removed and the
	 * "--all-records" argument was specified.
	 *
	 * @since  0.1.0
	 * @access private
	 *
	 * @param  array   $payments_array   The EDD Customer payments ids.
	 */
	private function delete_payment_meta( $payments_array ) {
		foreach ( $payments_array as $payment_id ) {
			edd_delete_purchase( $payment_id, false, true );
		}
	}

	/**
	 * Update EDD Customer payments ids.
	 *
	 * This action only executes when the customer was removed and the
	 * "--all-records" argument wasn't specified.
	 *
	 * The new customer id will be 0.
	 *
	 * @since  0.1.0
	 * @access private
	 *
	 * @param  array   $payments_array   The EDD Customer payments ids.
	 */
	private function update_payment_meta( $payments_array ) {
		foreach ( $payments_array as $payment_id ) {
			edd_update_payment_meta( $payment_id, '_edd_payment_customer_id', 0 );
		}
	}

	/**
	 * Check if the current ID or email value is valid.
	 *
	 * @since  0.1.0
	 * @access private
	 *
	 * @param  string   $id_or_email   The current user id or email.
	 * @return boolean                 Whether the current id or email is a valid value.
	 */
	private function is_valid_id_or_email( $id_or_email ) {
		return is_numeric( $id_or_email ) ? true : is_email( $id_or_email );
	}
}

WP_CLI::add_command( 'edd customers', 'EDD_Customers_Command_Extended' );
