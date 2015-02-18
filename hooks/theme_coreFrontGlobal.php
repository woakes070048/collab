//<?php

class collab_hook_theme_coreFrontGlobal extends _HOOK_CLASS_
{

/* !Hook Data - DO NOT REMOVE */
public static function hookData() {
 return array_merge_recursive( array (
  'globalTemplate' => 
  array (
    0 => 
    array (
      'selector' => 'div:first',
      'type' => 'add_before',
      'content' => '{{$html = \IPS\collab\Application::collabWrapContent( $html );}}',
    ),
  ),
  'userBar' => 
  array (
    0 => 
    array (
      'selector' => '#elAccountSettingsLink',
      'type' => 'add_after',
      'content' => '{{if count( member.collabs() )}}
<li class=\'ipsMenu_item\' id=\'elCollabMembershipsLink\'><a href=\'{url="app=collab&module=collab&controller=settings"}\' title=\'{lang="collab_manage_memberships" sprintf="\IPS\Member::loggedIn()->language()->addToStack(\'__app_collab\')"}\'>{lang="collab_manage_memberships" sprintf="\IPS\Member::loggedIn()->language()->addToStack(\'__app_collab\')"}</a></li>
{{endif}}',
    ),
  ),
), parent::hookData() );
}
/* End Hook Data */








}