<?php
/**
 * Attachments Element File
 *
 * Element listing associated Attachments of the View's Model
 * Add, delete (detach) an Attachment
 *
 * Copyright (c) 2007-2008 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 *
 * PHP version 5
 * CakePHP version 1.2
 *
 * @package    media
 * @subpackage media.views.elemetns
 * @author     David Persson <davidpersson@qeweurope.org>
 * @copyright  2007-2008 David Persson <davidpersson@qeweurope.org>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */

/* Set $assocAlias and $model if you're using this element multiple times in one form */

if (!isset($assocAlias)) {
	$assocAlias = 'Attachment';
} else {
	$assocAlias = Inflector::singularize($assocAlias);
}

if (!isset($model)) {
	$model = $form->model();
}

$modelId = $form->value($form->model().'.id');

if(isset($this->data[$assocAlias][0]['basename'])) {
	array_unshift($this->data[$assocAlias],array());
}
?>
<div class="attachments element">
	<?php printf(__('%s', true), Inflector::pluralize($assocAlias)) ?>
	<!-- New Attachment -->
	<div class="new">
	<?php
		echo $form->hidden($assocAlias . '.0.model',array('value' => $model));
		echo $form->hidden($assocAlias . '.0.group',array('value' => strtolower($assocAlias)));
		echo $form->input($assocAlias . '.0.file', array(
							'label' => __('File',true),
							'type'  => 'file',
							'error' => array(
								'error'      => __('An error occured while transferring the file.', true),
								'resource'   => __('The file is invalid.', true),
								'access'     => __('The file cannot be processed.', true),
								'location'   => __('The file cannot be transferred from or to location.', true),
								'permission' => __('Executable files cannot be uploaded.', true),
								'size'       => __('The file is too large.', true),
								'pixels'     => __('The file is too large.', true),
								'extension'  => __('The file has wrong extension.', true),
								'mimeType'   => __('The file has wrong mime type.', true),
								)
							)
						);
		echo $form->input($assocAlias . '.0.alternative', array(
							'label' => __('Textual replacement', true),
							'value' => '',
							'error' => __('A textual replacement must be provided.', true)
							)
						);
	?>
	</div>
	<!-- Existing Attachments -->
	<div class="existing">
	<?php if(isset($this->data[$assocAlias])): ?>
		<?php for($i = 1; $i < count($this->data[$assocAlias]); $i++): ?>
		<div>
		<?php
			$item = $this->data[$assocAlias][$i];

			echo $form->hidden($assocAlias . '.' . $i . '.id',array('value' => $item['id']));
			echo $form->hidden($assocAlias . '.'.$i . '.model',array('value' => $model));
			echo $form->hidden($assocAlias . '.'.$i . '.group',array('value' => $item['group']));
			echo $form->hidden($assocAlias . '.'.$i . '.dirname',array('value' => $item['dirname']));
			echo $form->hidden($assocAlias . '.'.$i . '.basename',array('value' => $item['basename']));
			echo $form->hidden($assocAlias . '.'.$i . '.alternative',array('value' => $item['alternative']));

		 	if ($file = $medium->file($item['dirname'].DS.$item['basename'])) {
		 		echo $medium->embed('xxs/' . $item['dirname'] . DS . $item['basename'], array('restrict' => array('image')));

		 		$Medium = Medium::factory($file);
				$size = filesize($file);

				if (isset($number)) {
					$size = $number->toReadableSize($size);
				}

				printf(	'<span><a href="%s">%s</a>&nbsp;(%s/%s) <em>%s</em></span>',
						$medium->url($file), $item['basename'],
						strtolower($Medium->name), $size, $item['alternative']);
			}

		 	echo $form->input($assocAlias . '.' . $i . '.delete', array('label' => __('Release', true), 'type' => 'checkbox', 'value' => 0));
		?>
		</div>
	<?php endfor ?>
	</div>
<?php endif ?>
</div>