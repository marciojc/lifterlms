<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Person_Handler {

	private static $meta_prefix = 'llms_';

	/**
	 * Generate a unique login based on the user's email address
	 * @param  string $email user's email address
	 * @return string
	 * @since  3.0.0
	 */
	private static function generate_username( $email ) {

		/**
		 * Allow custom username generation
		 */
		$custom_username = apply_filters( 'lifterlms_generate_username', null, $email );
		if ( $custom_username ) {
			return $custom_username;
		}

		$username = sanitize_user( current( explode( '@', $email ) ) );
		$orig_username = $username;
		$i = 1;
		while ( username_exists( $username ) ) {

			$username = $orig_username . $i;
			$i++;

		}

		return apply_filters( 'lifterlms_gnerated_username', $username, $email );

	}


	public static function get_login_fields() {

		$gen_usernames = ( 'yes' === get_option( 'lifterlms_registration_generate_username' ) );

		return apply_filters( 'lifterlms_person_login_fields', array(
			array(
				'columns' => 6,
				'id' => 'llms_login',
				'label' => $gen_usernames ? __( 'Email Address', 'lifterlms' ) : __( 'Username or Email Address', 'lifterlms' ),
				'last_column' => false,
				'required' => true,
				'type'  => $gen_usernames ? 'email' : 'text',
			),
			array(
				'columns' => 6,
				'id' => 'llms_password',
				'label' => __( 'Password', 'lifterlms' ),
				'last_column' => true,
				'required' => true,
				'type'  => 'password',
			),
			array(
				'columns' => 3,
				'id' => 'llms_login_button',
				'value' => __( 'Login', 'lifterlms' ),
				'last_column' => false,
				'required' => false,
				'type'  => 'submit',
			),
			array(
				'columns' => 6,
				'id' => 'llms_remember',
				'label' => __( 'Remember me', 'lifterlms' ),
				'last_column' => false,
				'required' => false,
				'type'  => 'checkbox',
			),
			array(
				'columns' => 3,
				'id' => 'llms_lost_password',
				'last_column' => true,
				'description' => '<a href="' . esc_url( llms_lostpassword_url() ) . '">' . __( 'Lost your password?', 'lifterlms' ) . '</a>',
				'type' => 'html',
				'wrapper_classes' => 'align-right',
			),
		) );

	}


	public static function get_available_fields( $screen = 'registration', $data = array() ) {

		$uid = get_current_user_id();

		// setup all the fields to load
		$fields = array();

		// this isn't needed if we're on an update screen or
		if ( 'update' !== $screen && ( 'checkout' !== $screen || ! $uid ) ) {
			$fields[] = array(
				'columns' => 12,
				'id' => 'user_login',
				'label' => __( 'Username', 'lifterlms' ),
				'last_column' => true,
				'required' => true,
				'type'  => ( 'yes' === get_option( 'lifterlms_registration_generate_username' ) ) ? 'hidden' : 'text',
			);
		}

		// on the checkout screen, if we already have a user we can remove these fields:
		// username, email, email confirm, password, password confirm, password meter
		if ( 'checkout' !== $screen || ! $uid ) {
			$email_con = get_option( 'lifterlms_user_info_field_email_confirmation_' . $screen . '_visibility' );
			$fields[] = array(
				'columns' => ( 'no' === $email_con ) ? 12 : 6,
				'id' => 'email_address',
				'label' => __( 'Email Address', 'lifterlms' ),
				'last_column' => ( 'no' === $email_con ) ? true : false,
				'matched' => 'email_address_confirm',
				'required' => true,
				'type'  => 'email',
			);
			if ( 'yes' === $email_con ) {
				$fields[] = array(
					'columns' => 6,
					'id' => 'email_address_confirm',
					'label' => __( 'Confirm Email Address', 'lifterlms' ),
					'last_column' => true,
					'match' => 'email_address',
					'required' => true,
					'type'  => 'email',
				);
			}

			$fields[] = array(
				'columns' => 6,
				'classes' => 'llms-password',
				'id' => 'password',
				'label' => __( 'Password', 'lifterlms' ),
				'last_column' => false,
				'matched' => 'password_confirm',
				'type'  => 'password',
				'required' => true,
			);
			$fields[] = array(
				'columns' => 6,
				'classes' => 'llms-password-confirm',
				'id' => 'password_confirm',
				'label' => __( 'Confirm Password', 'lifterlms' ),
				'last_column' => true,
				'match' => 'password',
				'required' => true,
				'type'  => 'password',
			);

			if ( 'yes' === get_option( 'lifterlms_registration_password_strength' ) ) {
				$strength = llms_get_minimum_password_strength();
				if ( 'strong' === $strength ) {
					$desc = __( 'A %s password is required.', 'lifterlms' );
				} else {
					$desc = __( 'A minimum password strength of %s is required.', 'lifterlms' );
				}

				$fields[] = array(
					'columns' => 12,
					'classes' => 'llms-password-strength-meter',
					'description' => sprintf( $desc, llms_get_minimum_password_strength_name() ) . ' ' . __( 'The password must be at least 6 characters in length. Consider adding letters, numbers, and symbols to increase the password strength.', 'lifterlms' ),
					'id' => 'llms-password-strength-meter',
					'last_column' => true,
					'type'  => 'html',
				);
			}
		}

		$names = get_option( 'lifterlms_user_info_field_names_' . $screen . '_visibility' );
		if ( 'hidden' !== $names ) {
			$fields[] = array(
				'columns' => 6,
				'id' => 'first_name',
				'label' => __( 'First Name', 'lifterlms' ),
				'last_column' => false,
				'required' => ( 'required' === $names ) ? true : false,
				'type'  => 'text',
			);
			$fields[] = array(
				'columns' => 6,
				'id' => 'last_name',
				'label' => __( 'Last Name', 'lifterlms' ),
				'last_column' => true,
				'required' => ( 'required' === $names ) ? true : false,
				'type'  => 'text',
			);
		}


		$address = get_option( 'lifterlms_user_info_field_address_' . $screen . '_visibility' );
		if ( 'hidden' !== $address ) {
			$fields[] = array(
				'columns' => 8,
				'id' => self::$meta_prefix . 'billing_address_1',
				'label' => __( 'Street Address', 'lifterlms' ),
				'last_column' => false,
				'required' => ( 'required' === $address ) ? true : false,
				'type'  => 'text',
			);
			$fields[] = array(
				'columns' => 4,
				'id' => self::$meta_prefix . 'billing_address_2',
				'label' => '&nbsp;',
				'last_column' => true,
				'placeholder' => __( 'Apartment, suite, or unit', 'lifterlms' ),
				'required' => false,
				'type'  => 'text',
			);
			$fields[] = array(
				'columns' => 6,
				'id' => self::$meta_prefix . 'billing_city',
				'label' => __( 'City', 'lifterlms' ),
				'last_column' => false,
				'required' => ( 'required' === $address ) ? true : false,
				'type'  => 'text',
			);
			$fields[] = array(
				'columns' => 3,
				'id' => self::$meta_prefix . 'billing_state',
				'label' => __( 'State', 'lifterlms' ),
				'last_column' => false,
				'required' => ( 'required' === $address ) ? true : false,
				'type'  => 'text',
			);
			$fields[] = array(
				'columns' => 3,
				'id' => self::$meta_prefix . 'billing_zip',
				'label' => __( 'Zip Code', 'lifterlms' ),
				'last_column' => true,
				'required' => ( 'required' === $address ) ? true : false,
				'type'  => 'text',
			);
			$fields[] = array(
				'columns' => 12,
				'id' => self::$meta_prefix . 'billing_country',
				'label' => __( 'Country', 'lifterlms' ),
				'last_column' => true,
				'options' => get_lifterlms_countries(),
				'required' => ( 'required' === $address ) ? true : false,
				'type'  => 'select',
			);
		}

		$phone = get_option( 'lifterlms_user_info_field_phone_' . $screen . '_visibility' );
		if ( 'hidden' !== $phone ) {
			$fields[] = array(
				'columns' => 12,
				'id' => self::$meta_prefix . 'phone',
				'label' => __( 'Phone Number', 'lifterlms' ),
				'last_column' => true,
				'placeholder' => _x( '(123) 456 - 7890', 'Phone Number Placeholder', 'lifterlms' ),
				'required' => ( 'required' === $phone ) ? true : false,
				'type'  => 'text',
			);
		}

		$fields = apply_filters( 'lifterlms_get_person_fields', $fields, $screen );

		// populate fields with data, if we have any
		if ( $data ) {
			$fields = self::fill_fields( $fields, $data );
		}

		return $fields;

	}

	/**
	 * Field an array of user fields retrieved from self::get_available_fields() with data
	 * the resulting array will be the data retrived from self::get_available_fields() with "value" keys filled for each field
	 *
	 * @param  array $fields array of fields from self::get_available_fields()
	 * @param  array $data   array of data (from a $_POST or function)
	 * @return array
	 *
	 * @since  3.0.0
	 */
	private static function fill_fields( $fields, $data ) {

		if ( is_numeric ( $data ) ) {
			$user = new WP_User( $data );
		}

		foreach( $fields as &$field ) {

			if ( 'password' === $field['type'] ) {
				continue;
			}

			$name = isset( $field['name'] ) ? $field['name'] : $field['id'];
			if ( isset( $data[$name] ) ) {
				$field['value'] = $data[$name];
			} elseif( isset( $user ) ) {
				$field['value'] = $user->{$name};
			}
		}

		return $fields;

	}


	private static function insert_data( $data = array(), $action = 'registration' ) {


		if ( 'registration' === $action ) {
			$insert_data = array(
				'role' => 'studnet',
				'show_admin_bar_front' => false,
				'user_email' => $data['email_address'],
				'user_login' => $data['user_login'],
				'user_pass' => $data['password'],
			);

			$extra_data = array(
				'first_name',
				'last_name',
			);

			$insert_func = 'wp_insert_user';
			$meta_func = 'add_user_meta';

		} elseif ( 'update' === $action ) {

			$insert_data = array(
				'ID' => $data['user_id'],
			);

			$extra_data = array(
				'first_name',
				'last_name',
				'user_email',
				'user_pass',
			);


			$insert_func = 'wp_update_user';
			$meta_func = 'update_user_meta';

		} else {
			return new WP_Error( 'invalid', __( 'Invalid action' ) );
		}


		foreach( $extra_data as $field ) {
			if ( isset( $data[ $field ] ) ) {
				$insert_data[ $field ] = $data[$field];
			}
		}

		// attempt to insert the data
		$person_id = $insert_func( apply_filters( 'lifterlms_user_' . $action . '_insert_user', $insert_data, $data, $action ) );

		// return the error object if registration fails
		if ( is_wp_error( $person_id ) ) {
			return apply_filters( 'lifterlms_user_' . $action . '_failure', $person_id, $data, $action );
		}

		// add user ip address
		$data[ self::$meta_prefix . 'ip_address' ] = llms_get_ip_address();

		// metas
		$possible_metas = array(
			self::$meta_prefix . 'billing_address_1',
			self::$meta_prefix . 'billing_address_2',
			self::$meta_prefix . 'billing_city',
			self::$meta_prefix . 'billing_state',
			self::$meta_prefix . 'billing_zip',
			self::$meta_prefix . 'billing_country',
			self::$meta_prefix . 'ip_address',
			self::$meta_prefix . 'phone',
		);
		$insert_metas = array();
		foreach( $possible_metas as $meta ) {
			if ( isset( $data[ $meta ] ) ) {
				$insert_metas[ $meta ] = $data[$meta];
			}
		}

		// record all meta values
		$metas = apply_filters( 'lifterlms_user_' . $action . '_insert_user_meta', $insert_metas, $data, $action );
		foreach( $metas as $key => $val ) {
			$meta_func( $person_id, $key, $val );
		}

		// if agree to terms data is present, record the agreement date
		if ( isset( $data[ self::$meta_prefix . 'agree_to_terms' ] ) && 'yes' === $data[ self::$meta_prefix . 'agree_to_terms' ] ) {

			$meta_func( $person_id, self::$meta_prefix . 'agree_to_terms', current_time( 'mysql' ) );

		}

		return $person_id;

	}


	public static function login( $data ) {

		do_action( 'lifterlms_before_user_login', $data );

		// validate the fields & allow custom validation to occur
		$valid = self::validate_fields( apply_filters( 'lifterlms_user_login_data', $data ), 'login' );

		// if errors found, return them
		if ( is_wp_error( $valid ) ) {

			return apply_filters( 'lifterlms_user_login_errors', $valid, $data );

		}
		// log the user in
		else {

			$creds = array();
			$e = new WP_Error( 'login-error', __( 'Could not find an account with the supplied email address and password combination.', 'lifterlms' ) );

			// get the username from the email address
			if ( 'yes' === get_option( 'lifterlms_registration_generate_username' ) && apply_filters( 'lifterlms_get_username_from_email', true ) ) {

				$user = get_user_by( 'email', $data['llms_login'] );

				if ( isset( $user->user_login ) ) {

					$creds['user_login'] = $user->user_login;

				} else {

					return $e;

				}

			} else {

				$creds['user_login'] = $data['llms_login'];

			}

			$creds['user_password'] = $_POST['llms_password'];
			$creds['remember'] = isset( $_POST['llms_remember'] );

			$ssl = is_ssl() ? true : false;

			$signon = wp_signon( apply_filters( 'lifterlms_login_credentials', $creds ), $ssl );

			if ( is_wp_error( $signon ) ) {
				$e = apply_filters( 'lifterlms_user_login_errors', $e, $data );
				return $e;
			} else {
				return $signon->ID;
			}

		}

	}



	/**
	 * Perform validations according to the registration screen and register a user
	 *
	 * @see  llms_register_user() for a classless wrapper for this function
	 *
	 * @param  array  $data   array of user data
	 *                        array(
	 *                        	'user_login' => '',
	 *                        	'email_address' => '',
	 *                        	'email_address_confirm' => '',
	 *                        	'password' => '',
	 *                        	'password_confirm' => '',
	 *                        	'first_name' => '',
	 *                        	'last_name' => '',
	 *                        	'llms_billing_address_1' => '',
	 *                        	'llms_billing_address_2' => '',
	 *                        	'llms_billing_city' => '',
	 *                        	'llms_billing_state' => '',
	 *                        	'llms_billing_zip' => '',
	 *                        	'llms_billing_country' => '',
	 *                        	'llms_phone' => '',
	 *                        )
	 * @param  string $screen  screen to perform validations for, accepts "registration" or "checkout"
	 * @param  bool   $signon  if true, also signon the newly created user
	 * @return int|WP_Error
	 *
	 * @since  3.0.0
	 */
	public static function register( $data = array(), $screen = 'registration', $signon = true ) {

		do_action( 'lifterlms_before_user_registration', $data, $screen );

		// generate a username if we're supposed to generate a username
		if ( 'yes' === get_option( 'lifterlms_registration_generate_username' ) ) {
			$data['user_login'] = self::generate_username( $data['email_address'] );
		}

		// validate the fields & allow custom validation to occur
		$valid = self::validate_fields( apply_filters( 'lifterlms_user_registration_data', $data, $screen ), $screen );

		// if errors found, return them
		if ( is_wp_error( $valid ) ) {

			return apply_filters( 'lifterlms_user_registration_errors', $valid, $data, $screen );

		}

		// register the user
		else {

			do_action( 'lifterlms_user_registration_after_validation', $data, $screen );

			// create the user and update all metadata
			$person_id = self::insert_data( $data, 'registration' ); // even during checkout we want to call this registration

			// return the error object if registration fails
			if ( is_wp_error( $person_id ) ) {
				return $person_id; // this is filtered already
			}

			// signon
			if ( $signon ) {
				llms_set_person_auth_cookie( $person_id, false );
			}

			// fire actions
			do_action( 'lifterlms_created_person', $person_id, $data, $screen );
			do_action( 'lifterlms_user_registered', $person_id, $data, $screen );

			// return the ID
			return $person_id;

		}

	}


	/**
	 * Perform validations according to $screen and update the user
	 *
	 * @see  llms_update_user() for a classless wrapper for this function
	 *
	 * @param  array  $data   array of user data
	 *                        array(
	 *                        	'user_id' => '',
	 *                        	'user_login' => '',
	 *                        	'email_address' => '',
	 *                        	'email_address_confirm' => '',
	 *                        	'password' => '',
	 *                        	'password_confirm' => '',
	 *                        	'first_name' => '',
	 *                        	'last_name' => '',
	 *                        	'llms_billing_address_1' => '',
	 *                        	'llms_billing_address_2' => '',
	 *                        	'llms_billing_city' => '',
	 *                        	'llms_billing_state' => '',
	 *                        	'llms_billing_zip' => '',
	 *                        	'llms_billing_country' => '',
	 *                        	'llms_phone' => '',
	 *                        )
	 * @param  string $screen  screen to perform validations for, accepts "update" or "checkout"
	 * @return int|WP_Error
	 *
	 * @since  3.0.0
	 */
	public static function update( $data = array(), $screen = 'update' ) {

		do_action( 'lifterlms_before_user_update', $data, $screen );

		// user_id will automatically be the current user if non provided
		if ( empty( $data['user_id'] ) ) {
			$data['user_id'] = get_current_user_id();
		}

		// if no user id available, return an error
		if ( ! $data['user_id'] ) {
			$e = new WP_Error();
			$e->add( 'user_id', __( 'No user ID specified.', 'lifterlms' ), 'missing-user-id' );
			return $e;
		}

		// validate the fields & allow custom validation to occur
		$valid = self::validate_fields( apply_filters( 'lifterlms_user_update_data', $data, $screen ), $screen );

		// if errors found, return them
		if ( is_wp_error( $valid ) ) {

			return apply_filters( 'lifterlms_user_update_errors', $valid, $data, $screen );

		}

		// update the user
		else {

			do_action( 'lifterlms_user_update_after_validation', $data, $screen );

			// create the user and update all metadata
			$person_id = self::insert_data( $data, 'update' );

			// return the error object if registration fails
			if ( is_wp_error( $person_id ) ) {
				return $person_id; // this is filtered already
			}

			do_action( 'lifterlms_user_updated', $person_id, $data, $screen );

			return $person_id;

		}

	}

	/**
	 * Validate submitted user data for registration or profile updates
	 *
	 * @param  array  $data   user data array
	 *                        array(
	 *                        	'user_login' => '',
	 *                        	'email_address' => '',
	 *                        	'email_address_confirm' => '',
	 *                        	'password' => '',
	 *                        	'password_confirm' => '',
	 *                        	'first_name' => '',
	 *                        	'last_name' => '',
	 *                        	'llms_billing_address_1' => '',
	 *                        	'llms_billing_address_2' => '',
	 *                        	'llms_billing_city' => '',
	 *                        	'llms_billing_state' => '',
	 *                        	'llms_billing_zip' => '',
	 *                        	'llms_billing_country' => '',
	 *                        	'llms_phone' => '',
	 *                        )
	 * @param  string $screen screen to validate fields against, accepts "checkout", "registration", or "update"
	 * @return true|WP_Error
	 *
	 * @since  3.0.0 [<description>]
	 */
	public static function validate_fields( $data, $screen = 'registration' ) {

		if ( 'login' === $screen ) {

			$fields = self::get_login_fields();

		} else {

			$fields = self::get_available_fields( $screen );

		}


		// $data['user_login'] = 'admin';
		// $data['email_address'] = 'thomas@gocodebox.com';
		// $data['email_address'] = 'asdf';
		// unset( $data['password_confirm'] );
		// $data['password_confirm'] = 'asdfa9df';
		// $data['llms_billing_country'] = 'asdflkioasdf';

		$e = new WP_Error();

		$matched_values = array();

		foreach( $fields as $field ) {

			$name = isset( $field['name'] ) ? $field['name'] : $field['id'];
			$label = isset( $field['label'] ) ? $field['label'] : $name;

			$val = isset( $data[$name] ) ? $data[$name] : '';

			// ensure required fields are submitted
			if ( isset( $field['required'] ) && $field['required'] && empty( $val ) ) {

				$e->add( $field['id'], sprintf( __( '%s is a required field', 'lifterlms' ), $label ), 'required' );
				continue;

			}

			$val = sanitize_text_field( $val );

			// check email field for uniqueness
			if ( 'email_address' === $name ) {
				if ( email_exists( $val ) ) {
					$e->add( $field['id'], sprintf( __( 'An account with the email address "%s" already exists.', 'lifterlms' ), $val ), 'email-exists' );
				}
			}

			// validate the username
			if ( 'user_login' === $name ) {

				// blacklist usernames for security purposes
				$banned_usernames = apply_filters( 'llms_usernames_blacklist', array( 'admin', 'test', 'administrator', 'password', 'testing' ) );

				if ( in_array( $val, $banned_usernames ) || ! validate_username( $val ) ) {

					$e->add( $field['id'], sprintf( __( 'The username "%s" is invalid, please try a different username.', 'lifterlms' ), $val ), 'invalid-username' );

				} elseif ( username_exists( $val ) ) {

					$e->add( $field['id'], sprintf( __( 'An account with the username "%s" already exists.', 'lifterlms' ), $val ), 'username-exists' );

				}

			}

			// scrub and check field data types
			if ( isset( $field['type'] ) ) {

				switch( $field['type'] ) {

					// add the opposite value if not set
					case 'checkbox':
					break;

					// ensure it's a selectable option
					case 'select':
					case 'radio':
						if ( ! in_array( $val, array_keys( $field['options'] ) ) ) {
							$e->add( $field['id'], sprintf( __( '"%s" is an invalid option for %s', 'lifterlms' ), $val, $label ), 'invalid' );
						}
					break;

					// case 'password':
					// case 'text':
					// case 'textarea':
					// break;

					// make sure the value is numeric
					case 'number':
						if ( ! is_numeric( $val ) ) {
							$e->add( $field['id'], sprintf( __( '%s is a required field', 'lifterlms' ), $label ), 'invalid' );
							continue 2;
						}
					break;

					// validate the email address
					case 'email':
						if ( ! is_email( $val ) ) {
							$e->add( $field['id'], sprintf( __( '%s must be a valid email address', 'lifterlms' ), $label ), 'invalid' );
						}
					break;

				}

			}

			// store this fields label so it can be used in a match error later if necessary
			if ( ! empty( $field['matched'] ) ) {

				$matched_values[ $field['matched'] ] = $label;

			}

			// match matchy fields
			if ( ! empty( $field['match'] ) ) {

				$match = isset( $data[ $field['match'] ] ) ? $data[ $field['match'] ] : false;

				if ( ! $match || $val !== $match ) {

					$e->add( $field['id'], sprintf( __( '%s must match %s', 'lifterlms' ), $matched_values[$field['id']], $label ), 'match' );

				}

			}


		}

		// return errors if we have errors
		if ( $e->get_error_messages() ) {

			return $e;

		}

		return true;

	}

}