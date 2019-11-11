<?php
/**
 * The Database Spam Filter.
 *
 * If a comment comes in, this filter will check if the email address, the author's name or its
 * IP address is already associated with a spam comment. If so, we will mark the comment as spam.
 *
 * @package Antispam Bee Filter
 */

declare(strict_types = 1);

namespace Pluginkollektiv\AntispamBee\Filter;

use Pluginkollektiv\AntispamBee\Entity\DataInterface;
use Pluginkollektiv\AntispamBee\Option\OptionFactory;
use Pluginkollektiv\AntispamBee\Option\OptionInterface;

/**
 * Class DbSpam
 *
 * @package Pluginkollektiv\AntispamBee\Filter
 */
class DbSpam implements SpamFilterInterface {

	/**
	 * If already created, this will contain the OptionInterface.
	 *
	 * @var OptionInterface
	 */
	private $options;

	/**
	 * The factory will produce the option interface.
	 *
	 * @var OptionFactory
	 */
	private $option_factory;

	/**
	 * The database connection.
	 *
	 * @var \wpdb
	 */
	private $wpdb;

	/**
	 * DbSpam constructor.
	 *
	 * @param OptionFactory $option_factory
	 * @param \wpdb         $wpdb
	 */
	public function __construct( OptionFactory $option_factory, \wpdb $wpdb ) {
		$this->option_factory = $option_factory;
		$this->wpdb           = $wpdb;
	}

	/**
	 * Determines, whether the data structure is spam.
	 *
	 * @param DataInterface $data
	 *
	 * @return float
	 */
	public function filter( DataInterface $data ) : float {

		$params = [];
		$filter = [];
		if ( ! empty( $data->website() ) ) {
			$filter[] = '`comment_author_url` = %s';
			$params[] = wp_unslash( $data->website() );
		}
		if ( ! empty( $data->ip() ) ) {
			$filter[] = '`comment_author_IP` = %s';
			$params[] = wp_unslash( $data->ip() );
		}

		if ( ! empty( $data->email() ) ) {
			$filter[] = '`comment_author_email` = %s';
			$params[] = wp_unslash( $data->email() );
		}
		if ( empty( $params ) ) {
			return (float) 0;
		}

        // phpcs:disable WordPress.WP.PreparedSQL.NotPrepared
        // phpcs:disable WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
		$filter_sql = implode( ' OR ', $filter );

		$result = $this->wpdb->get_var(
			$this->wpdb->prepare(
				sprintf(
					"SELECT `comment_ID` FROM `{$this->wpdb->comments}`
                     WHERE `comment_approved` = 'spam' AND (%s) LIMIT 1",
					$filter_sql
				),
				$params
			)
		);
        // phpcs:enable WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
        // phpcs:enable WordPress.WP.PreparedSQL.NotPrepared

		return (float) ! empty( $result );
	}

	/**
	 * Nothing to register here.
	 *
	 * @return bool
	 */
	public function register() : bool {
		return true;
	}

	/**
	 * Returns the options for this filter.
	 *
	 * @return OptionInterface
	 */
	public function options() : OptionInterface {
		if ( $this->options ) {
			return $this->options;
		}
		$args          = [
			'name'        => __( 'DB Spam', 'antispam-bee' ),
			'description' => __( 'text.', 'antispam-bee' ),
		];
		$this->options = $this->option_factory->from_args( $args );
		return $this->options;
	}

	/**
	 * Returns the ID of the filter.
	 *
	 * @return string
	 */
	public function id() : string {
		return 'spam_ip';
	}
}