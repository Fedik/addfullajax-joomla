<?php
/**
 * @version	2016.03.25 (1.3) use Fullajax lib v1.3.4
 * @package Add FullAjax for Joomla!
 * @author  Fedir Zinchuk
 * @link    http://www.getsite.org.ua
 * @license	GNU/GPL http://www.gnu.org/licenses/gpl.html
 */
defined( '_JEXEC' ) or die( 'Get lost?' );

class plgSystemAddFullajax extends JPlugin
{
	/**
	 * indicate whether need ajax response
	 * @var bool
	 */
	protected $nedAjaxRespons = false;

	/**
	 * indicate whether we allowed connect/use ajax script on page
	 * @var bool
	 */
	protected $axJsAllowed    = false;

	/**
	 * default template name
	 * @var string
	 */
	protected $defTemplate    = 'system';

	/**
	 * check whether we need ajax response
	 */
	public function onAfterRoute() {
		$app = JFactory::getApplication();

		if ($app->isAdmin())
		{
			return;
		}

		if(isset($_SERVER['HTTP_AJAX_ENGINE']) && $_SERVER['HTTP_AJAX_ENGINE'] === 'Fullajax'
			|| $app->input->get('ax') === 'ok' // || $use_ajax
		) {
			$this->nedAjaxRespons = true;

			//save location in header, because we cannot take it via xhr.getResponseHeader('Location');
			//need for save a right URL in History in XMLHttpRequest Redirect case
			$url = JFactory::getURI()->toString(array('path', 'query', 'fragment')); // get patch
			//trick for make compatible with the url encoded by encodeURIComponent()
			$revert = array(
					'%21'=>'!', '%2A'=>'*', '%27'=>"'", '%28'=>'(', '%29'=>')',
					'%2F' => '/', '%2C' => ',', '%3F' => '?', '%3D' => '=', '%26' => '&', '%3A' => ':'
				);
			$url = strtr(rawurlencode($url), $revert);
			header('Ax-Location: ' . $url);

		}
		//compare templatestyles
		if($this->params->get('useDiffStyle', 1) && isset($_SERVER['HTTP_AX_CURENT_STYLE']))
		{
			$oldStyle = explode(',', base64_decode($_SERVER['HTTP_AX_CURENT_STYLE']));
			$nedTemplate = $app->getTemplate(true);
			//refresh page if requested other template
			if ($nedTemplate->id != $oldStyle[1])
			{
				$this->sendReload();
			}
		}
	}


	/**
	 * check whether we allowed use FullAJAX script
	 * and assign new template for AJAX answer, if need
	 */
	 public function onAfterDispatch()
	 {
		$app = JFactory::getApplication();
		$doc = JFactory::getDocument();

		if ($app->isAdmin())
		{
			return;
		}

		$menu = $app->getMenu();
		$menu_active = $menu->getActive();

		//check whether we allowed connect fullajax script
		$items_noax = (array) $this->params->get('menu_items_no_ax_load', array());
		if(!($doc instanceof JDocumentHtml)
			|| $app->isAdmin()
			|| ($menu_active && in_array($menu_active->id, $items_noax))
			|| ($app->input->get('option') === 'com_search' && $app->input->get('type') === 'json')
			|| ($app->input->get('option') === 'com_content' && $app->input->get('print'))
			|| ($app->input->get('option') === 'com_mailto' && $app->input->get('view') === 'mailto')
			|| $app->input->get('layout')  === 'edit' //disable on edit, for prevernt some errors with the editor
			|| $app->input->get('layout')  === 'modal'
		){
			// Need to refresh the page if the request was from fullajax
			// to prevent display full site inside block
			if ($this->nedAjaxRespons)
			{
				$this->sendReload();
				return;
			}
			$this->nedAjaxRespons = false;
		}
		elseif($this->nedAjaxRespons)
		{
			//render only part of page if ajax request
			//save default template
			$this->defTemplate = $app->getTemplate();

			$app->set('plg_fullajax_ajaxrequest', true);

			//change default template
			switch ($this->params->get('positionupd', 3))
			{
				case 3:
					break;
				case 2:
					$app->input->set('plg_fullajax_contid', $this->params->get('contid','content'));
					$app->setTemplate('fullajax_tmpl');
					// joomla 3.2 fix
					if (version_compare(JVERSION, '3.2', 'ge'))
					{
						$template = $app->getTemplate(true);
						$app->set('theme', $template->template);
						$app->set('themeParams', $template->params);
					}
					break;
				case 1:
				default:
					$app->setTemplate('system');
					// joomla 3.2 fix
					if (version_compare(JVERSION, '3.2', 'ge'))
					{
						$template = $app->getTemplate(true);
						$app->set('theme', $template->template);
						$app->set('themeParams', $template->params);
					}
					break;
			}

			//add active menu items, for check active by id: class="item-ID"
			$menu_active_tree = $menu_active ? array_reverse($menu_active->tree) : array();
			//check if alias exist
			foreach ($menu->getItems('type', 'alias') as $item)
			{
				if ($item->params->get('aliasoptions') == $menu_active->id)
				{
					$menu_active_tree[] = $item->id;
				}
			}
			$menu_active_tree = json_encode($menu_active_tree);
			$doc->addScriptDeclaration('var fullAjaxMItems = ' . $menu_active_tree . ';');
		}
		else
		{
			//we allow add own JavaScript
			//@see: onBeforeRender()
			$this->axJsAllowed = true;
		}
	}

	/**
	 * connect FullAJAX scripts,
	 * and add wrappers for the positions that need update if position update == auto
	 */
	 public function onBeforeRender()
	 {
		$app = JFactory::getApplication();
		$doc = JFactory::getDocument();
		$positionupd = $this->params->get('positionupd', 3);

		//add own JS if allowed
		if ($this->axJsAllowed)
		{
			// fullAjax script
			JHtml::_('script', 'plg_system_addfullajax/fullajax.min.js', false, true);

			// add jQuery that ca be used for animation
			JHtml::_('jquery.framework');

			// fullAjax configuration
			$doc->addScriptDeclaration("/*--- AddFullAJAX ---*/\n" . $this->getJsData());

			if($positionupd == 1)
			{
				//add fullajax Model2Blocks configuration for update block
				$markers = $this->parsePositionParams();
				if (!empty($markers))
				{
					$markers = array_unique(array_values($markers));
					$markers[] = $this->params->get('contid', 'content');
					$this->addModel2Blocks($markers);
				}
			}
		}

		//if  update module == automatic
		if ($app->isSite() && $positionupd == 3)
		{
			//get template info
			$has_get = method_exists($doc, 'get');
			$tags = $has_get ? $doc->get('_template_tags') : $this->getValue($doc, '_template_tags');
			$tmpl = $has_get ? $doc->get('_template') : $this->getValue($doc, '_template');

			// Show warning if empty
			if(empty($tags) || empty($tmpl))
			{
				$app->enqueueMessage('AddFullAJAX error: Automatic positions update does not supports for a current template!', 'error');
				return;
			}

			//allowed positions
			$positions = (array) $this->params->get('allowed_positions', array() );
			$positions[] = ''; //'' - empty for 'message' and 'component'

			$patterns = array();
			$replacer = array();
			$markers = array();
			$tmpl_new = '';

			if ($this->nedAjaxRespons)
			{
				$tmp = '<body>';
				//colect positions that need update
				//and add Model2Blocks markers
				foreach ($tags as $jdoc => $data){
					if ($data['type'] != 'head' && in_array($data['name'], $positions))
					{
						//add markers. Use 'name' for positions, 'type' - for message and component
						$name = $data['name'] ? $data['name'] : $data['type'];
						$tmp .= '<!-- :ax:flax-'. $name .':begin: //-->'.$jdoc.'<!-- :ax:flax-' . $name . ':end: //-->';
					}
				}
				$tmp .= '</body>';

				//clean <body> and insert only position that we need update
				//in some cases regexp here can cause PHP Segmentation fault (11)
				//$tmpl_new = preg_replace('#<body.*>(.|\n)*</body>#i', $tmp, $tmpl);
				$body_start = stripos($tmpl, '<body');
				$body_end = stripos($tmpl, '</body>') + 7;

				$tmpl_new = substr_replace($tmpl, $tmp, $body_start, $body_end - $body_start);
			}
			elseif($this->axJsAllowed)
			{
				//add wrappers for a position update using FullAJAX Model2Blocks
				foreach ($tags as $jdoc => $data){
					if ($data['type'] != 'head'  && in_array($data['name'], $positions) )
					{
						//add markers(id`s) . Use 'name' for positions, 'type' - for message and component
						$name = $data['name'] ? $data['name'] : $data['type'];
						$patterns[] = $jdoc;
						$replacer[] = '<div id="flax-'. $name .'" class="flax-wrapper">'.$jdoc.'</div>';
						$markers[] = 'flax-'. $name;
					}
				}
				$this->addModel2Blocks($markers);
				$tmpl_new = str_replace($patterns, $replacer, $tmpl);
			}

			if ($tmpl_new)
			{
				method_exists($doc, 'set') ? $doc->set('_template', $tmpl_new) : $this->setValue($doc, '_template', $tmpl_new);
			}
		}

	}

	/**
	 * render the positions for AJAX answer if position update == semiautomatic
	 */
	public function onAfterRender()
	{
		$app = JFactory::getApplication();
		$positionupd = $this->params->get('positionupd', 3);

		if ($this->nedAjaxRespons
				&& $positionupd != 2
				&& $positionupd != 3
		){
			$doc = JFactory::getDocument();
			$pos = '';
			//render modules if  update module == semiautomatic
			$posParams = $this->parsePositionParams();
			if($positionupd == 1 && !empty($posParams))
			{
				// Render modules
				$posInTemplate = $this->parseTemplate($posParams);
				$app->setTemplate($this->defTemplate);
				// joomla 3.2 fix
				if (version_compare(JVERSION, '3.2', 'ge'))
				{
					$template = $app->getTemplate(true);
					$app->set('theme', $template->template);
					$app->set('themeParams', $template->params);
				}
				$renderer = $doc->loadRenderer('modules');
				$blocks = array_unique(array_values($posParams));
				$positions = array();
				$m = 0;
				foreach ($blocks as $block) {//render blocks
					$positions[$m]  = '<!-- :ax:'.trim($block).':begin: //-->';
					foreach ($posInTemplate as $p ) {
						if ($p['block'] == $block)
						{
							//render modules in block
							$positions[$m] .= $renderer->render($p['name'], $p['attribs'], null);
						}
					}
					$positions[$m] .= '<!-- :ax:'.trim($block).':end: //-->';
					$m++;
				}
				$pos = implode(' ',$positions);

			}

			// JResponse is Deprecated
			$body = method_exists($app, 'getBody') ? $app->getBody() : JResponse::getBody();

			// remove system css
			$body = preg_replace("/<link.*href=([\"']([^\"]+(general|template|template_rtl)\.css.*?)[\"']).*\/>/i", '', $body);
			// add markers where shows content
			$body = preg_replace('/<body.*>/i', '<body>  <!-- :ax:'.$this->params->get('contid','content').':begin: //-->', $body);
			$body = str_ireplace('</body>','<!-- :ax:'.$this->params->get('contid','content').':end: //--> '.$pos.'</body>', $body);

			// JResponse is Deprecated
			method_exists($app, 'setBody') ? $app->setBody($body) : JResponse::setBody($body);
 		}

 		return true;

	}


	/**
	 * Generate JavaScript for FullAjax configuration
	 * @return string
	 */
	protected function getJsData()
	{
		$app = JFactory::getApplication();
		$templ = $app->getTemplate(true);
		$content_id = ($this->params->get('positionupd', 3) != 3) ? $this->params->get('contid','content') : 'flax-component';
		$cnfg_data  = "var fullAjaxId = '" . $content_id . "';\n";
		$cnfg_data .= "var fullAjaxBase = '".rtrim(JUri::base(),'/')."';\n";
		$cnfg_data .=  $this->params->get('cnfg_data',"
FLAX.Filter.add({url:'/', id:fullAjaxId});
FLAX.Filter.add({query:['task=weblink','task=profile','task=user.login','task=user.logout','task=article.edit'],  type:'nowrap'});
FLAX.Filter.on('beforewrap', function(o) {
 var id = o.el.getAttribute('id');
 var regExt = /.+\.(jpg|jpeg|gif|png|mp3|mp4|ogg|ogv|webm|pdf|txt|odf|ods)$/i;
 if(id == ('login-form') || id == ('form-login') || (o.el.href && (regExt.test(o.el.href) || o.el.href.indexOf('#') != -1))){return false;}
});
FLAX.Default.sprt_url = '!';
FLAX.linkEqual['!ax!'+fullAjaxId+'!'] = 'ajx';
FLAX.linkEqual['[~q~]'] = '?';
//FLAX.directLink();
");
		//the filters for ignore menu item
		$menu_items_ignor = $this->params->get('menu_items_ignor', array());
		$items_noax = $this->params->get('menu_items_no_ax_load', array());
		if(!empty($menu_items_ignor) || !empty($items_noax))
		{
			$links = $this->getIgnMenuIt(array_merge($menu_items_ignor, $items_noax));
			if(!empty($links['url']))
			{
				$cnfg_data .= "\nFLAX.Filter.add({url:['" . implode('\',\'', $links['url']). "'], type:'nowrap'});";
			}
			elseif (!empty($links['query']))
			{
				$cnfg_data .= "\nFLAX.Filter.add({query:['" . implode('\',\'', $links['query']). "'], type:'nowrap'});";
			}
		}

		//if use diferent styles
		if($this->params->get('useDiffStyle', 1))
		{
			$cnfg_data .= '
FLAX.Html.onall( \'beforerequest\', function(o){
 if(!o.options.headers){o.thread.setOptions({headers:{\'Ax-Curent-Style\':\''.base64_encode($templ->template.','.$templ->id).'\'}});}
 else{o.options.headers[\'Ax-Curent-Style\'] = \''.base64_encode($templ->template.','.$templ->id).'\';}
});';
		}
		//usefull thing if need tell client for reload page, via JResponse::setHeader('Ax-Action', 'reload');
		//and hack for XMLHttpRequest redirect http://www.w3.org/TR/XMLHttpRequest/#the-send-method
		$cnfg_data .= "\nFLAX.Html.onall('response', function(o){
 if(o.xhr.getResponseHeader){
  var axL = o.xhr.getResponseHeader('Ax-Location'), axAc = o.xhr.getResponseHeader('Ax-Action');
  if(axL && o.url != axL) o.options.url = o.url = axL;
  if(axL && axAc == 'reload') location = axL;
 }
});";
		//disable userside caching
		if($this->params->get('userCache', 1) == 0)
		{
			$cnfg_data .= "\nFLAX.Default.HAX_ANTICACHE = 1;FLAX.Default.USE_HISTORY_CACHE = 0;";
		}
		//disble using HTML5 history API
		if($this->params->get('useHTML5', 1) == 0)
		{
			$cnfg_data .= "\nFLAX.Default.USE_HTML5_HISTORY = 0;";
		}
		//add some hack if Google Analytics
		if($this->params->get('useGA', 0) == 1)
		{
			$cnfg_data .= "\nFLAX.Html.onall('load', function(o){ _gaq.push(['_trackPageview', o.url]); });";
		}
		//scroll up after each request
		if($this->params->get('scrlUp', 1) == 1)
		{
			//for nice scroll ;)
			$cnfg_data .= "\n".'FLAX.Html.onall("response", function(){jQuery("html, body").animate({scrollTop: 0}, 300);});';
		}
		//enable autocheck active menu item
		if($this->params->get('checkmenuit', 1))
		{
			$cnfg_data .= "\n/*--- Change active menu item ---*/";

			//check active by item id: class="item-ID"
			$menuClass = trim($this->params->get('menuClass', '*'));
			$searchOldActiv = array();
			$searchNewAcriv = array();
			//build js selectors
			if($menuClass && $menuClass != '*')
			{
				$menuSelectors = array_unique(explode(',', $menuClass));
				foreach($menuSelectors as $sel){
					if($sel = trim($sel))
					{
						$searchOldActiv[] = $sel . ' .active';
						$searchOldActiv[] = $sel . ' .current';
						$searchNewAcriv[] = $sel . ' li.item-\'+it';
					}
				}
			}
			else
			{
				$searchOldActiv[] = 'ul .active, ul .current';
				$searchNewAcriv[] = 'ul li.item-\'+it';
			}

			$cnfg_data .= "
var fullAjaxMItems = [];
FLAX.Html.onall('load', function(){
 jQuery('" . implode(',', $searchOldActiv) . "').removeClass('active current');
 for(var i = 0, l = fullAjaxMItems.length; i < l; i++) {
  var it = fullAjaxMItems[i];
  var e = jQuery('" . implode('+\',', $searchNewAcriv) . ");
  if(e.length){var c = i == 0 ? 'current active' : 'active'; e.addClass(c);".
  			(($this->params->get('checkmenuit_active_for_a', 0)) ? "e.children('a').addClass(c);" : '')
  ."}
 };
});";
		}

		if($this->params->get('on_anim', 1)){
			$cnfg_data .= "\n/*--- FX ---*/\n". $this->params->get('anim_data',"
var fullAjaxGif = jQuery('<img/>',{
  'id':'fullAjaxGif','alt':'Loading...',
  'src':fullAjaxBase + '/media/plg_system_addfullajax/images/ajax-loader.gif',
  'style': 'position:absolute;left:50%;top:40%;z-index:800;'
});
FLAX.Effect.add({id:fullAjaxId, start: function(id, request){
  var content = jQuery('#'+fullAjaxId);
  if (!jQuery('#fullAjaxGif').length){fullAjaxGif.insertBefore(content);};
  content.stop().animate({opacity:0},1000,request());
 },end: function(id){
  var i = jQuery('#fullAjaxGif');
  if (i.length){i.remove();};
  jQuery('#'+fullAjaxId).stop().animate({opacity: 1},800);
 }
});
");
		}

		//for debuging
		if($this->params->get('debug_ajax', 0) == 1)
		{
			$cnfg_data .= "\nFLAX.Default.DEBUG_AJAX=1;";
		}
		if($this->params->get('debug_script', 0) == 1)
		{
			$cnfg_data .= "\nFLAX.Default.DEBUG_SCRIPT=1;";
		}
		if($this->params->get('debug_link', 0) == 1)
		{
			$cnfg_data .= "\nFLAX.Default.DEBUG_LINK=1;";
		}
		if($this->params->get('debug_style', 0) == 1)
		{
			$cnfg_data .= "\nFLAX.Default.DEBUG_STYLE=1;";
		}
		return $cnfg_data;

	}

	/**
	 * Generate and add JavaScript for FullAJAX Model2Blocks
	 * @param array $markers - array with block ids
	 */
	protected function addModel2Blocks($markers) {
		$markers = array_unique($markers);

		if ($js = json_encode(array_combine($markers, $markers)))
		{
			$js =  'FLAX.Model2Blocks[fullAjaxId] = '. $js .';';
			JFactory::getDocument()->addScriptDeclaration($js);
		}
	}

	/**
	 * Generate menu item for no wrap
	 * @param array $itemids - id`s of ignored menu items
	 *
	 * @return array
	 */
	protected function getIgnMenuIt( array $itemids)
	{
		$links = array();
		$isSef = JApplicationSite::getRouter()->getMode();

		foreach ($itemids as $id) {
			$links[] = $isSef ? JRoute::_('index.php?Itemid=' . $id) : 'Itemid=' . $id;
		}

		// Return array for url or query
		if ($isSef)
		{
			return array('url' => array_unique($links));
		}
		else
		{
			return array('query' => array_unique($links));
		}
	}

	/**
	 * Parse parameters for positions.
	 * Example params for three positions: positions:id1|position2:id1|position3:id2
	 */
	protected function parsePositionParams()
	{
		$positions_src = explode('|', $this->params->get('posParams',''));
		if(empty($positions_src[0]))
		{
			return array();
		}

		$posParams = array();
		foreach ($positions_src as $pos) {
			$tmp = null;
			$tmp = explode(':',trim($pos));
			if(count($tmp) == 2){
				$posParams[trim($tmp[0])]=trim($tmp[1]);
			}
		}

		return $posParams;
	}

	/**
	 * Parse a document template and find modules position
	 * @param   array   $posParams - contains info about position that need render
	 *
	 * @return	The parsed contents of the template
	 */
	protected function parseTemplate($posParams)
	{
		$matches = array();
		$posInTemplate = array();
		$tmplFile = file_get_contents(JPATH_THEMES.'/'.$this->defTemplate.'/index.php');
#		if (preg_match_all('#<jdoc:include\ type="([^"]+)" (.*)\/>#iU', $tmplFile, $matches))
		if (preg_match_all('#<jdoc:include\ type="(module|modules)" (.*)\/>#iU', $tmplFile, $matches))
		{
			$count = count($matches[1]);
			$needPos = array_keys($posParams);

			for ($i = 0; $i < $count; $i++)
			{
				$attribs = JUtility::parseAttributes($matches[2][$i]);
				$type  = $matches[1][$i];
				$name  = isset($attribs['name']) ? $attribs['name'] : null;
				//return info only for a positions that we need
				if(in_array($name, $needPos))
				{
					unset($needPos[array_search($name, $needPos)]);
					$posInTemplate[$i] = array('block' => $posParams[$name],'type' => $type, 'name' => $name, 'attribs' => $attribs);
				}

			}
		}
		return $posInTemplate;
	}

	/**
	 *  send command to client for refresh page
	 */
	protected function sendReload()
	{
		$url = JFactory::getURI()->toString(); // get link
		JResponse::setHeader('Ax-Action', 'reload'); // send command to reload
		JResponse::sendHeaders();
		echo '<div><p>Wait a moment! Reloading ...</p><p>Or click <a ax:wrap="0" href="'.$url.'">here</a>...</p></div>';
		JFactory::getApplication()->close();
	}

	/**
	 * getValue() and setValue() need for j3.0 cause get() set() is removed there
	 */

	/**
	 * Method that gets a protected or private property in a class by relfection
	 * @param   object  $object
	 * @param   string  $propertyName
	 *
	 * @return  mixed  The value of the property.
	 */
	protected function getValue($object, $propertyName)
	{
		$refl = new ReflectionClass($object);
		$property = $refl->getProperty($propertyName);
		$property->setAccessible(true);
		return $property->getValue($object);
	}

	/**
	 * Method that sets a protected or private property in a class by relfection
	 * @param   object  $object
	 * @param   string  $propertyName
	 * @param   mixed   $value
	 *
	 * @return  void
	 */
	protected function setValue($object, $propertyName, $value)
	{
		$refl = new ReflectionClass($object);
		$property = $refl->getProperty($propertyName);
		$property->setAccessible(true);
		$property->setValue($object, $value);
	}

}

