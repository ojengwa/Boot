<?php
	require( 'view/setup.php' );

	if (!empty($_POST)) {
		setup();
	}

/**
 * Creates tables that will be used by Bootsite.
 *
 * @todo  Create a nice popup to inform the user that tables have been successfully created.
 * Implement section where user makes use of their own tables.
 * @return void
 */
function setup() {
	if (isset($_POST['yes'])) {
		if (Database::createBootsiteTables()) {
			echo 'tables created';
			echo '<script>
				window.location.href = "' . BOOTSITE_BASE_URL . '/";
			</script>';
		}
	} else {
		echo 'you want yours';
	}
}


/**
 * Checks that the expected database config variable is defined,
 * and if Bootsite config table already exists.
 *
 * @todo  complete this method so that the return value of _doesConfigTableExist
 * is the right row count of the resultset. At the moment it's faulty.
 * @return bool
 */
	 function checkBootsiteTableExists() {
		global $BOOTSITE_DATABASE_CONFIG;

		foreach ($BOOTSITE_DATABASE_CONFIG as $config => $value) {
			if (empty($value)) {
				if ($config === 'password') {
					continue;
				}
				return false;
			}
		}

		if (self::connect()) {
			if (self::_doesConfigTableExist() === 1) {
				return true;
			}
			return false;
		}
	}
	 
	 
	 
	 /**
 * Checks if Bootsite_config table exists in the user's database.
 *
 * @todo  Get the resultset to evaluate to an expected value: 1 for 1 row in the resultset
 * and 0 for zero rows in the resultset. At the moment it returns 0 for both cases.
 * @return int
 */
	function _doesConfigTableExist() {
		global $BOOTSITE_DATABASE_CONFIG, $pdo;

		$query = $pdo->prepare("SELECT *
			FROM information_schema.tables
			WHERE table_name = 'bootsite_config'
			AND table_schema = '" . $BOOTSITE_DATABASE_CONFIG['database'] . "';");
		$query->execute();

		return $query->rowCount();
	}

/**
 * Gets and executes SQL commands for creating all default Bootsite tables.
 *
 * @return bool
 */
	function createBootsiteTables() {
		global $pdo;
		$script = BOOTSITE_DIR . '/scripts/bootsite_base_tables.sql';

		if (self::createPersistentConnection()) {
			if (file_exists($script)) {
				$query = file_get_contents($script);
				$qr = $pdo->exec($query);
				return true;
			} else {
				echo 'Bootsite Database migration script not found.';
				return false;
			}
		}
	}
	 
?>