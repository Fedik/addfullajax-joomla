<?php
/**
 * @version		2012.06.03 (0.8)
 * @package Add FullAjax for Joomla 2.5
 * @author  Fedik
 * @email	getthesite@gmail.com
 * @link    http://www.getsite.org.ua
 * @license	GNU/GPL http://www.gnu.org/licenses/gpl.html
 */
defined( '_JEXEC' ) or die( 'Get lost?' );

//JFormHelper::loadFieldClass('groupedlist');
JFormHelper::loadFieldClass('list');

// Import the com_menus helper.
require_once realpath(JPATH_ADMINISTRATOR . '/components/com_modules/helpers/modules.php');

/**
 * Supports an HTML select list of positions
 */
class JFormFieldPositions extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 */
	public $type = 'Positions';

	/**
	 * Method to get the field option
	 *
	 * @return  array  The field option objects as a nested array
	 */
	protected function getOptions()
	{
		// Initialize variables.
		$options = array();
		$clientId = 0;

		//get positions
		$positions_array = ModulesHelper::getPositions($clientId);
		//$templates = ModulesHelper::getTemplates(0);

		$options = array_merge(parent::getOptions(), $positions_array);

		return $options;

	}
}