<?php
/*
Plugin Name: WP-URLCache
Plugin URI: http://blog.fusi0n.org
Description: WP-URLCache allows you to use a shortcode to locally cache outgoing links in your posts and pages.
Version: 0.2
Author: Pier-Luc Petitclerc
Author URI: http://blog.fusi0n.org
Contributors: mcnicks
License: http://creativecommons.org/licenses/by/3.0/
*/

class WP_URLCache
{

  /**
   * @var string Local cache directory
   * @access private
   */
  private $cacheDir;

  /**
   * @var string URL to local cache directory
   * @access private
   */
  private $cacheUrl;

  /**
   * @var array WP-URLCache Options Data
   * @access private
   */
  private $opts;

  /**
   * PHP5 Class Constructor
   * Checks if /cache is writable, adds shortcode and Settings stuff.
   * @param void
   * @return null
   * @access public
   * @author Pier-Luc Petitclerc <pL@fusi0n.org>
   * @since 1.0
   */
  public function __construct() {
    $this->cacheDir = dirname(__FILE__).'/cache';
    $this->cacheUrl = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__), '', plugin_basename(__FILE__)).'cache';
    if (!is_writeable($this->cacheDir)) { update_option('wpu_coral', '1'); }
    add_shortcode('urlcache', array(&$this, 'wpu_shortcode'));
    if (is_admin()) {
      add_action('plugin_action_links_'.plugin_basename(__FILE__), array(&$this, 'wpu_plugin_links'));
      add_action('admin_init', array(&$this, 'wpu_section_register'));
    }
  }

  function __call($name, $args) {
    if (substr($name, 0, 13) == 'wpu_settings_') {
      $setting = str_replace('settings_', '', $name);
      $this->wpu_settings($setting);
    }
  }

  /**
   * Adds Settings link to plugin's links
   * @param array $links Already present links
   * @return array Links array containing Settings
   * @access public
   * @author Pier-Luc Petitclerc <pL@fusi0n.org>
   * @since 1.0
  */
  public function wpu_plugin_links($links) {
    $additionalLinks = array('<a href="options-misc.php">'.__('Settings').'</a>');
    return array_merge($additionalLinks, $links);
  }

  /**
   * Option page (section) registration hook
   * @param void
   * @return null
   * @author Pier-Luc Petitclerc <pL@fusi0n.org>
   * @access public
   * @since 1.0
  */
  public function wpu_section_register() {
    // Main section within 'Settings/Miscellaneous' page
    add_settings_section('wp_urlcache', 'WP-URLCache Options', array(&$this, 'wpu_section_callback'), 'misc');

    register_setting('urlcache', 'wpu_coral');
    add_settings_field('wpu_coral', 'Use <a href="http://www.coralcdn.org/" target="_blank">Coral</a> Cache', array(&$this, 'wpu_settings_coral'), 'misc', 'wp_urlcache');

    register_setting('urlcache', 'wpu_extension');
    add_settings_field('wpu_extension', 'Local cache file extension', array(&$this, 'wpu_settings_extension'), 'misc', 'wp_urlcache');

    register_setting('urlcache', 'wpu_cachetext');
    add_settings_field('wpu_cachetext', 'Cache link text', array(&$this, 'wpu_settings_cachetext'), 'misc', 'wp_urlcache');

    register_setting('urlcache', 'wpu_cachetarget');
    add_settings_field('wpu_cachetarget', 'Cache link target', array(&$this, 'wpu_settings_cachetarget'), 'misc', 'wp_urlcache');

    register_setting('urlcache', 'wpu_cachealt');
    add_settings_field('wpu_cachealt', 'Cache link ALT', array(&$this, 'wpu_settings_cachealt'), 'misc', 'wp_urlcache');
  }

  /**
   * Ghost method to output main settings section callback
   * @param void
   * @return null
   * @access public
   * @author Pier-Luc Petitclerc <pL@fusi0n.org>
   * @since 1.0
   */
  public function wpu_section_callback() { return; }

  public function wpu_settings($setting) {
    switch ($setting) {
      case 'wpu_cachetext':
      case 'wpu_cachetarget':
      case 'wpu_cachealt':
      case 'wpu_extension':
        echo '<input type="text" name="'.$setting.'" value="'.get_option($setting).'" />';
        break;
      case 'wpu_coral':
        echo '<input type="checkbox" name="'.$setting.'" value="1" ';
        echo get_option($setting) == '1'? 'checked="checked" />' : '/>';
        break;
    }
  }

  /**
   * WordPress Activation Hook
   * Adds Options, creates cache directory and set right permissions
   * @param void
   * @return null
   * @access public
   * @since 1.0
   * @author Pier-Luc Petitclerc <pL@fusi0n.org>
   */
  public function wpu_activation_hook() {
    add_option('wpu_cachetext', 'cached');
    add_option('wpu_cachetarget', '_self');
    add_option('wpu_cachealt', 'Cached Version');
    add_option('wpu_extension', 'html');
    add_option('wpu_coral', '0');
    if (!is_dir($this->cacheDir)) { mkdir($this->cacheDir, 0777); }
    elseif (!is_writeable($this->cacheDir)) { chmod($this->cacheDir, 0777); }
  }

  /**
   * Adds ShortCode capabilities
   * @param array $params Shortcode parameters
   * @param string $content ShortCode Contents
   * @access public
   * @author Pier-Luc Petitclerc <pL@fusi0n.org>
   * @since 1.0
   * @return string ShortCode output
   */
  public function wpu_shortcode($params, $content) {
    extract(shortcode_atts(array('url'      => '',
                                 'target'   => get_option('wpu_cachetarget'),
                                 'alt'      => get_option('wpu_cachealt'),
                                 'cachetext'=> get_option('wpu_cachetext')), $params));
    $cacheLink = get_option('wpu_coral') == '1'? 'http://redirect.nyud.net/?url='.$url : $this->_makeCache($url);
    $output = '<a href="'.$url.'" target="'.$target.'" alt="'.$alt.'">'.$content.'</a> (<a href="'.$cacheLink.'">'.$cachetext.'</a>)';
    return $output;
  }

  /**
   * Detects whether to create cache file or return its local location
   * @param string $url Remote URL to cache
   * @return string Local cached copy URL
   * @access private
   * @author Pier-Luc Petitclerc <pL@fusi0n.org>
   * @since 1.0
   */
  private function _makeCache($url) {
    $cacheFileName= md5($url.get_the_ID()).'.'.get_option('wpu_extension');
    $wpurl        = $this->cacheUrl.'/'.$cacheFileName;
    $cachePath    = $this->cacheDir.'/'.$cacheFileName;
    if (!$this->_isCached($cachePath)) {
      $this->_cacheContents($cachePath, $this->_getContents($url));
    }
    return $wpurl;
  }

  /**
   * Checks if given file location already exists (therefore is cached)
   * @param string $file Local cache file location
   * @return bool True if file exist, false if it does not
   * @access private
   * @author Pier-Luc Petitclerc <pL@fusi0n.org>
   * @since 1.0
   */
  private function _isCached($file) { return @file_exists($file); }

  /**
   * Connects to remote URL and scrapes contents
   * @param string $url Remote URL
   * @return string Remote contents
   * @access private
   * @since 1.0
   * @author Pier-Luc Petitclerc <pL@fusi0n.org>
   */
  private function _getContents($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 4);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_USERAGENT, 'WP-URLCache 0.1 - http://blog.fusi0n.org/');
    $buffer = curl_exec($ch);
    curl_close($ch);
    $allowed = '<p><a><span><div><blockquote><em><strong><ul><li><ol><table><td><tr><thead><tbody><h1><h2><h3><h4><h5><h6><hr><br>';
    return strip_tags($buffer, $allowed);
  }

  /**
   * Writes data to local file
   * @param string $file Cache file path
   * @param string $contents Remote file contents
   * @return null
   * @access private
   * @since 1.0
   * @author Pier-Luc Petitclerc <pL@fusi0n.org>
   */
  private function _cacheContents($file, $contents) {
    file_put_contents($file, $contents);
  }
}

// Fire in the hole!
$wpu = new WP_URLCache();
register_activation_hook(__FILE__, array(&$wpu, 'wpu_activation_hook'));