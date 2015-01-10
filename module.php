<?php
/*
 * Fancy Branches Module
 *
 * webtrees: Web based Family History software
 * Copyright (C) 2014 webtrees development team.
 * Copyright (C) 2014 JustCarmen.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA
 */

use WT\Auth;
use WT\Log;

class fancy_branches_WT_Module extends WT_Module implements WT_Module_Config, WT_Module_Menu {
	
	public function __construct() {
		parent::__construct();
		// Load any local user translations
		if (is_dir(WT_MODULES_DIR.$this->getName().'/language')) {
			if (file_exists(WT_MODULES_DIR.$this->getName().'/language/'.WT_LOCALE.'.mo')) {
				WT_I18N::addTranslation(
					new Zend_Translate('gettext', WT_MODULES_DIR.$this->getName().'/language/'.WT_LOCALE.'.mo', WT_LOCALE)
				);
			}
			if (file_exists(WT_MODULES_DIR.$this->getName().'/language/'.WT_LOCALE.'.php')) {
				WT_I18N::addTranslation(
					new Zend_Translate('array', WT_MODULES_DIR.$this->getName().'/language/'.WT_LOCALE.'.php', WT_LOCALE)
				);
			}
			if (file_exists(WT_MODULES_DIR.$this->getName().'/language/'.WT_LOCALE.'.csv')) {
				WT_I18N::addTranslation(
					new Zend_Translate('csv', WT_MODULES_DIR.$this->getName().'/language/'.WT_LOCALE.'.csv', WT_LOCALE)
				);
			}
		}
	}

	// Extend WT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ WT_I18N::translate('Fancy Branches');
	}

	// Extend WT_Module
	public function getDescription() {
		return /* I18N: Description of the module */ WT_I18N::translate('Expand or collapse branches in the webtrees branches list with a single click');
	}

	// Extend WT_Module_Config
	public function modAction($mod_action) {
		switch($mod_action) {
		case 'admin_config':

			require WT_ROOT.'includes/functions/functions_edit.php';

			$controller=new WT_Controller_Page;
			$controller
				->restrictAccess(Auth::isAdmin())
				->setPageTitle(WT_I18N::translate('Fancy Branches'))
				->pageHeader();

			if (WT_Filter::post('action')=='save' && WT_Filter::checkCsrf()) {
				$this->setSetting('FB',  WT_Filter::postInteger('NEW_FB'));
				Log::addConfigurationLog($this->getTitle().' config updated');
			}

			$FB = $this->getSetting('FB');
			echo '
				<h2>'.$controller->getPageTitle().'</h2>
				<form method="post" name="configform" action="'.$this->getConfigLink().'">
					<input type="hidden" name="action" value="save">'.WT_Filter::getCsrf().'
					<label>'.WT_I18N::translate('Use “d’Aboville” numbering system').'</label>'
					.two_state_checkbox('NEW_FB', $FB).'
					<input type="submit" value="'.WT_I18N::translate('Save').'" />
				</form>';
			break;
		default:
			header('HTTP/1.0 404 Not Found');
		}
	}

	// Implement WT_Module_Config
	public function getConfigLink() {
		return 'module.php?mod='.$this->getName().'&amp;mod_action=admin_config';
	}

	// Implement WT_Module_Menu
	public function defaultMenuOrder() {
		return 999;
	}

	// Implement WT_Module_Menu
	public function getMenu() {
		// We don't actually have a menu - this is just a convenient "hook" to execute code at the right time during page execution
		global $controller;

		if (WT_SCRIPT_NAME == 'branches.php') {
			// load the module stylesheet
			echo $this->includeCss(WT_MODULES_DIR.$this->getName().'/style.css');

			$controller
				->addExternalJavaScript(WT_MODULES_DIR.$this->getName().'/js/jquery.treeview.js')
				->addInlineJavaScript('
				jQuery("#branches-page form")
					.after("<div id=\"treecontrol\"><a href=\"#\">'.WT_I18N::translate('Collapse all').'</a> | <a href=\"#\">'.WT_I18N::translate('Expand all').'</a></div>")
					.after("<div class=\"loading-image\"></div>");

				jQuery(jQuery("#branches-page ol").get().reverse()).each(function(){
					var html = jQuery(this).html();
					if (html === "") {
						jQuery(this).remove();
					}
					else {
  						jQuery(this).replaceWith("<ul>" + html +"</ul>")
					}
				});
				jQuery("#branches-page ul:first").attr("id", "branch-list");

				jQuery("li[title=\"'.WT_I18N::translate('Private').'\"]").hide();
			');

			if ($this->getSetting('FB')) {
				$controller->addInlineJavaScript('
					jQuery("#branch-list, #branch-list ul, #branch-list li").addClass("aboville");
				');
			}

			$controller->addInlineJavaScript('
				jQuery("#branch-list").treeview({
					collapsed: true,
					animated: "slow",
					control:"#treecontrol"
				});
				jQuery("#branch-list").css("visibility", "visible");
				jQuery(".loading-image").css("display", "none");
			');
		}
		return null;
	}

	// Implement the css stylesheet for this module
	private function includeCss($css) {
		return
			'<script>
				if (document.createStyleSheet) {
					document.createStyleSheet("'.$css.'"); // For Internet Explorer
				} else {
					var newSheet=document.createElement("link");
					newSheet.setAttribute("href","'.$css.'");
					newSheet.setAttribute("type","text/css");
					newSheet.setAttribute("rel","stylesheet");
					document.getElementsByTagName("head")[0].appendChild(newSheet);
				}
			</script>';
	}
}