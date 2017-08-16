<?php
/**
 * REST API Order Notes controller
 *
 * Handles requests to the /orders/<order_id>/notes endpoint.
 *
 * @author   WooThemes
 * @category API
 * @package  WooCommerce/API
 * @since    2.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Order Notes controller class.
 *
 * @package WooCommerce/API
 */
class WC_REST_Dev_Order_Notes_Controller extends WC_REST_Order_Notes_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wc/v3';

	/**
	 * Prepare a single order note output for response.
	 *
	 * @param WP_Comment $note Order note object.
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response $response Response data.
	 */
	public function prepare_item_for_response( $note, $request ) {
		$data = array(
			'id'               => (int) $note->comment_ID,
			'author'           => __( 'WooCommerce', 'woocommerce' ) === $note->comment_author ? 'system' : $note->comment_author,
			'date_created'     => wc_rest_prepare_date_response( $note->comment_date ),
			'date_created_gmt' => wc_rest_prepare_date_response( $note->comment_date_gmt ),
			'note'             => $note->comment_content,
			'customer_note'    => (bool) get_comment_meta( $note->comment_ID, 'is_customer_note', true ),
		);

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data    = $this->add_additional_fields_to_object( $data, $request );
		$data    = $this->filter_response_by_context( $data, $context );

		// Wrap the data in a response object.
		$response = rest_ensure_response( $data );

		$response->add_links( $this->prepare_links( $note ) );

		/**
		 * Filter order note object returned from the REST API.
		 *
		 * @param WP_REST_Response $response The response object.
		 * @param WP_Comment       $note     Order note object used to create response.
		 * @param WP_REST_Request  $request  Request object.
		 */
		return apply_filters( 'woocommerce_rest_prepare_order_note', $response, $note, $request );
	}

	/**
	 * Get the Order Notes schema, conforming to JSON Schema.
	 *
	 * @return array
	 */
	public function get_item_schema() {
		$schema = parent::get_item_schema();
		$schema['properties']['author'] = array(
			'description' => __( 'Order note author.', 'woocommerce' ),
			'type'        => 'string',
			'context'     => array( 'view', 'edit' ),
		);
		return $schema;
	}

}
