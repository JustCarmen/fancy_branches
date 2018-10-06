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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/* Only the backend works in 2.0
 * For the frontend we have to wait until the branches-list has been converted to a view
 * See app/Http/Controllers/BrancheController.php
 */
class FancyBranchesModule extends AbstractModule implements ModuleConfigInterface {
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
	public function getAdminAction(): Response {
		$this->layout = 'layouts/administration';
		return $this->viewResponse('admin', [
			'use_d_aboville' => $this->getPreference('FB'),
			'title'          => $this->getTitle()
		]);
	}

	/** {@inheritdoc} */
	public function postAdminAction(Request $request): RedirectResponse {
		$use_d_aboville = (bool) $request->get('NEW_FB');
		$this->setPreference('FB', $use_d_aboville);

		$url = route('module', [
			'module' => 'fancy_branches',
			'action' => 'Admin'
		]);

		return new RedirectResponse($url);
	}
}

return new FancyBranchesModule;
