<?php
/**
* @version		2.0.0
* @package		PagesAndItems com_pagesanditems
* @copyright	Copyright (C) 2006-2011 Carsten Engel. All rights reserved.
* @license		http://www.gnu.org/copyleft/gpl.html GNU/GPL
* @author		www.pages-and-items.com
*/

//no direct access
if( !defined('_JEXEC')){
	die('Restricted access');
}

require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'html'.DS.'contentadministrator.php');

//declare the var to hide fields etc.
$display_none = 'style="display: none;"';

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHTML::_('behavior.mootools');
JHtml::_('behavior.calendar');

if($this->model->isSite)
{
	$frontend = 1;
}
else
{
	$frontend = 0;
}

$pageType = JRequest::getVar('pageType','');

//menutype from menu-item
$menutype = $this->menutype;

$pageId = JRequest::getVar('pageId',0);
//dump($pageId);
//get item_id
if($frontend)
{
	$item_id = JRequest::getVar('item_id', '' );
}
else
{
	$item_id = JRequest::getVar('itemId', '' );
}
$item_id = JRequest::getVar('itemId', JRequest::getVar('item_id', '' ) );


$sub_task = JRequest::getVar('sub_task','');
if($sub_task==''){
	if($item_id==''){
		$sub_task = 'new';
	}else{
		$sub_task = 'edit';
	}
}

//PI ACL
//this stuff is not in the view because it is also used for the frontend
if($sub_task=='new' && !$item_id){
	//new item		
	$this->helper->to_previous_page_when_no_permission('3');
}else{
	//edit item
	$this->helper->to_previous_page_when_no_permission('4');
}


//only access for the right usertypes
if($this->model->joomlaVersion < '1.6')
{
	if($frontend)
	{
		$allowed_user_types = array('Author','Editor','Publisher','Manager','Administrator','Super Administrator');
	}
	else
	{
		$allowed_user_types = array('Manager','Administrator','Super Administrator');
	}
	if(!in_array($this->model->user_type, $allowed_user_types))
	{
		die('You have no permission to edit content');
	}
}
else
{
	
	
	if(!$this->user->authorise('core.edit', 'com_content.article.'.(int)$item_id)){
		echo JText::_('COM_PAGESANDITEMS_NO_PERMISSION_TO_EDIT_THIS_ITEM');
		exit;	
	}
	

	
	
}

if($frontend){
	//get extra css to style frontend
	$doc =&JFactory::getDocument();
	$doc->addStylesheet('components/com_pagesanditems/css/frontend_edit.css'); //same as addScript but for css
}


//get page_id
//$page_id = JRequest::getVar('pageId', '' );
//get date and time

$datenow = $this->model->get_date_now(true);
$config = $this->model->getConfig();
$show_title_item = true;

$show_tree = true;
//switch for new and edit
if($sub_task=='new')
{
	//begin getting data for new item
	
	$popup = JRequest::getVar('tmpl', null );
	if($popup && $popup == 'component')
	{
		$show_tree = false;
	}
	
	
	
	
	//get category id of menuitem
	/*
		TODO over pagetype? ore model
	*/
	$cat_id = '';
	$section_id = '';
	if(!$frontend)
	{
		$this->model->db->setQuery("SELECT * FROM #__menu WHERE id='$pageId' LIMIT 1");
		//$rows = $this->model->db->loadObjectList();
		//$row = $rows[0];
		$row = $this->model->db->loadObject();
		if($row)
		{
			$cat_id = str_replace('index.php?option=com_content&view=category&layout=blog&id=','',$row->link);
		}
		else
		{
			$cat_id = 0;
		}
		
		//get the section which the category belongs to
		$this->model->db->setQuery("SELECT id, section, name FROM #__categories WHERE id='$cat_id'");//name?
		$rows = $this->model->db->loadObjectList();
		$row = $rows[0];
		if($row)
		{
			$section_id = $row->section;
		}
		else
		{
			$section_id = 0;
		}
	}
	
	//get category and section id from url
	if($frontend)
	{
		$section_id = intval(JRequest::getVar('section', '' ));
		$cat_id = intval(JRequest::getVar('category', '' ));
		$hide_page_select = JRequest::getVar('hide_select', '' );
	}
	//set vars for new item
	$item_id = '';
	$itemTitle = '';
	$itemTitleAlias = '';
	$itemIntroText = '';
	$itemFullText = '';
	$text = '';	
	$itemCreatedByAlias = '';
	$itemMetadesc = '';
	$itemMetakey = '';
	$user_id = '';
	$created = $datenow;
	$created_by = $this->model->user_id;
	$item_publish_up = $datenow;
	$item_publish_down = JText::_('COM_PAGESANDITEMS_NEVER');
	$version = '0';
	
	//$item_type = JRequest::getVar('item_type', 'text');
	$item_type = JRequest::getVar('item_type', JRequest::getVar('select_itemtype', 'text'));	
		
	$metadata_thing['robots'] = '';
	$metadata_thing['author'] = '';
	$values = array();
	$values['introtext'] = 1;
	$values['item_title'] = 1;
	
	$itemAttribs = 'show_title=
show_pdf_icon=
show_print_icon=
show_email_icon=
link_titles=
show_intro=
show_section=
link_section=
show_category=
link_category=
show_vote=
show_author=
show_create_date=
show_modify_date=
keyref=
language=en-GB
readmore=
';
	
	//explode array attributes
	
	$itemAttribs = explode( "\n", $itemAttribs);
	for($n = 0; $n < count($itemAttribs); $n++){
		$temp = explode('=',$itemAttribs[$n]);
		$var = $temp[0];
		$value = '';
		if(count($temp)==2){
			$value = $temp[1];
		}
		$values[$var] = trim($value);
	}
	
	
	
	//end getting data for new item
	?>
	
<script language="javascript" type="text/javascript">
<!--
		
<?php
//needed to rename the function for frontend because the javascript function got overwritten somewhere by the core
if($frontend)
{
?>
	function submitbutton2(pressbutton) 
	{
<?php
}
else
{
?>
	//function submitbutton(pressbutton) 
<?php 
if($this->model->joomlaVersion < '1.6')
{
	$joomlaSubmit = '';
	echo 'function submitbutton(pressbutton)'."\n";
}
else
{
	$joomlaSubmit = 'Joomla.';
	echo 'Joomla.submitbutton = function(pressbutton)'."\n";
}
?>


	{
<?php
}
?>

		
		/*
		for custom submittbutton from itemtype
		we need to split pressbutton
		
		pressbutton = eg. itemtype.content.task
		
		
		*/
		if (pressbutton == 'item.cancel') 
		{
			//submitform( pressbutton );
			document.getElementById('task').value = pressbutton;
			document.adminForm.submit();
			return;
		}
		
		
		if (pressbutton == 'item.item_apply') 
		{
			document.getElementById('item_apply').value = 1;
		}
		if (pressbutton == 'item.item_apply' || pressbutton == 'item.item_save') 
		{
			//alert('save apply');
			item_title = document.getElementById('jform_title').value;
			<?php
			if($this->model->joomlaVersion < '1.6')
			{
			?>
			item_title = trim(item_title);
			<?php
			}
			else
			{
			?>
				item_title = item_title.trim();
			<?php
			}
			?>
			if (item_title == '') 
			{
				alert('<?php echo addslashes(JText::_('COM_PAGESANDITEMS_NO_TITLE')); ?>');
				return;
			}
<?php
			//if itemtype is 'other_item' do validation
			if($item_type=='other_item'){
			?>
			if(document.adminForm.other_item_id.value == '0'){
				alert('<?php echo addslashes(JText::_('COM_PAGESANDITEMS_NO_OTHERITEM_SELECTED')); ?>');
				return;
			}	
			<?php
			}

if($frontend)
{

/*
with new pagetypes we can have article without section / categorie
so we can this remove
*/
?>
			//else if(document.adminForm.cat_id.value == '' || document.adminForm.cat_id.value=='0')
			else if(document.adminForm.jform_catid.value == '' || document.adminForm.jform_catid.value=='0')			
			{
				alert('<?php echo addslashes(JText::_('COM_PAGESANDITEMS_NO_PAGE_SELECTED')); ?>');
				return;
			}
<?php
}//END if($frontend)
?>
			else
			{
<?php
//if custom itemtype
if(strpos($item_type, 'ustom_'))
{
?>
				//validate_custom_itemtype_fields
				if(validate_custom_itemtype_fields())
				{
<?php
//if itemtype plugin
}
elseif($item_type!='content' && $item_type!='text' && $item_type!='html' && $item_type!='other_item')
{
?>
				//validate_itemtype
				if(validate_itemtype())
				{
<?php
}
//if custom itemtype or itemtype plugin
if($item_type!='content' && $item_type!='text' && $item_type!='html' && $item_type!='other_item')
{
?>
					//submitform('item.item_save');
					document.getElementById('task').value = 'item.item_save';
					document.adminForm.submit();
				}
<?php
}
else
{
?>
				//no validation
				//alert('submit');
				//submitform('item.item_save');
				document.getElementById('task').value = 'item.item_save';
				document.adminForm.submit();
<?php
}
?>
			}
		}
	}
-->
</script>
<?php
}
else
{
	//begin getting data to edit item
	
	
		
	
	
	//data from item
	$this->model->db->setQuery("SELECT * FROM #__content WHERE id='$item_id' LIMIT 1");
	$rows = $this->model->db->loadObjectList();
	$row = $rows[0];
	
	$item_id = $row->id;
	$itemTitle = htmlspecialchars($row->title);
	
	
	$itemTitleAlias = htmlspecialchars($row->alias);
	
		
	$itemIntroText = htmlspecialchars($row->introtext);
	$itemFullText = htmlspecialchars($row->fulltext);
	if($itemFullText!=''){
		$text = $itemIntroText.'<hr id="system-readmore" />'.$itemFullText;
	}else{
		$text = $itemIntroText;
	}
	
	$itemState = $row->state;
	$cat_id = $row->catid;
	$section_id = $row->sectionid;
	$itemCreatedByAlias = $row->created_by_alias;
	$item_publish_up = $row->publish_up;
	$item_publish_up = $this->model->get_date_to_format($item_publish_up);
	$item_publish_down = $row->publish_down;
	if($item_publish_down!='0000-00-00 00:00:00'){
		$item_publish_down = $this->model->get_date_to_format($item_publish_down);
	}
	$itemMetakey = $row->metakey;
	$itemMetadesc = $row->metadesc;
	$itemAttribs = $row->attribs;
	$version = $row->version;
	$itemAccess = $row->access;
	$item_hits = $row->hits;
	$created = $row->created;
	$created = $this->model->get_date_to_format($created);
	$created_by = $row->created_by;
	$item_modified = $row->modified;
	$item_modified = $this->model->get_date_to_format($item_modified);
	
	if($itemAttribs==''){
		$itemAttribs = 'item_title=1
introtext=1
show_title=
show_pdf_icon=
show_print_icon=
show_email_icon=
link_titles=
show_intro=
show_section=
link_section=
show_category=
link_category=
show_vote=
show_author=
show_create_date=
show_modify_date=
keyref=
language=en-GB
readmore=
';
	
	}
	
	/*
	//testing date stuff
	
	echo $this->model->get_date_now(0).'<br />';
	echo 'uit db='.$item_modified.'<br />';
	$item_modified = $this->model->get_date_to_format($item_modified);
	echo 'naar format='.$item_modified.'<br />';
	
		
		$item_modified = $this->model->get_date_ready_for_database($item_modified);
		
		echo 'naar db='.$item_modified.'<br />';
	*/
	
	if($item_publish_down=='0000-00-00 00:00:00'){
		$item_publish_down = JText::_('COM_PAGESANDITEMS_NEVER');
	}
	
	$user_id = $this->model->user_id;
	
	//if author only new items and own items can be editted
	if($this->model->user_type=='Author'){
		//check if the item to edit is the authors own article
		if($this->model->user_id!=$created_by){
			echo "<script> alert('".JText::_('COM_PAGESANDITEMS_NOITEMACCESS')."'); window.history.go(-1); </script>";
			exit();
		}
	}
		
	//explode array attributes
	$itemAttribs = explode( "\n", $itemAttribs);
	for($n = 0; $n < count($itemAttribs); $n++){
		//list($var,$value) = split('=',$itemAttribs[$n]); 
		//$values[$var] = trim($value); 
		
		$values_temp = explode('=',$itemAttribs[$n]); 
		$var = $values_temp[0];
		$value = str_replace($var.'=','',$itemAttribs[$n]);
		$value = trim($value);
		$values[$var] = $value;
	}
	
	$metadata = $row->metadata;
	$metadata = explode( "\n", $metadata);
	for($n = 0; $n < count($metadata); $n++)
	{
		//list($var,$value) = split('=',$metadata[$n]); 
		//$metadata_thing[$var] = trim($value); 
		
		$values_temp = explode('=',$metadata[$n]); 
		$var = $values_temp[0];
		$value = str_replace($var.'=','',$metadata[$n]);
		$value = trim($value);
		$metadata_thing[$var] = $value;
	}

	
	//get data from item index
	$this->model->db->setQuery("SELECT * FROM #__pi_item_index WHERE item_id='$item_id' LIMIT 1");
	$rows = $this->model->db->loadObjectList();
	
	if($rows)
	{
		$row = $rows[0];
		$show_title_item = $row->show_title;
		$item_type = $row->itemtype;
	}
	else
	{
		$show_title_item = true;
		$item_type = 'text';		
	}
	
	if($item_type=='' || $item_type=='content')
	{		
		$item_type = 'text';
	}
		
	//check if item is currently on frontpage
	$this->model->db->setQuery("SELECT content_id FROM #__content_frontpage WHERE content_id='$item_id' LIMIT 1");
	$rows = $this->model->db->loadObjectList();
	if(count($rows)>0)
	{
		$itemFrontpage = true;
	}else{
		$itemFrontpage = false;
	}
	
	?>
	<script language="javascript" type="text/javascript">
		<!--
		<?php
		
		
		
		
		
		//needed to rename the function for frontend because the javascript function got overwritten somewhere by the core
		if($frontend){
		?>
		function submitbutton2(pressbutton) {
		<?php
		}else{
		?>

//		function submitbutton(pressbutton) 
	<?php 
	if($this->model->joomlaVersion < '1.6')
	{
		$joomlaSubmit = '';
		echo 'function submitbutton(pressbutton)'."\n";
	}
	else
	{
		$joomlaSubmit = 'Joomla.';
		echo 'Joomla.submitbutton = function(pressbutton)'."\n";
	}
	?>
		{
		<?php
		}
		?>
			do_item_save =false;
			if (pressbutton == 'item.cancel') {
				submitform( pressbutton );
				document.getElementById('task').value = 'item.cancel';
				document.adminForm.submit();
				return;
			}			
			
			if (pressbutton == 'item_move_select') {
				document.location.href = 'index.php?option=com_pagesanditems&view=item_move_select&pageId=<?php if($frontend){echo 'nothing';}else{echo $pageId;} ?>&item_id=<?php echo $item_id; ?>';
			}
			
			if (pressbutton == 'item.item_archive'){	
				if(confirm("<?php echo JText::_('COM_PAGESANDITEMS_SURE_ARCHIVE'); ?>")){	
					document.getElementById('sub_task').value = 'archive';							
					document.getElementById('task').value = 'item.state';				
					document.adminForm.submit();
				}
				return false;
			}
			
			if (pressbutton == 'item.item_publish') {
				if(confirm("<?php echo JText::_('COM_PAGESANDITEMS_SURE_PUBLISH'); ?>")){
					document.getElementById('sub_task').value = 'publish';
					document.getElementById('task').value = 'item.state';
					document.adminForm.submit();
				}
				return false;
			}
			
			if (pressbutton == 'item.item_unpublish') {
				if(confirm("<?php echo JText::_('COM_PAGESANDITEMS_SURE_UNPUBLISH'); ?>")){
					document.getElementById('sub_task').value = 'unpublish';
					document.getElementById('task').value = 'item.state';
					document.adminForm.submit();
				}
				return false;
			}
			if (pressbutton == 'item.item_trash') {
				if(confirm("<?php echo JText::_('COM_PAGESANDITEMS_SURE_TRASH'); ?>")){
					document.getElementById('sub_task').value = 'trash';
					document.getElementById('task').value = 'item.state';
					document.adminForm.submit();					
				}
				return false;
			}			
			if (pressbutton == 'item.item_delete') {
				if(confirm("<?php echo JText::_('COM_PAGESANDITEMS_SURE_DELETE'); ?>")){					
					document.getElementById('sub_task').value = 'delete';							
					document.getElementById('task').value = 'item.state';
					document.adminForm.submit();					
				}
				return false;
			}
			
			if (pressbutton == 'item.item_apply') {
				if (document.getElementById('jform_title').value == '') {
					alert('<?php echo JText::_('COM_PAGESANDITEMS_NO_TITLE'); ?>');
					return;
				} else {
					document.getElementById('item_apply').value = 1;
					do_item_save =true;
				}
			}
			if (pressbutton == 'item.item_save') {
				if (document.getElementById('jform_title').value == '') {
					alert('<?php echo JText::_('COM_PAGESANDITEMS_NO_TITLE'); ?>');
					return;
				<?php
				if($frontend)
				{
				/*
				with the new pagetypes we can have articles without section / categorie
				so we can remove this
				*/
				?>
					}else if(document.adminForm.cat_id.value == '' || document.adminForm.cat_id.value=='0'){
						alert('<?php echo JText::_('COM_PAGESANDITEMS_NO_PAGE_SELECTED'); ?>');
						return;
				<?php
				}
				?>
				} else {
					do_item_save =true;
				}
			}
			if(do_item_save){
				<?php
					//if custom itemtype
					if(strpos($item_type, 'ustom_')){
				?>
				if(validate_custom_itemtype_fields())
				{
				<?php
					}elseif($item_type!='content' && $item_type!='text' && $item_type!='html' && $item_type!='other_item'){//if itemtype plugin
					?>
					if(validate_itemtype()){
					<?php
					}
				
					if($item_type=='other_item'){
					?>
					if(document.adminForm.other_item_id.value == '0'){
						alert('<?php echo addslashes(JText::_('COM_PAGESANDITEMS_NO_OTHERITEM_SELECTED')); ?>');
						return;
					}	
					<?php
					}
					?>
					//submitform('item.item_save');
					document.getElementById('task').value = 'item.item_save';
					document.adminForm.submit();
				<?php
					//if custom itemtype or itemtype plugin
					if($item_type!='content' && $item_type!='text' && $item_type!='html' && $item_type!='other_item'){
				?>
				}
				<?php
					//if custom itemtype
					}
				?>
				
			}
		}
		-->
	</script>
	<?php
}//end getting data to edit item

$path = realpath(dirname(__FILE__).DS.'..'.DS.'..'.DS.'..');
require_once($path.DS.'includes'.DS.'extensions'.DS.'itemtypehelper.php');
if(strpos($item_type, 'ustom_'))
{
	//here we will load all custom_? 
	$itemtype = ExtensionItemtypeHelper::importExtension(null, 'custom',true,null,true);
}
else
{
	//here we will load all the other 
	//content, text, html and other_item are integrated
	$itemtype = ExtensionItemtypeHelper::importExtension(null, $item_type,true,null,true);
}

//$itemtype = ExtensionHelper::importExtension('itemtype',null, null,true,null,true);
$dispatcher = &JDispatcher::getInstance();

//javascript for frontend to set section and category from select
if($frontend){
?>
<script language="javascript" type="text/javascript">
<!--
function set_section_and_category(section_category){
	pos = section_category.indexOf("_");
	category = section_category.substr((pos+1),section_category.length);
	document.getElementById('cat_id').value = category;
	section = section_category.substr(0,pos);
	document.getElementById('section_id').value = section;
}
function set_section(section){
	document.getElementById('section_id').value = section;
}
function set_category(section_category){
	document.getElementById('cat_id').value = category;
}
-->
</script>
	
<?php
}//end if frontend

//auto select category, only at backend
if(!$frontend){
	echo '<script>'."\n";
	
	echo 'function select_category(){'."\n";	
	echo 'document.getElementById(\'jform_catid\').value = \''.$cat_id.'\';'."\n";	
	echo '}'."\n";
	
	echo 'if(window.addEventListener)window.addEventListener("load",select_category,false);'."\n";
	echo 'else if(window.attachEvent)window.attachEvent("onload",select_category);'."\n";
	
	echo '</script>'."\n";
}

//if pagetype 'featured' like homepage, make 'featured' selected
if($pageType=='content_featured'){
	echo '<script>'."\n";
	
	echo 'function select_featured(){'."\n";	
	echo 'document.getElementById(\'jform_featured\').value = \'1\';'."\n";	
	echo '}'."\n";
	
	echo 'if(window.addEventListener)window.addEventListener("load",select_featured,false);'."\n";
	echo 'else if(window.attachEvent)window.attachEvent("onload",select_featured);'."\n";
	
	echo '</script>'."\n";
}

//set empty item_type to text/content
if($item_type=='')
{
	$item_type = 'text';	
}

//if itemtype is not installed
if(!$this->model->checkItemTypeInstall($item_type))
{
	echo '<script> alert(\''.addslashes(JText::_('COM_PAGESANDITEMS_ITEMTYPENOTINSTALLED')).$item_type.'\'); window.history.go(-1); </script>';
	exit();
}

//if itemtype is not published, throw error
if (!in_array($item_type, $this->model->getItemtypes()))
{
	echo '<script> alert(\''.addslashes(JText::_('COM_PAGESANDITEMS_ITEMTYPENOTPUBLISHED')).$item_type.'\'); window.history.go(-1); </script>';
	exit();
}

if(!$frontend && $show_tree)
{
	echo '<table cellspacing="0" cellpadding="0" border="0" width="100%">';
	echo '<tr><td  valign="top" width="20%">';	
	echo $this->pageTree;	
	echo '</td><td valign="top">';
}

if($frontend)
{
//	echo '<form name="adminForm" method="post" action="index.php?option=com_pagesanditems&view=item_save" enctype="multipart/form-data">';
	echo '<form id="adminForm" name="adminForm" method="post" action="index.php?option=com_pagesanditems&view=item&task=item.item_save" enctype="multipart/form-data">';
}else{
	echo '<form id="adminForm" name="adminForm" method="post" action="" enctype="multipart/form-data">';
}
	
?>
	
		<input type="hidden" id="option" name="option" value="com_pagesanditems" />
		<input type="hidden" name="view" value="item" />		
		<input type="hidden" id="item_apply" name="item_apply" value="" />
		<input type="hidden" name="id" value="<?php echo $item_id; ?>" />
		<input type="hidden" name="item_id" value="<?php echo $item_id; ?>" />
		<input type="hidden" name="itemId" value="<?php echo $item_id; ?>" />
		<input type="hidden"  id="cat_id" name="cat_id" value="<?php echo $cat_id; ?>" />
		<input type="hidden" id="section_id" name="section_id" value="<?php echo $section_id; ?>" />
		<input type="hidden" id="page_id" name="page_id" value="<?php echo $pageId; ?>" />
		<input type="hidden"  id="pageId" name="pageId" value="<?php echo $pageId; ?>" />		
		<input type="hidden" name="modified_by" value="<?php echo $user_id; ?>" />		
		<input type="hidden" name="created_by" value="<?php echo $created_by; ?>" />
		<input type="hidden" name="version" value="<?php echo ($version+1); ?>" />
		<input type="hidden" name="item_type" value="<?php echo $item_type; ?>" />
		<input type="hidden" name="edit_from_frontend" value="<?php if($frontend){echo '1';} ?>" />
				
		<input type="hidden" id="sub_task" name="sub_task" value="<?php echo $sub_task; ?>" />
		<input type="hidden" id="task" name="task" value="item.item_save" />
		<input type="hidden" id="subsub_task" name="subsub_task" value="" />
		<input type="hidden" id="menutype" name="menutype" value="<?php echo $menutype; ?>">

		<input type="hidden" id="extension" name="extension" value="<?php echo $item_type; ?>">
		<input type="hidden" id="extensionType" name="extensionType" value="itemtype">		
		
		<input type="hidden" id="pageType" name="pageType" value="<?php echo JRequest::getVar('pageType',''); ?>" />
		<input type="hidden" id="type" name="type" value="" />
		<?php echo JHtml::_('form.token'); ?>
		<input type="hidden" name="return" value="<?php echo JRequest::getCmd('return');?>" />
		
		<?php
			$global_hide_show = array(array('',JText::_('COM_PAGESANDITEMS_GLOBAL')),array('0',JText::_('COM_PAGESANDITEMS_HIDE')),array('1',JText::_('COM_PAGESANDITEMS_SHOW')));
			$global_no_yes = array(array('',JText::_('COM_PAGESANDITEMS_GLOBAL')),array('0',JText::_('COM_PAGESANDITEMS_NO')),array('1',JText::_('COM_PAGESANDITEMS_YES')));		
			
			
			if($frontend){
				$path_to_root = '';
			}else{
				$path_to_root = '../';
			}
			
			
			
			//layout and script for frontend
			if($frontend)
			{
				if($this->model->joomlaVersion < '1.6')
				{
					echo '<script type="text/javascript" src="includes/js/joomla.javascript.js"></script>';
					echo '<script type="text/javascript" src="media/system/js/mootools.js"></script>';
				}
				echo '<link type="text/css" rel="stylesheet" href="administrator/components/com_pagesanditems/css/pagesanditems.css" />';
			}
			
			//get tabs-script
			//COMMENT no tabpane in J1.6
			if($this->model->joomlaVersion < '1.6')
			{
				echo '<link type="text/css" rel="stylesheet" href="'.$path_to_root.'includes/js/tabs/tabpane.css" />';
				echo '<script type="text/javascript" src="'.$path_to_root.'includes/js/tabs/tabpane_mini.js"></script>';
			}
		
			
			
		
		?>
	
	<style type="text/css">	
	
	.text_area{
		width: 100px;
	}
	
	<?php if(!$frontend){ ?>
	.calendar{
		cursor: pointer;
		float: left;
	}
	<?php } ?>
	</style>
	
	<?php
	
	//fix left-side of tabs issue
	//TODO move to css?
		echo '
		<style type="text/css">
			
			div#tabs_item div.tab-row h2.tab a{
				background: url('.$path_to_root.'administrator/components/com_pagesanditems/images/tab_off.png) no-repeat left top;
			}
			
			div#tabs_item div.tab-row h2.selected a{
				background: url('.$path_to_root.'administrator/components/com_pagesanditems/images/tab_on.gif) no-repeat left top;
				padding: 2px 10px 3px 10px;
			}
		</style>';
	
	//get language for itemtype, defaults to english
	//is automatic over the extensions

	//breadcrumbs only at backend
	if(!$frontend && $show_tree)
	{
	?>
	<div id="pi_breadcrumb">
		<a href="index.php?option=com_pagesanditems&amp;view=page&amp;sub_task=edit&amp;pageId=<?php echo $pageId; ?>"><?php echo JText::_('COM_PAGESANDITEMS_PAGE'); ?></a> >
		<?php echo JText::_('COM_PAGESANDITEMS_ITEM'); ?> [<?php echo $this->model->translate_item_type($item_type); ?>]<?php if($sub_task=='new'){echo ' '.JText::_('COM_PAGESANDITEMS_NEW');}?>
	</div>
	<?php
	}
	
	//submit buttons only at frontend
	if($frontend)
	{
		echo '<div class="paddingList" style="margin-top: 40px;">';
			echo '<div>';
				echo '<div class="right_align">';
					$image= PagesAndItemsHelper::getDirIcons().'icon-32-pi.png';
					echo '<img src="'.$image.'" alt="" style="float:left;" />&nbsp;';
				
					$button = PagesAndItemsHelper::getButtonMaker();
					$button->imagePath = $this->model->dirIcons;
					$button->buttonType = 'input';
					$button->text = JText::_('COM_PAGESANDITEMS_SAVE');
					//$button->alt = 'alt JText::_('COM_PAGESANDITEMS_CONVERT_TO_PI_ITEM')(s)';
					$button->onclick = 'submitbutton2(\'item.item_save\')';
					$button->imageName = 'base/icon-16-disk.png';
					echo $button->makeButton();
					
					$button = PagesAndItemsHelper::getButtonMaker();
					$button->imagePath = $this->model->dirIcons;
					$button->buttonType = 'input';
					$button->text = JText::_('COM_PAGESANDITEMS_CANCEL');
					//$button->alt = 'alt JText::_('COM_PAGESANDITEMS_CONVERT_TO_PI_ITEM')(s)';
					$button->onclick = 'history.back();';
					$button->imageName = 'base/icon-16-cancel.png';
					echo $button->makeButton();
					
					//echo '<input type="button" value="'.JText::_('COM_PAGESANDITEMS_SAVE').'" onclick="submitbutton2(\'item_save\')" />&nbsp;&nbsp;&nbsp;';
					//echo '<input type="button" value="'.JText::_('COM_PAGESANDITEMS_CANCEL').'" onclick="history.back();" />';
				echo '</div>';
			echo '</div>';
		echo '</div>';
	}
			
			
	$has_content = '';

	$itemtypeHtmlContent = & new JObject();
	$itemtypeHtmlContent->text = '';
	$results = $dispatcher->trigger('onItemtypeDisplay_item_content', array(&$itemtypeHtmlContent,$item_type,$this->model)); //,$item_id,$text,$itemIntroText,$itemFullText));
	if($itemtypeHtmlContent->text != '')
	{
		$has_content = 'properties';
	}
	echo $itemtypeHtmlContent->text;
	/*
	
	has_content mean the itemtype use subviews?
	//TODO other way
	if(file_exists($this->controller->pathPluginsItemtypes.'/'.$item_type.'/admin/item_content.php'))
	{
		require_once($this->controller->pathPluginsItemtypes.'/'.$item_type.'/admin/item_content.php');
		$has_content = 'properties';
	}
	*/
	?>
	<table class="adminform">
	<tr>
		<th>
			<?php
				//$menuItemsType = $this->menuItemsTypes->components->content_article; //s->$pageType;
				$menuItemsType = $this->menuItemsTypes['content_article'];
				$image = null;
				if($sub_task == 'new')
				{
					if(isset($menuItemsType->icons->item_new->imageUrl))
					{
						$image = $menuItemsType->icons->item_new->imageUrl;
					}
				}
				else
				{
					if(isset($menuItemsType->icons->item_edit->imageUrl))
					{
						$image = $menuItemsType->icons->item_edit->imageUrl;
					}				
				}
				if($image)
				{
					echo '<img src="'.$image.'" alt="" style="vertical-align: middle;position: relative;" />&nbsp;';
				}
				else
				{
					//echo '<img src="'.$this->controller->dirIcons.'icon-16-menu.png" alt="" style="vertical-align: middle;" />&nbsp;';
				}
				//echo JText::_('COM_PAGESANDITEMS_ITEM_CAP').' '.$has_content;
				echo empty($this->item->id) ? JText::_('COM_CONTENT_NEW_ARTICLE') : JText::sprintf('COM_CONTENT_EDIT_ARTICLE', $this->item->id);
				
			?>
		</th>
	</tr>
	<tr>
		<td> 
			<table cellspacing="0" cellpadding="0" border="0" width="100%">
				<tr>
				  <td valign="top">
					<?php
						
												
						//TODO for pagetype not content_category_blog we will have sections and category input
						if($frontend && $this->model->joomlaVersion < '1.6')
						{
							//if(!$hide_page_select){
						
								$pi_sections_array = $this->model->get_sections();
								//print_r($pi_sections_array);
								
								$pi_category_array = $this->model->get_categories();
								//print_r($pi_category_array);
								
								$pages_array = array();
								if($config['new_item_section_category_select'] != 'section_categories')
								{
									//make select based on pages in menu
									
									//get all categoryblog pages in an array
									//TODO over pagetype?
									$menuitems = $this->model->getMenuitems();
									foreach($menuitems as $menu_item_page)
									{
										if((strstr($menu_item_page->link, 'index.php?option=com_content&view=category&layout=blog') && $menu_item_page->type!='url' && $menu_item_page->type=='component') ||
										($menu_item_page->type=='content_category_blog')
										)
										{
											$cat_id_temp = str_replace('index.php?option=com_content&view=category&layout=blog&id=','',$menu_item_page->link);
											$pages_array[] = array($menu_item_page->id, $menu_item_page->name, $cat_id_temp);
										}
									}
								}else{
									//make select based on sections and categories, NOT based on pages in the menu
									foreach($pi_category_array as $pi_category){
										$category_section_id = $pi_category[2];
										foreach($pi_sections_array as $pi_sections){
											if($pi_sections[0]==$category_section_id){
												$pages_array[] = array('', $pi_category[1], $pi_category[0]);
											}
										}
									}
								}

								//make new array combining the 3 arrays and filtering for access
								$array_pages_sections_categories = array();
								for($n = 0; $n < count($pages_array); $n++){
									$pageId = $pages_array[$n][0];
									$page_title = $pages_array[$n][1];
									
									//get the section the pages category is linked to
									for($m = 0; $m < count($pi_category_array); $m++){
										if($pages_array[$n][2]==$pi_category_array[$m][0]){
											$page_cat_id = $pages_array[$n][2];
											$page_section_id = $pi_category_array[$m][2];
											break;
										}
									}
									
									//get section name from id
									for($s = 0; $s < count($pi_category_array); $s++){
										if($page_section_id==$pi_sections_array[$s][0]){
											$page_section_name = $pi_sections_array[$s][1];
											break;
										}
									}
									if($this->model->check_section_access($page_section_id) && $this->model->check_category_access($page_cat_id) && $this->model->check_page_access($pageId)){
										$array_pages_sections_categories[] = array($pageId, $page_title, $page_section_name, $page_cat_id, $page_section_id);
									}
								}
								//print_r($array_pages_sections_categories);
								
								//sort array by order
								$column = '';//reset column if you used this elsewhere
								$column = array();
								foreach($array_pages_sections_categories as $sortarray){
									$column[] = $sortarray[1];
								}
								$sort_order = SORT_ASC;//define as a var or else ioncube goes mad
								array_multisort($column, $sort_order, $array_pages_sections_categories);
								/*
								TODO build select section and categorie in other way?
								
								*/
								
								
								echo '<div>';
								echo JText::_('COM_PAGESANDITEMS_PAGE').' / '.strtolower(JText::_('COM_PAGESANDITEMS_SECTION')).': ';
								echo '<select name="pages" onchange="set_section_and_category(this.value)">';
								echo '<option value="0_0">'.JText::_('COM_PAGESANDITEMS_SELECT_PAGE2').'</option>';
								for($p = 0; $p < count($array_pages_sections_categories); $p++){
									echo '<option value="'.$array_pages_sections_categories[$p][4].'_'.$array_pages_sections_categories[$p][3].'"';
									//select the current category when edittinig from the frontend
									if($array_pages_sections_categories[$p][3]==$cat_id){
										echo ' selected="selected"';
									}
									echo '>';
									echo $array_pages_sections_categories[$p][1].' / '.$array_pages_sections_categories[$p][2];
									echo '</option>';
								}
								echo '</select>';
								echo '</div>';
								//echo '<br />';
								//echo '<br />';
							//}
						}
						else
						{
						
						}
						$itemtypeHtml = & new JObject();
						$itemtypeHtml->text = '';
						
						//ADD ms: 02.05.2011
						$managerItemtypeItemEdit = & new JObject();
						$managerItemtypeItemEdit->text = '';

						//ADD END ms: 02.05.2011
						/*
						trigger here the pi_fish for itemtypes?
						only for not custom_ itemtypes?
						$item_type
						*/
						
						
						$params = null;
						
						$dispatcher->trigger('onGetParams',array(&$params, $item_type));
						//echo '1234';
						//dump($params);
						
						if($item_id)
						{
							$path = realpath(dirname(__FILE__).DS.'..'.DS.'..'.DS.'..');
							require_once($path.DS.'includes'.DS.'extensions'.DS.'managerhelper.php');
							$extensions = ExtensionManagerHelper::importExtension(null,null, true,null,true);
							$dispatcher->trigger('onManagerItemtypeItemEdit', array (&$managerItemtypeItemEdit,$item_type,$item_id,$params));
						
						}
						
						
						
						
						/*
						ms: i think we remove the lines 1183 to 1239 and lines 1383 to 1388
						and use an manager for translate not custom-itemtype
						*/
						/*$languageItemtypeHtml = & new JObject();
						$languageItemtypeHtml->text = '';
						//if not custom itemtype and have we an item_id
						if(strpos($item_type, 'ustom_') === false && $item_id)
						{
							//$params = null;
							//$dispatcher->trigger('onGetParams',array(&$params, $item_type));
							if($params)
							{
								
								//	ok we have the params let us get if the itemtype will translate
								//	we must set in the itemtype params this
								//	and we must set what tables this will be the difficults
								//	
								//	in itemtype = content||text||html||other_item we have only  #__content
								//	in other we can have #__content and #__* 
								//	
								//	for own tables and sub_tables the itemtype must manage this self
									
								
								if($params->get('translatable',0))
								{
									//rewrite to helpers/language.php
									//like 
									
									$path = realpath(dirname(__FILE__).DS.'..'.DS.'..'.DS.'..');
									require_once($path.DS.'helpers'.DS.'language.php');
									//$languageParams = ???
									$content_table = $params->get('content_table','content');
									if($content_table)
									{
										//CHANGE ms: 02.05.2011
										$languageItemtypeHtml->text = PagesAndItemsHelperLanguage::languageDisplayItemTypeItemEdit($item_type,$item_id,$content_table,$params);
										//echo 'XXXXXXXXXXXXXXXXXX';
									}
									//
									

									//ok the itemtype will translatable we trigger the pi_fish
									// and is pi_fish not avaible nothing will display
									//$path = realpath(dirname(__FILE__).DS.'..'.DS.'..'.DS.'..');
									//require_once($path.DS.'includes'.DS.'extensions'.DS.'fieldtypehelper.php');
									//$extension = ExtensionFieldtypeHelper::importExtension(null, 'pi_fish',true,null,true);

									
									//$itemtypeTranlateHtml = '';
									//$content_table = $params->get('content_table','content');
									//$dispatcher->trigger('onPi_FishDisplayItemTypeItemEdit', array(&$itemtypeTranslateHtml,$item_type,$item_id,$content_table)); 

									

									//echo $itemtypeTranslateHtml;

								}
								
							}
						}*/
						
						//$results = $dispatcher->trigger('onItemtypeDisplay_item_edit', array(&$itemtypeHtml,$item_type,$item_id,$text,$itemIntroText,$itemFullText));
						

						echo "<script language=\"javascript\"  type=\"text/javascript\">\n";
						echo "<!--\n";	
						echo "function validate_itemtype(){\n\n";
						echo "is_valid = true;\n";	
						echo "alert_message = \"";
	
						$translated = JText::_('PI_EXTENSION_ITEMTYPE_'.strtoupper($item_type).'_ALERT_MESSAGE');
						if($translated <> 'PI_EXTENSION_ITEMTYPE_'.strtoupper($item_type).'_ALERT_MESSAGE')
						{
							//we have an string
							echo $translated;
						}
						echo "\";\n";
						if(file_exists(JPATH_COMPONENT_ADMINISTRATOR.DS.'extensions'.DS.'itemtypes'.DS.$item_type.DS.'item_validation.js'))
						{
							echo JFile::read(JPATH_COMPONENT_ADMINISTRATOR.DS.'extensions'.DS.'itemtypes'.DS.$item_type.DS.'item_validation.js');
						}
						echo "return is_valid;\n";
						echo "};\n";
						//close javascript
						echo "//-->\n";
						echo "</script>\n";
						
						//echo $itemtypeHtml->text;
						//echo '<br />';
						/*
						*/
						//echo plugin-specific fields
						
						?>
						<div class="clr"></div>
		
						<!-- <div class="width-70 fltlft" style="padding-top: 7px;"> -->
						<!-- <div class="width-60 fltlft" style="padding-top: 7px;"> -->
						<!-- <div class="width-60 fltlft" > -->
						<div class="width-70 fltlft" >
							<fieldset class="adminform" <?php if(!$this->helper->check_display('item_props_details')){echo $display_none;} ?>>
								<?php //ms: is in frontend_edit.css if(!$frontend){ ?>
								<legend id="content_details"><?php echo JText::_('JDETAILS'); ?></legend>
								<?php //} ?>
								<ul class="adminformlist">
									<li <?php if(!$this->helper->check_display('item_props_title')){echo $display_none;} ?>>
									<?php echo $this->form->getLabel('title'); ?>
									<?php echo $this->form->getInput('title'); ?></li>
					
									<li <?php if(!$this->helper->check_display('item_props_alias')){echo $display_none;} ?>>
									<?php echo $this->form->getLabel('alias'); ?>
									<?php echo $this->form->getInput('alias'); ?></li>
					
									<li <?php if(!$this->helper->check_display('item_props_category')){echo $display_none;} ?>>
									<?php echo $this->form->getLabel('catid'); ?>
									<?php echo $this->form->getInput('catid'); ?></li>
					
									<li <?php if(!$this->helper->check_display('item_props_status')){echo $display_none;} ?>>
									<?php echo $this->form->getLabel('state'); ?>
									
									<?php 
									$class = '';
									$state_label = '';									
									if (!$this->canDo->get('core.edit.state')){
										$class = 'class="display_none"';
										$state = $this->item->state; 
										//switch ($state_label) {										
										switch ($state) {
										case -2:
											$state_label = JText::_('COM_PAGESANDITEMS_TRASHED');
											break;
										case 0:
											$state_label = JText::_('COM_PAGESANDITEMS_UNPUBLISHED');
											break;
										case 1:
											$state_label = JText::_('COM_PAGESANDITEMS_PUBLISHED');
											break;	
										case 2:
											$state_label = JText::_('COM_PAGESANDITEMS_ARCHIVED');
											break;
										}									
									}									
									?>
									<span <?php echo $class; ?>>
										<?php
										echo $this->form->getInput('state');										
										?>
									</span>	
									<span>
										<?php echo $state_label; ?>
									</span>								
									</li>
									
									<li <?php if(!$this->helper->check_display('item_props_access')){echo $display_none;} ?>>
									<?php echo $this->form->getLabel('access'); ?>
									<?php echo $this->form->getInput('access'); ?></li>
									
									<?php 
									if(!$frontend){
										if ($this->canDo->get('core.admin')): ?>
											<li <?php if(!$this->helper->check_display('item_props_permissions')){echo $display_none;} ?>>
											<span class="faux-label"><?php echo JText::_('JGLOBAL_ACTION_PERMISSIONS_LABEL'); ?></span>
												<div class="button2-left"><div class="blank">
													<button type="button" onclick="document.location.href='#access-rules';">
														<?php echo JText::_('JGLOBAL_PERMISSIONS_ANCHOR'); ?>
													</button>
												</div></div>
											</li>
										<?php 
										endif;
									}
									 ?>

									<li <?php if(!$this->helper->check_display('item_props_featured')){echo $display_none;} ?>>
									<?php echo $this->form->getLabel('featured'); ?>
									<?php echo $this->form->getInput('featured'); ?></li>

									<li <?php if(!$this->helper->check_display('item_props_language')){echo $display_none;} ?>>
									<?php echo $this->form->getLabel('language'); ?>
									<?php echo $this->form->getInput('language'); ?></li>
					
								
									<li <?php if(!$item_id || !$this->helper->check_display('item_props_id')  || $frontend){echo $display_none;} ?>><?php echo $this->form->getLabel('id'); ?>
									<?php echo $this->form->getInput('id'); ?></li>
									
									<?php
									
									
									
									?>
								</ul>
								
								
								
							<?php
							
							//CHANGE ms: 02.05.2011
							//echo '<div class="clr"></div> -->';
							/*
							eventuall not the best way to display
							with fieldset so remove the </fieldset> <fieldset> and label
							*/
							echo '</fieldset>';
								//if ($languageItemtypeHtml->text != '')
								//{
									//ms: here we want put the language/joomfish content?
									//and other
								//	echo $languageItemtypeHtml->text;
								//}
								if ($managerItemtypeItemEdit->text != '')
								{
									//ms: here we want put the manager content?
									echo $managerItemtypeItemEdit->text;
								}
								
							echo '<fieldset class="adminform" id="pi_content_pane">';
							
							//ms: set in custom_itemtype class pane-toggler-down
							$paneSlider = '';
							if(strpos($item_type, 'ustom_'))
							{
								$paneSlider = 'pane-toggler-down';
							}
							//COM_CONTENT_FIELD_ARTICLETEXT_LABEL
							echo '<legend id="toggle_content" class="hasTip '.$paneSlider.'" title="'.JText::_($this->form->getFieldAttribute('articletext','label')).'::'.JText::_($this->form->getFieldAttribute('articletext','description')).'">'.JText::_($this->form->getFieldAttribute('articletext','label'));
							if(strpos($item_type, 'ustom_'))
							{
								echo ' ['.JText::_('COM_PAGESANDITEMS_MANAGE_EXTENSIONS_TIP_ITEMTYPE1').': '.JText::_('COM_PAGESANDITEMS_CUSTOMITEMTYPE').']';
								//add an slider?
								echo '<a onclick="javascript:toggelContent();"><span></span></a>';
								$html = '<script language="JavaScript"  type="text/javascript">';
								$html .= "<!--\n";
								$html .= "function toggelContent() {\n";
								$html .= "	if(document.id('toggle_content').hasClass('pane-toggler-down')){\n";
								$html .= "	document.id('toggle_content').addClass('pane-toggler')\n";
								$html .= "	document.id('toggle_content').removeClass('pane-toggler-down')\n";
								$html .= "	document.id('target_content').setProperty('style','display:none;')\n";
								//
								$html .= "	}\n";
								$html .= "	else{\n";
								$html .= "	document.id('toggle_content').addClass('pane-toggler-down')\n";
								$html .= "	document.id('toggle_content').removeClass('pane-toggler')\n";
								$html .= "	document.id('target_content').setProperty('style','display:block;')\n";
								$html .= "	}\n";
								$html .= "}\n";
								$html .= "</script>\n";
								echo $html;
							}
							echo '</legend>';
							//echo $this->form->getLabel('articletext'); //the orginal without fieldset
							//echo '<div class="clr"></div>';
							//END CHANGE ms:
							
								?>
								
								<?php 
								$results = $dispatcher->trigger('onItemtypeDisplay_item_edit', array(&$itemtypeHtml,$item_type,$item_id,$text,$itemIntroText,$itemFullText));
								
					
								if($item_type!='text'){
									//any other itemtype
									echo '<div id="target_content">';
									echo $itemtypeHtml->text;
									echo '</div>';
								}
								
								echo '<div';
								if($item_type!='text' || !$this->helper->check_display('item_props_articletext')){
									//if itemtype is not 'text',, then still put the normal fields on the page
									//but hidden, so the value gets parsed to the com_content save function
									echo ' style="display: none;"';
									//ms: only the textarea will output
									$this->form->setFieldAttribute('articletext','type','textarea');
								}
								echo '>';
								echo $this->form->getInput('articletext');
								echo '</div>';
									
								echo '<div class="clr"></div>';
								
								?>
							</fieldset>
						</div>
						<div class="width-30 fltrt">						
						<!-- <div class="width-40 fltrt"> -->
						
							<?php
							//mootools script to hide sliders as set in PI config
							$panels_to_hide = array();
							if(!$this->helper->check_display('item_props_pioptions')){
								$panels_to_hide[] = 'pi-item-options';
							}
							if(!$this->helper->check_display('item_props_metadataoptions')){
								$panels_to_hide[] = 'meta-options';
							}
							if(!$this->helper->check_display('item_props_articleoptions')){
								$panels_to_hide[] = 'basic-options';
							}
							if(!$this->helper->check_display('item_props_publishingoptions')){
								$panels_to_hide[] = 'publishing-details';
							}
							if(count($panels_to_hide)){
								echo '<script>'."\n";
								echo 'var panels_array = new Array(';
								$first = 1;
								foreach($panels_to_hide as $panel_to_hide){
									if(!$first){
										echo ',';									
									}else{
										$first = 0;
									}
									echo '"';
									echo $panel_to_hide;
									echo '"';								
								}
								echo ');'."\n";
								echo 'window.addEvent(\'domready\', function() {'."\n";
									echo 'for (i = 0; i < panels_array.length; i++){'."\n";
										echo 'var myElement = document.id(panels_array[i]);'."\n";
										echo 'var parent = myElement.getParent();'."\n";
										echo 'parent.style.display = \'none\';'."\n";
									echo '}'."\n";
								echo '});'."\n";
								
								echo '</script>'."\n";
							}
							
							?>
						
						
							<?php echo JHtml::_('sliders.start','content-sliders-'.$this->item->id, array('useCookie'=>1)); ?>
							
								
					
								<?php echo JHtml::_('sliders.panel',JText::_('COM_CONTENT_FIELDSET_PUBLISHING'), 'publishing-details'); ?>
								<fieldset class="panelform">
									<ul class="adminformlist" <?php if(!$this->helper->check_display('item_props_publishingoptions')){echo $display_none;} ?>>
										<li <?php if(!$this->helper->check_display('item_props_createdby') || $frontend){echo $display_none;} ?>>
										<?php echo $this->form->getLabel('created_by'); ?>
										<?php echo $this->form->getInput('created_by'); ?></li>
					
										<li <?php if(!$this->helper->check_display('item_props_createdbyalias') || $frontend){echo $display_none;} ?>>
										<?php echo $this->form->getLabel('created_by_alias'); ?>
										<?php echo $this->form->getInput('created_by_alias'); ?></li>
					
										<li <?php if(!$this->helper->check_display('item_props_createddate')){echo $display_none;} ?>>
										<?php echo $this->form->getLabel('created'); ?>
										<?php 
										if(!$frontend){
											$this->form->setFieldAttribute('created','type','calendartime'); 
										}
										?>
										<?php echo $this->form->getInput('created'); ?></li>
					
										<li <?php if(!$this->helper->check_display('item_props_start')){echo $display_none;} ?>>
										<?php echo $this->form->getLabel('publish_up'); ?>
										<?php 
										if(!$frontend){
											$this->form->setFieldAttribute('publish_up','type','calendartime'); 
										}
										echo $this->form->getInput('publish_up'); 
										?></li>
					
										<li <?php if(!$this->helper->check_display('item_props_finish')){echo $display_none;} ?>>
										<?php echo $this->form->getLabel('publish_down'); ?>
										<?php 
										if(!$frontend){
											$this->form->setFieldAttribute('publish_down','type','calendartime');
										}
										?>
										<?php echo $this->form->getInput('publish_down'); ?></li>
					
										<?php if ($this->item->modified_by) : ?>
											<li <?php if(!$this->helper->check_display('item_props_modified_by') || $frontend){echo $display_none;} ?>>
											<?php echo $this->form->getLabel('modified_by'); ?>
											<?php echo $this->form->getInput('modified_by'); ?></li>
					
											<li <?php if(!$this->helper->check_display('item_props_modified') || $frontend){echo $display_none;} ?>>
											<?php echo $this->form->getLabel('modified'); ?>
											<?php echo $this->form->getInput('modified'); ?></li>
										<?php endif; ?>
					
										<?php if ($this->item->version) : ?>
											<li <?php if(!$this->helper->check_display('item_props_revision') || $frontend){echo $display_none;} ?>>
											<?php echo $this->form->getLabel('version'); ?>
											<?php echo $this->form->getInput('version'); ?></li>
										<?php endif; ?>
					
										<?php if ($this->item->hits) : ?>
											<li <?php if(!$this->helper->check_display('item_props_hits') || $frontend){echo $display_none;} ?>>
											<?php echo $this->form->getLabel('hits'); ?>
											<?php echo $this->form->getInput('hits'); ?></li>
										<?php endif; ?>
									</ul>
								</fieldset>
								
								
								
								<?php $fieldSets = $this->form->getFieldsets('attribs');?>
								<?php foreach ($fieldSets as $name => $fieldSet) :?>
									<?php echo JHtml::_('sliders.panel',JText::_($fieldSet->label), $name.'-options');?>
									<?php if (isset($fieldSet->description) && trim($fieldSet->description)) :?>
										<p class="tip"><?php echo $this->escape(JText::_($fieldSet->description));?></p>
									<?php endif;?>
									<fieldset class="panelform">
										<ul class="adminformlist">
										<?php 										
										foreach ($this->form->getFieldset($name) as $field) : ?>
											<?php 
											$temp = $field->name;
											$temp = str_replace('jform[attribs][', '', $temp);
											$temp = str_replace(']', '', $temp);
											$field_name = 'item_props_'.$temp;																			
											?>
											<li <?php 
											if($field_name!='item_props_spacer2'){
												if(!$this->helper->check_display($field_name)){
													echo $display_none;
												} 
											}else{
												//hide line
												echo $display_none;											
											}
											?>>
											<?php echo $field->label; ?><?php echo $field->input; ?></li>
										<?php endforeach; 
										
										?>
										</ul>
									</fieldset>
								<?php endforeach; ?>
								
					
								
								
								<?php echo JHtml::_('sliders.panel',JText::_('JGLOBAL_FIELDSET_METADATA_OPTIONS'), 'meta-options'); ?>
								<fieldset class="panelform" <?php if(!$this->helper->check_display('item_props_metadataoptions')){echo $display_none;} ?>>
									<div <?php if(!$this->helper->check_display('item_props_desc')){echo $display_none;} ?>>
									<?php echo $this->form->getLabel('metadesc'); ?>
									<?php echo $this->form->getInput('metadesc'); ?>
									</div>
									
									<div <?php if(!$this->helper->check_display('item_props_keywords')){echo $display_none;} ?>>
									<?php echo $this->form->getLabel('metakey'); ?>
									<?php echo $this->form->getInput('metakey'); ?>
									</div>
									
									<?php foreach($this->form->getGroup('metadata') as $field): ?>
										<?php if ($field->hidden): ?>
											<?php echo $field->input; ?>
										<?php else: ?>
											<?php 
											$temp = $field->name;
											$temp = str_replace('jform[metadata][', '', $temp);
											$temp = str_replace(']', '', $temp);
											$field_name = 'item_props_'.$temp;																			
											?>
											<div <?php if(!$this->helper->check_display($field_name)){echo $display_none;} ?>>
											<?php echo $field->label; ?>
											<?php echo $field->input; ?>
											</div>
										<?php endif; ?>
									<?php endforeach; ?>
								</fieldset>
								
								
								
								
								<?php echo JHtml::_('sliders.panel',JText::_('COM_PAGESANDITEMS_ITEM_OPTIONS'), 'pi-item-options'); ?>
								<fieldset class="panelform">
								<!-- <div> -->
								<ul class="adminformlist">
									<?php if($item_type!='other_item'){ 
									echo '<li ';
									if(!$this->helper->check_display('item_props_instance')){
										echo $display_none;
									}
									echo '>';
									echo '<label>';
									echo JText::_('COM_PAGESANDITEMS_INSTANCES_OF_THIS_ITEM'); //.':<br />'; 
									echo '</label>';
									//get all instances of this item
									$this->model->db->setQuery( "SELECT c.catid, c.sectionid, o.item_id"
									. "\nFROM #__content AS c"		
									. "\nLEFT JOIN #__pi_item_other_index AS o"
									. "\nON c.id=o.item_id"
									. "\nWHERE other_item_id='$item_id'"
									. "\nAND (c.state='0' OR c.state='1')"
									. "\nORDER BY c.ordering ASC"
									);
									$instances_of_item = $this->model->db->loadObjectList();
									
									//get sections
									$this->model->db->setQuery("SELECT id, title FROM #__sections");
									$all_sections_db = $this->model->db->loadObjectList();
									
									//get categories
									$this->model->db->setQuery("SELECT id, title FROM #__categories");
									$all_categories_db = $this->model->db->loadObjectList();
									//echo '<fieldset class="radio">';
									echo '<fieldset>';
									if(count($instances_of_item)){
										echo '<ul>';
										foreach($instances_of_item as $instance_of_item)
										{
											//find the page_id
											$menuitems = $this->model->getMenuitems();
											foreach($menuitems as $menu_item_page){
												$temp_cat_id = 0;
												//if category blog
												if((strstr($menu_item_page->link, 'index.php?option=com_content&view=category&layout=blog') && $menu_item_page->type!='url' && $menu_item_page->type=='component') ||
												($menu_item_page->type=='content_category_blog')
												){
													//get the category id of each menu item
													$pos_cat_id = strpos($menu_item_page->link,'id=');
													$temp_cat_id = substr($menu_item_page->link, ($pos_cat_id+3), strlen($menu_item_page->link));
													if($instance_of_item->catid==$temp_cat_id){
														$original_page_id = $menu_item_page->id;
														break;
													}
												}
											}
											if($frontend){
												echo '<li><a href="index.php?option=com_pagesanditems&view=item&sub_task=edit&pageId='.$original_page_id.'&item_id='.$instance_of_item->item_id.'">';
											}else{
												echo '<li><a href="index.php?option=com_pagesanditems&view=item&sub_task=edit&pageId='.$original_page_id.'&itemId='.$instance_of_item->item_id.'">';
											}									
											
											foreach($all_categories_db as $category_row){
												if($category_row->id==$instance_of_item->catid){
													echo $category_row->title.'';
													break;
												}
											}
											echo '</a></li>';
										}
										echo '</ul>';
									}else{
										echo JText::_('COM_PAGESANDITEMS_NO_INSTANCES');
									}
									
									echo '<br />';
									/*
									make as button
									*/
									$link = 'index.php?option=com_pagesanditems&view=';
									if($frontend){
										$link .='item&type=content_blog_category&sub_task=new&pageId=0&item_type=other_item';
									}else{
										$link .='instance_select';
									}
									$link .='&other_item_id='.$item_id;
									echo '<div class="button2-left">';
										echo '<div class="blank">';
											echo '<a href="'.$link.'">';
												echo JText::_('COM_PAGESANDITEMS_CREATE_INSTANCE');
											echo '</a>';
										echo '</div>';
									echo '</div>';
									/*
									echo '<a href="index.php?option=com_pagesanditems&view=';
									if($frontend){
										echo 'item&type=content_blog_category&sub_task=new&pageId=0&item_type=other_item';
									}else{
										echo 'instance_select';
									}
									echo '&other_item_id='.$item_id.'">'.JText::_('COM_PAGESANDITEMS_CREATE_INSTANCE').'</a>';
									*/
									echo '</fieldset>';
									echo '</li>';
												
								}//end if not item instance
								?>								
								<li <?php if(!$this->helper->check_display('item_props_pishowtitle')){echo $display_none;} ?>>
								<label>
								<?php echo JText::_('COM_PAGESANDITEMS_SHOW_TITLE'); ?>
								</label>
									<fieldset class="radio">
									<input type="checkbox" name="show_title_item" value="1" <?php if($show_title_item){ echo "checked=\"checked\""; }?> />
									</fieldset>
								
								</li>
								</ul>
								
								</fieldset>
								
							<?php echo JHtml::_('sliders.end'); ?>
						</div>
						
						<div class="clr"></div>
						
						<?php
						//submit buttons only at frontend
						if($frontend)
						{
							echo '<div class="paddingList right_align">';
							$button = PagesAndItemsHelper::getButtonMaker();
							$button->imagePath = $this->model->dirIcons;
							$button->buttonType = 'input';
							$button->text = JText::_('COM_PAGESANDITEMS_SAVE');
							//$button->alt = 'alt JText::_('COM_PAGESANDITEMS_CONVERT_TO_PI_ITEM')(s)';
							$button->onclick = 'submitbutton2(\'item.item_save\')';
							$button->imageName = 'base/icon-16-disk.png';
							echo $button->makeButton();
							
							$button = PagesAndItemsHelper::getButtonMaker();
							$button->imagePath = $this->model->dirIcons;
							$button->buttonType = 'input';
							$button->text = JText::_('COM_PAGESANDITEMS_CANCEL');
							//$button->alt = 'alt JText::_('COM_PAGESANDITEMS_CONVERT_TO_PI_ITEM')(s)';
							$button->onclick = 'history.back();';
							$button->imageName = 'base/icon-16-cancel.png';
							echo $button->makeButton();
							
							//echo '<input type="button" value="'.JText::_('COM_PAGESANDITEMS_SAVE').'" onclick="submitbutton2(\'item_save\')" />&nbsp;&nbsp;&nbsp;';
							//echo '<input type="button" value="'.JText::_('COM_PAGESANDITEMS_CANCEL').'" onclick="history.back();" />';
							echo '</div>';
						}
						elseif($show_tree)
						{
							if($this->model->joomlaVersion < '1.6')
							{
							echo '<table class="toolbar"><tr>';
							echo '<td class="button" id="toolbar-save">';
							echo '<a href="#" onclick="javascript: submitbutton(\'item.item_save\')" class="toolbar">';
							echo '<span class="icon-32-save" title="'.JText::_('COM_PAGESANDITEMS_SAVE').'"></span>'.JText::_('COM_PAGESANDITEMS_SAVE').'</a>';
							echo '</td>';
							echo '<td class="button" id="toolbar-apply">';
							echo '<a href="#" onclick="javascript: submitbutton(\'item.item_apply\')" class="toolbar">';
							echo '<span class="icon-32-apply" title="'.JText::_('COM_PAGESANDITEMS_APPLY').'"></span>'.JText::_('COM_PAGESANDITEMS_APPLY').'</a>';
							echo '</td>';
							echo '<td class="button" id="toolbar-cancel">';
							echo '<a href="#" onclick="javascript: submitbutton(\'item.cancel\')" class="toolbar">';
							echo '<span class="icon-32-cancel" title="'.JText::_('COM_PAGESANDITEMS_CANCEL').'"></span>'.JText::_('COM_PAGESANDITEMS_CANCEL').'</a></td></tr></table>';
							echo '</td></tr></table>';
							}
							else
							{
							echo '<div class="toolbar-list" style="float:left;">';
								echo '<ul>';
									echo '<li class="button" id="toolbar-save">';
										echo '<a href="#" onclick="javascript: Joomla.submitbutton(\'item.item_save\')" class="toolbar">';
											echo '<span class="icon-32-save" title="'.JText::_('COM_PAGESANDITEMS_SAVE').'">';
											echo '</span>';
											echo JText::_('COM_PAGESANDITEMS_SAVE');
										echo '</a>';
									echo '</li>';
				
									echo '<li class="button" id="toolbar-apply">';
										echo '<a href="#" onclick="javascript: Joomla.submitbutton(\'item.item_apply\')" class="toolbar">';
											echo '<span class="icon-32-apply" title="'.JText::_('COM_PAGESANDITEMS_APPLY').'">';
											echo '</span>';
											echo JText::_('COM_PAGESANDITEMS_APPLY');
										echo '</a>';
									echo '</li>';
									echo '<li class="button" id="toolbar-cancel">';
										echo '<a href="#" onclick="javascript: Joomla.submitbutton(\'item.cancel\')" class="toolbar">';
											echo '<span class="icon-32-cancel" title="'.JText::_('COM_PAGESANDITEMS_CANCEL').'">';
											echo '</span>';
											echo JText::_('COM_PAGESANDITEMS_CANCEL');
										echo '</a>';
									echo '</li>';
								echo '</ul>';
							echo '</div>';
							}
						}
						
						if(!$frontend){
						?>
					
						<div class="clr"></div>
						<?php if ($this->canDo->get('core.admin')): ?>
							<div class="width-100 fltlft" <?php if(!$this->helper->check_display('item_props_permissions')){echo $display_none;} ?>>
								<?php echo JHtml::_('sliders.start','permissions-sliders-'.$this->item->id, array('useCookie'=>1)); ?>
					
									<?php echo JHtml::_('sliders.panel',JText::_('COM_CONTENT_FIELDSET_RULES'), 'access-rules'); ?>
					
									<fieldset class="panelform">
										<?php echo $this->form->getLabel('rules'); ?>
										<?php echo $this->form->getInput('rules'); ?>
									</fieldset>
					
								<?php echo JHtml::_('sliders.end'); ?>
							</div>
						<?php endif; 
						}//end backend
						?>					
					<?php
					
					/*
					if(!$frontend)
					{
						echo '</td><td valign="top">';
					}
					else
					{
						echo '</td></tr><tr><td>';
					}
					*/
					
					?>
					
					
					
				
			</td>
			</tr>
		</table> </td>
		</tr>
		</table>
		
		
		
		
		
		
	</form>
	
<?php

if(!$frontend)
{
	echo '</td></tr></table>';
}





require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'views'.DS.'default'.DS.'tmpl'.DS.'default_footer.php');
// 		//the stylesheets must load into document not header so no other stylesheets can  override it
//		echo "<link href=\"components/com_pagesanditems/css/pagesanditems.css\" rel=\"stylesheet\" type=\"text/css\" />\n";
//		echo "<link href=\"components/com_pagesanditems/css/dtree.css\" rel=\"stylesheet\" type=\"text/css\" />\n";
//$this->model->display_footer();
?>