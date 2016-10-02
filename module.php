<?php
/**
 * webtrees: online genealogy
 * Copyright (C) 2016 webtrees development team
 * Copyright (C) 2016 JustCarmen
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
namespace JustCarmen\WebtreesAddOns\FancyBranches;

use Composer\Autoload\ClassLoader;
use Fisharebest\Webtrees\Controller\BaseController;
use Fisharebest\Webtrees\Filter;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Log;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module\ModuleConfigInterface;
use Fisharebest\Webtrees\Module\ModuleMenuInterface;
use JustCarmen\WebtreesAddOns\FancyBranches\Template\AdminTemplate;

class FancyBranchesModule extends AbstractModule implements ModuleConfigInterface, ModuleMenuInterface {

	const CUSTOM_VERSION	 = '1.7.8';
	const CUSTOM_WEBSITE	 = 'http://www.justcarmen.nl/fancy-modules/fancy-branches/';

	public function __construct() {
		parent::__construct('fancy_branches');

		// register the namespaces
		$loader = new ClassLoader();
		$loader->addPsr4('JustCarmen\\WebtreesAddOns\\FancyBranches\\', WT_MODULES_DIR . $this->getName() . '/app');
		$loader->register();
	}

	// Extend Module
	public function getTitle() {
		return /* I18N: Name of a module */ I18N::translate('Fancy Branches');
	}

	// Extend Module
	public function getDescription() {
		return
			/* I18N: Description of the module */ I18N::translate('Expand or collapse branches in the webtrees branches list with a single click.');
	}

	// Extend ModuleConfigInterface
	public function modAction($mod_action) {
		switch ($mod_action) {
			case 'admin_config':
				if (Filter::postBool('save') && Filter::checkCsrf()) {
					$this->setSetting('FB', Filter::postInteger('NEW_FB'));
					Log::addConfigurationLog($this->getTitle() . ' config updated');
				}
				$template = new AdminTemplate;
				return $template->pageContent();
			default:
				http_response_code(404);
				break;
		}
	}

	// Implement ModuleConfigInterface
	public function getConfigLink() {
		return 'module.php?mod=' . $this->getName() . '&amp;mod_action=admin_config';
	}

	// Implement ModuleMenuInterface
	public function defaultMenuOrder() {
		return 999;
	}

	// Implement ModuleMenuInterface
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
					include_css("' . WT_MODULES_DIR . $this->getName() . '/css/style.css");
					', BaseController::JS_PRIORITY_HIGH)
				->addInlineJavaScript('
				jQuery("#branches-page form")
					.after("<div id=\"treecontrol\"><a href=\"#\">' . I18N::translate('Collapse all') . '</a> | <a href=\"#\">' . I18N::translate('Expand all') . '</a></div>")
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

					jQuery("li[title=\"' . I18N::translate('Private') . '\"]").hide();
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

return new FancyBranchesModule;
