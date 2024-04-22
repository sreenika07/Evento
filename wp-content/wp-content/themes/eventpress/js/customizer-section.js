( function( api ) {

	// Extends our custom "example-1" section.
	api.sectionConstructor['plugin-section'] = api.Section.extend( {

		// No events for this type of section.
		attachEvents: function () {},

		// Always make the section active.
		isContextuallyActive: function () {
			return true;
		}
	} );

} )( wp.customize );


function eventpressfrontpagesectionsscroll( section_id ){
    var scroll_section_id = "header";

    var $contents = jQuery('#customize-preview iframe').contents();

    switch ( section_id ) {
        case 'accordion-section-slider_setting':
        scroll_section_id = "header";
        break;

        case 'accordion-section-wedding_event_setting':
        scroll_section_id = "about-event";
        break;

        case 'accordion-section-Funfact_setting':
        scroll_section_id = "counter";
        break;
		
	   case 'accordion-section-gallery_setting':
        scroll_section_id = "gallery";
        break;
		
        case 'accordion-section-blog_setting':
        scroll_section_id = "latest-news";
        break;
    }

    if( $contents.find('#'+scroll_section_id).length > 0 ){
        $contents.find("html, body").animate({
        scrollTop: $contents.find( "#" + scroll_section_id ).offset().top
        }, 1000);
    }
}

 jQuery('body').on('click', '#sub-accordion-panel-eventpress_frontpage_sections .control-subsection .accordion-section-title', function(event) {
        var section_id = jQuery(this).parent('.control-subsection').attr('id');
        eventpressfrontpagesectionsscroll( section_id );
});