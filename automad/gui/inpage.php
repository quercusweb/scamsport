<?php 
/*
 *	                  ....
 *	                .:   '':.
 *	                ::::     ':..
 *	                ::.         ''..
 *	     .:'.. ..':.:::'    . :.   '':.
 *	    :.   ''     ''     '. ::::.. ..:
 *	    ::::.        ..':.. .''':::::  .
 *	    :::::::..    '..::::  :. ::::  :
 *	    ::'':::::::.    ':::.'':.::::  :
 *	    :..   ''::::::....':     ''::  :
 *	    :::::.    ':::::   :     .. '' .
 *	 .''::::::::... ':::.''   ..''  :.''''.
 *	 :..:::'':::::  :::::...:''        :..:
 *	 ::::::. '::::  ::::::::  ..::        .
 *	 ::::::::.::::  ::::::::  :'':.::   .''
 *	 ::: '::::::::.' '':::::  :.' '':  :
 *	 :::   :::::::::..' ::::  ::...'   .
 *	 :::  .::::::::::   ::::  ::::  .:'
 *	  '::'  '':::::::   ::::  : ::  :
 *	            '::::   ::::  :''  .:
 *	             ::::   ::::    ..''
 *	             :::: ..:::: .:''
 *	               ''''  '''''
 *	
 *
 *	AUTOMAD
 *
 *	Copyright (c) 2017-2020 by Marc Anton Dahmen
 *	http://marcdahmen.de
 *
 *	Licensed under the MIT license.
 *	http://automad.org/license
 */


namespace Automad\GUI;
use Automad\Core as Core;


defined('AUTOMAD') or die('Direct access not permitted!');


/**
 *	The InPage class provides all methods related to edit content directly in the page. 
 *
 *	@author Marc Anton Dahmen
 *	@copyright Copyright (c) 2017-2020 by Marc Anton Dahmen - <http://marcdahmen.de>
 *	@license MIT license - http://automad.org/license
 */

class InPage {
	
	
	/**
	 *  The constructor.
	 */
	
	public function __construct() {
		
		if (User::get()) {
			
			// Prepare text modules.
			Text::parseModules();
			
		}
		
	}
	
	
	/**
	 *  Process the page markup and inject all needed GUI markup if an user is logged in.
	 *      
	 *  @param string $str
	 *  @return string The processed $str
	 */
	
	public function createUI($str) {
		
		if (User::get()) {
			$str = $this->injectAssets($str);
			$str = $this->injectMarkup($str);
			$str = $this->processTemporaryEditButtons($str);
		}
		
		return $str;
		
	}
	
	
	/**
	 *	Inject GUI markup like bottom menu and modal dialogs.
	 *      
	 *  @param string $str
	 *  @return string The processed $str
	 */
	
	private function injectMarkup($str) {
		
		$urlGui = AM_BASE_INDEX . AM_PAGE_DASHBOARD;
		$urlData = $urlGui . '?' . http_build_query(array('context' => 'edit_page', 'url' => AM_REQUEST)) . '#' . Core\Str::sanitize(Text::get('btn_data'));
		$urlFiles = $urlGui . '?' . http_build_query(array('context' => 'edit_page', 'url' => AM_REQUEST)) . '#' . Core\Str::sanitize(Text::get('btn_files'));
		$urlSys = $urlGui . '?context=system_settings';
		$attr = 'class="am-inpage-menu-button" data-uk-tooltip';
		$request = AM_REQUEST;
		$logoSvg = file_get_contents(AM_BASE_DIR . '/automad/gui/svg/logo.svg');
		$btnData = Text::get('btn_data');
		$btnFiles = Text::get('btn_files');
		$sysTitle = Text::get('sys_title');
		$inpageEditTitle = Text::get('inpage_edit_title');
		$btnClose = Text::get('btn_close');
		$btnSave = Text::get('btn_save');

		$modalSelectImage = Components\Modal\SelectImage::render();
		$modalLink = Components\Modal\Link::render();

		$queryString = '';
		
		if (!empty($_SERVER['QUERY_STRING'])) {
			$queryString = $_SERVER['QUERY_STRING'];
		}
		
		$html = <<< HTML
				<div class="am-inpage">
					<div class="am-inpage-menubar">
						<div class="uk-button-group">
							<a href="$urlGui" class="am-inpage-menu-button">$logoSvg</a>
							<a href="$urlData" title="$btnData" $attr><i class="uk-icon-file-text-o"></i></a>
							<a href="$urlFiles" title="$btnFiles" $attr><i class="uk-icon-folder-open-o"></i></a>
							<a href="$urlSys" title="$sysTitle" $attr><i class="uk-icon-sliders"></i></a>
							<a href="#" class="am-drag-handle am-inpage-menu-button">
								<i class="uk-icon-arrows"></i>
							</a>
						</div>
					</div>
					<div id="am-inpage-edit-modal" class="uk-modal">
						<div class="uk-modal-dialog uk-modal-dialog-blank">
							<div class="uk-container uk-container-center">
								<form 
								class="uk-form uk-form-stacked uk-margin-top" 
								data-am-inpage-handler="${urlGui}?ajax=inpage_edit"
								>
									<div class="uk-modal-header">
										$inpageEditTitle
										<a href="#" class="uk-modal-close uk-close"></a>
									</div>
									<div class="uk-margin-bottom">
										<i class="uk-icon-file-text"></i>&nbsp;
										<span id="am-inpage-edit-modal-title"></span>
									</div>
									<input type="hidden" name="url" value="$request" />
									<input type="hidden" name="query" value="$queryString" />
									<div class="uk-modal-footer">
										<div class="uk-text-right uk-margin-large-bottom">
											<button type="button" class="uk-modal-close uk-button">
												<i class="uk-icon-close"></i>&nbsp; 
												$btnClose
											</button>
											<button type="submit" class="uk-button uk-button-success">
												<i class="uk-icon-check"></i>&nbsp;
												$btnSave
											</button>
										</div>
									</div>
								</form>
							</div>
						</div>
					</div>
					$modalSelectImage
					$modalLink
				</div>
HTML;
		
		return str_replace('</body>', Prefix::tags($html) . '</body>', $str);
		
	}
	
	
	/**
	 *	Add all needed assets for inpage-editing to the <head> element. 
	 *      
	 *  @param string $str
	 *  @return string The processed markup
	 */
	
	private function injectAssets($str) {
		
		$versionSanitized = Core\Str::sanitize(AM_VERSION);
		$assets = 	"\n" .
					'<!-- Automad GUI -->' . "\n" .
					'<link href="' . AM_BASE_URL . '/automad/gui/dist/libs.min.css?v=' . $versionSanitized . '" rel="stylesheet">' . "\n" .
					'<link href="' . AM_BASE_URL . '/automad/gui/dist/automad.min.css?v=' . $versionSanitized . '" rel="stylesheet">' . "\n" .
					'<script type="text/javascript" src="' . AM_BASE_URL . '/automad/gui/dist/libs.min.js?v=' . $versionSanitized . '"></script>' . "\n" .
					'<script type="text/javascript" src="' . AM_BASE_URL . '/automad/gui/dist/automad.min.js?v=' . $versionSanitized . '"></script>' . "\n" .
					// Cleanup window object by removing jQuery and UIkit.
					'<script type="text/javascript">$.noConflict(true);delete window.UIkit;delete window.UIkit2;</script>' . "\n" .
					'<!-- Automad GUI end -->' . "\n";
			
		// Check if there is already any other script tag and try to prepend all assets as first items.
		if (preg_match('/\<(script|link).*\<\/head\>/is', $str)) {
			return preg_replace('/(\<(script|link).*\<\/head\>)/is', $assets . "\n$1", $str);
		} else {
			return str_replace('</head>', $assets . "\n</head>", $str);
		}
		
	}
	
	
	/**
	 *  Inject a temporary markup for an edit button.
	 *      
	 *  @param string $value
	 *  @param string $key
	 *  @param object $Context
	 *  @return string The processed $value 
	 */
	
	public function injectTemporaryEditButton($value, $key, $Context) {
		
		// Only inject button if $key is no runtime var and a user is logged in.
		if (preg_match('/^\w/', $key) && User::get()) {
			$value .= 	AM_DEL_INPAGE_BUTTON_OPEN . 
						json_encode(array(
							'context' => $Context->get()->url, 
							'key' => $key
						), JSON_UNESCAPED_SLASHES) . 
						AM_DEL_INPAGE_BUTTON_CLOSE;
		}	
		
		return $value;
		
	}
	
	
	/**
	 * 	Process the temporary buttons to edit variable in the page. 
	 *  All invalid buttons (within tags and in links) will be removed.
	 *      
	 *  @param string $str
	 *  @return string The processed markup
	 */
	
	private function processTemporaryEditButtons($str) {
		
		// Remove invalid buttons.
		// Within HTML tags.	
		// Like <div data-attr="...">
		$str = preg_replace_callback('/\<[^>]+\>/is', function($matches) {
			return preg_replace('/' . Core\Regex::inPageEditButton() . '/is', '', $matches[0]);
		}, $str);
		
		// In head, script, links, buttons etc.
		// Like <head>...</head>
		$str = preg_replace_callback('/\<(a|button|head|script|select|textarea)\b.+?\<\/\1\>/is', function($matches) {
			return preg_replace('/' . Core\Regex::inPageEditButton() . '/is', '', $matches[0]);
		}, $str);
		
		// Enable valid buttons.
		$str = str_replace(
			array(AM_DEL_INPAGE_BUTTON_OPEN, AM_DEL_INPAGE_BUTTON_CLOSE), 
			array(
				Prefix::attributes(' <span class="am-inpage"><a href="#am-inpage-edit-modal" class="am-inpage-edit-button" data-uk-modal="{modal:false}" data-am-inpage-content=\''), 
				Prefix::attributes('\'><i class="uk-icon-pencil"></i>&nbsp;&nbsp;' . Text::get('btn_edit') . '</a></span>&nbsp;&nbsp;')
			), 
			$str
		);
		
		return $str;
		
	}
	
	
}
