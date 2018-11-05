<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

class Themify_Updater {

    /**
     * @var string $update_type Whether this is a 'plugin' update or an 'theme' update.
     */
    private $update_types = array('plugin' => 'update_plugins', 'theme' => 'update_themes');
    private $updates = array();
    private $transients = false;
    private $updater_cache = false;
    private $versions_url;
    public $api_domain = 'https://themify.me';
    private $api_actions = array('check' => 'check-license','get' => 'get-themify','res' => 'themify-res');
    private $versions_xml = false;
	private $key = false;
	private $key_error = array( 'code' => 'ok', 'message' => '', 'short' => '');
    private static $instance = null;
    private $has_premium = false;

    /**
     * Creates or returns an instance of this class.
     *
     * @return Themify_Updater class single instance.
     */
    public static function get_instance() {
        return null == self::$instance ? self::$instance = new self : self::$instance;
    }

    public function __construct() {

        if ( !is_admin() ) {
            return;
        }

        // Themify products version xml file
        $this->versions_url = $this->api_domain . '/versions/versions.xml';

		$this->hooks();

        if( !defined('THEMIFY_UPGRADER') ) define('THEMIFY_UPGRADER', true);
        if( !THEMIFY_UPGRADER ) {
            return false;
        }

		if (defined('WP_DEBUG') && WP_DEBUG) {
            delete_transient("themify_updater_cache");
        }

        $this->load_updater_cache();

    }

    private function get_plugins() {

        if ( ! function_exists( 'get_plugins' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $installed_plugins = get_plugins();
        if( !empty( $installed_plugins ) ) {
            foreach ( $installed_plugins as $key => $plugin ) {
                $plugin_name = dirname( $key );
                if ( !$this->has_attribute($plugin_name, 'wp_hosted') && $this->is_update_available($plugin_name, $plugin['Version'])) {
                    $temp_class = new stdClass;
                    $temp_class->name = $plugin_name;
                    $temp_class->nicename = $plugin['Name'];
                    $temp_class->basename = $key;
                    $temp_class->themify_uri = $plugin['PluginURI'];
                    $temp_class->type = 'plugin';
                    $temp_class->version = $this->get_remote_version($plugin_name);
                    $temp_class->slug = $plugin_name;

                    array_push($this->updates, $temp_class);
                }

                if ( !$this->has_attribute($plugin_name, 'wp_hosted') && !$this->has_attribute($plugin_name, 'free') ) $this->has_premium = true;
            }
        }
    }

    private function get_themes() {

       if ( THEMIFY_UPDATER_NETWORK_ENABLED ) {
            $installed_themes = $this->wp_get_themes();
        } else {
            $installed_themes = wp_get_themes();
        }

        if( !empty( $installed_themes ) ) {
            foreach ($installed_themes as $key => $theme) {
                if (!$this->has_attribute($key, 'wp_hosted') && $this->is_update_available($key, $theme->get('Version'))) {
                    $temp_class = new stdClass;
                    $temp_class->name = $key;
                    $temp_class->nicename = $theme->get('Name');
                    $temp_class->basename = $key;
                    $temp_class->themify_uri = $theme->get('ThemeURI');
                    $temp_class->type = 'theme';
                    $temp_class->version = $this->get_remote_version($key);
                    $temp_class->slug = $key;

                    array_push($this->updates, $temp_class);
                }

                if ( !$this->has_attribute($key, 'wp_hosted') && !$this->has_attribute($key, 'free') ) $this->has_premium = true;
            }
        }
    }

	protected function get_key($reNew = false) {

        $key = $this->key;
        if ( !$key || $reNew) {
	        $key = get_option('themify_updater_licence','');

            if (!empty($key) ) {
                $key = json_decode($key, true);
                if (!$key) {
	                $key = array('key' => '', 'username' => '');
                }
            } else {
	            $key = array('key' => '', 'username' => '');
            }
            $this->key = $key;
        }
        return ( $key['key'] );
    }

    protected function get_username($reNew = false) {

	    $key = $this->key;
	    if ( !$key || $reNew) {
		    $key = get_option('themify_updater_licence','');

		    if (!empty($key) ) {
			    $key = json_decode($key, true);
			    if (!$key) {
				    $key = array('key' => '', 'username' => '');
			    }
		    } else {
			    $key = array('key' => '', 'username' => '');
		    }
		    $this->key = $key;
	    }
	    return ( $key['username'] );
    }

	private function hooks() {

		add_action('admin_notices', array($this, 'admin_notices'), 3);
		add_action('themify_verify_license', array($this , 'themify_verify_license' ));
        add_action('admin_menu', array($this, 'menu'));
        add_action( 'admin_head', array( $this, 'dismiss' ) );

        if( !defined('THEMIFY_UPGRADER') ) define('THEMIFY_UPGRADER', true);
        if( !THEMIFY_UPGRADER )
            return;

        add_action('site_transient_update_plugins', array($this, 'wp_updater'), 10, 2);
        add_action('site_transient_update_themes', array($this, 'wp_updater'), 10, 2);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_script'));
        add_action('admin_footer', array($this, 'prompt'));
	}

	public function menu() {
        add_submenu_page( 'index.php', __('Themify Licence', 'themify-updater'), __('Themify Licence', 'themify-updater'),
            'manage_options', 'themify-licence', array($this, 'menu_page_callback'));
    }
    public function dismiss() {
        if ( isset( $_GET['dismiss_themify_updater_notices'] ) && check_admin_referer( 'dismiss_themify_updater_notices' ) ) {
            update_user_meta( get_current_user_id(), 'dismiss_themify_updater_notices', 2 );
        }
    }

    public function menu_page_callback() {

        if ( isset($_POST['updater_licence'] ) ) {
            $key = preg_replace("/[^0-9a-zA-Z]/", "", $_POST['updater_licence']);
            $username = preg_replace("/[^0-9a-zA-Z_-]/", "", $_POST['themify_username']);
            $hideKey = isset($_POST['hidekey']) ? true : false;
            update_option('themify_updater_licence', json_encode(array('key' => $key, 'username' => $username, 'hideKey' => $hideKey)));
            do_action('themify_verify_licence');
        }

		$options = get_option('themify_updater_licence', '');
        $username = '';
        $key = '';
        $hideKey = false;
        if ( !empty($options) ) {
            $options = json_decode( $options,true);
            if ( is_array($options) ) {
                $username = $options['username'];
                $key = $options['key'];
                $hideKey = isset($options['hideKey']) ? $options['hideKey'] : false;
            }
        }

        if ($hideKey) {
            $key = preg_replace("/[0-9a-zA-Z]/", "*", $key);
        }

        require (THEMIFY_UPDATER_DIR_PATH.'/templates/admin_menu.php');
    }

    private function load_updater_cache($update = false) {
        $cache = get_transient('themify_updater_cache');

        if ( !$cache || (time() - $cache['lastChecked']) > (6 * HOUR_IN_SECONDS) || $update ) {
            $cache = array();
            $cache['lastChecked'] = time();
        }

        $this->updater_cache = $cache;
    }

    /**
     * @param $key
     * @return bool|array
     */
    private function get_updater_cache_for($key) {

        if ( isset($this->updater_cache[$key]) ) return $this->updater_cache[$key];

        return false;
    }

    /**
     * @param $key
     * @param $value
     */
    private function set_updater_cache_for($key,$value) {

        $this->updater_cache[$key] = $value;

        set_transient('themify_updater_cache', $this->updater_cache, 6 * HOUR_IN_SECONDS);

    }

    /**
     * @param $key
     */
    private function unset_updater_cache_for($key) {

        if ( isset($this->updater_cache[$key]) ) unset($this->updater_cache[$key]);

        set_transient('themify_updater_cache', $this->updater_cache, 6 * HOUR_IN_SECONDS);

    }

    private function set_updater_cache () {
        set_transient('themify_updater_cache', $this->updater_cache, 6 * HOUR_IN_SECONDS);
    }

    /**
     * @return array
     */
    private function get_products_with_access () {
        $url = $this->apiRequestPath('res');

	    $content = wp_remote_get( $url );
	    if( is_wp_error( $content ) || !is_array( $content )) {
		    $content = array();
	    } else {
		    $content = json_decode($content['body'], true);
        }

        return $content;
    }

    /**
     * @return array
     */
    private function get_products() {

        if ( $this->get_updater_cache_for('products') ) {
            return $this->get_updater_cache_for('products');
        }

        if ( !$this->has_premium ) {
            return array();
        }

        $products = $this->get_products_with_access();

        $this->set_updater_cache_for('products', $products);

        return $products;
    }

	private function create_update_transient () {
        $this->themify_verify_license();

        if (!is_array($this->transients)) {

            $theme_transient = new stdClass();
            $plugin_transient = new stdClass();
	        $theme_transient->response = $theme_transient->checked = array();
	        $plugin_transient->response = $plugin_transient->checked = array();


            foreach ($this->updates as $update) {
                $transient = new stdClass();

	            $package = '';
	            $products = $this->get_products();

                if ( is_array($products) && !empty($products) && in_array($update->slug, $products ) ) {
		            $package =  $this->apiRequestPath('get', $update->slug);
                }

                if ( $this->has_attribute($update->slug, 'free') ) {
                    $package = $this->api_domain . '/files/'. $update->slug .'/'.$update->slug.'.zip';
                }

                if ($update->type === 'theme') {
                    $transient = array();
                    $transient['theme'] = $update->name;
                    $transient['new_version'] = $update->version;
                    $transient['url'] = $update->themify_uri;
                    $transient['package'] = $package;
	                $theme_transient->response[$update->basename] = $transient;
	                $theme_transient->checked[$update->basename] = $update->version;
                    add_action( "after_theme_row_". $update->basename, array($this, 'theme_update_row'), 8, 2 );
                } else {
                    $transient->name = $update->nicename;
                    $transient->plugin = $update->basename;
                    $transient->slug = $update->slug;
                    $transient->new_version = $update->version;
                    $transient->url = $update->themify_uri;
                    $transient->package = $package;
	                $plugin_transient->response[$update->basename] = $transient;
	                $plugin_transient->checked[$update->basename] = $update->version;
                    add_action( "after_plugin_row_". $update->basename , array($this , 'plugin_update_row'), 8, 2 );
                }
            }
            $this->transients['theme'] = $theme_transient;
            $this->transients['plugin'] = $plugin_transient;
        }



    }

	public function wp_updater ($transient, $for) {

        if ($for !== 'update_themes' && $for !== 'update_plugins') return $transient;

		$type = 'theme';
		if ($for === 'update_plugins') {
			$type = 'plugin';
        }

		if (empty($this->updates)) {
			$this->get_themes();
			$this->get_plugins();
			$this->create_update_transient();
		}

		if ( isset($transient->last_checked) ) {
			foreach ($this->transients[$type]->response as $key => $response) {
				$transient->response[$key] = $response;
				$transient->checked[$key] = $this->transients[$type]->checked[$key];
			}
        }

        return $transient;

	}

	function themify_verify_license () {

		$license = preg_replace("/[^0-9a-zA-Z]/", "", $this->get_key(true));
		$user = preg_replace("/[^0-9a-zA-Z_-]/", "", $this->get_username(true));
		$new_cache = false;

		if(!isset($this->updater_cache['license'])) {
		    $licence_cache = $this->get_updater_cache_for('license');
            $licence_cache = $licence_cache ? $licence_cache : array( 'error' => true, 'license_expires' => time(), 'old_key' => $license, 'old_username' => $user);
        } else {
            $licence_cache = $this->updater_cache['license'];
        }

        if (empty($licence_cache['license_expires'])) {
            $license_expires = 1;
        } else {
            $license_expires = ( (int)  $licence_cache['license_expires'] ) - time();
        }

		if($license != $licence_cache['old_key'] || $user != $licence_cache['old_username']) {
            $licence_cache['error'] = true;
            $this->unset_updater_cache_for('products');
            $this->unset_updater_cache_for('license');
            update_user_meta( get_current_user_id(), 'dismiss_themify_updater_notices', 1 );
		}

		if ($licence_cache['error'] || $license_expires < 0) {
			$url = $this->apiRequestPath();

			$content = wp_remote_get( $url );
			if( is_wp_error( $content ) || !is_array( $content )) {
				$content = '';
			} else {
				$content = $content['body'];
			}

            $new_cache = true;

			if ( !empty($content) ) {
				$licence_cache = json_decode($content, true);
				$licence_cache['error'] = false;
                $licence_cache['license_expires'] = isset($licence_cache['license_expires']) ? strtotime($licence_cache['license_expires']) : '';
			} else {
				$licence_cache = array();
				$licence_cache['message'] = __('Themify Updater: Failed to check license key.', 'themify-updater');
				$licence_cache['code'] = 'failed_to_check';
				$licence_cache['error'] = true;
			}
			unset($content);
		}

		$admin_notices = false;
		$shortMessage = '';

		if( !isset($licence_cache['code']) || $licence_cache['code'] !== 'ok' || $licence_cache['error']) {

			if ( !isset($licence_cache['code']) || $licence_cache['error']) {
				$admin_notices = $shortMessage = __('Themify Updater: Failed to check license key.', 'themify-updater');
			}

			if ( isset($licence_cache['code']) && $licence_cache['code'] !== 'ok') {
			    switch ($licence_cache['code']) {
                    case 'usernameMismatch':
                        $shortMessage = __('Username and license key doesn\'t match.','themify-updater');
                        $admin_notices = sprintf('%s <a href="%s" class="">%s</a>.', __("Themify Updater: username and license key doesn't match. Please ", 'themify-updater'), esc_attr( admin_url( 'index.php?page=themify-licence' ) ), __('correct it', 'themify-updater'));
                        break;
                    case 'license_empty':
                        $shortMessage = __('License key is missing.','themify-updater');
                        $admin_notices = sprintf('%s <a href="%s" class="">%s</a>%s', __("Themify Updater: license key is missing. Please enter ", 'themify-updater'), esc_attr( admin_url( 'index.php?page=themify-licence' ) ), __('Themify License', 'themify-updater'), __(' key.', 'themify-updater'));
	                    break;
                    case 'license_not_found':
                        $shortMessage = __('License key is invalid','themify-updater');
                        $admin_notices = sprintf('%s <a href="%s" class="">%s</a>%s', __("Themify Updater: ", 'themify-updater'), esc_attr( admin_url( 'index.php?page=themify-licence' ) ), __('license key', 'themify-updater'), __(' is invalid. Please enter a valid license key.', 'themify-updater'));
	                    break;
                    case 'license_expired':
                        $shortMessage = __('Your license key is expired.','themify-updater');
                        $admin_notices = sprintf('%s <a href="%s" class="">%s</a>%s', __("Themify Updater: your license key is expired. Please renew membership or ", 'themify-updater'), esc_attr('https://themify.me/contact'), __('contact Themify', 'themify-updater'), __(' for more details.', 'themify-updater'));
	                    break;
                    case 'license_disabled':
                        $shortMessage = __('Your license key is disabled.','themify-updater');
                        $admin_notices = sprintf('%s <a href="%s" class="">%s</a>%s', __("Themify Updater: your license key is disabled. Please ", 'themify-updater'), esc_attr('https://themify.me/contact'), __('contact Themify', 'themify-updater'), __(' for more details.', 'themify-updater'));
	                    break;
                    default:
				        $admin_notices = $licence_cache['message'];
                }
			}
		}

		$licence_cache['old_key'] = $license;
		$licence_cache['old_username'] = $user;
		$this->key_error['code'] = $licence_cache['code'];
		$this->key_error['message'] = $admin_notices;
		$this->key_error['short'] = $shortMessage;

		if ($new_cache) {
            $this->set_updater_cache_for('license', $licence_cache);
        }
	}

    public function prompt() {
        ?>
        <div class="themify_updater_alert"></div>
        <!-- prompts -->
        <div class="themify-updater-promt-box">
            <div class="show-error">
                <p class="error-msg"><?php _e('There were some errors updating the theme', 'themify-updater'); ?></p>
            </div>
        </div>
        <div class="themify_updater_promt_overlay"></div>
        <!-- /prompts -->
        <?php

        //Admin_Footer is the last hook called in plugin so updating cache from here is ok.
        $this->set_updater_cache();
    }

    public function admin_notices() {
        $notifications = '';
        $is_dismiss = get_user_meta( get_current_user_id(), 'dismiss_themify_updater_notices', true );

        if ( (!empty($this->key_error['message']) && $this->has_premium) ) {
            if ( empty($is_dismiss) || (int) $is_dismiss === 1) {
                ?>
                <div class="notice notice-error is-dismissible">
                    <p><?php echo $this->key_error['message']; ?>
                        <a href="<?php echo esc_url(wp_nonce_url(add_query_arg('dismiss_themify_updater_notices', 'themify_updater'), 'dismiss_themify_updater_notices')); ?>"
                           class="dismiss-notice"
                           target="_parent"><?php _e('Dismiss this notice', 'themify-updater'); ?></a></p>
                </div>
                <?php
            }
        }

        foreach ($this->updates as $update) {

            $classes = array('themify-updater');

	        $update->url = '#';

            $products = $this->get_products();

	        if ( ( !in_array($update->slug, $products) || !empty($this->key_error['message']) ) && !$this->has_attribute($update->slug, 'free') ) {
                $classes[] = 'themify-updater-stop';
            }

            $classes = array_unique($classes);

            $notifications = sprintf(
                __('<p class="update update-nag">
                                    %s version %s is now available.
                                    <a href="%s" title="" class="%s" data-plugin="%s" data-nicename_short="%s" data-update_type="%s" data-base="%s" data-nonce="%s">Update now</a>
                                    or view the 
                                    <a href="%s" title="" class="themify_updater_changelogs" target="_blank" data-changelog="%s">changelog</a>
                                    for details.
                                    </p>', 'themify-updater'),
                $update->nicename,
                $update->version,
                esc_url($update->url),
                esc_attr(implode(' ', $classes)),
                esc_attr($update->slug),
                esc_attr($update->nicename),
                esc_attr(str_replace('_','-',$this->update_types[$update->type])),
                esc_attr($update->basename),
                wp_create_nonce('updates'),
                esc_url('https://themify.me/changelogs/' . $update->name . '.txt'),
                esc_url('https://themify.me/changelogs/' . $update->name . '.txt')
            );

	        echo '<div class="notifications">' . $notifications . '</div>';
        }

    }

    private function get_remote_version($name = '') {
        $version = '';

		if ( !is_object($this->versions_xml) ) {
			$this->get_version_xml();
		}

        if (is_object($this->versions_xml)) {
	        $query = "//version[@name='" . $name . "']";
	        $elements = $this->versions_xml->query($query);
	        if ($elements->length) {
		        foreach ($elements as $field) {
			        $version = $field->nodeValue;
		        }
	        }
        }

        return $version;
    }

    private function get_version_xml() {

        if(  $this->get_updater_cache_for('version') ) {
            $body = $this->get_updater_cache_for('version');
        } else {
            $response = wp_remote_get($this->versions_url);
            if (is_wp_error($response)) {
                return false;
            }

            $body = wp_remote_retrieve_body($response);
            if (is_wp_error($body) || empty($body)) {
                return false;
            }

            $this->set_updater_cache_for('version', $body);
        }

        $xml = new DOMDocument;
        $xml->loadXML(trim($body));
        $xml->preserveWhiteSpace = false;
        $xml->formatOutput = true;
        $this->versions_xml = new DOMXPath($xml);

    }

    private function has_attribute($name, $attr) {

	    $ret = false;

	    if ( !is_object($this->versions_xml) ) {
		    $this->get_version_xml();
	    }

	    if (is_object($this->versions_xml)) {
		    $query = "//version[@name='" . $name . "']";
		    $elements = $this->versions_xml->query($query);
		    if ($elements->length) {
			    foreach ($elements as $field) {
                    $ret = $field->getAttribute($attr);
                    $ret = empty($ret) ? false : true;
			    }
		    }
	    }

	    return $ret;

    }

    public function is_update_available($name = '', $version = '1.0') {

        $new_version = $this->get_remote_version($name);

        return version_compare($version, $new_version, '<');
    }

    public function enqueue_script() {
        $translation_array = array(
                    'check_backup' => __('Make sure to backup before upgrading. Files and settings may get lost or changed.', 'themify-updater'),
                    'error_message' => $this->key_error['message']
                );
        wp_enqueue_script('themify-upgrader', $this->enque_min(THEMIFY_UPDATER_DIR_URL . 'js/themify-upgrader.js'), array('jquery'), THEMIFY_UPDATER_VERSION, true);
        wp_localize_script('themify-upgrader', 'themify_upgrader', $translation_array);
		wp_enqueue_style('themify-updater-style', $this->enque_min(THEMIFY_UPDATER_DIR_URL . 'css/themify-upgrader.css'), array(), THEMIFY_UPDATER_VERSION, 'all');
    }

    public function enque_min( $url, $check = false ) {
        static $is_disabled = null;
        if ( $is_disabled === null ) {
            $is_disabled =( defined( 'WP_DEBUG' ) &&  WP_DEBUG ) || ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) || ( defined( 'THEMIFY_DEBUG' ) && THEMIFY_DEBUG );
        }
        if( $is_disabled ) {
            return $check ? false : $url;
        }
        $f = pathinfo( $url );
        $return = 0;
        if ( strpos( $f['filename'], '.min.', 2 ) === false ) {
            $absolute = str_replace( WP_CONTENT_URL, '', $f['dirname'] );
            $name = $f['filename'] . '.min.' . $f['extension'];
            if ( is_file( trailingslashit( WP_CONTENT_DIR ) . trailingslashit( $absolute ) . $name ) ) {
                if( $check ) {
                    $return = 1;
                } else {
                    $url = trailingslashit( $f['dirname'] ) . $name;
                }
            }
        }

        return $check ? $return : $url;
    }

	private function apiRequestPath( $request = 'check', $product = '' ) {
		$domain = $this->api_domain;
		$path = '/member/softsale/api/';
		$key = '?key='. urlencode($this->get_key());
		$product = !empty($product) ? '&product='. urlencode( $product ) : '';
		switch ($request) {
			case 'get':
				$action = urlencode( $this->api_actions['get'] );
                $key .= '&u='. urlencode( $this->get_username() );
				break;
			case 'res':
				$action = urlencode( $this->api_actions['res'] );
                $key .= '&u='. urlencode( $this->get_username() );
				break;
			default :
				$action = urlencode($this->api_actions['check'] );
                $key .= '&u='. urlencode( $this->get_username() );
		}

		return $domain . $path . $action . $key . $product;
	}

	private function wp_get_themes( $args = array() ) {

        $theme_names = WP_Theme::get_allowed_on_network();
        $themes = array();
        $temp = false;
        foreach ($theme_names as $key => $name) {
            $temp = wp_get_theme( $key );
            if ($temp->exists()) {
                $themes[$key] = $temp;
            }
        }

        return $themes;
    }

    public function plugin_update_row( $file, $plugin_data ) {

        if ( !isset($this->transients['plugin']->response) || ! isset( $this->transients['plugin']->response[ $file ] ) ) {
            return false;
        }

        remove_action( "after_plugin_row_$file", 'wp_plugin_update_row', 10, 2 );

        $response = $this->transients['plugin']->response[ $file ];

        $plugins_allowedtags = array(
            'a'       => array( 'href' => array(), 'title' => array() ),
            'abbr'    => array( 'title' => array() ),
            'acronym' => array( 'title' => array() ),
            'code'    => array(),
            'em'      => array(),
            'strong'  => array(),
        );

        $plugin_name   = wp_kses( $plugin_data['Name'], $plugins_allowedtags );

        /** @var WP_Plugins_List_Table $wp_list_table */
        $wp_list_table = _get_list_table( 'WP_Plugins_List_Table' );

        if ( is_network_admin() || ! is_multisite() ) {
            if ( is_network_admin() ) {
                $active_class = is_plugin_active_for_network( $file ) ? ' active' : '';
            } else {
                $active_class = is_plugin_active( $file ) ? ' active' : '';
            }

            $details_url = esc_url( 'https://themify.me/changelogs/'. dirname($file). '.txt' );

            echo '<tr class="plugin-update-tr' . $active_class . '" id="' . esc_attr( $response->slug . '-update' ) . '" data-slug="' . esc_attr( $response->slug ) . '" data-plugin="' . esc_attr( $file ) . '"><td colspan="' . esc_attr( $wp_list_table->get_column_count() ) . '" class="plugin-update colspanchange"><div class="update-message notice inline notice-warning notice-alt"><p>';

            if ( ! current_user_can( 'update_plugins' ) ) {
                /* translators: 1: plugin name, 2: details URL, 3: additional link attributes, 4: version number */
                printf( __( 'There is a new version of %1$s available. <a href="%2$s" title="" class="themify_updater_changelogs" target="_blank" data-changelog="%3$s">View version %4$s details</a>.' ),
                    $plugin_name,
                    $details_url,
                    $details_url,
                    $response->new_version
                );
            } elseif ( empty( $response->package ) ) {
                /* translators: 1: plugin name, 2: details URL, 3: additional link attributes, 4: version number */
                printf( __( 'There is a new version of %1$s available. <a href="%2$s" title="" class="themify_updater_changelogs" target="_blank" data-changelog="%3$s">View version %4$s details</a>. <em>%5$s Automatic update is unavailable.</em>' ),
                    $plugin_name,
                    $details_url,
                    $details_url,
                    $response->new_version,
                    $this->key_error['short']
                );
            } else {
                /* translators: 1: plugin name, 2: details URL, 3: additional link attributes, 4: version number, 5: update URL, 6: additional link attributes */
                printf( __( 'There is a new version of %1$s available. <a href="%2$s" title="" class="themify_updater_changelogs" target="_blank" data-changelog="%3$s">View version %4$s details</a> or <a href="%5$s" %6$s>update nows</a>.' ),
                    $plugin_name,
                    $details_url,
                    $details_url,
                    $response->new_version,
                    wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&plugin=' ) . $file, 'upgrade-plugin_' . $file ),
                    sprintf( 'class="update-link" aria-label="%s"',
                        /* translators: %s: plugin name */
                        esc_attr( sprintf( __( 'Update %s now' ), $plugin_name ) )
                    )
                );
            }

            echo '</p></div></td></tr>';
        }
    }

    public function theme_update_row( $theme_key, $theme ) {

        if ( ! isset($this->transients['theme']->response) || ! isset( $this->transients['theme']->response[ $theme_key ] ) ) {
            return false;
        }

        remove_action( "after_theme_row_$theme_key", 'wp_theme_update_row', 10, 2 );

        $response = $this->transients['theme']->response[ $theme_key ];

        $details_url = esc_url ('https://themify.me/changelogs/'. dirname($file). '.txt' );

        /** @var WP_MS_Themes_List_Table $wp_list_table */
        $wp_list_table = _get_list_table( 'WP_MS_Themes_List_Table' );

        $active = $theme->is_allowed( 'network' ) ? ' active' : '';

        echo '<tr class="plugin-update-tr' . $active . '" id="' . esc_attr( $theme->get_stylesheet() . '-update' ) . '" data-slug="' . esc_attr( $theme->get_stylesheet() ) . '"><td colspan="' . $wp_list_table->get_column_count() . '" class="plugin-update colspanchange"><div class="update-message notice inline notice-warning notice-alt"><p>';
        if ( ! current_user_can( 'update_themes' ) ) {
            /* translators: 1: theme name, 2: details URL, 3: additional link attributes, 4: version number */
            printf( __( 'There is a new version of %1$s available. <a href="%2$s" title="" class="themify_updater_changelogs" target="_blank" data-changelog="%3$s">View version %4$s details</a>.'),
                $theme['Name'],
                $details_url,
                $details_url,
                $response['new_version']
            );
        } elseif ( empty( $response['package'] ) ) {
            /* translators: 1: theme name, 2: details URL, 3: additional link attributes, 4: version number */
            printf( __( 'There is a new version of %1$s available. <a href="%2$s" title="" class="themify_updater_changelogs" target="_blank" data-changelog="%3$s">View version %4$s details</a>. <em>%5$s Automatic update is unavailable.</em>' ),
                $theme['Name'],
                $details_url,
                $details_url,
                $response['new_version'],
                $this->key_error['short']
            );
        } else {
            /* translators: 1: theme name, 2: details URL, 3: additional link attributes, 4: version number, 5: update URL, 6: additional link attributes */
            printf( __( 'There is a new version of %1$s available. <a href="%2$s" title="" class="themify_updater_changelogs" target="_blank" data-changelog="%3$s">View version %4$s details</a> or <a href="%5$s" %6$s>update now</a>.' ),
                $theme['Name'],
                $details_url,
                $details_url,
                $response['new_version'],
                wp_nonce_url( self_admin_url( 'update.php?action=upgrade-theme&theme=' ) . $theme_key, 'upgrade-theme_' . $theme_key ),
                sprintf( 'class="update-link" aria-label="%s"',
                    /* translators: %s: theme name */
                    esc_attr( sprintf( __( 'Update %s now' ), $theme['Name'] ) )
                )
            );
        }

        echo '</p></div></td></tr>';
    }
}
