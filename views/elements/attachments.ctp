<?php
/**
 * Attachments Element File
 *
 * Element listing associated attachments of the view's model.
 * Add, delete (detach) an Attachment.  This element requires
 * the media helper to be loaded and `$this->data` to be populated.
 *
 * Possible options:
 *  - `'previewVersion'` Defaults to `'xxs'`.
 *  - `'assocAlias'` Defaults to `'Attachment'`.
 *  - `'model'` Defaults to the model of the current form.
 *  - `'title'` Defaults to the plural form of `'assocAlias'`.
 *
 * Copyright (c) 2007-2011 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 *
 * PHP version 5
 * CakePHP version 1.3
 *
 * @package    media
 * @subpackage media.views.elements
 * @copyright  2007-2011 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */

if (!isset($this->Media) || !is_a($this->Media, 'MediaHelper')) {
	$message = 'Attachments Element - The media helper is not loaded but required.';
	trigger_error($message, E_USER_NOTICE);
	return;
}

if (!isset($previewVersion)) {
	$previewVersion = 's';
}

/* Set $assocAlias and $model if you're using this element multiple times in one form */

if (!isset($assocAlias)) {
	$assocAlias = 'Attachment';
} else {
	$assocAlias = Inflector::singularize($assocAlias);
}

if (!isset($model)) {
	$model = $this->Form->model();
}

$modelId = $this->Form->value($this->Form->model().'.id');

if (isset($this->data[$assocAlias][0]['basename'])) {
	array_unshift($this->data[$assocAlias],array());
}

if (!isset($title)) {
	$title = sprintf(__('%s', true), Inflector::pluralize($assocAlias));
}
?>
<div class="attachments element">
	<?php echo $title ?>
	<!-- New Attachment -->
	<div class="new">
	<?php
		echo $this->Form->hidden($assocAlias . '.0.model', array('value' => $model));
		echo $this->Form->hidden($assocAlias . '.0.group', array('value' => strtolower($assocAlias)));
		echo $this->Form->input($assocAlias . '.0.file', array(
			'label' => __('File', true),
			'type'  => 'file',
			'error' => array(
				'error'      => __('An error occured while transferring the file.', true),
				'resource'   => __('The file is invalid.', true),
				'access'     => __('The file cannot be processed.', true),
				'location'   => __('The file cannot be transferred from or to location.', true),
				'permission' => __('Executable files cannot be uploaded.', true),
				'size'       => __('The file is too large.', true),
				'pixels'     => __('The file is too large.', true),
				'extension'  => __('The file has the wrong extension.', true),
				'mimeType'   => __('The file has the wrong MIME type.', true),
		)));
		echo $this->Form->input($assocAlias . '.0.alternative', array(
			'label' => __('Textual replacement', true),
			'value' => '',
			'error' => __('A textual replacement must be provided.', true)
		));
	?>
	</div>
	<!-- Existing Attachments -->
	<div class="existing">
	<?php if (isset($this->data[$assocAlias])): ?>
		<?php for ($i = 1; $i < count($this->data[$assocAlias]); $i++): ?>
		<div>
		<?php
			$item = $this->data[$assocAlias][$i];

			echo $this->Form->hidden($assocAlias . '.' . $i . '.id', array('value' => $item['id']));
			echo $this->Form->hidden($assocAlias . '.' . $i . '.model', array('value' => $model));
			echo $this->Form->hidden($assocAlias . '.' . $i . '.group', array('value' => $item['group']));
			echo $this->Form->hidden($assocAlias . '.' . $i . '.dirname', array('value' => $item['dirname']));
			echo $this->Form->hidden($assocAlias . '.' . $i . '.basename', array('value' => $item['basename']));
			echo $this->Form->hidden($assocAlias . '.' . $i . '.alternative', array('value' => $item['alternative']));

			if ($file = $this->Media->file("{$item['dirname']}/{$item['basename']}")) {
				$url = $this->Media->url($file);
				$size = $this->Media->size($file);
				$name = $this->Media->name($file);

				echo $this->Media->embed($this->Media->file("{$previewVersion}/{$item['dirname']}/{$item['basename']}"), array(
					'restrict' => array('image')
				));

				if (isset($this->Number)) {
					$size = $this->Number->toReadableSize($size);
				} else {
					$size .= ' Bytes';
				}

				printf(
					'<span class="description">%s&nbsp;(%s/%s) <em>%s</em></span>',
					$url ? $this->Html->link($item['basename'], $url) : $item['basename'],
					$name,
					$size,
					$item['alternative']
				);
			}
			echo $this->Form->input($assocAlias . '.' . $i . '.delete', array(
				'label' => __('Release', true),
				'type' => 'checkbox',
				'value' => 0
			));
		?>
		</div>
		<?php endfor ?>
	<?php endif ?>
	</div>
</div>