<?php
defined( 'ABSPATH' ) || exit;

/**
 * Activity endpoints.
 *
 * @since 0.1.0
 */
class BP_REST_Members_Controller extends WP_REST_Controller {

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
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'activity',
			'type'       => 'object',

			'properties' => array(
				'user_id' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'A unique integer ID for the user.', 'buddypress' ),
					'readonly'    => true,
					'type'        => 'integer',
				),

				'user_name' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'The name of the user', 'buddypress' ),
					'type'        => 'string',
				),

				'user_name' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'The sanitized name of the user', 'buddypress' ),
					'type'        => 'string',
				),

				'user_avatar' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'Avatar of the user', 'buddypress' ),
					'type'        => 'string',
				),

				'user_permalink' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'User profile page link', 'buddypress' ),
					'type'        => 'string',
				),


				'diary_title' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( "Name of the user's diary", 'buddypress' ),
					'type'        => 'string',
				),

				'total_friend_count' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'Number of friends the user has', 'buddypress' ),
					'type'        => 'int',
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
			'enum'              => ['active', 'newest', 'popular', 'online', 'alphabetical', 'random'],
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

	public function prepare_item_for_response( $activity, $request, $is_raw = false ) {
		$data = array(
			'author'                => $activity->user_id,
			'component'             => $activity->component,
			'content'               => $activity->content,
			'date'                  => $this->prepare_date_response( $activity->date_recorded ),
			'id'                    => $activity->id,
			'link'                  => $activity->primary_link,
			'parent'                => $activity->type === 'activity_comment' ? $activity->item_id : 0,
			'prime_association'     => $activity->item_id,
			'secondary_association' => $activity->secondary_item_id,
			'status'                => $activity->is_spam ? 'spam' : 'published',
			'title'                 => $activity->action,
			'type'                  => $activity->type,
		);

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data    = $this->add_additional_fields_to_object( $data, $request );
		$data    = $this->filter_response_by_context( $data, $context );

		$response = rest_ensure_response( $data );
		$response->add_links( $this->prepare_links( $activity ) );

		/**
		 * Filter an activity value returned from the API.
		 *
		 * @param array           $response
		 * @param WP_REST_Request $request Request used to generate the response.
		 */
		return apply_filters( 'rest_prepare_buddypress_activity_value', $response, $request );
	}


	/**
	 * Retrieve activities.
	 *
	 * @since 0.1.0
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Request List of activity object data.
	 */
	public function get_items( $request ) {
		$args = $this->_get_args($request);

		$retval     = array();
		if ( bp_has_members( $args ) ) :
			while ( bp_members() ) : bp_the_member();
				$member = [];
				$member['user_id'] = bp_get_member_user_id();
				$member['user_name'] = bp_get_profile_field_data( ['field'=>'Name', 'user_id'=>bp_get_member_user_id()] );
				$member['user_nicename'] = bp_get_member_user_nicename();
				$member['user_avatar'] = bp_get_member_avatar();
				$member['user_permalink'] = bp_get_member_permalink();
				$member['diary_title'] = bp_get_profile_field_data( ['field'=>'Diary Title', 'user_id'=>bp_get_member_user_id()] );
				$member['total_friend_count'] = bp_get_member_total_friend_count();
				$retval[] = $member;
			endwhile;
		endif;

		return new WP_REST_Response( $retval, 200 );
	}

	private function _get_args($request) {
		$args = array(
			'exclude'      => $request['exclude'],
			'include'      => $request['include'],
			'page'         => $request['page'],
			'per_page'     => $request['per_page'],
			'search_terms' => $request['search'],
			'meta_key'     => $request['meta_key'],
			'meta_value'   => $request['meta_value'],
			'user_id'      => $request['user_id'],
			'type'         => $request['type'],
			'populate_extras' => $request['populate_extras'] === false ? false: true
		);

		if ( !empty($args['include']) ) {
			$args['count_total'] = false;
		}

		foreach(['user_id', 'meta_key', 'meta_value', 'exclude', 'include'] as $param) {
			if (array_key_exists($param, $args) && empty($args[$param])) {
				unset($args[$param]);
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
		$retval     = array();
		if ( bp_has_members( ['include'=>[(int) $request['id']], 'type'=>'alphabetical'] ) ) :
			while ( bp_members() ) : bp_the_member();
				$member = [];
				$member['user_id'] = bp_get_member_user_id();
				$member['user_name'] = bp_get_profile_field_data( ['field'=>'Name', 'user_id'=>bp_get_member_user_id()] );
				$member['user_nicename'] = bp_get_member_user_nicename();
				$member['user_avatar'] = bp_get_member_avatar();
				$member['user_permalink'] = bp_get_member_permalink();
				$member['diary_title'] = bp_get_profile_field_data( ['field'=>'Diary Title', 'user_id'=>bp_get_member_user_id()] );
				$member['total_friend_count'] = bp_get_member_total_friend_count();
				$retval = $member;
			endwhile;
		endif;

		return new WP_REST_Response( $retval, 200 );

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
	 * Prepare links for the request.
	 *
	 * @since 0.1.0
	 *
	 * @param array $activity Activity.
	 *
	 * @return array Links for the given plugin.
	 */
	protected function prepare_links( $activity ) {
		$base = sprintf( '/%s/%s/', $this->namespace, $this->rest_base );

		// Entity meta.
		$links = array(
			'self'       => array(
				'href' => rest_url( $base . $activity->id ),
			),
			'collection' => array(
				'href' => rest_url( $base ),
			),
			'author'     => array(
				'href' => rest_url( '/wp/v2/users/' . $activity->user_id ),
			)
		);

		if ( $activity->type === 'activity_comment' ) {
			$links['up'] = array(
				'href' => rest_url( $base . $activity->item_id ),
			);
		}

		return $links;
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
