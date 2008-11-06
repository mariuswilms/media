<div class="movies view">
<h2><?php  __('Movie');?></h2>
	<dl><?php $i = 0; $class = ' class="altrow"';?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Id'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $movie['Movie']['id']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Created'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $movie['Movie']['created']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Modified'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $movie['Movie']['modified']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Title'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $movie['Movie']['title']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Director'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $movie['Movie']['director']; ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="attachments media">
	<?php foreach ($movie['Poster'] as $poster): ?>
		<div>
			<?php echo $medium->embed($poster['dirname'] . DS . $poster['basename']) ?>
		</div>
	<?php endforeach ?>
</div>
<div class="actions">
	<ul>
		<li><?php echo $html->link(__('Edit Movie', true), array('action'=>'edit', $movie['Movie']['id'])); ?> </li>
		<li><?php echo $html->link(__('Delete Movie', true), array('action'=>'delete', $movie['Movie']['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $movie['Movie']['id'])); ?> </li>
		<li><?php echo $html->link(__('List Movies', true), array('action'=>'index')); ?> </li>
		<li><?php echo $html->link(__('New Movie', true), array('action'=>'add')); ?> </li>
	</ul>
</div>
