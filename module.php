<?php
/*
 * Fancy Branches Module
 *
 * webtrees: Web based Family History software
 * Copyright (C) 2015 webtrees development team.
 * Copyright (C) 2015 JustCarmen.
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
		if (is_dir(WT_MODULES_DIR . $this->getName() . '/language')) {
			if (file_exists(WT_MODULES_DIR . $this->getName() . '/language/' . WT_LOCALE . '.mo')) {
				WT_I18N::addTranslation(
					new Zend_Translate('gettext', WT_MODULES_DIR . $this->getName() . '/language/' . WT_LOCALE . '.mo', WT_LOCALE)
				);
			}
			if (file_exists(WT_MODULES_DIR . $this->getName() . '/language/' . WT_LOCALE . '.php')) {
				WT_I18N::addTranslation(
					new Zend_Translate('array', WT_MODULES_DIR . $this->getName() . '/language/' . WT_LOCALE . '.php', WT_LOCALE)
				);
			}
			if (file_exists(WT_MODULES_DIR . $this->getName() . '/language/' . WT_LOCALE . '.csv')) {
				WT_I18N::addTranslation(
					new Zend_Translate('csv', WT_MODULES_DIR . $this->getName() . '/language/' . WT_LOCALE . '.csv', WT_LOCALE)
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
		switch ($mod_action) {
			case 'admin_config':

				require WT_ROOT . 'includes/functions/functions_edit.php';

				$controller = new WT_Controller_Page;
				$controller
					->restrictAccess(Auth::isAdmin())
					->setPageTitle(WT_I18N::translate('Fancy Branches'))
					->pageHeader();

				if (WT_Filter::postBool('save') && WT_Filter::checkCsrf()) {
					$this->setSetting('FB', WT_Filter::postInteger('NEW_FB'));
					Log::addConfigurationLog($this->getTitle() . ' config updated');
				}

				$FB = $this->getSetting('FB');
				?>
				<ol class="breadcrumb small">
					<li><a href="admin.php"><?php echo WT_I18N::translate('Control panel'); ?></a></li>
					<li><a href="admin_modules.php"><?php echo WT_I18N::translate('Module administration'); ?></a></li>
					<li class="active"><?php echo $controller->getPageTitle(); ?></li>
				</ol>
				<h2><?php echo $this->getTitle(); ?></h2>
				<form method="post" name="form1">
					<?php echo WT_Filter::getCsrf(); ?>
					<input type="hidden" name="save" value="1">
					<div class="form-group">
						<div class="checkbox">
							<label class="control-label col-sm-4">
								<?php echo WT_I18N::translate('Use “d’Aboville” numbering system'); ?>
							</label>
							<div class="col-sm-8">
								<?php echo radio_buttons('NEW_FB', array(WT_I18N::translate('no'), WT_I18N::translate('yes')), $FB, 'class="radio-inline"'); ?>
								<p class="small text-muted"><?php echo WT_I18N::translate('The “D’aboville” numbering system is a method to split descending generations into numbering sections. Each generation and each child gets a succeeding number seperated by a dot.'); ?></p>
							</div>
						</div>
					</div>
					<button class="btn btn-primary" type="submit">						
						<i class="fa fa-check"></i>
						<?php echo WT_I18N::translate('save'); ?>
					</button>
				</form>

				<?php
				break;
			default:
				header('HTTP/1.0 404 Not Found');
		}
	}

	// Implement WT_Module_Config
	public function getConfigLink() {
		return 'module.php?mod=' . $this->getName() . '&amp;mod_action=admin_config';
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

			$controller
				->addExternalJavaScript(WT_MODULES_DIR . $this->getName() . '/js/jquery.treeview.js')
				->addInlineJavaScript('
					function include_css(css_file) {
						var html_doc = document.getElementsByTagName("head")[0];
						var css = document.createElement("link");
						css.setAttribute("rel", "stylesheet");
						css.setAttribute("type", "text/css");
						css.setAttribute("href", css_file);
						html_doc.appendChild(css);
					}
					include_css("' . WT_MODULES_DIR . $this->getName() . '/style.css");
					', WT_Controller_Base::JS_PRIORITY_HIGH)
				->addInlineJavaScript('
				jQuery("#branches-page form")
					.after("<div id=\"treecontrol\"><a href=\"#\">' . WT_I18N::translate('Collapse all') . '</a> | <a href=\"#\">' . WT_I18N::translate('Expand all') . '</a></div>")
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

					jQuery("li[title=\"' . WT_I18N::translate('Private') . '\"]").hide();
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

}
