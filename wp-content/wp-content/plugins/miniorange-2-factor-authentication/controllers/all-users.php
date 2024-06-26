<?php
/**
 * Description: This file is used to show the user details.
 *
 * @package miniorange-2-factor-authentication/controllers.
 */

// Needed in both.

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

echo '<div class="mo_wpns_divided_layout">
		<div class="mo_wpns_setting_layout">';


echo ' <h2><b> User Details </b></h2>
        <hr>';

echo ' <table  id="mo2f_user_details" class="display" cellspacing="0" width="100%">
      <thead > 
         <tr>
                 <th>Username</th>
                 <th>Registered 2FA Email</th>
                 <th>Role</th>
                 <th>Method selected</th>
                 <th>Reset 2-Factor</th>
                 
                 
                 
         </tr>
         
         
      </thead>
      
     <tbody > ';


		mo2f_show_user_details();


echo '   </tbody>
     </table>
     </div>
     </div>
     
     <script>
	jQuery(document).ready(function() {
		$("#mo2f_user_details").DataTable({
			"order": [[ 0, "desc" ]]
		});
		
	} );

      

</script>';





