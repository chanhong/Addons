<?php if (!defined('APPLICATION')) exit();

// 2.0.4 - mosullivan:
// Removed deprecated function call. 
// Corrected css reference. 
// Fixed a bug that caused emoticons to not open the dropdown when editing.
// Cleaned up plugin to reference itself properly, allowing for multiple emotify's on a single page to work together.
/**
 * Note: Added jquery events required for proper display/hiding of emoticons
 * as write & preview buttons are clicked on forms in Vanilla 2.0.14. These
 * are necessary in order for this plugin to work properly.
 */

class EmotifyPlugin implements Gdn_IPlugin {
   
   public function AssetModel_StyleCss_Handler($sender) {
      $sender->AddCssFile('emotify.css', 'plugins/Emotify');
   }
	
    /**
    * Disable Emoji sets.
    */
    public function Gdn_Dispatcher_AfterAnalyzeRequest_Handler() {
        SaveToConfig('Garden.EmojiSet', 'none', false);
    }
	
	/**
	 * Replace emoticons in comments.
	 */
	public function Base_AfterCommentFormat_Handler($sender) {
		if (!C('Plugins.Emotify.FormatEmoticons', TRUE))
			return;

		$object = $sender->EventArguments['Object'];
		$object->FormatBody = $this->DoEmoticons($object->FormatBody);
		$sender->EventArguments['Object'] = $object;
	}
	
	public function DiscussionController_Render_Before($sender) {
		$this->_EmotifySetup($sender);
	}

	/**
	 * Return an array of emoticons.
	 */
	public static function GetEmoticons() {
		return [
			':)]' => '100',
			';))' => '71',
			':)>-' => '67',
			':)&gt;-' => '67',
			':))' => '21',
			':)' => '1',
			':(|)' => '51',
			':((' => '20',
			':(' => '2',
			';)' => '3',
			';-)' => '3',
			':D' => '4',
			':-D' => '4',
			';;)' => '5',
			'>:D<' => '6',
			'&gt;:D&lt;' => '6',
			':-/' => '7',
			':/' => '7',
			':x' => '8',
			':X' => '8',
			':\">' => '9',
			':\"&gt;' => '9',
			':P' => '10',
			':p' => '10',
         '<:-P' => '36',
			':-p' => '10',
			':-P' => '10',
			':-*' => '11',
			':*' => '11',
			'=((' => '12',
			':-O' => '13',
			':O)' => '34',
			':O' => '13',
			'X(' => '14',
			':>' => '15',
			':&gt;' => '15',
			'B-)' => '16',
			':-S' => '17',
			'#:-S' => '18',
			'#:-s' => '18',
			'>:)' => '19',
			'>:-)' => '19',
			'&gt;:)' => '19',
			'&gt;:-)' => '19',
			':-((' => '20', 
         ':-(' => '2',
			":'(" => '20',
			":'-(" => '20',
			':-))' => '21',
			':-)' => '1',
			':|' => '22',
			':-|' => '22',
			'/:)' => '23',
			'/:-)' => '23',
			'=))' => '24',
			'O:-)' => '25',
			'O:)' => '25',
			':-B' => '26',
			'=;' => '27',
			'I-)' => '28',
			'8-|' => '29',
			'L-)' => '30',
			':-&' => '31',
			':-&amp;' => '31',
			':0&amp;' => '31',
			':-$' => '32',
			'[-(' => '33',
			'8-}' => '35',
			'&lt;:-P' => '36',
			'(:|' => '37',
			'=P~' => '38',
			':-??' => '106',
			':-?' => '39',
			'#-o' => '40',
			'#-O' => '40',
			'=D>' => '41',
			'=D&gt;' => '41',
			':-SS' => '42',
			':-ss' => '42',
			'@-)' => '43',
			':^o' => '44',
			':-w' => '45',
			':-W' => '45',
			':-<' => '46',
			':-&lt;' => '46',
			'>:P' => '47',
			'>:p' => '47',
			'&gt;:P' => '47',
			'&gt;:p' => '47',
//			'<):)' => '48',
//			'&lt;):)' => '48',
			':@)' => '49',
			'3:-O' => '50',
			'3:-o' => '50',
			'~:>' => '52',
			'~:&gt;' => '52',
//			'@};-' => '53',
//			'%%-' => '54',
//			'**==' => '55',
//			'(~~)' => '56',
			'~O)' => '57',
			'*-:)' => '58',
			'8-X' => '59',
//			'=:)' => '60',
			'>-)' => '61',
			'&gt;-)' => '61',
			':-L' => '62',
			':L' => '62',
			'[-O<' => '63',
			'[-O&lt;' => '63',
			'$-)' => '64',
			':-\"' => '65',
			'b-(' => '66',
			'[-X' => '68',
			'\\:D/' => '69',
			'>:/' => '70',
			'&gt;:/' => '70',
//			'o->' => '72',
//			'o-&gt;' => '72',
//			'o=>' => '73',
//			'o=&gt;' => '73',
//			'o-+' => '74',
//			'(%)' => '75',
			':-@' => '76',
			'^:)^' => '77',
			':-j' => '78',
//			'(*)' => '79',
			':-c' => '101',
			'~X(' => '102',
			':-h' => '103',
			':-t' => '104',
			'8->' => '105',
			'8-&gt;' => '105',
			'%-(' => '107',
			':o3' => '108',
			'X_X' => '109',
			':!!' => '110',
			'\\m/' => '111',
			':-q' => '112',
			':-bd' => '113',
			'^#(^' => '114',
			':bz' => '115',
			':ar!' => 'pirate'
//			'[..]' => 'transformer'
		];
	}
	
	/**
	 * Replace emoticons in comment preview.
	 */
	public function PostController_AfterCommentPreviewFormat_Handler($sender) {
		if (!C('Plugins.Emotify.FormatEmoticons', TRUE))
			return;
		
		$sender->Comment->Body = $this->DoEmoticons($sender->Comment->Body);
	}
	
	public function PostController_Render_Before($sender) {
		$this->_EmotifySetup($sender);
	}
   
   public function NBBCPlugin_AfterNBBCSetup_Handler($sender, $args) {
//      $BBCode = new BBCode();
      $bBCode = $args['BBCode'];
      $bBCode->smiley_url = SmartAsset('/plugins/Emotify/design/images');
      
      $smileys = [];
      foreach (self::GetEmoticons() as $text => $filename) {
         $smileys[$text]= $filename.'.gif';
      }
      
      $bBCode->smileys = $smileys;
   }
	
	/**
	 * Thanks to punbb 1.3.5 (GPL License) for this function - ported from their do_smilies function.
	 */
	public static function DoEmoticons($text) {
		$text = ' '.$text.' ';
		$emoticons = EmotifyPlugin::GetEmoticons();
		foreach ($emoticons as $key => $replacement) {
			if (strpos($text, $key) !== FALSE)
				$text = preg_replace(
					"#(?<=[>\s])".preg_quote($key, '#')."(?=\W)#m",
					'<span class="Emoticon Emoticon' . $replacement . '"><span>' . $key . '</span></span>',
					$text
				);
		}

		return substr($text, 1, -1);
	}

	/**
	 * Prepare a page to be emotified.
	 */
	private function _EmotifySetup($sender) {
		$sender->AddJsFile('emotify.js', 'plugins/Emotify');  
		// Deliver the emoticons to the page.
      $emoticons = [];
      foreach ($this->GetEmoticons() as $i => $gif) {
         if (!isset($emoticons[$gif]))
            $emoticons[$gif] = $i;
      }
      $emoticons = array_flip($emoticons);

		$sender->AddDefinition('Emoticons', base64_encode(json_encode($emoticons)));
	}
	
	public function Setup() {
		//SaveToConfig('Plugins.Emotify.FormatEmoticons', TRUE);
		SaveToConfig('Garden.Format.Hashtags', FALSE); // Autohashing to search is incompatible with emotify
	}
	
}
