<?php
defined( 'ABSPATH' ) || exit;

/**
 * Member (User profile) endpoints.
 *
 * @since 0.1.0
 */
class BP_REST_Members_Controller extends WP_REST_Controller {

	const FIELD_USER_ID_API = 'user_id';
	const FIELD_USER_NICENAME_API = 'user_nicename';
	const FIELD_USER_AVATAR_API = 'user_avatar';
	const FIELD_USER_PERMALINK_API = 'user_permalink';
	const FIELD_USER_TOTAL_FRIEND_COUNT_API = 'total_friend_count';

	const FIELD_NAME_XPROFILE = 'Name';
	const FIELD_NAME_API = 'user_name';
	const FIELD_ABOUT_ME_XPROFILE = 'About me';
	const FIELD_ABOUT_ME_API = 'user_about_me';
	const FIELD_GENDER_XPROFILE = 'Gender';
	const FIELD_GENDER_API = 'user_gender';
	const FIELD_DOB_XPROFILE = 'Date of birth';
	const FIELD_DOB_API = 'user_date_of_birth';
	const FIELD_COUNTRY_XPROFILE = 'Country';
	const FIELD_COUNTRY_API = 'user_country';
	const FIELD_STATEPROV_XPROFILE = 'State/Province';
	const FIELD_STATEPROV_API = 'user_state_province';
	const FIELD_DIARY_TITLE_XPROFILE = 'Diary Title';
	const FIELD_DIARY_TITLE_API = 'diary_title';
	const FIELD_DIARY_DESC_XPROFILE = 'Diary Description';
	const FIELD_DIARY_DESC_API = 'diary_description';
	const FIELD_DIARY_PRIVACY_XPROFILE = 'Privacy';
	const FIELD_DIARY_PRIVACY_API = 'diary_privacy';
	const FIELD_DIARY_ALLOW_COMMENTS_XPROFILE = 'Allow comments';
	const FIELD_DIARY_ALLOW_COMMENTS_API = 'diary_allow_comments';
	const FIELD_DIARY_ALLOW_PRIV_DIARY_COMMENT_XPROFILE = 'Allow private diaries to comment';
	const FIELD_DIARY_ALLOW_PRIV_DIARY_COMMENT_API = 'diary_allow_private_diaries_to_comment';
	const FIELD_DIARY_ALLOW_PRIV_COMMENT_ON_DIARY_XPROFILE = 'Allow private comments on diary';
	const FIELD_DIARY_ALLOW_PRIV_COMMENT_ON_DIARY_API = 'diary_allow_private_comments_on_diary';

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 */
	public function __construct() {
		$this->namespace = 'buddypress/v1';
		$this->rest_base = buddypress()->members->id;
	}

	/**
	 * Register the plugin routes.
	 *
	 * @since 0.1.0
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base, [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_items' ],
				'permission_callback' => [ $this, 'get_items_permissions_check' ],
				'args'                => $this->get_collection_params(),
			],
			'schema' => [ $this, 'get_public_item_schema' ],
		] );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_item' ],
				'permission_callback' => [ $this, 'get_item_permissions_check' ],
				'args'                => [
					'context' => $this->get_context_param( [ 'default' => 'view' ] ),
				],
			],
			'schema' => [ $this, 'get_public_item_schema' ],
		] );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', [
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [ $this, 'update_item' ],
				'permission_callback' => [ $this, 'update_item_permissions_check' ],
				'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE )
			]
		] );
	}

	/**
	 * Updates a user profile.
	 *
	 * @since 0.0.1
	 * @access public
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|WP_REST_Response Response object on success, or error object on failure.
	 */
	public function update_item( $request ) {
		$id = (int) $request['id'];

		$user = get_user_by( "id", $id );

		if ( empty( $user ) ) {
			return new WP_Error( 'rest_user_invalid_id', __( 'Invalid user ID.' ), [ 'status' => 404 ] );
		}

		$prepared_args = $this->prepare_item_for_database( $request );

		if ( is_wp_error( $prepared_args ) ) {
			return $prepared_args;
		}

		if ( array_key_exists( self::FIELD_NAME_API, $prepared_args ) ) {
			xprofile_set_field_data( self::FIELD_NAME_XPROFILE, $id, $prepared_args[ self::FIELD_NAME_API ] );
		}
		if ( array_key_exists( self::FIELD_ABOUT_ME_API, $prepared_args ) ) {
			xprofile_set_field_data( self::FIELD_ABOUT_ME_XPROFILE, $id, $prepared_args[ self::FIELD_ABOUT_ME_API ] );
		}
		if ( array_key_exists( self::FIELD_GENDER_API, $prepared_args ) ) {
			xprofile_set_field_data( self::FIELD_GENDER_XPROFILE, $id, $prepared_args[ self::FIELD_GENDER_API ] );
		}
		if ( array_key_exists( self::FIELD_DOB_API, $prepared_args ) ) {
			xprofile_set_field_data( self::FIELD_DOB_XPROFILE, $id, $prepared_args[ self::FIELD_DOB_API ] );
		}
		if ( array_key_exists( self::FIELD_COUNTRY_API, $prepared_args ) ) {
			xprofile_set_field_data( self::FIELD_COUNTRY_XPROFILE, $id, $prepared_args[ self::FIELD_COUNTRY_API ] );
		}
		if ( array_key_exists( self::FIELD_STATEPROV_API, $prepared_args ) ) {
			xprofile_set_field_data( self::FIELD_STATEPROV_XPROFILE, $id, $prepared_args[ self::FIELD_STATEPROV_API ] );
		}
		if ( array_key_exists( self::FIELD_DIARY_TITLE_API, $prepared_args ) ) {
			xprofile_set_field_data( self::FIELD_DIARY_TITLE_XPROFILE, $id, $prepared_args[ self::FIELD_DIARY_TITLE_API ] );
		}
		if ( array_key_exists( self::FIELD_DIARY_DESC_API, $prepared_args ) ) {
			xprofile_set_field_data( self::FIELD_DIARY_DESC_XPROFILE, $id, $prepared_args[ self::FIELD_DIARY_DESC_API ] );
		}
		if ( array_key_exists( self::FIELD_DIARY_PRIVACY_API, $prepared_args ) ) {
			xprofile_set_field_data( self::FIELD_DIARY_PRIVACY_XPROFILE, $id, $prepared_args[ self::FIELD_DIARY_PRIVACY_API ] );
		}
		if ( array_key_exists( self::FIELD_DIARY_ALLOW_COMMENTS_API, $prepared_args ) ) {
			xprofile_set_field_data( self::FIELD_DIARY_ALLOW_COMMENTS_XPROFILE, $id, $prepared_args[ self::FIELD_DIARY_ALLOW_COMMENTS_XPROFILE ] );
		}
		if ( array_key_exists( self::FIELD_DIARY_ALLOW_PRIV_DIARY_COMMENT_API, $prepared_args ) ) {
			xprofile_set_field_data( self::FIELD_DIARY_ALLOW_PRIV_DIARY_COMMENT_XPROFILE, $id, $prepared_args[ self::FIELD_DIARY_ALLOW_PRIV_DIARY_COMMENT_API ] );
		}
		if ( array_key_exists( self::FIELD_DIARY_ALLOW_PRIV_COMMENT_ON_DIARY_API, $prepared_args ) ) {
			xprofile_set_field_data( self::FIELD_DIARY_ALLOW_PRIV_COMMENT_ON_DIARY_XPROFILE, $id, $prepared_args[ self::FIELD_DIARY_ALLOW_PRIV_COMMENT_ON_DIARY_API ] );
		}

		$request->set_param( 'context', 'edit' );

		$updated_user_profile = $this->_get_user_profile_datum( [
			'include' => [ (int) $request['id'] ],
			'type'    => 'alphabetical'
		] );

		if ( empty( $updated_user_profile ) ) {
			// should never happen b/c an invalid id would have been detected by now, but just in case
			return new WP_REST_Response( [], 200 );
		}

		return new WP_REST_Response( $updated_user_profile[0], 200 );

	}

	private function _prepare_string_arg( $current_array, $key, $request ) {
		$retArray = $current_array;
		if ( isset( $request[ $key ] ) && is_string( $request[ $key ] ) ) {
			$retArray[ $key ] = sanitize_text_field( $request[ $key ] );
		}

		return $retArray;
	}

	/**
	 * Prepares a single comment to be inserted into the database.
	 *
	 * @since 0.0.1
	 * @access protected
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return array|WP_Error Prepared comment, otherwise WP_Error object.
	 */
	protected function prepare_item_for_database( $request ) {
		$prepared_user_profile = [];

		$string_param_keys = [
			self::FIELD_DIARY_TITLE_API,
			self::FIELD_DIARY_DESC_API,
			self::FIELD_NAME_API,
			self::FIELD_ABOUT_ME_API,
			self::FIELD_GENDER_API,
			self::FIELD_COUNTRY_API,
			self::FIELD_STATEPROV_API,
			self::FIELD_DIARY_PRIVACY_API,
			self::FIELD_DIARY_ALLOW_COMMENTS_API,
			self::FIELD_DIARY_ALLOW_PRIV_DIARY_COMMENT_API,
			self::FIELD_DIARY_ALLOW_PRIV_COMMENT_ON_DIARY_API
		];

		foreach ( $string_param_keys as $key ) {
			$prepared_user_profile = $this->_prepare_string_arg( $prepared_user_profile, $key, $request );
		}

		if ( isset( $request[ self::FIELD_DOB_API ] ) && is_string( $request[ self::FIELD_DOB_API ] ) ) {
			$prepared_user_profile[ self::FIELD_DOB_API ] = date( "Y-m-d H:i:s", strtotime( sanitize_text_field( $request[ self::FIELD_DOB_API ] ) ) );
		}

		if ( isset( $request['user_avatar'] ) && is_string( $request['user_avatar'] ) ) {
			$prepared_user_profile['user_avatar'] = esc_url_raw( $request['user_avatar'] );
		}

		/**
		 * Filters a user profile after it is prepared for the database.
		 *
		 * Allows modification of the user profile right after it is prepared for the database.
		 *
		 * @since 0.0.1
		 *
		 * @param array $prepared_user_profile The prepared user profile data.
		 * @param WP_REST_Request $request The current request.
		 */
		return apply_filters( 'rest_preprocess_bp_user_profile', $prepared_user_profile, $request );
	}

	/**
	 * Get the plugin schema, conforming to JSON Schema.
	 *
	 * @since 0.1.0
	 *
	 * @return array
	 */
	public function get_item_schema() {
		$schema = [
			'$schema' => 'http://json-schema.org/draft-04/schema#',
			'title'   => 'member',
			'type'    => 'object',

			'properties' => [
				self::FIELD_USER_ID_API => [
					'context'     => [ 'view', 'edit' ],
					'description' => __( 'A unique integer ID for the user.', 'buddypress' ),
					'readonly'    => true,
					'type'        => 'integer',
					'readonly'    => 'true'
				],

				self::FIELD_NAME_API => [
					'context'     => [ 'view', 'edit' ],
					'description' => __( 'The name of the user', 'buddypress' ),
					'type'        => 'string',
					'arg_options' => [
						'sanitize_callback' => 'sanitize_text_field',
					],
				],

				self::FIELD_ABOUT_ME_API => [
					'context'     => [ 'view', 'edit' ],
					'description' => __( 'The about me of the user', 'buddypress' ),
					'type'        => 'string',
					'arg_options' => [
						'sanitize_callback' => 'sanitize_text_field',
					],
				],

				self::FIELD_COUNTRY_API => [
					'context'     => [ 'view', 'edit' ],
					'description' => __( 'The country of the user', 'buddypress' ),
					'type'        => 'string',
					'arg_options' => [
						'sanitize_callback' => 'sanitize_text_field',
					],
				],

				self::FIELD_STATEPROV_API => [
					'context'     => [ 'view', 'edit' ],
					'description' => __( 'The state or province of the user', 'buddypress' ),
					'type'        => 'string',
					'arg_options' => [
						'sanitize_callback' => 'sanitize_text_field',
					],
				],

				self::FIELD_DOB_API => [
					'context'     => [ 'view', 'edit' ],
					'description' => __( 'The birth date of the user', 'buddypress' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'arg_options' => [
						'sanitize_callback' => 'sanitize_text_field',
					],
				],

				self::FIELD_GENDER_API => [
					'context'     => [ 'view', 'edit' ],
					'description' => __( 'The gender of the user', 'buddypress' ),
					'enum'        => [ 'Male', 'Female' ],
					'type'        => 'string',
					'arg_options' => [
						'sanitize_callback' => 'sanitize_text_field',
					],
				],

				self::FIELD_USER_NICENAME_API => [
					'context'     => [ 'view', 'edit' ],
					'description' => __( 'The sanitized name of the user', 'buddypress' ),
					'type'        => 'string',
					'readonly'    => true
				],

				self::FIELD_USER_AVATAR_API => [
					'context'     => [ 'view', 'edit' ],
					'description' => __( 'Avatar of the user', 'buddypress' ),
					'type'        => 'string',
					'format'      => 'url',
					'arg_options' => [
						'sanitize_callback' => 'esc_url',
					],
				],

				self::FIELD_USER_PERMALINK_API => [
					'context'     => [ 'view', 'edit' ],
					'description' => __( 'User profile page link', 'buddypress' ),
					'type'        => 'string',
					'format'      => 'url',
					'readonly'    => true
				],

				self::FIELD_DIARY_TITLE_API => [
					'context'     => [ 'view', 'edit' ],
					'description' => __( "Name of the user's diary", 'buddypress' ),
					'type'        => 'string',
					'arg_options' => [
						'sanitize_callback' => 'sanitize_text_field',
					],
				],

				self::FIELD_DIARY_DESC_API => [
					'context'     => [ 'view', 'edit' ],
					'description' => __( "Description of the user's diary", 'buddypress' ),
					'type'        => 'string',
					'arg_options' => [
						'sanitize_callback' => 'sanitize_text_field',
					],
				],

				self::FIELD_DIARY_PRIVACY_API => [
					'context'     => [ 'view', 'edit' ],
					'description' => __( "User's diary privacy settings", 'buddypress' ),
					'enum'        => [ 'Friends', 'Public', 'Private' ],
					'type'        => 'string',
					'arg_options' => [
						'sanitize_callback' => 'sanitize_text_field',
					],
				],

				self::FIELD_DIARY_ALLOW_COMMENTS_API => [
					'context'     => [ 'view', 'edit' ],
					'description' => __( "User's diary privacy settings", 'buddypress' ),
					'enum'        => [ 'Friends', 'Public', 'No' ],
					'type'        => 'string',
					'arg_options' => [
						'sanitize_callback' => 'sanitize_text_field',
					],
				],

				self::FIELD_DIARY_ALLOW_PRIV_DIARY_COMMENT_API => [
					'context'     => [ 'view', 'edit' ],
					'description' => __( "User's diary allowance for private diaries to comment", 'buddypress' ),
					'enum'        => [ 'Yes', 'No' ],
					'type'        => 'string',
					'arg_options' => [
						'sanitize_callback' => 'sanitize_text_field',
					],
				],

				self::FIELD_DIARY_ALLOW_PRIV_COMMENT_ON_DIARY_API => [
					'context'     => [ 'view', 'edit' ],
					'description' => __( "User's diary allowance for private comments to be left", 'buddypress' ),
					'enum'        => [ 'Yes', 'No', 'Allow only private comments' ],
					'type'        => 'string',
					'arg_options' => [
						'sanitize_callback' => 'sanitize_text_field',
					],
				],

				self::FIELD_USER_TOTAL_FRIEND_COUNT_API => [
					'context'     => [ 'view', 'edit' ],
					'description' => __( 'Number of friends the user has', 'buddypress' ),
					'type'        => 'int',
					'readonly'    => true
				]
			]
		];

		return $schema;
	}

	/**
	 * Get the query params for collections of plugins.
	 *
	 * @since 0.1.0
	 *
	 * @return array
	 */
	public function get_collection_params() {
		$params                       = parent::get_collection_params();
		$params['context']['default'] = 'view';

		$params['include'] = [
			'description'       => __( 'Ensure result set includes specific user IDs.', 'buddypress' ),
			'type'              => 'array',
			'default'           => [],
			'sanitize_callback' => 'wp_parse_id_list',
		];

		$params['exclude'] = [
			'description'       => __( 'Ensure result set excludes specific IDs.', 'buddypress' ),
			'type'              => 'array',
			'default'           => [],
			'sanitize_callback' => 'wp_parse_id_list',
		];

		$params['per_page'] = [
			'description'       => __( 'Maximum number of results returned per result set.', 'buddypress' ),
			'default'           => 20,
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'validate_callback' => 'rest_validate_request_arg',
		];

		$params['page'] = [
			'description'       => __( 'Offset the result set by a specific number of pages of results.', 'buddypress' ),
			'default'           => 1,
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'validate_callback' => 'rest_validate_request_arg',
		];

		$params['type'] = [
			'description'       => __( 'Limit result set to items with a specific member type.', 'buddypress' ),
			'type'              => 'string',
			'enum'              => [ 'active', 'newest', 'popular', 'online', 'alphabetical', 'random' ],
			'default'           => 'alphabetical',
			'validate_callback' => 'rest_validate_request_arg',
		];

		$params['search'] = [
			'description'       => __( 'Limit result set to items that match this search query.', 'buddypress' ),
			'default'           => null,
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'validate_callback' => 'rest_validate_request_arg',
		];

		$params['meta_key'] = [
			'description'       => __( 'Limit result set to items that match this metadata.', 'buddypress' ),
			'default'           => null,
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'validate_callback' => 'rest_validate_request_arg',
		];

		$params['meta_value'] = [
			'description'       => __( 'Limit result set to items that match this search query.', 'buddypress' ),
			'default'           => null,
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'validate_callback' => 'rest_validate_request_arg',
		];

		$params['user_id'] = [
			'description'       => __( 'Ensure result set includes specific user IDs.', 'buddypress' ),
			'type'              => 'array',
			'default'           => [],
			'sanitize_callback' => 'wp_parse_id_list',
		];

		$params['populate_extras'] = [
			'description'       => __( 'Fetch extra meta for each user such as their full name, if they are a friend of the logged in user, their last activity time.', 'buddypress' ),
			'default'           => true,
			'type'              => 'boolean',
			'sanitize_callback' => 'sanitize_text_field',
			'validate_callback' => 'rest_validate_request_arg',
		];

		return $params;
	}

	/**
	 * Retrieve members.
	 *
	 * @since 0.1.0
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response List of members.
	 */
	public function get_items( $request ) {

		$retval = $this->_get_user_profile_datum( $this->_get_args( $request ) );

		return new WP_REST_Response( $retval, 200 );
	}

	private function _get_args( $request ) {
		$args = [
			'exclude'         => $request['exclude'],
			'include'         => $request['include'],
			'page'            => $request['page'],
			'per_page'        => $request['per_page'],
			'search_terms'    => $request['search'],
			'meta_key'        => $request['meta_key'],
			'meta_value'      => $request['meta_value'],
			'user_id'         => $request['user_id'],
			'type'            => $request['type'],
			'populate_extras' => $request['populate_extras'] === false ? false : true
		];

		if ( ! empty( $args['include'] ) ) {
			$args['count_total'] = false;
		}

		foreach ( [ 'user_id', 'meta_key', 'meta_value', 'exclude', 'include' ] as $param ) {
			if ( array_key_exists( $param, $args ) && empty( $args[ $param ] ) ) {
				unset( $args[ $param ] );
			}
		}

		return $args;
	}

	/**
	 * Retrieve user profile.
	 *
	 * @since 0.1.0
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Request|WP_Error Plugin object data on success, WP_Error otherwise.
	 */
	public function get_item( $request ) {
		$retval = $this->_get_user_profile_datum( [ 'include' => [ (int) $request['id'] ], 'type' => 'alphabetical' ] );
		if ( empty( $retval ) ) {
			return new WP_REST_Response( [], 200 );
		}

		return new WP_REST_Response( $retval[0], 200 );
	}

	private function _get_user_profile_datum( $args ) {
		$ret = [];
		if ( bp_has_members( $args ) ) :
			while ( bp_members() ) : bp_the_member();
				$member                                                      = [];
				$member[ self::FIELD_USER_ID_API ]                           = bp_get_member_user_id();
				$member[ self::FIELD_USER_AVATAR_API ]                       = bp_get_member_avatar();
				$member[ self::FIELD_USER_PERMALINK_API ]                    = bp_get_member_permalink();
				$member[ self::FIELD_NAME_API ]                              = bp_get_profile_field_data( [
					'field'   => self::FIELD_NAME_XPROFILE,
					'user_id' => bp_get_member_user_id()
				] );
				$member['user_nicename']                                     = bp_get_member_user_nicename();
				$member[ self::FIELD_ABOUT_ME_API ]                          = bp_get_profile_field_data( [
					'field'   => self::FIELD_ABOUT_ME_XPROFILE,
					'user_id' => bp_get_member_user_id()
				] );
				$member[ self::FIELD_GENDER_API ]                            = bp_get_profile_field_data( [
					'field'   => self::FIELD_GENDER_XPROFILE,
					'user_id' => bp_get_member_user_id()
				] );
				$member[ self::FIELD_DOB_API ]                               = $this->prepare_date_response( bp_get_profile_field_data( [
					'field'   => self::FIELD_DOB_XPROFILE,
					'user_id' => bp_get_member_user_id()
				] ) );
				$member[ self::FIELD_COUNTRY_API ]                           = bp_get_profile_field_data( [
					'field'   => self::FIELD_COUNTRY_XPROFILE,
					'user_id' => bp_get_member_user_id()
				] );
				$member[ self::FIELD_STATEPROV_API ]                         = bp_get_profile_field_data( [
					'field'   => self::FIELD_STATEPROV_XPROFILE,
					'user_id' => bp_get_member_user_id()
				] );
				$member[ self::FIELD_DIARY_TITLE_API ]                       = bp_get_profile_field_data( [
					'field'   => self::FIELD_DIARY_TITLE_XPROFILE,
					'user_id' => bp_get_member_user_id()
				] );
				$member[ self::FIELD_DIARY_DESC_API ]                        = bp_get_profile_field_data( [
					'field'   => self::FIELD_DIARY_DESC_XPROFILE,
					'user_id' => bp_get_member_user_id()
				] );
				$member[ self::FIELD_DIARY_PRIVACY_API ]                     = bp_get_profile_field_data( [
					'field'   => self::FIELD_DIARY_PRIVACY_XPROFILE,
					'user_id' => bp_get_member_user_id()
				] );
				$member[ self::FIELD_DIARY_ALLOW_COMMENTS_API ]              = bp_get_profile_field_data( [
					'field'   => self::FIELD_DIARY_ALLOW_COMMENTS_XPROFILE,
					'user_id' => bp_get_member_user_id()
				] );
				$member[ self::FIELD_DIARY_ALLOW_PRIV_DIARY_COMMENT_API ]    = bp_get_profile_field_data( [
					'field'   => self::FIELD_DIARY_ALLOW_PRIV_DIARY_COMMENT_XPROFILE,
					'user_id' => bp_get_member_user_id()
				] );
				$member[ self::FIELD_DIARY_ALLOW_PRIV_COMMENT_ON_DIARY_API ] = bp_get_profile_field_data( [
					'field'   => self::FIELD_DIARY_ALLOW_PRIV_COMMENT_ON_DIARY_XPROFILE,
					'user_id' => bp_get_member_user_id()
				] );
				$member[ self::FIELD_USER_TOTAL_FRIEND_COUNT_API ]           = bp_get_member_total_friend_count();
				$ret[]                                                       = $member;
			endwhile;
		endif;

		return $ret;
	}

	/**
	 * Check if a given request has access to get information about a specific member.
	 *
	 * @since 0.1.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return bool
	 */
	public function get_item_permissions_check( $request ) {
		return $this->get_items_permissions_check( $request );
	}

	/**
	 * Check if a given request has access to get information about a specific member.
	 *
	 * @since 0.1.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return bool
	 */
	public function update_item_permissions_check( $request ) {
		return true; // TODO re-evaluate after oauth integrated
	}

	/**
	 * Check if a given request has access to member items.
	 *
	 * @since 0.1.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|bool
	 */
	public function get_items_permissions_check( $request ) {
		// TODO: handle private activities etc
		return true;
	}

	/**
	 * Convert the input date to RFC3339 format.
	 *
	 * @param string $date_gmt
	 * @param string|null $date Optional. Date object.
	 *
	 * @return string|null ISO8601/RFC3339 formatted datetime.
	 */
	protected function prepare_date_response( $date_gmt, $date = null ) {
		if ( isset( $date ) ) {
			return mysql_to_rfc3339( $date );
		}

		if ( $date_gmt === '0000-00-00 00:00:00' ) {
			return null;
		}

		return mysql_to_rfc3339( $date_gmt );
	}
}
