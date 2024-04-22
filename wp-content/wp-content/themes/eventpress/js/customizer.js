/**
 * File customizer.js.
 *
 * Theme Customizer enhancements for a better user experience.
 *
 * Contains handlers to make Theme Customizer preview reload changes asynchronously.
 */

( function( $ ) {

	// Site title and description.
	wp.customize( 'blogname', function( value ) {
		value.bind( function( to ) {
			$( '.site-title' ).text( to );
		} );
	} );
	wp.customize( 'blogdescription', function( value ) {
		value.bind( function( to ) {
			$( '.site-description' ).text( to );
		} );
	} );

	// Header text color.
	wp.customize( 'header_textcolor', function( value ) {
		value.bind( function( to ) {
			if ( 'blank' === to ) {
				$( '.site-title, .site-description' ).css( {
					'clip': 'rect(1px, 1px, 1px, 1px)',
					'position': 'absolute'
				} );
			} else {
				$( '.site-title, .site-description' ).css( {
					'clip': 'auto',
					'position': 'relative'
				} );
				$( '.site-title, .site-description' ).css( {
					'color': to
				} );
			}
		} );
	} );
	
	$(document).ready(function ($) {
        $('input[data-input-type]').on('input change', function () {
            var val = $(this).val();
            $(this).prev('.cs-range-value').html(val);
            $(this).val(val);
        });
    })
	
	
	// wedding_section_title
	wp.customize(
		'wedding_section_title', function( value ) {
			value.bind(
				function( newval ) {
					$( '#about-event .section-title h2' ).text( newval );
				}
			);
		}
	);
	
	// wedding_section_description
	wp.customize(
		'wedding_section_description', function( value ) {
			value.bind(
				function( newval ) {
					$( '#about-event .section-title p' ).text( newval );
				}
			);
		}
	);
	
	// funfact_section_title
	wp.customize(
		'funfact_section_title', function( value ) {
			value.bind(
				function( newval ) {
					$( '#counter .section-title h2' ).text( newval );
				}
			);
		}
	);
	
	// funfact_section_description
	wp.customize(
		'funfact_section_description', function( value ) {
			value.bind(
				function( newval ) {
					$( '#counter .section-title p' ).text( newval );
				}
			);
		}
	);
	
	// blog_title
	wp.customize(
		'blog_title', function( value ) {
			value.bind(
				function( newval ) {
					$( '#latest-news .section-title h2' ).text( newval );
				}
			);
		}
	);
	
	// blog_description
	wp.customize(
		'blog_description', function( value ) {
			value.bind(
				function( newval ) {
					$( '#latest-news .section-title p' ).text( newval );
				}
			);
		}
	);
	
	// gallery_title
	wp.customize(
		'gallery_title', function( value ) {
			value.bind(
				function( newval ) {
					$( '#gallery .section-title h2' ).text( newval );
				}
			);
		}
	);
	
	// gallery_description
	wp.customize(
		'gallery_description', function( value ) {
			value.bind(
				function( newval ) {
					$( '#gallery .section-title p' ).text( newval );
				}
			);
		}
	);
	
	// foot_regards_text
	wp.customize(
		'foot_regards_text', function( value ) {
			value.bind(
				function( newval ) {
					$( '.footer-section .footer-logo h2' ).text( newval );
				}
			);
		}
	);
	
	/**
	 * logo_width
	 */
	wp.customize( 'logo_width', function( value ) {
		value.bind( function( logo_width ) {
			jQuery( '.logo-bbc img' ).css( 'max-width', logo_width + 'px' );
		} );
	} );
	
	
} )( jQuery );