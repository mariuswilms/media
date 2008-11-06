<div class="movies index">
<h2><?php __('Movies');?></h2>
<p>
<?php
echo $paginator->counter(array(
'format' => __('Page %page% of %pages%, showing %current% records out of %count% total, starting on record %start%, ending on %end%', true)
));
?></p>
<table cellpadding="0" cellspacing="0">
<tr>
	<th><?php echo $paginator->sort('id');?></th>
	<th><?php echo $paginator->sort('created');?></th>
	<th><?php echo $paginator->sort('modified');?></th>
	<th><?php echo $paginator->sort('title');?></th>
	<th><?php echo $paginator->sort('director');?></th>
	<th class="actions"><?php __('Actions');?></th>
</tr>
<?php
$i = 0;
foreach ($movies as $movie):
	$class = null;
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}
?>
	<tr<?php echo $class;?>>
		<td>
			<?php echo $movie['Movie']['id']; ?>
		</td>
		<td>
			<?php echo $movie['Movie']['created']; ?>
		</td>
		<td>
			<?php echo $movie['Movie']['modified']; ?>
		</td>
		<td>
			<?php echo $movie['Movie']['title']; ?>
		</td>
		<td>
			<?php echo $movie['Movie']['director']; ?>
		</td>
		<td class="actions">
			<?php echo $html->link(__('View', true), array('action'=>'view', $movie['Movie']['id'])); ?>
			<?php echo $html->link(__('Edit', true), array('action'=>'edit', $movie['Movie']['id'])); ?>
			<?php echo $html->link(__('Delete', true), array('action'=>'delete', $movie['Movie']['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $movie['Movie']['id'])); ?>
		</td>
	</tr>
<?php endforeach; ?>
</table>
</div>
<div class="paging">
	<?php echo $paginator->prev('<< '.__('previous', true), array(), null, array('class'=>'disabled'));?>
 | 	<?php echo $paginator->numbers();?>
	<?php echo $paginator->next(__('next', true).' >>', array(), null, array('class'=>'disabled'));?>
</div>
<div class="actions">
	<ul>
		<li><?php echo $html->link(__('New Movie', true), array('action'=>'add')); ?></li>
	</ul>
</div>
