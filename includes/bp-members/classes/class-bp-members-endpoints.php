<?php
defined( 'ABSPATH' ) || exit;

/**
 * Activity endpoints.
 *
 * @since 0.1.0
 */
class BP_REST_Members_Controller extends WP_REST_Controller {

	const FIELD_NAME_XPROFILE= 'Name';
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
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'                => $this->get_collection_params(),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
				'args'                => array(
					'context' => $this->get_context_param( array( 'default' => 'view' ) ),
				),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_item' ),
				'permission_callback' => array( $this, 'update_item_permissions_check' ),
				'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE )
			)
		) );
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
		$id = (int) $request['user_id'];

		$user = get_user_by( "id", $id );

		if ( empty( $user ) ) {
			return new WP_Error( 'rest_user_invalid_id', __( 'Invalid user ID.' ), array( 'status' => 404 ) );
		}

		$prepared_args = $this->prepare_item_for_database( $request );

		if ( is_wp_error( $prepared_args ) ) {
			return $prepared_args;
		}

		xprofile_set_field_data( 'Name', $id, $prepared_args['user_name'] );
		xprofile_set_field_data( 'About me', $id, $prepared_args['user_about_me'] );
		xprofile_set_field_data( 'Gender', $id, $prepared_args['user_gender'] );
		xprofile_set_field_data( 'Date of birth', $id, $prepared_args['user_date_of_birth'] );
		xprofile_set_field_data( 'Country', $id, $prepared_args['user_country'] );
		xprofile_set_field_data( 'State/Province', $id, $prepared_args['user_state_province'] );
		xprofile_set_field_data( 'Diary Title', $id, $prepared_args['diary_title'] );
		xprofile_set_field_data( 'Diary Description', $id, $prepared_args['diary_description'] );
		xprofile_set_field_data( 'Privacy', $id, $prepared_args['diary_privacy'] );
		xprofile_set_field_data( 'Allow comments', $id, $prepared_args['diary_allow_comments'] );
		xprofile_set_field_data( 'Allow private diaries to comment', $id, $prepared_args['diary_allow_private_diaries_to_comment'] );
		xprofile_set_field_data( 'Allow private comments on diary', $id, $prepared_args['diary_allow_private_comments_on_diary'] );

		$request->set_param( 'context', 'edit' );

		$updated_user_profile = $this->_get_user_profile_data( $request['user_id'] );

		return new WP_REST_Response( $updated_user_profile, 200 );
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
		$prepared_user_profile = array();

		$string_param_keys = [
			'diary_title',
			'diary_description',
			'user_name',
			'user_about_me',
			'user_gender',
			'user_country',
			'user_state_province',
			'diary_privacy',
			'diary_allow_comments',
			'diary_allow_private_diaries_to_comment',
			'diary_allow_private_comments_on_diary'
		];

		foreach ( $string_param_keys as $key ) {
			$prepared_user_profile = $this->_prepare_string_arg( $prepared_user_profile, $key, $request );
		}

		if ( isset( $request['user_date_of_birth'] ) && is_string( $request['user_date_of_birth'] ) ) {
			$prepared_user_profile['user_date_of_birth'] = date( "Y-m-d H:i:s", strtotime( sanitize_text_field( $request['user_date_of_birth'] ) ) );
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
		$schema = array(
			'$schema' => 'http://json-schema.org/draft-04/schema#',
			'title'   => 'activity',
			'type'    => 'object',

			'properties' => array(
				'user_id' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'A unique integer ID for the user.', 'buddypress' ),
					'readonly'    => true,
					'type'        => 'integer',
					'readonly'    => 'true'
				),

				'user_name' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'The name of the user', 'buddypress' ),
					'type'        => 'string',
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),

				'user_about_me' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'The about me of the user', 'buddypress' ),
					'type'        => 'string',
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),

				'user_country' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'The country of the user', 'buddypress' ),
					'type'        => 'string',
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),

				'user_state_province' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'The state or province of the user', 'buddypress' ),
					'type'        => 'string',
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),

				'user_date_of_birth' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'The birth date of the user', 'buddypress' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),

				'user_gender' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'The gender of the user', 'buddypress' ),
					'enum'        => array( 'Male', 'Female' ),
					'type'        => 'string',
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),

				'user_nicename' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'The sanitized name of the user', 'buddypress' ),
					'type'        => 'string',
					'readonly'    => true
				),

				'user_avatar' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'Avatar of the user', 'buddypress' ),
					'type'        => 'string',
					'format'      => 'url',
					'arg_options' => array(
						'sanitize_callback' => 'esc_url',
					),
				),

				'user_permalink' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'User profile page link', 'buddypress' ),
					'type'        => 'string',
					'format'      => 'url',
					'readonly'    => true,
				),

				'diary_title' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( "Name of the user's diary", 'buddypress' ),
					'type'        => 'string',
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),

				'diary_description' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( "Description of the user's diary", 'buddypress' ),
					'type'        => 'string',
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),

				'diary_privacy' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( "User's diary privacy settings", 'buddypress' ),
					'enum'        => array( 'Friends', 'Public', 'Private' ),
					'type'        => 'string',
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),

				'diary_allow_comments' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( "User's diary privacy settings", 'buddypress' ),
					'enum'        => array( 'Friends', 'Public', 'No' ),
					'type'        => 'string',
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),

				'diary_allow_private_diaries_to_comment' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( "User's diary allowance for private diaries to comment", 'buddypress' ),
					'enum'        => array( 'Yes', 'No' ),
					'type'        => 'string',
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),

				'diary_allow_private_comments_on_diary' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( "User's diary allowance for private comments to be left", 'buddypress' ),
					'enum'        => array( 'Yes', 'No', 'Allow only private comments' ),
					'type'        => 'string',
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),

				'total_friend_count' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'Number of friends the user has', 'buddypress' ),
					'type'        => 'int',
					'readonly'    => true
				)
			)
		);

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

		$params['include'] = array(
			'description'       => __( 'Ensure result set includes specific user IDs.', 'buddypress' ),
			'type'              => 'array',
			'default'           => array(),
			'sanitize_callback' => 'wp_parse_id_list',
		);

		$params['exclude'] = array(
			'description'       => __( 'Ensure result set excludes specific IDs.', 'buddypress' ),
			'type'              => 'array',
			'default'           => array(),
			'sanitize_callback' => 'wp_parse_id_list',
		);

		$params['per_page'] = array(
			'description'       => __( 'Maximum number of results returned per result set.', 'buddypress' ),
			'default'           => 20,
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'validate_callback' => 'rest_validate_request_arg',
		);

		$params['page'] = array(
			'description'       => __( 'Offset the result set by a specific number of pages of results.', 'buddypress' ),
			'default'           => 1,
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'validate_callback' => 'rest_validate_request_arg',
		);

		$params['type'] = array(
			'description'       => __( 'Limit result set to items with a specific activity type.', 'buddypress' ),
			'type'              => 'string',
			'enum'              => [ 'active', 'newest', 'popular', 'online', 'alphabetical', 'random' ],
			'default'           => 'alphabetical',
			'validate_callback' => 'rest_validate_request_arg',
		);

		$params['search'] = array(
			'description'       => __( 'Limit result set to items that match this search query.', 'buddypress' ),
			'default'           => null,
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'validate_callback' => 'rest_validate_request_arg',
		);

		$params['meta_key'] = array(
			'description'       => __( 'Limit result set to items that match this metadata.', 'buddypress' ),
			'default'           => null,
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'validate_callback' => 'rest_validate_request_arg',
		);

		$params['meta_value'] = array(
			'description'       => __( 'Limit result set to items that match this search query.', 'buddypress' ),
			'default'           => null,
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'validate_callback' => 'rest_validate_request_arg',
		);

		$params['user_id'] = array(
			'description'       => __( 'Ensure result set includes specific user IDs.', 'buddypress' ),
			'type'              => 'array',
			'default'           => array(),
			'sanitize_callback' => 'wp_parse_id_list',
		);

		$params['populate_extras'] = array(
			'description'       => __( 'Fetch extra meta for each user such as their full name, if they are a friend of the logged in user, their last activity time.', 'buddypress' ),
			'default'           => true,
			'type'              => 'boolean',
			'sanitize_callback' => 'sanitize_text_field',
			'validate_callback' => 'rest_validate_request_arg',
		);

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
		$args = array(
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
		);

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
	 * Retrieve activity.
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
				$member                                           = [];
				$member['user_id']                                = bp_get_member_user_id();
				$member['user_avatar']                            = bp_get_member_avatar();
				$member['user_permalink']                         = bp_get_member_permalink();
				$member['user_name']                              = bp_get_profile_field_data( [
					'field'   => 'Name',
					'user_id' => bp_get_member_user_id()
				] );
				$member['user_nicename']                          = bp_get_member_user_nicename();
				$member['user_about_me']                          = bp_get_profile_field_data( [
					'field'   => 'About me',
					'user_id' => bp_get_member_user_id()
				] );
				$member['user_gender']                            = bp_get_profile_field_data( [
					'field'   => 'Gender',
					'user_id' => bp_get_member_user_id()
				] );
				$member['user_date_of_birth']                     = $this->prepare_date_response( bp_get_profile_field_data( [
					'field'   => 'Date of birth',
					'user_id' => bp_get_member_user_id()
				] ) );
				$member['user_country']                           = bp_get_profile_field_data( [
					'field'   => 'Country',
					'user_id' => bp_get_member_user_id()
				] );
				$member['user_state_province']                    = bp_get_profile_field_data( [
					'field'   => 'State/Province',
					'user_id' => bp_get_member_user_id()
				] );
				$member['diary_title']                            = bp_get_profile_field_data( [
					'field'   => 'Diary Title',
					'user_id' => bp_get_member_user_id()
				] );
				$member['diary_description']                      = bp_get_profile_field_data( [
					'field'   => 'Diary Description',
					'user_id' => bp_get_member_user_id()
				] );
				$member['diary_privacy']                          = bp_get_profile_field_data( [
					'field'   => 'Privacy',
					'user_id' => bp_get_member_user_id()
				] );
				$member['diary_allow_comments']                   = bp_get_profile_field_data( [
					'field'   => 'Allow comments',
					'user_id' => bp_get_member_user_id()
				] );
				$member['diary_allow_private_diaries_to_comment'] = bp_get_profile_field_data( [ 'field'   => 'Allow private diaries to comment',
				                                                                                 'user_id' => bp_get_member_user_id()
				] );
				$member['diary_allow_private_comments_on_diary']  = bp_get_profile_field_data( [ 'field'   => 'Allow private comments on diary',
				                                                                                 'user_id' => bp_get_member_user_id()
				] );
				$member['total_friend_count']                     = bp_get_member_total_friend_count();
				$ret[]                                            = $member;
			endwhile;
		endif;

		return $ret;
	}

	/**
	 * Check if a given request has access to get information about a specific activity.
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
	 * Check if a given request has access to get information about a specific activity.
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
	 * Check if a given request has access to activity items.
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
