<?php
/**
 * @brief		Collaboration collab (Software, Content, Social Group, Clan, Etc.)
 * @author		Kevin Carwile (http://www.linkedin.com/in/kevincarwile)
 * @copyright		(c) 2014 - Kevin Carwile
 * @package		Collaboration
 * @since		10 Dec 2014
 */

namespace IPS\collab;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 *  Collab menu
 */
class _Menu extends \IPS\Node\Model implements \IPS\Node\Permissions
{
	/**
	 * @brief	[ActiveRecord] Database Prefix
	 */
	public static $databasePrefix = 'menu_';
	
	/**
	 * @brief	[ActiveRecord] ID Database Column
	 */
	public static $databaseColumnId = 'id';

	/**
	 * @brief	[ActiveRecord] Database table
	 * @note	This MUST be over-ridden
	 */
	public static $databaseTable	= 'collab_menu';
		
	/**
	 * @brief	[ActiveRecord] Database ID Fields
	 */
	protected static $databaseIdFields = array();
	
	/**
	 * @brief	Bitwise keys
	 */
	protected static $bitOptions = array();

	/**
	 * @brief	[Node] Order Database Column
	 */
	public static $databaseColumnOrder = 'weight';
	
	/**
	 * @brief	[Node] Node Title
	 */
	public static $nodeTitle = 'collab_menu_items';

	/**
	 * @brief	[ActiveRecord] Multiton Store
	 * @note	This needs to be declared in any child classes as well, only declaring here for editor code-complete/error-check functionality
	 */
	protected static $multitons	= array();
	
	/**
	 *  Disable Copy Button
	 */	
	public $noCopyButton = TRUE;
	
	/**
	 * @brief	[Node] App for permission index
	 */
	public static $permApp = 'collab';
	
	/**
	 * @brief	[Node] Type for permission index
	 */
	public static $permType = 'collab_menu';

	/**
	 * @brief	[Node] Prefix string that is automatically prepended to permission matrix language strings
	 */
	public static $permissionLangPrefix = 'collab_menu_';

	/**
	 * @brief	The map of permission columns
	 */
	public static $permissionMap = array
	(
		'view'			=> 'view',
	);

	/**
	 * [Node] Does the currently logged in user have permission to add a child node to this node?
	 *
	 * @return	bool
	 */
	public function canAdd()
	{
		return FALSE;
	}
	
	/**
	 * [Node] Does the currently logged in user have permission to add aa root node?
	 *
	 * @return	bool
	 */
	public static function canAddRoot()
	{
		$collab = \IPS\collab\Application::activeCollab();
		
		return $collab->collabCan( 'editMenu' );	
	}
	
	/**
	 * [Node] Does the currently logged in user have permission to edit this node?
	 *
	 * @return	bool
	 */
	public function canEdit()
	{
		$collab = \IPS\collab\Application::activeCollab();
		
		return $collab->collabCan( 'editMenu' );	
	}
	
	/**
	 * [Node] Does the currently logged in user have permission to copy this node?
	 *
	 * @return	bool
	 */
	public function canCopy()
	{
		return FALSE;	
	}
	
	/**
	 * [Node] Does the currently logged in user have permission to delete this node?
	 *
	 * @return	bool
	 */
	public function canDelete()
	{
		$collab = \IPS\collab\Application::activeCollab();
		
		return $collab->collabCan( 'editMenu' );	
	}

	/**
	 * [Node] Does the currently logged in user have permission to edit permissions for this node?
	 *
	 * @return	bool
	 */
	public function canManagePermissions()
	{
		$collab = \IPS\collab\Application::activeCollab();
		
		return $collab->collabCan( 'editMenu' );	
	}
	
	/**
	 * [Node] Get Title
	 *
	 * @return	string|null
	 */
	protected function get__title()
	{
		return $this->title;
	}
	
	/**
	 * [Node] Set Title
	 *
	 * @return	string|null
	 */
	protected function set__title( $val )
	{
		$this->title = $val;
	}
	
	protected function get__description()
	{
		return $this->link;
	}
	
	/**
	 * [Node] Return the custom badge for each row
	 *
	 * @return	NULL|array		Null for no badge, or an array of badge data (0 => CSS class type, 1 => language string, 2 => optional raw HTML to show instead of language string)
	 */
	protected function get__badge()
	{	
		return NULL;
	}

	/**
	 * [Node] Add/Edit Form
	 *
	 * @param	\IPS\Helpers\Form	$form	The form
	 * @return	void
	 */
	public function form( &$form )
	{
		$collab 	= \IPS\collab\Application::activeCollab();
		$menuitem	= $this->id ? $collab->authObj( $this ) : $this;
		
		$icon_options = array
		(
			'' => '',
			'glass' => "&#xf000",
			'music' => "&#xf001",
			'search' => "&#xf002",
			'envelope-o' => "&#xf003",
			'heart' => "&#xf004",
			'star' => "&#xf005",
			'star-o' => "&#xf006",
			'user' => "&#xf007",
			'film' => "&#xf008",
			'th-large' => "&#xf009",
			'th' => "&#xf00a",
			'th-list' => "&#xf00b",
			'check' => "&#xf00c",
			'times' => "&#xf00d",
			'search-plus' => "&#xf00e",
			'search-minus' => "&#xf010",
			'power-off' => "&#xf011",
			'signal' => "&#xf012",
			'cog' => "&#xf013",
			'trash-o' => "&#xf014",
			'home' => "&#xf015",
			'file-o' => "&#xf016",
			'clock-o' => "&#xf017",
			'road' => "&#xf018",
			'download' => "&#xf019",
			'arrow-circle-o-down' => "&#xf01a",
			'arrow-circle-o-up' => "&#xf01b",
			'inbox' => "&#xf01c",
			'play-circle-o' => "&#xf01d",
			'repeat' => "&#xf01e",
			'refresh' => "&#xf021",
			'list-alt' => "&#xf022",
			'lock' => "&#xf023",
			'flag' => "&#xf024",
			'headphones' => "&#xf025",
			'volume-off' => "&#xf026",
			'volume-down' => "&#xf027",
			'volume-up' => "&#xf028",
			'qrcode' => "&#xf029",
			'barcode' => "&#xf02a",
			'tag' => "&#xf02b",
			'tags' => "&#xf02c",
			'book' => "&#xf02d",
			'bookmark' => "&#xf02e",
			'print' => "&#xf02f",
			'camera' => "&#xf030",
			'font' => "&#xf031",
			'bold' => "&#xf032",
			'italic' => "&#xf033",
			'text-height' => "&#xf034",
			'text-width' => "&#xf035",
			'align-left' => "&#xf036",
			'align-center' => "&#xf037",
			'align-right' => "&#xf038",
			'align-justify' => "&#xf039",
			'list' => "&#xf03a",
			'outdent' => "&#xf03b",
			'indent' => "&#xf03c",
			'video-camera' => "&#xf03d",
			'picture-o' => "&#xf03e",
			'pencil' => "&#xf040",
			'map-marker' => "&#xf041",
			'adjust' => "&#xf042",
			'tint' => "&#xf043",
			'pencil-square-o' => "&#xf044",
			'share-square-o' => "&#xf045",
			'check-square-o' => "&#xf046",
			'arrows' => "&#xf047",
			'step-backward' => "&#xf048",
			'fast-backward' => "&#xf049",
			'backward' => "&#xf04a",
			'play' => "&#xf04b",
			'pause' => "&#xf04c",
			'stop' => "&#xf04d",
			'forward' => "&#xf04e",
			'fast-forward' => "&#xf050",
			'step-forward' => "&#xf051",
			'eject' => "&#xf052",
			'chevron-left' => "&#xf053",
			'chevron-right' => "&#xf054",
			'plus-circle' => "&#xf055",
			'minus-circle' => "&#xf056",
			'times-circle' => "&#xf057",
			'check-circle' => "&#xf058",
			'question-circle' => "&#xf059",
			'info-circle' => "&#xf05a",
			'crosshairs' => "&#xf05b",
			'times-circle-o' => "&#xf05c",
			'check-circle-o' => "&#xf05d",
			'ban' => "&#xf05e",
			'arrow-left' => "&#xf060",
			'arrow-right' => "&#xf061",
			'arrow-up' => "&#xf062",
			'arrow-down' => "&#xf063",
			'share' => "&#xf064",
			'expand' => "&#xf065",
			'compress' => "&#xf066",
			'plus' => "&#xf067",
			'minus' => "&#xf068",
			'asterisk' => "&#xf069",
			'exclamation-circle' => "&#xf06a",
			'gift' => "&#xf06b",
			'leaf' => "&#xf06c",
			'fire' => "&#xf06d",
			'eye' => "&#xf06e",
			'eye-slash' => "&#xf070",
			'exclamation-triangle' => "&#xf071",
			'plane' => "&#xf072",
			'calendar' => "&#xf073",
			'random' => "&#xf074",
			'comment' => "&#xf075",
			'magnet' => "&#xf076",
			'chevron-up' => "&#xf077",
			'chevron-down' => "&#xf078",
			'retweet' => "&#xf079",
			'shopping-cart' => "&#xf07a",
			'folder' => "&#xf07b",
			'folder-open' => "&#xf07c",
			'arrows-v' => "&#xf07d",
			'arrows-h' => "&#xf07e",
			'bar-chart' => "&#xf080",
			'twitter-square' => "&#xf081",
			'facebook-square' => "&#xf082",
			'camera-retro' => "&#xf083",
			'key' => "&#xf084",
			'cogs' => "&#xf085",
			'comments' => "&#xf086",
			'thumbs-o-up' => "&#xf087",
			'thumbs-o-down' => "&#xf088",
			'star-half' => "&#xf089",
			'heart-o' => "&#xf08a",
			'sign-out' => "&#xf08b",
			'linkedin-square' => "&#xf08c",
			'thumb-tack' => "&#xf08d",
			'external-link' => "&#xf08e",
			'sign-in' => "&#xf090",
			'trophy' => "&#xf091",
			'github-square' => "&#xf092",
			'upload' => "&#xf093",
			'lemon-o' => "&#xf094",
			'phone' => "&#xf095",
			'square-o' => "&#xf096",
			'bookmark-o' => "&#xf097",
			'phone-square' => "&#xf098",
			'twitter' => "&#xf099",
			'facebook' => "&#xf09a",
			'github' => "&#xf09b",
			'unlock' => "&#xf09c",
			'credit-card' => "&#xf09d",
			'rss' => "&#xf09e",
			'hdd-o' => "&#xf0a0",
			'bullhorn' => "&#xf0a1",
			'bell' => "&#xf0f3",
			'certificate' => "&#xf0a3",
			'hand-o-right' => "&#xf0a4",
			'hand-o-left' => "&#xf0a5",
			'hand-o-up' => "&#xf0a6",
			'hand-o-down' => "&#xf0a7",
			'arrow-circle-left' => "&#xf0a8",
			'arrow-circle-right' => "&#xf0a9",
			'arrow-circle-up' => "&#xf0aa",
			'arrow-circle-down' => "&#xf0ab",
			'globe' => "&#xf0ac",
			'wrench' => "&#xf0ad",
			'tasks' => "&#xf0ae",
			'filter' => "&#xf0b0",
			'briefcase' => "&#xf0b1",
			'arrows-alt' => "&#xf0b2",
			'users' => "&#xf0c0",
			'link' => "&#xf0c1",
			'cloud' => "&#xf0c2",
			'flask' => "&#xf0c3",
			'scissors' => "&#xf0c4",
			'files-o' => "&#xf0c5",
			'paperclip' => "&#xf0c6",
			'floppy-o' => "&#xf0c7",
			'square' => "&#xf0c8",
			'bars' => "&#xf0c9",
			'list-ul' => "&#xf0ca",
			'list-ol' => "&#xf0cb",
			'strikethrough' => "&#xf0cc",
			'underline' => "&#xf0cd",
			'table' => "&#xf0ce",
			'magic' => "&#xf0d0",
			'truck' => "&#xf0d1",
			'pinterest' => "&#xf0d2",
			'pinterest-square' => "&#xf0d3",
			'google-plus-square' => "&#xf0d4",
			'google-plus' => "&#xf0d5",
			'money' => "&#xf0d6",
			'caret-down' => "&#xf0d7",
			'caret-up' => "&#xf0d8",
			'caret-left' => "&#xf0d9",
			'caret-right' => "&#xf0da",
			'columns' => "&#xf0db",
			'sort' => "&#xf0dc",
			'sort-desc' => "&#xf0dd",
			'sort-asc' => "&#xf0de",
			'envelope' => "&#xf0e0",
			'linkedin' => "&#xf0e1",
			'undo' => "&#xf0e2",
			'gavel' => "&#xf0e3",
			'tachometer' => "&#xf0e4",
			'comment-o' => "&#xf0e5",
			'comments-o' => "&#xf0e6",
			'bolt' => "&#xf0e7",
			'sitemap' => "&#xf0e8",
			'umbrella' => "&#xf0e9",
			'clipboard' => "&#xf0ea",
			'lightbulb-o' => "&#xf0eb",
			'exchange' => "&#xf0ec",
			'cloud-download' => "&#xf0ed",
			'cloud-upload' => "&#xf0ee",
			'user-md' => "&#xf0f0",
			'stethoscope' => "&#xf0f1",
			'suitcase' => "&#xf0f2",
			'bell-o' => "&#xf0a2",
			'coffee' => "&#xf0f4",
			'cutlery' => "&#xf0f5",
			'file-text-o' => "&#xf0f6",
			'building-o' => "&#xf0f7",
			'hospital-o' => "&#xf0f8",
			'ambulance' => "&#xf0f9",
			'medkit' => "&#xf0fa",
			'fighter-jet' => "&#xf0fb",
			'beer' => "&#xf0fc",
			'h-square' => "&#xf0fd",
			'plus-square' => "&#xf0fe",
			'angle-double-left' => "&#xf100",
			'angle-double-right' => "&#xf101",
			'angle-double-up' => "&#xf102",
			'angle-double-down' => "&#xf103",
			'angle-left' => "&#xf104",
			'angle-right' => "&#xf105",
			'angle-up' => "&#xf106",
			'angle-down' => "&#xf107",
			'desktop' => "&#xf108",
			'laptop' => "&#xf109",
			'tablet' => "&#xf10a",
			'mobile' => "&#xf10b",
			'circle-o' => "&#xf10c",
			'quote-left' => "&#xf10d",
			'quote-right' => "&#xf10e",
			'spinner' => "&#xf110",
			'circle' => "&#xf111",
			'reply' => "&#xf112",
			'github-alt' => "&#xf113",
			'folder-o' => "&#xf114",
			'folder-open-o' => "&#xf115",
			'smile-o' => "&#xf118",
			'frown-o' => "&#xf119",
			'meh-o' => "&#xf11a",
			'gamepad' => "&#xf11b",
			'keyboard-o' => "&#xf11c",
			'flag-o' => "&#xf11d",
			'flag-checkered' => "&#xf11e",
			'terminal' => "&#xf120",
			'code' => "&#xf121",
			'reply-all' => "&#xf122",
			'star-half-o' => "&#xf123",
			'location-arrow' => "&#xf124",
			'crop' => "&#xf125",
			'code-fork' => "&#xf126",
			'chain-broken' => "&#xf127",
			'question' => "&#xf128",
			'info' => "&#xf129",
			'exclamation' => "&#xf12a",
			'superscript' => "&#xf12b",
			'subscript' => "&#xf12c",
			'eraser' => "&#xf12d",
			'puzzle-piece' => "&#xf12e",
			'microphone' => "&#xf130",
			'microphone-slash' => "&#xf131",
			'shield' => "&#xf132",
			'calendar-o' => "&#xf133",
			'fire-extinguisher' => "&#xf134",
			'rocket' => "&#xf135",
			'maxcdn' => "&#xf136",
			'chevron-circle-left' => "&#xf137",
			'chevron-circle-right' => "&#xf138",
			'chevron-circle-up' => "&#xf139",
			'chevron-circle-down' => "&#xf13a",
			'html5' => "&#xf13b",
			'css3' => "&#xf13c",
			'anchor' => "&#xf13d",
			'unlock-alt' => "&#xf13e",
			'bullseye' => "&#xf140",
			'ellipsis-h' => "&#xf141",
			'ellipsis-v' => "&#xf142",
			'rss-square' => "&#xf143",
			'play-circle' => "&#xf144",
			'ticket' => "&#xf145",
			'minus-square' => "&#xf146",
			'minus-square-o' => "&#xf147",
			'level-up' => "&#xf148",
			'level-down' => "&#xf149",
			'check-square' => "&#xf14a",
			'pencil-square' => "&#xf14b",
			'external-link-square' => "&#xf14c",
			'share-square' => "&#xf14d",
			'compass' => "&#xf14e",
			'caret-square-o-down' => "&#xf150",
			'caret-square-o-up' => "&#xf151",
			'caret-square-o-right' => "&#xf152",
			'eur' => "&#xf153",
			'gbp' => "&#xf154",
			'usd' => "&#xf155",
			'inr' => "&#xf156",
			'jpy' => "&#xf157",
			'rub' => "&#xf158",
			'krw' => "&#xf159",
			'btc' => "&#xf15a",
			'file' => "&#xf15b",
			'file-text' => "&#xf15c",
			'sort-alpha-asc' => "&#xf15d",
			'sort-alpha-desc' => "&#xf15e",
			'sort-amount-asc' => "&#xf160",
			'sort-amount-desc' => "&#xf161",
			'sort-numeric-asc' => "&#xf162",
			'sort-numeric-desc' => "&#xf163",
			'thumbs-up' => "&#xf164",
			'thumbs-down' => "&#xf165",
			'youtube-square' => "&#xf166",
			'youtube' => "&#xf167",
			'xing' => "&#xf168",
			'xing-square' => "&#xf169",
			'youtube-play' => "&#xf16a",
			'dropbox' => "&#xf16b",
			'stack-overflow' => "&#xf16c",
			'instagram' => "&#xf16d",
			'flickr' => "&#xf16e",
			'adn' => "&#xf170",
			'bitbucket' => "&#xf171",
			'bitbucket-square' => "&#xf172",
			'tumblr' => "&#xf173",
			'tumblr-square' => "&#xf174",
			'long-arrow-down' => "&#xf175",
			'long-arrow-up' => "&#xf176",
			'long-arrow-left' => "&#xf177",
			'long-arrow-right' => "&#xf178",
			'apple' => "&#xf179",
			'windows' => "&#xf17a",
			'android' => "&#xf17b",
			'linux' => "&#xf17c",
			'dribbble' => "&#xf17d",
			'skype' => "&#xf17e",
			'foursquare' => "&#xf180",
			'trello' => "&#xf181",
			'female' => "&#xf182",
			'male' => "&#xf183",
			'gratipay' => "&#xf184",
			'sun-o' => "&#xf185",
			'moon-o' => "&#xf186",
			'archive' => "&#xf187",
			'bug' => "&#xf188",
			'vk' => "&#xf189",
			'weibo' => "&#xf18a",
			'renren' => "&#xf18b",
			'pagelines' => "&#xf18c",
			'stack-exchange' => "&#xf18d",
			'arrow-circle-o-right' => "&#xf18e",
			'arrow-circle-o-left' => "&#xf190",
			'caret-square-o-left' => "&#xf191",
			'dot-circle-o' => "&#xf192",
			'wheelchair' => "&#xf193",
			'vimeo-square' => "&#xf194",
			'try' => "&#xf195",
			'plus-square-o' => "&#xf196",
			'space-shuttle' => "&#xf197",
			'slack' => "&#xf198",
			'envelope-square' => "&#xf199",
			'wordpress' => "&#xf19a",
			'openid' => "&#xf19b",
			'university' => "&#xf19c",
			'graduation-cap' => "&#xf19d",
			'yahoo' => "&#xf19e",
			'google' => "&#xf1a0",
			'reddit' => "&#xf1a1",
			'reddit-square' => "&#xf1a2",
			'stumbleupon-circle' => "&#xf1a3",
			'stumbleupon' => "&#xf1a4",
			'delicious' => "&#xf1a5",
			'digg' => "&#xf1a6",
			'pied-piper' => "&#xf1a7",
			'pied-piper-alt' => "&#xf1a8",
			'drupal' => "&#xf1a9",
			'joomla' => "&#xf1aa",
			'language' => "&#xf1ab",
			'fax' => "&#xf1ac",
			'building' => "&#xf1ad",
			'child' => "&#xf1ae",
			'paw' => "&#xf1b0",
			'spoon' => "&#xf1b1",
			'cube' => "&#xf1b2",
			'cubes' => "&#xf1b3",
			'behance' => "&#xf1b4",
			'behance-square' => "&#xf1b5",
			'steam' => "&#xf1b6",
			'steam-square' => "&#xf1b7",
			'recycle' => "&#xf1b8",
			'car' => "&#xf1b9",
			'taxi' => "&#xf1ba",
			'tree' => "&#xf1bb",
			'spotify' => "&#xf1bc",
			'deviantart' => "&#xf1bd",
			'soundcloud' => "&#xf1be",
			'database' => "&#xf1c0",
			'file-pdf-o' => "&#xf1c1",
			'file-word-o' => "&#xf1c2",
			'file-excel-o' => "&#xf1c3",
			'file-powerpoint-o' => "&#xf1c4",
			'file-image-o' => "&#xf1c5",
			'file-archive-o' => "&#xf1c6",
			'file-audio-o' => "&#xf1c7",
			'file-video-o' => "&#xf1c8",
			'file-code-o' => "&#xf1c9",
			'vine' => "&#xf1ca",
			'codepen' => "&#xf1cb",
			'jsfiddle' => "&#xf1cc",
			'life-ring' => "&#xf1cd",
			'circle-o-notch' => "&#xf1ce",
			'rebel' => "&#xf1d0",
			'empire' => "&#xf1d1",
			'git-square' => "&#xf1d2",
			'git' => "&#xf1d3",
			'hacker-news' => "&#xf1d4",
			'tencent-weibo' => "&#xf1d5",
			'qq' => "&#xf1d6",
			'weixin' => "&#xf1d7",
			'paper-plane' => "&#xf1d8",
			'paper-plane-o' => "&#xf1d9",
			'history' => "&#xf1da",
			'circle-thin' => "&#xf1db",
			'header' => "&#xf1dc",
			'paragraph' => "&#xf1dd",
			'sliders' => "&#xf1de",
			'share-alt' => "&#xf1e0",
			'share-alt-square' => "&#xf1e1",
			'bomb' => "&#xf1e2",
			'futbol-o' => "&#xf1e3",
			'tty' => "&#xf1e4",
			'binoculars' => "&#xf1e5",
			'plug' => "&#xf1e6",
			'slideshare' => "&#xf1e7",
			'twitch' => "&#xf1e8",
			'yelp' => "&#xf1e9",
			'newspaper-o' => "&#xf1ea",
			'wifi' => "&#xf1eb",
			'calculator' => "&#xf1ec",
			'paypal' => "&#xf1ed",
			'google-wallet' => "&#xf1ee",
			'cc-visa' => "&#xf1f0",
			'cc-mastercard' => "&#xf1f1",
			'cc-discover' => "&#xf1f2",
			'cc-amex' => "&#xf1f3",
			'cc-paypal' => "&#xf1f4",
			'cc-stripe' => "&#xf1f5",
			'bell-slash' => "&#xf1f6",
			'bell-slash-o' => "&#xf1f7",
			'trash' => "&#xf1f8",
			'copyright' => "&#xf1f9",
			'at' => "&#xf1fa",
			'eyedropper' => "&#xf1fb",
			'paint-brush' => "&#xf1fc",
			'birthday-cake' => "&#xf1fd",
			'area-chart' => "&#xf1fe",
			'pie-chart' => "&#xf200",
			'line-chart' => "&#xf201",
			'lastfm' => "&#xf202",
			'lastfm-square' => "&#xf203",
			'toggle-off' => "&#xf204",
			'toggle-on' => "&#xf205",
			'bicycle' => "&#xf206",
			'bus' => "&#xf207",
			'ioxhost' => "&#xf208",
			'angellist' => "&#xf209",
			'cc' => "&#xf20a",
			'ils' => "&#xf20b",
			'meanpath' => "&#xf20c",
			'buysellads' => "&#xf20d",
			'connectdevelop' => "&#xf20e",
			'dashcube' => "&#xf210",
			'forumbee' => "&#xf211",
			'leanpub' => "&#xf212",
			'sellsy' => "&#xf213",
			'shirtsinbulk' => "&#xf214",
			'simplybuilt' => "&#xf215",
			'skyatlas' => "&#xf216",
			'cart-plus' => "&#xf217",
			'cart-arrow-down' => "&#xf218",
			'diamond' => "&#xf219",
			'ship' => "&#xf21a",
			'user-secret' => "&#xf21b",
			'motorcycle' => "&#xf21c",
			'street-view' => "&#xf21d",
			'heartbeat' => "&#xf21e",
			'venus' => "&#xf221",
			'mars' => "&#xf222",
			'mercury' => "&#xf223",
			'transgender' => "&#xf224",
			'transgender-alt' => "&#xf225",
			'venus-double' => "&#xf226",
			'mars-double' => "&#xf227",
			'venus-mars' => "&#xf228",
			'mars-stroke' => "&#xf229",
			'mars-stroke-v' => "&#xf22a",
			'mars-stroke-h' => "&#xf22b",
			'neuter' => "&#xf22c",
			'facebook-official' => "&#xf230",
			'pinterest-p' => "&#xf231",
			'whatsapp' => "&#xf232",
			'server' => "&#xf233",
			'user-plus' => "&#xf234",
			'user-times' => "&#xf235",
			'bed' => "&#xf236",
			'viacoin' => "&#xf237",
			'train' => "&#xf238",
			'subway' => "&#xf239",
			'medium' => "&#xf23a",
		);
		
		$form->add( new \IPS\Helpers\Form\Text( 'collab_menu_title', $this->title, TRUE, array() ) );
		$form->add( new \IPS\Helpers\Form\Url( 'collab_menu_link', new \IPS\Http\Url( $this->link ), TRUE, array() ) );
		$form->add( new \IPS\Helpers\Form\Select( 'collab_menu_icon', $this->icon ?: '', FALSE, array( 'options' => $icon_options ), NULL, NULL, NULL, 'collab_menu_icon' ) );
		
		parent::form( $form );
	}

	/**
	 * [Node] Save Add/Edit Form
	 *
	 * @param	array	$values	Values from the form
	 * @return	void
	 */
	public function saveForm( $values )
	{
		$collab 	= \IPS\collab\Application::activeCollab();
		
		$values[ 'menu_title' ] = $values[ 'collab_menu_title' ];
		$values[ 'menu_link' ] = (string) $values[ 'collab_menu_link' ];
		$values[ 'menu_icon' ] = $values[ 'collab_menu_icon' ];
		
		unset( $values[ 'collab_menu_title' ] );
		unset( $values[ 'collab_menu_link' ] );
		unset( $values[ 'collab_menu_icon' ] );
		
		return parent::saveForm( $values );
	}
	
	/**
	 * Check permissions
	 *
	 * @param	mixed								$permission		A key which has a value in static::$permissionMap['view'] matching a column ID in core_permission_index
	 * @param	\IPS\Member|\IPS\Member\Group|NULL	$member			The member or group to check (NULL for currently logged in member)
	 * @return	bool
	 * @throws	\OutOfBoundsException	If $permission does not exist in static::$permissionMap
	 */
	public function can( $permission, $member=NULL )
	{	
		return $this->canCollab( $permission, $member );
	}
	
	/**
	 * Set the permission index permissions
	 *
	 * @param	array	$insert	Permission data to insert
	 * @param	object	\IPS\Helpers\Form\Matrix
	 * @return  void
	 */
	public function setPermissions( $insert, \IPS\Helpers\Form\Matrix $matrix )
	{
		$insert[ 'perm_view' ] = '*';
		return parent::setPermissions( $insert, $matrix );
	}

	/**
	 * Get Collab
	 *
	 * @return	\IPS\Member
	 */
	public function collab()
	{
		try
		{
			$collab = \IPS\collab\Collab::load( $this->collab );
		}
		catch ( \OutOfRangeException $e ) 
		{
			return NULL;
		}
		
		return $collab;
	}
	
}