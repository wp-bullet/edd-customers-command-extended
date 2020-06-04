wp-cli/edd-customers-command-extended
================================

Extend the original "edd customers" EDD CLI command by adding functionality to delete a customer.

Quick links: [Delete an EDD customer](#delete-an-edd-customer)

## Installation

~~~~
wp package install https://github.com/wp-bullet/edd-customers-command-extended.git
~~~~

## Using

This package extends the `wp edd customers` command to delete a customer:

### Delete an EDD customer

Delete a customer by ID or email.

~~~~
wp edd customers [--delete=[<id_or_email>]] [--all-records] [--yes]
~~~~

**OPTIONS**

    [--delete=[<id_or_email>]]
		The EDD customer ID or email.

	[--all-records]
		Delete all payments records for the current EDD customer.

	[--yes]
		Allow to delete the EDD customer without any confirmation.

**EXAMPLES**

    # Delete a customer by ID.
	$ wp edd customers --delete=1

	# Delete a customer by email.
	$ wp edd customers --delete=wpbullet@gmail.com

	# Delete a customber with all records.
	$ wp edd customers --delete=1 --all-records
	
	# Delete a customer without confirmation.
	$ wp edd customers --delete=1 --yes
