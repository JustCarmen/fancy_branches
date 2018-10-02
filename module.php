<?php
/**
 * webtrees: online genealogy
 * Copyright (C) 2018 JustCarmen (http://www.justcarmen.nl)
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

use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module\ModuleConfigInterface;
use Fisharebest\Webtrees\Module\ModuleMenuInterface;
use Fisharebest\Webtrees\Tree;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FancyBranchesModule extends AbstractModule implements ModuleConfigInterface, ModuleMenuInterface {
	const CUSTOM_VERSION = '2.0.0-dev';
	const CUSTOM_WEBSITE = 'http://www.justcarmen.nl/fancy-modules/fancy-branches/';

	public function __construct() {
		parent::__construct('fancy_branches');
	}

	/** {@inheritdoc} */
	public function getTitle(): string {
		return /* I18N: Name of a module */ I18N::translate('Fancy Branches');
	}

	/** {@inheritdoc} */
	public function getDescription(): string {
		/* I18N: Description of the module */ 
		return I18N::translate('Expand or collapse branches in the webtrees branches list with a single click.');
	}

	/** {@inheritdoc} */
	public function getConfigLink(): string {
		return route('module', [
			'module' => $this->getName(),
			'action' => 'Admin',
		]);
	}

	/** {@inheritdoc} */
	public function defaultMenuOrder(): int {
		return 999;
	}

	/** {@inheritdoc} */
	public function getMenu(Tree $tree) {
		// We don't actually have a menu - this is just a convenient "hook" to execute code at the right time during page execution
		global $controller;

		if (Filter::get('route') === 'branches' && Filter::get('surname') !== "") {
			echo $this->includeCss();

			$controller
				->addExternalJavaScript($this->directory . '/assets/js/page.js')
				->addInlineJavaScript('
					$(".wt-main-container form")
						.after("<div id=\"treecontrol\"><a href=\"#\">' . I18N::translate('Collapse all') . '</a> | <a href=\"#\">' . I18N::translate('Expand all') . '</a></div>")
						.after("<div class=\"loading-image\"></div>");

					$($(".wt-main-container ol").get().reverse()).each(function(){
						var html = $(this).html();
						if (html === "") {
							$(this).remove();
						}
						else {
							$(this).replaceWith("<ul>" + html +"</ul>")
						}
					});
					$(".wt-main-container ul:first").attr("id", "branch-list");

					$("li[title=\"' . I18N::translate('Private') . '\"]").hide();
				');

			if ($this->getPreference('FB')) {
				$controller->addInlineJavaScript('
					$("#branch-list, #branch-list ul, #branch-list li").addClass("aboville");
				');
			}

			$controller->addInlineJavaScript('
				$("#branch-list").treeview({
					collapsed: true,
					animated: "slow",
					control:"#treecontrol"
				});
				$("#branch-list").show();
				$(".loading-image").hide();
			');
		}
		return null;
	}

	/** {@inheritdoc} */
	public function getAdminAction(): Response {
		$this->layout = 'layouts/administration';
		return $this->viewResponse('admin', [
            'use_d_aboville'	=> $this->getPreference('FB'),
			'title'				=> $this->getTitle()
        ]);
	}

	/** {@inheritdoc} */
	public function postAdminAction(Request $request): RedirectResponse	{
		$use_d_aboville = (bool) $request->get('NEW_FB');
		$this->setPreference('FB', $use_d_aboville);

		$url = route('module', [
            'module' => 'fancy_branches',
            'action' => 'Admin'
        ]);

        return new RedirectResponse($url);
	}

	/**
	 * Default Fancy script to load a module stylesheet
	 *
	 * The code to place the stylesheet in the header renders quicker than the default webtrees solution
	 * because we do not have to wait until the page is fully loaded
	 *
	 * @return javascript
	 */
	protected function includeCss() {
		return
		'<script>' .
		  'var newSheet=document.createElement("link"); ' .
		  'newSheet.setAttribute("rel","stylesheet"); ' .
		  'newSheet.setAttribute("type","text/css"); ' .
		  'newSheet.setAttribute("href","' . $this->directory . '/assets/css/style.css"); ' .
		  'document.getElementsByTagName("head")[0].appendChild(newSheet); ' .
		'</script>';
	}
}

return new FancyBranchesModule;
