<?php
/**
 * @version	2013.11.16 (1.0)
 * @package Add FullAjax for Joomla!
 * @author  Fedik
 * @email	getthesite@gmail.com
 * @link    http://www.getsite.org.ua
 * @license	GNU/GPL http://www.gnu.org/licenses/gpl.html
 */
defined( '_JEXEC' ) or die( 'Get lost?' );


/**
 * Add couple tricks for Add FullAJAX configuration
 */
class JFormFieldJsCssTricks extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 */
	public $type = 'JsCssTricks';

	/**
	 * Method to get the field input markup.
	 * There no any input.
	 */
	protected function getInput()
	{
		return ' ';
	}

	/**
	 * Method to get the field label markup.
	 * Here is we add couple tricks ;)
	 */
	protected function getLabel()
	{
		$doc = JFactory::getDocument();
		$app = JFactory::getApplication();

		if (version_compare(JVERSION, '3.0', 'ge')) {
			$app->setUserState('editor.source.syntax', 'js');
		} else {
			//codemirror.php have a bug on line 154 so we use 'css' instead of 'js' ... hehehe
			//http://joomlacode.org/gf/project/joomla/tracker/?action=TrackerItemEdit&tracker_item_id=26623
			$app->setUserState('editor.source.syntax', 'css');
			//fix for labels float
			$doc->addStyleDeclaration('#jform_params_cnfg_data-lbl,#jform_params_anim_data-lbl{clear:both;float:none;}');
		}

		//couple javascript tricks
		JHTML::_('behavior.framework', true);

		$js_def = '/plugins/system/addfullajax/fields/jscsstricks.js';
		//js file by template
		$js_tmpl = '/plugins/system/addfullajax/fields/jscsstricks.'.$app->getTemplate().'.js';

		if (JFile::exists(JPATH_ROOT . $js_tmpl)) {
			$doc->addScript( JURI::root(true) . $js_tmpl);
		} else {
			$doc->addScript( JURI::root(true) . $js_def);
		}

		return ' ';

	}
}

