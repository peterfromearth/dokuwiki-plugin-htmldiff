<?php
/**
 * Htmldiff Plugin
 *
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     peterfromearth <coder@peterfromearth.de>
 */


if (file_exists($autoload = __DIR__. '/vendor/autoload.php')) {
    require_once $autoload;
} 

use Caxy\HtmlDiff\HtmlDiff;
use Caxy\HtmlDiff\HtmlDiffConfig;
use dokuwiki\Extension\Event;

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class action_plugin_htmldiff extends DokuWiki_Action_Plugin {
    /**
     * plugin should use this method to register its handlers with the dokuwiki's event controller
     */
    function register(Doku_Event_Handler $controller) {
      $controller->register_hook('DIFF_RENDER', 'BEFORE', $this, 'event');
      $controller->register_hook('DIFF_TYPES', 'AFTER', $this, 'addType');
      $controller->register_hook('TPL_CONTENT_DISPLAY', 'BEFORE', $this, 'fixHtmlClass');
    }
    /**
     * Inserts a toolbar button
     */
    function event(Event $event) {
        global $ID;
        
        $data = $event->data;
        $difftype = $data['difftype'];
        
        if($difftype === 'plugin_htmldiff') {
            $config = new HtmlDiffConfig();
            
            $l_render = p_wiki_xhtml($ID, $data['l_rev'], true, $data['l_rev']);
            $r_render = p_wiki_xhtml($ID, $data['r_rev'], true, $data['r_rev']);
            
            $l_render = preg_replace('/<a[^>]*>(.*?)<\/a>/is', '$1', $l_render);
            $r_render = preg_replace('/<a[^>]*>(.*?)<\/a>/is', '$1', $r_render);
            
            $htmlDiff = HtmlDiff::create($l_render, $r_render, $config);
    		$content = $htmlDiff->build();
    		echo '<tr><td colspan="4" class="plugin_htmldiff">';
    		echo $content;
    		echo '</td></tr>';
    		
    		$event->preventDefault();
		
        }
    }
    
    /**
     * add difftype
     */
    function addType(Event $event) {
        $event->data['plugin_htmldiff'] = 'Rendered (HTML)';
    }
    
    /**
     * remove diff class from table
     */
    function fixHtmlClass(Event $event) {
        global $INPUT;
        if($INPUT->str('difftype') !== 'plugin_htmldiff') return;
        
        $event->data = str_replace('<table class="diff ', '<table class="', $event->data);
    }
}
