<?php
/**
* @version		2.1.3
* @package		PagesAndItems com_pagesanditems
* @copyright	Copyright (C) 2006-2012 Carsten Engel. All rights reserved.
* @license		http://www.gnu.org/copyleft/gpl.html GNU/GPL
* @author		www.pages-and-items.com
*/

defined('_JEXEC') or die;
?>
	<table class="adminlist">
		<thead class="piheader">
		<tr>
			<th class="left">
				<?php echo JText::_('COM_MENUS_HEADING_ASSIGN_MODULE');?>
			</th>
			<th> <!-- class="piheader">-->
				<?php echo JText::_('COM_MENUS_HEADING_DISPLAY');?>
			</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($this->modules as $i => &$module) : ?>
			<tr class="row<?php echo $i % 2;?>">
				<td>
					<?php $link = 'index.php?option=com_modules&amp;client_id=0&amp;task=module.edit&amp;id='. $module->id.'&amp;tmpl=component&amp;view=module&amp;layout=modal' ; ?>
					<a class="modal" href="<?php echo $link;?>" rel="{handler: 'iframe', size: {x: 900, y: 550}}" title="<?php echo JText::_('COM_MENUS_EDIT_MODULE_SETTINGS');?>">
						<?php echo JText::sprintf('COM_MENUS_MODULE_ACCESS_POSITION', $this->escape($module->title), $this->escape($module->access_title), $this->escape($module->position)); ?></a>

				</td>
				<td class="center">
					<?php if (is_null($module->menuid)) : ?>
						<?php if ($module->except):?>
							<?php echo JText::_('JYES'); ?>
						<?php else : ?>
							<?php echo JText::_('JNO'); ?>
						<?php endif;?>
					<?php elseif ($module->menuid > 0) : ?>
						<?php echo JText::_('JYES'); ?>
					<?php elseif ($module->menuid < 0) : ?>
						<?php echo JText::_('JNO'); ?>
					<?php else : ?>
						<?php echo JText::_('JALL'); ?>
					<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
