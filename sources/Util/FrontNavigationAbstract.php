<?php
/**
 * @brief		Collaboration Utilities
 * @author		Kevin Carwile (http://www.linkedin.com/in/kevincarwile)
 * @copyright		(c) 2014 - Kevin Carwile
 * @package		Collaboration
 * @since		10 Dec 2014
 */

namespace IPS\collab\Util;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

$ips40 = 'namespace IPS\collab\Util; class _FrontNavigationAbstract { public function canView() { return ! \IPS\Application::load( \'collab\' )->hide_tab; } }';
$ips41 = 'namespace IPS\collab\Util; class _FrontNavigationAbstract extends \IPS\core\FrontNavigation\FrontNavigationAbstract {}';

if ( class_exists( 'IPS\core\FrontNavigation\FrontNavigationAbstract' ) )
{
	eval( $ips41 );
}
else
{
	eval( $ips40 );
}
