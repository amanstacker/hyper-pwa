<?php 
// Exit if accessed directly
if ( ! defined('ABSPATH') ) exit;

function hypwa_filename_postfix() {
		
	if ( ! is_multisite() ) {
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason: Here it is just used for condition check.
		if (isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'], 'nginx') !== false) {
			return '-nginx';
		}
		return '';
	}
	
	return '-' . get_current_blog_id();
}

function hypwa_sw_filename() {
    return apply_filters( 'hypwa_sw_filename', 'hyper-pwa-sw' . hypwa_filename_postfix() . '.js' );
}

function hypwa_manifest_filename() {
	return 'hyper-pwa-manifest' . hypwa_filename_postfix() . '.json';
}

function hypwa_sw_url() {
	if ( 'dynamic' === HYPWA_Options::get( 'file_serving_method', 'dynamic' ) && ! get_option( 'permalink_structure' ) ) {
		return hypwa_convert_url_to_https( home_url( '?hypwa_sw=1' ) );
	}
	return hypwa_convert_url_to_https( home_url( '/' . hypwa_sw_filename() ) );
}

function hypwa_manifest_url() {
	if ( 'dynamic' === HYPWA_Options::get( 'file_serving_method', 'dynamic' ) && ! get_option( 'permalink_structure' ) ) {
		return home_url( '?hypwa_manifest=1' );
	}
	return home_url( '/' . hypwa_manifest_filename() );
}

/**
 * Convert a URL to HTTPS when appropriate.
 *
 * Skips localhost URLs to avoid issues in local development.
 *
 * @param string $url URL to convert.
 * @return string HTTPS URL.
 */
function hypwa_convert_url_to_https( $url ) {

	$host = wp_parse_url( $url, PHP_URL_HOST );

	if ( 'localhost' === $host ) {
		return $url;
	}

	return set_url_scheme( $url, 'https' );
}

/**
 * Get the PWA start URL.
 *
 * @return string
 */
function hypwa_get_start_url() {
	$start_url = get_permalink( (int) HYPWA_Options::get( 'start_page' ) );

	if ( ! $start_url ) {
		$start_url = home_url( '/' );
	}

	$start_url = hypwa_convert_url_to_https( $start_url );

	return hypwa_add_utm_parameters( $start_url );
}

/**
 * Add UTM parameters to a URL.
 *
 * @param string $url URL to modify.
 * @return string
 */
function hypwa_add_utm_parameters( $url ) {
	if ( ! HYPWA_Options::get( 'cf_utm_tracking_status' ) ) {
		return $url;
	}

	$utm = [];

	$fields = [
		'utm_source'   => 'cf_utm_source',
		'utm_medium'   => 'cf_utm_medium',
		'utm_campaign' => 'cf_utm_campaign',
		'utm_term'     => 'cf_utm_term',
		'utm_content'  => 'cf_utm_content',
	];

	foreach ( $fields as $param => $option ) {
		$value = trim( (string) HYPWA_Options::get( $option ) );

		if ( '' !== $value ) {
			$utm[ $param ] = $value;
		}
	}

	if ( empty( $utm ) ) {
		return $url;
	}

	return add_query_arg( $utm, $url );
}

/**
 * Get URL's to exclude from cache
 * */
function hypwa_get_excluded_caching_urls() {
    $excluded_urls = [];

    if ( ! HYPWA_Options::get( 'cf_exclude_from_caching_status' ) ) {
        return $excluded_urls;
    }

    $raw_urls = [];

    // 1. Post Type archive URLs
    $post_types = HYPWA_Options::get( 'cf_exclude_caching_post_types', [] );
    if ( ! empty( $post_types ) && is_array( $post_types ) ) {
		foreach ( $post_types as $post_type ) {
		    $archive_url = ''; // Reset each iteration

			if ( 'post' === $post_type ) {
			    $page_for_posts = (int) get_option( 'page_for_posts' );
			    if ( $page_for_posts > 0 ) {
			        $archive_url = get_permalink( $page_for_posts );
			    }
			    // If no static blog page set, skip — home_url('/') would exclude the whole site
			} elseif ( 'page' === $post_type ) {
			    continue;
			} else {
			    $archive_url = get_post_type_archive_link( $post_type );
			}

		    if ( ! empty( $archive_url ) ) {
		        $raw_urls[] = esc_url_raw( $archive_url );
		    }
		}
    }

    // 2. Taxonomy archive URLs
    $taxonomies = HYPWA_Options::get( 'cf_exclude_caching_taxonomies', [] );
    if ( ! empty( $taxonomies ) && is_array( $taxonomies ) ) {
        foreach ( $taxonomies as $taxonomy ) {
            if ( ! taxonomy_exists( $taxonomy ) ) {
                continue;
            }

            $terms = get_terms( [
                'taxonomy'   => $taxonomy,
                'hide_empty' => true,
            ] );

            if ( is_wp_error( $terms ) || empty( $terms ) ) {
                continue;
            }

            foreach ( $terms as $term ) {
                $term_url = get_term_link( $term );
                if ( ! is_wp_error( $term_url ) ) {
                    $raw_urls[] = esc_url_raw( $term_url );
                }
            }
        }
    }

    // 3. Individual Post/Page URLs
    $post_ids = HYPWA_Options::get( 'cf_exclude_caching_posts', [] );
    if ( ! empty( $post_ids ) && is_array( $post_ids ) ) {
        foreach ( $post_ids as $post_id ) {
            $post_id = (int) $post_id;

            if ( $post_id <= 0 ) {
                continue;
            }

            if ( 'publish' !== get_post_status( $post_id ) ) {
                continue;
            }

            $post_url = get_permalink( $post_id );
            if ( ! empty( $post_url ) ) {
                $raw_urls[] = esc_url_raw( $post_url );
            }
        }
    }

    // 4. Manual URL patterns from textarea
    $url_patterns = HYPWA_Options::get( 'cf_exclude_caching_url_patterns', '' );
    if ( ! empty( $url_patterns ) ) {
        foreach ( explode( "\n", $url_patterns ) as $line ) {
            $clean = esc_url_raw( trim( $line ) );
            if ( ! empty( $clean ) ) {
                $raw_urls[] = $clean;
            }
        }
    }

    // Extract path only from all full URLs so SW can use simple url.includes() match
    foreach ( $raw_urls as $url ) {
        $path = wp_parse_url( $url, PHP_URL_PATH );
        if ( ! empty( $path ) ) {
            $excluded_urls[] = $path;
        }
    }

    // Remove duplicates
    $excluded_urls = array_values( array_unique( $excluded_urls ) );

    return $excluded_urls;
}

/**
 * Add pre caching URL's
 * @param 	$urls 	array
 * @return 	$urls 	array
 * @since 	5.0.0
 * */
add_filter( 'hypwa_precache_urls', 'hypwa_precache_urls_clbk' );
function hypwa_precache_urls_clbk( $urls ) {
	
	$pre_cache_types 	=	HYPWA_Options::get( 'cf_pre_cache_post_types', [] );
	$pre_cache_urls 	=	HYPWA_Options::get( 'cf_pre_cache_manual_urls', '' );

	$cache_url_array 	=	[];

	if ( ! empty( $pre_cache_types ) && is_array( $pre_cache_types ) ) {
		foreach ( $pre_cache_types as $type => $cache_type ) {
			if ( $cache_type['enabled'] ) {

				if ( $cache_type['count'] > 0 ) {

					$post_ids = get_posts( array(
					    'post_type'      => $type,
					    'posts_per_page' => $cache_type['count'],
					    'post_status'    => 'publish',
					    'fields'         => 'ids',
					) );

					if ( ! empty( $post_ids ) ) {
						foreach ( $post_ids as $post_id ) {
							$cache_url_array[] 	=	hypwa_convert_url_to_https( get_permalink( (int) $post_id ) );
						}
					}

				}else if ( ! empty( $cache_type['specific'] ) ) {
					foreach ( $cache_type['specific'] as $specific) {
						$cache_url_array[] 	=	hypwa_convert_url_to_https( get_permalink( (int) $specific ) );	
					}
				}
		
			}
		}
	}

	if ( ! empty( $pre_cache_urls ) ) {
        foreach ( explode( "\n", $pre_cache_urls ) as $url ) {
            $clean = esc_url_raw( trim( $url ) );
            if ( ! empty( $clean ) ) {
                $cache_url_array[] = $clean;
            }
        }
    }

	if ( ! empty( $cache_url_array ) ) {
		$urls 	=	array_values( array_unique( array_merge( $urls, $cache_url_array ) ) );
	}
	

	return $urls;

}