<?php
/**
 * Plugin element to render facebook open graph like button
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.facebooklike
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

require_once JPATH_SITE . '/components/com_fabrik/models/element.php';

/**
 * Plugin element to render facebook open graph like button
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.facebooklike
 * @since       3.0
 */

class plgFabrik_ElementFblike extends plgFabrik_Element
{

	/**
	 * Does the element have a label
	 *
	 * @var bool
	 */
	protected $hasLabel = false;

	/**
	 * Db table field type
	 *
	 * @var  string
	 */
	protected $fieldDesc = 'INT(%s)';

	/**
	 * Db table field size
	 *
	 * @var  string
	 */
	protected $fieldLength = '1';


	/**
	 * Shows the data formatted for the list view
	 *
	 * @param   string  $data      elements data
	 * @param   object  &$thisRow  all the data in the lists current row
	 *
	 * @return  string	formatted value
	 */

	public function renderListData($data, &$thisRow)
	{
		$params = $this->getParams();
		$meta = array();
		$config = JFactory::getConfig();
		$ex = $_SERVER['SERVER_PORT'] == 80 ? 'http://' : 'https://';

		// $$$ rob no need to get other meta data as we are linking to the details which contains full meta info on what it is
		// you are liking
		$meta['og:url'] = $ex . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
		$meta['og:site_name'] = $config->getValue('sitename');
		$meta['fb:admins'] = $params->get('fblike_opengraph_applicationid');
		$str = FabrikHelperHTML::facebookGraphAPI($params->get('opengraph_applicationid'), $params->get('fblike_locale', 'en_US'), $meta);

		// In list view we link to the detailed record not the list view itself
		// means form or details view must be viewable by the user
		$url = $this->getListModel()->linkHref($this, $thisRow);
		return $str . $this->_render($url);
		return parent::renderListData($data, $thisRow);
	}

	/**
	 * Draws the html form element
	 *
	 * @param   array  $data           to preopulate element with
	 * @param   int    $repeatCounter  repeat group counter
	 *
	 * @return  string	elements html
	 */

	public function render($data, $repeatCounter = 0)
	{
		$params = $this->getParams();
		$meta = array();
		$formModel = $this->getForm();
		$config = JFactory::getConfig();
		$ex = $_SERVER['SERVER_PORT'] == 80 ? 'http://' : 'https://';
		$map = array('og:title' => 'fblike_title', 'og:type' => 'fblike_type', 'og:image' => 'fblike_image',
			'og:description' => 'fblike_description', 'og:street-address' => 'fblike_street_address', 'og:locality' => 'fblike_locality',
			'og:region' => 'fblike_region', 'og:postal-code' => 'fblike_postal_code', 'og:country-name' => 'fblike_country',
			'og:email' => 'fblike_email', 'og:phone_number' => 'fblike_phone_number', 'og:fax_number' => 'fblike_fax_number');

		foreach ($map as $k => $v)
		{
			$elid = $params->get($v);
			if ($elid != '')
			{
				$el = $formModel->getElement($elid, true);
				if (is_object($el))
				{
					$name = $el->getFullName(false, true, false);
					$v = JArrayHelper::getValue($data, $name);
					if ($k == 'og:image')
					{
						$v = $ex . $_SERVER['SERVER_NAME'] . $v;
					}
					if ($v !== '')
					{
						$meta[$k] = $v;
					}
				}
			}
		}

		$locEl = $formModel->getElement($params->get('fblike_location'), true);
		if ($locEl != '')
		{
			$loc = JArrayHelper::getValue($data, $locEl->getFullName(false, true, false));
			$loc = array_shift(explode(':', $loc));
			$loc = explode(",", $loc);
			if (count($loc) == 2)
			{
				$meta['og:latitude'] = $loc[0];
				$meta['og:longitude'] = $loc[1];
			}
		}
		$meta['og:url'] = $ex . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
		$meta['og:site_name'] = $config->getValue('sitename');
		$meta['fb:app_id'] = $params->get('fblike_opengraph_applicationid');
		$str = FabrikHelperHTML::facebookGraphAPI($params->get('fblike_opengraph_applicationid'), $params->get('fblike_locale', 'en_US'), $meta);
		$url = $params->get('fblike_url');

		// $$$tom placeholder option for URL params
		$w = new FabrikWorker;
		$url = $w->parseMessageForPlaceHolder($url, $data);
		return $str . $this->_render($url);
	}

	/**
	 * Render the button
	 *
	 * @param   string  $url  button url
	 *
	 * @return string
	 */

	protected function _render($url)
	{
		$params = $this->getParams();
		if ($url !== '')
		{
			if (!strstr($url, COM_FABRIK_LIVESITE))
			{
				// $$$ rob doesnt work with sef urls as $url already contains site folder.
				// $url = COM_FABRIK_LIVESITE.$url;
				$ex = $_SERVER['SERVER_PORT'] == 80 ? 'http://' : 'https://';
				$url = $ex . $_SERVER['SERVER_NAME'] . $url;
			}
			$href = "href=\"$url\"";
		}
		else
		{
			$href = '';
		}

		$layout = $params->get('fblike_layout', 'standard');
		$showfaces = $params->get('fblike_showfaces', 0) == 1 ? 'true' : 'false';
		$width = $params->get('fblike_width', 300);
		$action = $params->get('fblike_action', 'like');
		$font = $params->get('fblike_font', 'arial');
		$colorscheme = $params->get('fblike_colorscheme', 'light');
		$str = "<fb:like $href layout=\"$layout\" show_faces=\"$showfaces\" width=\"$width\" action=\"$action\" font=\"$font\" colorscheme=\"$colorscheme\" />";
		return $str;
	}

	/**
	 * Returns javascript which creates an instance of the class defined in formJavascriptClass()
	 *
	 * @param   int  $repeatCounter  repeat group counter
	 *
	 * @return  string
	 */

	public function elementJavascript($repeatCounter)
	{
		$id = $this->getHTMLId($repeatCounter);
		$opts = $this->getElementJSOptions($repeatCounter);
		$opts = json_encode($opts);
		return "new FbLike('$id', $opts)";
	}

}
