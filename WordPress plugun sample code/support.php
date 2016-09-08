<?php
/* 
Plugin Name: Support
Plugin URI: 
Description: Message
Author: Ereborapps Dev Team
Version: 1.0
Author URI: http://ereborapps.com/
License: GPLv2 or later
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

if(!defined('ABSPATH'))exit; //Exit if accessed directly
DEFINE('SP_PLUGIN_URL',WP_CONTENT_URL.'/plugins/support');

class support {
	
	function __construct(){
		add_action( 'admin_menu',array($this,'admin_menu'));
		add_action( 'admin_menu',array($this,'remove_admin_menus'));
		add_action( 'admin_bar_menu',array($this,'remove_wp_logo'),999);
		add_action( 'admin_init',array($this,'disallowed_admin_pages'));
		add_action( 'admin_head',array($this,'save_info'));
		add_action( 'init',array($this,'add_scripts'));
		//add_action( 'wp_ajax_save_text', array($this,'save_text_callback') );
		//add_action( 'wp_ajax_save_image', array($this,'save_image_callback') );
		add_action( 'wp_ajax_save_order', array($this,'save_order_callback') );
		add_action( 'wp_ajax_delete_message', array($this,'delete_message_callback') );
		add_action( 'wp_ajax_delete_child_message', array($this,'delete_child_message_callback') );		
		// Add Embede code in DB
		add_action( 'wp_ajax_save_embeded_code', array($this,'save_embeded_code_callback'));
		
	}

	/* Add menu in admin area */
	function admin_menu() {
		add_menu_page( 'Support', 'My Stackbot', 'manage_options', __FILE__, array(&$this, 'show_pages'),' ', 71);
		//add_submenu_page( __FILE__, 'My Stackbot', 'My Stackbot','manage_options', 'tracking-listing',array(&$this, 'show_tracking_listing'), 'dashicons-admin-post');
		add_submenu_page( __FILE__, 'Analytics', 'Analytics','manage_options', 'analytics',array(&$this, 'add_analytics'), 'dashicons-admin-post');
		add_submenu_page( __FILE__, 'Settings', 'Settings','manage_options', 'setting',array(&$this, 'show_general'), '');
	}
	
	function show_general(){
		wp_redirect(admin_url('options-general.php', 'http'), 301);
	}
	
	function show_pages(){
		if(isset($_GET['msg_id']) && !empty($_GET['msg_id'])) {
		$msg_id = $_GET['msg_id'];
		$this->show_edit_page($msg_id);
		}
		else if(isset($_POST['add_new_post']) && !empty($_POST['add_new_post'])){
			$this->add_post();
		}
		else if(isset($_POST['edit_logo']) && !empty($_POST['edit_logo'])){
			$this->edit_logo_area();
		}
		else{
			$this->show_listing_page();
		}
	}
	
	function show_listing_page(){
		$messages = $this->get_messages();
		add_thickbox();// For Popup 	?>
		
		    <form method="post">
			<input type="submit" name="add_new_post" class="button button-primary" value=" Add new post" style="float:right;margin:18px;width:155px">
			</form>
			<form method="post">
			<input type="submit" name="edit_logo" class="button button-primary" value=" Edit Logo" style="float:right;margin:18px;width:155px">
			</form>
			<a href="#TB_inline?width=600&height=350&inlineId=embed-popup" type="button" class="thickbox button button-primary" style="float:right;margin:18px;width:155px"> Embed Stackbot </a>
			
			<table class="wp-list-table widefat fixed striped pages">
			<thead>
			<tr>
			<th  scope="col" id="title" class="manage-column column-title "><a><span>Title</span></a></th>
			<th scope="col" id="author" class="manage-column column-author">Order</th>

			<th scope="col" id="date" class="manage-column column-date sortable asc">Action</th>
			</tr>
			</thead>
			<?php 

			if( $messages && is_array($messages) && count($messages)>0 ) {
			$k=1;
			foreach($messages as $item){
				if (($k % 2) == 1)
				{ $class="odd_class" ;}
				if (($k % 2) == 0)
				{ $class="even_class" ;} ?>
				<tbody id="the-list">
				<tr id="post-2" class="iedit author-self level-0 post-2 type-page status-publish hentry <?php echo $class ?>">
				<td class="title column-title has-row-actions column-primary page-title" data-colname="Title">
				<?php echo $text =  convert_smilies(substr($item->intro_text,0,170)); ?>
				</td>
				<td class="author column-author" data-colname="Author">
				<select name="save_order" onchange="change_order('<?php echo $item->id;?>',this.value)">
				
				<?php $total = $this->countStories();
					for($i=1;$i<=$total;$i++){
						if($i==$item->order){?>
						<option selected="selected" value='<?php echo $i ?>'><?php echo $i ?></option>
					<?php } else { ?>
						<option value='<?php echo $i ?>'><?php echo $i ?></option>
				<?php } } ?>
				
				</select>
				</td>
				<td class="date column-date" data-colname="Date">
				<a href="<?php echo $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'&amp;msg_id='.$item->id ?>">Edit</a>				
				<a msg_id="<?php echo $item->id;?>" href="#" class="delete_message">Delete</a>				
				</td>
				</tr>
				</tbody>
			<?php $k++; }} ?>
			</table>
	<!-- Popup Form -->		
	<div id="embed-popup" style="display:none;">
		<div class="embed-popup-h2">
			<?php $unique_code = uniqid();?>
			<p><img style="margin-left:180px;" src="<?php echo get_template_directory_uri() ?>/images/popup-logo.png" ></p>
			<h2>Embed your stackbot on your webpage</h2>		
			<p style="color:ButtonShadow;">Paste this javascript snippet right before your website body tag</p>			
			<input class="input-class js-copytextarea" embed_code="<?php echo $unique_code  ?>" value='<script id="bot_embed_id" embedId="<?php echo $unique_code  ?>" src="http://botux.co/chat-box/embed.js"></script>' >
			<p style="float: right; width: 425px;">	
			<input class="js-textareacopybtn" id="save_embeded_code" type="button" value=" Copy Code " class="button button-primary" style="width:160px;height:39px;">	
			</p>	
			
		</div>
	</div>		
	<!-- End Popup Form -->				
	<?php }
/**
 * Add Story
 * @params int $parent_id
 * @return void
 * 
 * */	
	
function add_story($parent_id){
	//echo "<pre/>";
	//print_r($_POST['extra']);die;
	$r= 0;
	$new = array();
	foreach($_POST['extra'] as $key=>$text){
		foreach($_POST['extra'][$key] as $key2=>$text1){
			$text = array();
				if($key2=='button'){
					$text [0]['button']  = stripslashes($text1);
					if(!empty($_POST['extra'][$key]['text'])){
						foreach($_POST['extra'][$key]['text'] as $key3=> $text2){
							$key3 = $key3 +1;
							if($text2=='image'){
								if(isset($_FILES['extra']['name'][$key][0]) && !empty($_FILES['extra']['name'][$key][0])){
									foreach($_FILES['extra']['name'][$key] as $key4=> $image){
										if($image){
											$temp_name = $_FILES['extra']['tmp_name'][$key][$key4];
											$size	 = $_FILES['extra']['size'][$key][$key4];
											$text[$key3] ['image']= $this->update_image($image,$temp_name,$size);
										}
									} 
								}
							}else{
								$text[$key3]['text'] = stripslashes($text2);
								$text [$key3]['url']  = !empty($_POST['extra'][$key]['url'][$key3]) ? stripslashes($_POST['extra'][$key]['url'][$key3]):'';								
							}
						}

						$new[$r] = $text;
					}
				}
			}
		$r++;
	} 
	
	$newa = json_encode($new) ;
	// Add json in database
	$this->add_child_message($newa,$parent_id);
		
}
	
/**
 * Add Scripts in head
 * 
 * */
function add_scripts() {
	wp_enqueue_script( "jquery-1.10.2", SP_PLUGIN_URL.'/js/jquery-1.10.2.js' ); 
	wp_enqueue_script( "script", SP_PLUGIN_URL.'/js/script.js');
	wp_enqueue_script( "validate", SP_PLUGIN_URL.'/js/jquery.validate.js');
	wp_localize_script('script', 'WPURL', array( 'siteurl' => get_option('siteurl') )); // add URL variable for jquery
	wp_localize_script('script', 'PLUGIN', array( 'url' =>site_url('wp-admin/admin.php?page=support/support.php' ))); // add URL variable for jquery

	wp_enqueue_style('style',SP_PLUGIN_URL.'/css/style.css'); 
}

	
}
new support();
 ?>
