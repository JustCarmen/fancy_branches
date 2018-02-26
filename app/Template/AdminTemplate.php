<?php
/*
 * webtrees: online genealogy
 * Copyright (C) 2018 JustCarmen (http://www.justcarmen.nl)
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace JustCarmen\WebtreesAddOns\FancyBranches\Template;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Bootstrap4;
use Fisharebest\Webtrees\Controller\PageController;
use Fisharebest\Webtrees\Filter;
use Fisharebest\Webtrees\Functions\FunctionsEdit;
use Fisharebest\Webtrees\I18N;
use JustCarmen\WebtreesAddOns\FancyBranches\FancyBranchesModule;

class AdminTemplate extends FancyBranchesModule {
	protected function pageContent() {
		$controller = new PageController;
		return
		$this->pageHeader($controller) .
		$this->pageBody($controller);
	}

	private function pageHeader(PageController $controller) {
		$controller
		->restrictAccess(Auth::isAdmin())
		->setPageTitle($this->getTitle())
		->pageHeader();
	}

	private function pageBody(PageController $controller) {
		echo Bootstrap4::breadcrumbs([
			route('admin-control-panel') => I18N::translate('Control panel'),
			route('admin-modules')       => I18N::translate('Module administration'),
		], $controller->getPageTitle()); ?>
		<h1><?= $controller->getPageTitle() ?></h1>
		<form method="post" name="form1">
		  <?= Filter::getCsrf() ?>
		  <input type="hidden" name="save" value="1">
		  <div class="row form-group">
			<label class="col-sm-4">
			  <?= I18N::translate('Use “d’Aboville” numbering system') ?>
			</label>
			<div class="col-sm-8">
			  <?php $FB = $this->getPreference('FB'); ?>
			  <?= Bootstrap4::radioButtons('NEW_FB', FunctionsEdit::optionsNoYes(), $FB, true) ?>
			  <p class="small text-muted"><?= I18N::translate('The “D’aboville” numbering system is a method to split descending generations into numbering sections. Each generation and each child gets a succeeding number seperated by a dot.') ?></p>
			</div>
		  </div>
		  <button class="btn btn-primary" type="submit">
			<i class="fa fa-check"></i>
			<?= I18N::translate('save') ?>
		  </button>
		</form>
		<?php
	}
}
