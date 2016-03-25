<?php
/**
 * @package Add FullAjax for Joomla!
 * @author  Fedir Zinchuk
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

		//fix for labels float
		$doc->addStyleDeclaration('#attrib-advanced .control-label{clear:both;float:none;}
#attrib-advanced .controls{margin-left: 0;}');

		//couple javascript tricks
		JHtml::_('jquery.framework');
		JHtml::_('script', 'plg_system_addfullajax/field-jscsstricks.js', false, true);

		return ' ';

	}
}

