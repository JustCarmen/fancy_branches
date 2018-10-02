<?php use Fisharebest\Webtrees\Bootstrap4; ?>
<?php use Fisharebest\Webtrees\Functions\FunctionsEdit; ?>
<?php use Fisharebest\Webtrees\I18N; ?>

<?= view('components/breadcrumbs', ['links' => [route('admin-control-panel') => I18N::translate('Control panel'), route('admin-modules') => I18N::translate('Modules'), $title]]) ?>

<h1><?= $title ?></h1>

<form action="<?= e(route('module', ['module' => 'fancy_branches', 'action' => 'Admin'])) ?>" method="post">
	<?= csrf_field() ?>
	<div class="row form-group">
		<label class="col-sm-4">
			<?= I18N::translate('Use “d’Aboville” numbering system') ?>
		</label>
		<div class="col-sm-8">
			<?= Bootstrap4::radioButtons('NEW_FB', FunctionsEdit::optionsNoYes(), $use_d_aboville, true) ?>
			<p class="small text-muted"><?= I18N::translate('The “D’aboville” numbering system is a method to split descending generations into numbering sections. Each generation and each child gets a succeeding number seperated by a dot.') ?></p>
		</div>
	</div>
	<button class="btn btn-primary" type="submit">
		<i class="fa fa-check"></i>
		<?= I18N::translate('save') ?>
	</button>
</form>
<p>