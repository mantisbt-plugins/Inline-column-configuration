<?php
# Copyright (c) 2011 Robert Munteanu (robert@lmn.ro)

# Inline column configuration for MantisBT is free software: 
# you can redistribute it and/or modify it under the terms of the GNU
# General Public License as published by the Free Software Foundation, 
# either version 2 of the License, or (at your option) any later version.
#
# Inline column configuration plugin for MantisBT is distributed in the hope 
# that it will be useful, but WITHOUT ANY WARRANTY; without even the 
# implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
# See the GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Inline column configuration plugin for MantisBT.  
# If not, see <http://www.gnu.org/licenses/>.

class InlineColumnConfigurationPlugin extends MantisPlugin {
    
    public function register() {
        $this->name = plugin_lang_get("title");
        $this->description = plugin_lang_get("description");

        $this->version = "1.2";

		if( version_compare( MANTIS_VERSION, '1.3', '<') ) {
			# this is version 1.2.x
			$this->requires = array(
				"MantisCore" => "1.2.6",
				"jQueryUI" => "1.8"
			);
		} else {
			# this is version 1.3.x
			$this->requires = array(
				"MantisCore" => "1.3, < 3.0"
			);
		};
	

        $this->author = "Robert Munteanu";
        $this->contact = "robert@lmn.ro";
        $this->url ="http://www.mantisbt.org/wiki/doku.php/mantisbt:inlinecolumnconfiguration";
    }
    
    public function hooks() {
        
        return array (
            'EVENT_MENU_FILTER' => 'add_configure_columns_link',
        	'EVENT_LAYOUT_RESOURCES' => 'resources',
        	'EVENT_LAYOUT_BODY_END' => 'add_columns_form'
        );
    }
    
    public function resources( $p_event ) {
        return '<script type="text/javascript" src="' . plugin_file( 'inline-column-configuration.js' ) . '"></script>';
    }    
    
    public function add_configure_columns_link() {
        
        // ALL_PROJECTS NOT SUPPORTED
		$t_url = helper_get_current_project() === ALL_PROJECTS
			? null
			: 'account_manage_columns_page.php' . '#inline-column-configuration';

        // TODO: remove OB once we have echo_link in MantisBT core
        ob_start();
		$check_vertion_new = version_compare( MANTIS_VERSION, '2.0', '<');
        if( $check_vertion_new ) {
			print_link( $t_url, plugin_lang_get( 'configure_columns' ), false, 'inline-configure-columns' );
		} else {
			print_link_button( $t_url, plugin_lang_get( 'configure_columns' ), 'btn btn-sm btn-primary btn-white btn-round', false );
		};
        
        $link = ob_get_contents();
        
        ob_end_clean();
        if ( $check_vertion_new ) {
			return array( $link, );
		} else {
			return $link;
		}
    }
    
    public function add_columns_form() {
        
        if  ( basename($_SERVER['SCRIPT_NAME']) != 'view_all_bug_page.php' )
            return;
        
        $t_user_id = auth_get_current_user_id();
        $t_project_id = helper_get_current_project();
        
        // ALL_PROJECTS NOT SUPPORTED
        if ( $t_project_id == ALL_PROJECTS )
            return;
        
        
        $t_all_columns = columns_get_all( $t_project_id );
        $t_view_columns = helper_get_columns_to_view( COLUMNS_TARGET_VIEW_PAGE, /* $p_viewable_only */ false );
        
		echo '<div id="manage-view-forms" class="hidden" title="' .
			plugin_lang_get('configure_columns') . ' ' .
			string_html_specialchars(
				sprintf( plugin_lang_get('project'), project_get_name( $t_project_id ) )
			) . '">';
        echo '<form id="manage-columns-form" method="post" action="manage_config_columns_set.php">';
        echo form_security_field( 'manage_config_columns_set' );
        echo '<input type="hidden" name="project_id" value="'. $t_project_id .'" />';
        echo '<input type="hidden" name="update_columns_for_current_project" value="true" />';        
        echo '<input type="hidden" name="form_page" value="account" />';
        echo '<input type="hidden" name="view_issues_columns" value="" />';
        echo '<input type="hidden" name="print_issues_columns" value="" />';
        echo '<input type="hidden" name="csv_columns" value="" />';
        echo '<input type="hidden" name="excel_columns" value="" />';
        echo '<table>';
        echo ' <tr valign="top"> ';
        
        $this->add_columns_form_by_target(COLUMNS_TARGET_VIEW_PAGE, $t_user_id, $t_project_id);
        $this->add_columns_form_by_target(COLUMNS_TARGET_CSV_PAGE, $t_user_id, $t_project_id);
        $this->add_columns_form_by_target(COLUMNS_TARGET_PRINT_PAGE, $t_user_id, $t_project_id);
        $this->add_columns_form_by_target(COLUMNS_TARGET_EXCEL_PAGE, $t_user_id, $t_project_id);
        
        echo ' </tr>';
        echo ' <tr><td colspan="4">';
        echo '  <input type="submit" class="button" value="' . plugin_lang_get('submit') . '" ></input>';
        echo ' </td></tr>';
        echo '</table>';
        echo '</form>';
        echo '</div>';
    }
    
    private function add_columns_form_by_target ( $p_target , $p_user_id, $p_project_id ) {

        $t_all_columns = columns_get_all( $p_project_id );
        $t_all_columns = array_map('strtolower', $t_all_columns);
        $t_view_columns = helper_get_columns_to_view( $p_target, /* $p_viewable_only */ false , $p_user_id);
        $t_deselected_columns = array_diff( $t_all_columns, $t_view_columns );
        
        echo '<td width="25%">';
        
        echo '<legend>' . plugin_lang_get('view_columns_' . $p_target) . '</legend>';
        echo '<fieldset id="columns_'. $p_target .'">';
        echo '<ol class="sortable">';
        foreach ( $t_view_columns as $t_column ) {
            echo '<li>';
            echo '<input type="checkbox" id="'. $p_target . '_' . $t_column .'" checked="checked" />';
            echo '<label for="' . $p_target . '_' . $t_column .'">'. $t_column .'</label>';
            echo '</li>';
        }
        
        foreach ( $t_deselected_columns as $t_column ) {
            echo '<li>';
            echo '<input type="checkbox" id="'. $p_target . '_' . $t_column .'" />';
            echo '<label for="' . $p_target . '_' . $t_column .'">'. $t_column .'</label>';
            echo '</li>';
        }
        echo '</ol>';
        echo '</fieldset>';
        echo '</td>';
        
    }
}

