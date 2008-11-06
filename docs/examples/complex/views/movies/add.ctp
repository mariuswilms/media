<div class="movies form">
<?php echo $form->create('Movie', array('type' => 'file'));?>
	<fieldset>
 		<legend><?php __('Add Movie');?></legend>
	<?php
		echo $form->input('title');
		echo $form->input('director');
	?>
	</fieldset>
	<?php echo $this->element('attachments',array('plugin' => 'attachments','assocAlias' => 'Poster', 'model' => 'Movie')); ?>
	<?php echo $this->element('attachments',array('plugin' => 'attachments','assocAlias' => 'Trailer', 'model' => 'Movie')); ?>
		
<?php echo $form->end('Submit');?>
</div>
<div class="actions">
	<ul>
		<li><?php echo $html->link(__('List Movies', true), array('action'=>'index'));?></li>
	</ul>
</div>
