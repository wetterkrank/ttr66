<?php
/**
 * The admin-specific functionality of the plugin.
 */

class TTR66_Admin {
	private $plugin_name;
	private $version;

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	// Add the TTR66 dash widget
	public function add_dashboard_widget() {
		wp_add_dashboard_widget('custom_help_widget', 'TTR66 - Управление сайтом', 'custom_dash_help');
		function custom_dash_help() {
			$url = admin_url();
			echo '
					<ul>
						<li><a href="'.$url.'post-new.php">Добавить новость</a></li>
						<li><a href="'.$url.'edit.php?post_type=brand">Добавить/редактировать бренд</a><br>
							Для произвольной сортировки брендов перетащите их мышкой в списке.</li>
						<li><a href="'.$url.'edit.php?post_type=brand&page=upload_pricelist">Загрузить прайслист из 1C</a></li>
						<li><a href="'.$url.'upload.php">Загрузка и хранение картинок</a><br>
						Желательно использовать логотипы в форматах .jpg или .png, они меньше размером и быстрее грузятся.</li>
					</ul>';
		}
	}
	
	// Add the custom submenu opening the XML upload page
	public function register_custom_menu_page() {
		if ( current_user_can( 'edit_others_posts' ) ) {
			add_submenu_page(
				'edit.php?post_type=brand',
				'Загрузка прайслиста',
				'Загрузить прайслист',
				'edit_others_posts',
				'upload_pricelist',
				array(&$this, 'process_upload'),
			);
		}
	}

	// Returns a SimpleXML object or false and error message
	function parse_xml_pricelist($filename, &$message) {
		$message = false;
		if ( !file_exists($filename)) {
			$message = "Файл не найден: " . $filename . "<br>";
			return false;
		} else {
			libxml_use_internal_errors(true);
			$xml = simplexml_load_file($filename);
			if ($xml === false) {
				$message = "Не удалось прочитать XML: " . $filename . "<br>";
				return false;
				// foreach(libxml_get_errors() as $error) echo "-", $error->message, "<br>";
			} else {
				return $xml;
			}
		}
	}

	// Receives SimpleXML object, returns an array(Vendor Id => Vendor Name)
	function list_xml_vendors($xml) {
			$xml_vendors_list = array();
			foreach($xml->children() as $vendor) 
			{
				$xml_vendors_list[(string)$vendor['Id']] = (string)$vendor['Name'];
			}
			return $xml_vendors_list;
	}

	// Returns an array of (ID => brand_id1c[0...n]) for all brand posts
	public function list_website_brands() {
		$brand_ids = array();
		$args = array(
			'post_type' => 'brand',
			'posts_per_page' => -1,
		);
		$brand_posts = get_posts($args);
		foreach ($brand_posts as $brand) {
			$brand_ids[$brand->ID] = explode(",", str_ireplace(" ", "", $brand->brand_id1c));
		}
		// print_r($brand_ids);
		return $brand_ids;
	}

	// Handles the POST-ed file reception
	public function receive_file($posted_FILES, &$message) {
		$file_to_upload = $posted_FILES['import']; // field value sent by the WP import form
		$upload_overrides = array(
			'test_form' => false,
		);
		add_filter('upload_dir', array(&$this, 'override_upload_dir'));
		add_filter( 'upload_mimes', array(&$this, 'override_mime_types' ));
		$movefile = wp_handle_upload( $file_to_upload, $upload_overrides );
		remove_filter('upload_dir', array(&$this, 'override_upload_dir'));
		remove_filter( 'upload_mimes', array(&$this, 'override_mime_types' ));
		
		if (!$movefile || isset( $movefile['error'])) {
			$message =  "Что-то пошло не так:<br>" . $movefile['error'] . "<br>";
			return false;
		} else return $movefile['file'];
	}

	// Display upload pricelist page and handle the import
	// wp_import_upload_form() used; it creates nonce 'import-upload' and sends action 'save', file 'import'
	// TODO: replace with own form?
	public function process_upload() {
		if ( !current_user_can( 'edit_others_posts' ) ) die();

		echo '<div class="wrap">';
		echo '<h1 class="wp-heading-inline">' . get_admin_page_title() . '</h1><p></p>';
		
		if ( empty($_POST['action'])) {
			echo "Выберите файл XML-файл экспорта из 1C.<br>";
			wp_import_upload_form('edit.php?post_type=brand&page=upload_pricelist');
		}
		elseif ( $_POST['action'] === 'save' ) { // field sent by the form
			$nonce = $_REQUEST['_wpnonce'];
			if ( !wp_verify_nonce( $nonce, 'import-upload' ) ) {
				die ("Ошибка доступа, попробуйте обновить страницу.<br>");
			}
			// Let's receive the file
			$filename = TTR66_Admin::receive_file($_FILES, $errormessage);
			if (!$filename) {
				echo $errormessage;
			} else {
				// Let's parse XML
				echo '<p>Файл успешно загружен: ' . $filename . "</p>";
				$xml = TTR66_Admin::parse_xml_pricelist($filename, $errormessage);
				if (!$xml) {
					echo $errormessage;
				}
				else {
					// Let's do the rest
					$website_brands = TTR66_Admin::list_website_brands();
					$xml_vendors = TTR66_Admin::list_xml_vendors($xml);
					foreach ($website_brands as $brand_id => $one_or_more_id1c) {
						$table = false;
						foreach ($one_or_more_id1c as $id1c) {
							// echo $brand_id . ": " . implode($one_or_more_id1c) . "<br>";
							echo "id ".$id1c.", ".get_the_title($brand_id)." ";
							$table_content = TTR66_Admin::table_body($id1c, $xml);
							if (!$table_content) {
								echo "<b>не найден в файле</b><br>";
							} else {
								$table .= $table_content;
								echo "&lt;- ".$xml_vendors[$id1c]."<br>";
								unset($xml_vendors[$id1c]);
							}
						} // end loop (Brand's 1C ids)
						if ($table) {
							$table = TTR66_Admin::table_header() . $table . TTR66_Admin::table_footer();
							if (!TTR66_Admin::update_brand($brand_id, $table, $errormessage)) {
								echo $errormessage . "<br>";
							}
						} // end if (have table)
					} // end loop (all website Brands)
					if ($xml_vendors) {
						echo "<p>";
						echo "<b>Есть в файле, но нет на сайте</b>:<br>";
						foreach ($xml_vendors as $id => $name) {
							echo "id " . $id . " (" . $name . ")<br>";
						}
						echo "</p>";
					}
				} // end if (xml parsed)
				wp_delete_file($filename);
			} // end if (file received)
		} // end if (correct POST action)
		echo '</div> <!-- wrap -->';
	}
	
	public function table_header() {
		$thead ='<table class="ttr66-pricelist">'."\n".
				'<thead>'."\n".
				'<tr><th>ID</th><th>Название</th><th style="text-align:right">Цена, руб.</th></tr>'."\n".
				'</thead>'."\n".
				'<tbody>'."\n";
		return $thead;
	}

	public function table_footer() {
		$tfoot = '</tbody>'."\n".
				 '</table>'."\n";
		return $tfoot;
	}

	public function format_price($price) {
		// && strpos($price, ".")
		if (is_numeric($price)) {
			$price = number_format($price, 2, ".", "");
		}
		return $price;
	}

	// Returns the HTML table rows for selected Brand
	public function table_body($id1c, $xml) {
		$table = false;
		$vendor = $xml->xpath("//Vendor[@Id='" . $id1c . "']");
		if ($vendor) {
			$table .= " ";
			foreach($vendor[0]->children() as $tovar) 
			{
				$table .= "<tr>"."\n";
				$table .= "<td>".esc_html((string)$tovar['Id'])."</td>";
				$table .= "<td>".esc_html((string)$tovar['Name'])."</td>";
				$table .= '<td style="text-align:right">'.esc_html(TTR66_Admin::format_price((string)$tovar['Price']))."</td>";
				$table .= "</tr>"."\n";
			}	
		}
		return $table;
	}

	// Updates the Brand post content
	public function update_brand($target_id, $table, &$message) {
		$message = false;
		$target_post = array(
			'ID'           => $target_id,
			'post_content' => $table,
		);
		$target_id = wp_update_post($target_post, true);
		if (is_wp_error($target_id)) {
			$message .= "<b>Не удалось записать данные</b>";
			// $errors = $target_id->get_error_messages();
			// foreach ($errors as $error) echo $error . ", ";
			return false;
		} else return true;
	}
	
	// Temporarily change upload dir to upload_dir/tmp
	public function override_upload_dir($upload) {
		$upload['subdir'] = '/tmp';
		$upload['path']   = $upload['basedir'] . $upload['subdir'];
		$upload['url']    = $upload['baseurl'] . $upload['subdir'];
		return $upload;
	}
	
	// Temporarily add XML to allowed file types
	function override_mime_types( $mimes ) {
		$mimes['xml']  = 'text/xml';
		return $mimes;
	}
	
}
