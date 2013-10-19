<?php
/*
Plugin Name: personal-authors-category
Plugin URI: http://webdesignseo.ru/personal-authors-category
Description: При регистрации нового автора плагин автоматически создаёт для него рубрики в родительских рубриках, указанных администратором. Публиковать записи автор сможет только в своих рубриках.
Author: AlexeyKnyazev
Version: 1.0
Author URI: http://webdesignseo.ru
*/

add_action("init", "personal_authors_category_init");

function personal_authors_category_init() {
   load_plugin_textdomain( 'personal-authors-category', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

function rus2translit($string) {
    $converter = array(
        'а' => 'a',   'б' => 'b',   'в' => 'v',
        'г' => 'g',   'д' => 'd',   'е' => 'e',
        'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
        'и' => 'i',   'й' => 'y',   'к' => 'k',
        'л' => 'l',   'м' => 'm',   'н' => 'n',
        'о' => 'o',   'п' => 'p',   'р' => 'r',
        'с' => 's',   'т' => 't',   'у' => 'u',
        'ф' => 'f',   'х' => 'h',   'ц' => 'c',
        'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
        'ь' => '',  'ы' => 'y',   'ъ' => '',
        'э' => 'e',   'ю' => 'yu',  'я' => 'ya',
        'А' => 'A',   'Б' => 'B',   'В' => 'V',
        'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
        'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',
        'И' => 'I',   'Й' => 'Y',   'К' => 'K',
        'Л' => 'L',   'М' => 'M',   'Н' => 'N',
        'О' => 'O',   'П' => 'P',   'Р' => 'R',
        'С' => 'S',   'Т' => 'T',   'У' => 'U',
        'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
        'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
        'Ь' => '',  'Ы' => 'Y',   'Ъ' => '',
        'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',
    );
    return strtr($string, $converter);
}
function str2url($str) {
  $str = rus2translit($str);
  $str = strtolower($str);
  $str = preg_replace('~[^-a-z0-9_]+~u', '-', $str);
  $str = trim($str, "-");
  return $str;
}

// Creates a category when registering a new user
add_action('user_register', 'authorcatreg_action');
function authorcatreg_action($uid) {

  require_once( ABSPATH . '/wp-admin/includes/taxonomy.php');

  $sanitized_user_login = sanitize_user(get_userdata($uid)->data->user_login);

  $catmass = array();
  foreach (unserialize(get_option('personal_authors_category_catname')) as $key => $value) {
    $catname = get_cat_name( $value );
    $catname = str_replace("{catname}", $catname, get_option('personal_authors_category_format'));
    $catname = str_replace("{login}", $sanitized_user_login, $catname);
    $nickcat = str2url($catname);
    $my_cat = array(
      'cat_name'             => $catname,
      'category_description' => $catname,
      'category_nicename'    => $nickcat,
      'category_parent'      => $value,
    );
    $catmass[] = wp_insert_category($my_cat);
  }
  $meta_value = serialize($catmass);
  add_user_meta( $uid, "personal_authors_category_meta", $meta_value);
}

 // Adds a new item to the menu of categories
function personal_authors_category_add_admin_pages()
{
    add_posts_page(__('personal authors category', 'personal-authors-category'), __('personal authors category', 'personal-authors-category'), 8, 'catreg', 'personal_authors_category_options_page');
}

// Format in which category will be created for the author
function personal_authors_category_register_new_user ()
{
  $catmass= array();
  foreach (unserialize(get_option('personal_authors_category_catname')) as $key => $value) {
      $catname = get_cat_name( $value );
      $catname= str_replace("{catname}", $catname, get_option('personal_authors_category_format'));
      $catname= str_replace("{login}", $sanitized_user_login, $catname);
      $nickcat= str2url($catname);
      $my_cat = array('cat_name' => $catname, 'category_description' => $catname,
      'category_nicename' => $nickcat,
      'category_parent' => $value);
      $catmass[]=wp_insert_category($my_cat);
  }
  $meta_value=serialize($catmass);
  add_user_meta( $user_id, "personal_authors_category_meta", $meta_value);
  add_action('register_post','personal_authors_category_register_new_user');

// Generates settings page
function personal_authors_category_options_page()
{
	if ($_GET['up']==1)
  {
      require_once( ABSPATH . '/wp-admin/includes/taxonomy.php');
      require_once( ABSPATH . '/wp-includes/user.php');
    $mess="";
    $need=get_option('personal_authors_category_catname');
    if (!empty($need))
    {
      global $wpdb;
  		$users = $wpdb->get_results("SELECT user_login,ID FROM $wpdb->users", ARRAY_A);
  		foreach($users as $value1)
  		{
        $catmass= array();
        foreach (unserialize(get_option('personal_authors_category_catname')) as $key => $value) {
            $catname = get_cat_name( $value );
            $catname= str_replace("{catname}", $catname, get_option('personal_authors_category_format'));
            $catname= str_replace("{login}", $value1[user_login], $catname);
            $pr= get_cat_ID( $catname );
            if ($pr == '0') {
              $nickcat= str2url($catname);
              $my_cat = array('cat_name' => $catname, 'category_description' => $catname,
              'category_nicename' => $nickcat,
              'category_parent' => $value);
              $catmass[]=wp_insert_category($my_cat);
              $mess.= "".__('user', 'personal-authors-category')."".$value1[user_login]."".__('with id', 'personal-authors-category')."".$value1[ID]."".__('added the category', 'personal-authors-category')."".$catname."<br />";
            }
            else $catmass[]=$pr;
        }
        if (sizeof($catmass) != 0)
        {
          $meta_value=serialize($catmass);
          delete_user_meta($value1[ID], "personal_authors_category_meta");
          add_user_meta( $value1[ID], "personal_authors_category_meta", $meta_value, true);
        }
      }
    }
    echo $mess;
  }
  ?>
  <h2><?php _e('settings', 'personal-authors-category'); ?></h2>
  <?php
  add_option('personal_authors_category_format', '{catname} {login}');
  add_option('personal_authors_category_catname', '1');
  global $wpdb;
if (!empty($_POST['personal_authors_category_cats'])) {
  update_option('personal_authors_category_format', $_POST['personal_authors_category_format']);
  update_option('personal_authors_category_catname', serialize($_POST['personal_authors_category_cats']));
}

// Form settings
	echo
	"
		<form name='personal_authors_category_base_setup' method='post' action='".$_SERVER['PHP_SELF']."?page=catreg&amp;updated=true'>
	";
	echo	"
	<style type='text/css' media='screen'>
	.catcheckbox
    {
	  float: left; height: 30px;
	}
	.catlabel {
      height: 30px;
	  float: right;
	  margin: 0 8px;
	}
	.addoldusers {
	margin: 15px;
	padding: 7px;
	border: 2px #fc3 solid;
	text-transform: none;
	-webkit-border-radius: 12px;
	-moz-border-radius: 12px;
	border-radius: 12px;
	}
	.divcheck {
	float: left;
	clear: both;
	}
	</style>
<table>";
?>
			<tr>
				<td width='20%' valign='top' style='text-align:left;'><?php _e('category format','personal-authors-category'); ?></td>
<?php
				echo"<td><input type='text' name='personal_authors_category_format' value='".get_option('personal_authors_category_format')."'/></td>"; ?>
				<td width='50%' style='color:#666666;'><i><?php _e('category format label','personal-authors-category'); ?></i></td>
			</tr>
     <tr>

				<td width='20%' valign='top' style='text-align:left;'><?php _e('select category','personal-authors-category'); ?></td>
			<td>
<?php
           $fivesdrafts = $wpdb->get_results("SELECT term_id, name FROM $wpdb->terms where term_id IN (SELECT term_id FROM $wpdb->term_taxonomy WHERE parent=0 AND taxonomy ='category');");

          foreach ($fivesdrafts as $fivesdraft) {
               $tid = $fivesdraft->term_id;
              foreach (unserialize(get_option('personal_authors_category_catname')) as $key => $value) {
                if ($value == $tid)
                {
                  $selec='checked';
                  break;
                }
                else
                {
                    $selec=$value."-".$tid;
                }
              }
             echo "<div class='divcheck'><input type='checkbox' name='personal_authors_category_cats[]' $selec value='".$fivesdraft->term_id."'><label class='catlabel'>".$fivesdraft->name."</label></div>";
          }
?></td>
        <td width='50%' style='color:#666666;'><i><?php _e('checkbox label','personal-authors-category'); ?></i></td>
			</tr>
		</table>
    <input type='submit' value=<?php _e('save','personal-authors-category');?>>
    </form><br /><br /><b><a class="addoldusers" href="<?php echo $_SERVER['PHP_SELF'] ; ?>?page=catreg&up=1"><?php _e('add category old users','personal-authors-category');?></a></b><br /><br />

<style>
/* Admin header */
#personal-authors-category-donate {
 text-align: left;
 background-color: #f4f4f4;
 padding: 10px 10px 10px 15px;
 margin: 15px;
 border: 2px #fc3 solid;
 text-transform: none;
 -webkit-border-radius: 12px;
 -moz-border-radius: 12px;
 border-radius: 12px;
}

#personal-authors-category-donate a {
 margin-right: 15px;
}

</style>

<div id="personal-authors-category-donate">
<h3><?php _e('donate','personal-authors-category');?></h3>
<p><?php _e('donate message','personal-authors-category'); ?></p>
<h4><?php _e('paypal','personal-authors-category'); ?></h4>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="SR9FD2CNW5REW">
<input type="image" src="https://www.paypalobjects.com/ru_RU/RU/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal — более безопасный и легкий способ оплаты через Интернет!">
<img alt="" border="0" src="https://www.paypalobjects.com/ru_RU/i/scr/pixel.gif" width="1" height="1">
</form>
<h4><?php _e('webmoney','personal-authors-category'); ?></h4>
<img src="http://webdesignseo.ru/wp-content/plugins/personal-authors-category/images/blue.gif" width="88" height="31" alt="" border="0">
<script>

      jQuery(document).ready(function(){

        var options = {
          beforeSubmit: function(data) {
            jQuery("#limg").show();
          },
          success: function(data) {
            jQuery("#limg").hide();
            eval(data);
          }
        };

        jQuery("#WpwmForm-1").ajaxForm(options);
      });

      function _wmButtonSwitch(s) {
       var arr = new Array("b_classic", "b_light_rus", "b_light_eng");
       for(var i=0; i < arr.length; i++) {
         var el = document.getElementById(arr[i]);
         if(el != null) {
           el.style.display = "none";
         }
       }

       var el = document.getElementById(s);
       if(el != null) {
         el.style.display = "";
       }
      }
        var wp_webmoney_aPurses_1 = new Array("purse_1_0","purse_1_1");
        function wp_webmoney_check_currency_1(el_id) {

          for(var i=0; i<wp_webmoney_aPurses_1.length;i++) {
            var el = document.getElementById(wp_webmoney_aPurses_1[i]);
            if(el != null) {
              el.style.display = (wp_webmoney_aPurses_1[i] == el_id?"":"none");
            }
          }
        }</script>
      <div>
      <form id="WpwmForm-1" style="text-align: left; display: inline;" action="http://webdesignseo.ru/wp-content/plugins/wp-webmoney/pay.php?time=1381954669" method="post">

      <span id="donor_error_field-1" style="color: #FF0000;"></span>

      <span id="wpwm_form-1">
       <br><span id="purse_1_0" ><input type="text" name="amount[0]" size="3" id="amount-1" value=""  /> <small>RUB</small></span><span id="purse_1_1" style="display:none;"><input type="text" name="amount[1]" size="3" id="amount-1" value=""  /> <small>USD</small></span><br><input type="radio" name="purse_type" value="0" checked="true" onclick="wp_webmoney_check_currency_1('purse_1_0');"> RUB&nbsp;<input type="radio" name="purse_type" value="1"  onclick="wp_webmoney_check_currency_1('purse_1_1');"> USD&nbsp;<br><input type="button" value="Сделать пожертвование" onclick="return _wpwmHideShow('wpwm_form-1','donor_form-1');">
      </span>

      <span id="donor_form-1" style="display: none;">

      <label for="donor_name-1"><?php _e('donate name','personal-authors-category'); ?></label><br />
			<input type="text" name="donor_name" id="donor_name-1" value="" /><br />

			<label for="donor_email-1"><?php _e('donate email','personal-authors-category'); ?></label><br />
			<input type="text" name="donor_email" id="donor_email-1" value="" /><br />

			<label for="donor_site-1"><?php _e('donate web','personal-authors-category'); ?></label><br />
			<input type="text" name="donor_site" id="donor_site-1" value="" /><br />

			<label for="donor_comments-1"><?php _e('donate comment','personal-authors-category'); ?></label><br />
			<textarea name="donor_comments" id="donor_comments-1"></textarea><br />

			<input type="submit" name="wpwm_submit" value=<?php _e('send donate','personal-authors-category');?>> <img id="limg" src="http://webdesignseo.ru/wp-content/plugins/wp-webmoney/loading.gif" style="display: none;">
			<input type="hidden" name="element_id" value="1" />
      </span>
      </form>
<h4><?php _e('yandex','personal-authors-category');?></h4>
<iframe frameborder="0" allowtransparency="true" scrolling="no" src="https://money.yandex.ru/embed/donate.xml?uid=410011502946954&amp;default-sum=&amp;targets=%D0%9D%D0%B0+%D1%80%D0%B0%D0%B7%D0%B2%D0%B8%D1%82%D0%B8%D0%B5+%D0%BF%D0%BB%D0%B0%D0%B3%D0%B8%D0%BD%D0%B0+personal-authors-category&amp;target-visibility=on&amp;project-name=Personal-Authors-Category+%D0%BF%D0%BB%D0%B0%D0%B3%D0%B8%D0%BD+%D0%B4%D0%BB%D1%8F+wordpress&amp;project-site=http%3A%2F%2Fwebdesignseo.ru&amp;button-text=01&amp;comment=on&amp;hint=%D0%9F%D1%80%D0%B8+%D0%B6%D0%B5%D0%BB%D0%B0%D0%BD%D0%B8%D0%B8+%D0%BC%D0%BE%D0%B6%D0%B5%D1%82%D0%B5+%D0%B2%D0%B2%D0%B5%D1%81%D1%82%D0%B8+%D0%B7%D0%B4%D0%B5%D1%81%D1%8C+%D1%81%D0%B2%D0%BE%D0%B9+%D0%BA%D0%BE%D0%BC%D0%BC%D0%B5%D0%BD%D1%82%D0%B0%D1%80%D0%B8%D0%B9.+%D0%9E%D1%81%D1%82%D0%B0%D0%B2%D0%B8%D0%B2+%D0%B0%D0%B4%D1%80%D0%B5%D1%81+%D1%81%D0%B2%D0%BE%D0%B5%D0%B3%D0%BE+%D1%81%D0%B0%D0%B9%D1%82%D0%B0%2C+%D0%BD%D0%B0%D0%B8%D0%B1%D0%BE%D0%BB%D0%B5%D0%B5+%D1%89%D0%B5%D0%B4%D1%80%D1%8B%D0%B5+%D0%B4%D0%B0%D1%80%D0%B8%D1%82%D0%B5%D0%BB%D0%B8+%D0%BF%D0%BE%D0%BB%D1%83%D1%87%D0%B0%D1%82+%D1%80%D0%B0%D0%B7%D0%BC%D0%B5%D1%89%D0%B5%D0%BD%D0%B8%D0%B5+%D1%81%D1%81%D1%8B%D0%BB%D0%BA%D0%B8+%D0%BD%D0%B0+%D1%81%D0%B2%D0%BE%D0%B9+%D1%81%D0%B0%D0%B9%D1%82+%D0%BD%D0%B0+%D0%BE%D0%B4%D0%BD%D0%BE%D0%BC+%D0%B8%D0%B7+%D0%BD%D0%B0%D0%B8%D0%B1%D0%BE%D0%BB%D0%B5%D0%B5+%D0%BF%D0%BE%D0%B4%D1%85%D0%BE%D0%B4%D1%8F%D1%89%D0%B8%D1%85+%D0%BC%D0%BE%D0%B8%D1%85+%D0%BF%D1%80%D0%BE%D0%B5%D0%BA%D1%82%D0%BE%D0%B2.&amp;fio=on&amp;mail=on" width="450" height="233"></iframe>
<h3><?php _e('terms of use','personal-authors-category');?></h3>
<p><?php _e('terms of use message','personal-authors-category');?></p>
</div>

<?php }

function apc_category() {
  global $current_user;
  get_currentuserinfo();
  $cats = unserialize(get_user_meta($current_user->ID,'personal_authors_category_meta',true));
  foreach($cats as $cat ){
    $c = get_category($cat);
    echo '<label><input name="post_category[]" type="checkbox"'.$checked.' value="'.$c->term_id.'"> '.$c->name .'</label><br />';
}
}

// Displays a block with the personal categories user if the user is not an administrator.
if (is_admin()) {
function add_meta_box1() {
  if(!current_user_can('administrator')) {
  add_meta_box('apcatcat', __('metatitle', 'personal-authors-category'),'apc_category','post' ,'side','low');
}
}
add_action('admin_menu', 'personal_authors_category_add_admin_pages');
add_action( 'add_meta_boxes', 'add_meta_box1');
}

// Removes a list of all the categories for all users except the administrator
if (is_admin()) {
function my_remove_meta_boxes() {
 if(!current_user_can('administrator')) {
   remove_meta_box('categorydiv', 'post', 'side');
 }
}
add_action( 'admin_menu', 'my_remove_meta_boxes' );
}

?>