//<?php

class collab_hook_themeForumsFrontIndex extends _HOOK_CLASS_
{

/* !Hook Data - DO NOT REMOVE */
public static function hookData() {
 return array_merge_recursive( array (
  'forumRow' => 
  array (
    0 => 
    array (
      'selector' => '.ipsDataItem_category .cForumIcon_redirect',
      'type' => 'replace',
      'content' => '<span class=\'ipsItemStatus ipsItemStatus_large cForumIcon_redirect {{if !\IPS\forums\Topic::containerUnread( $forum ) }}ipsItemStatus_read{{endif}}\'>
    {{if \IPS\collab\Collab::checkAndLoadUrl( $forum->redirect_url ) or \IPS\collab\Category::checkAndLoadUrl( $forum->redirect_url )}}
	  <i class=\'fa fa-users\'></i>
    {{else}}
      <i class=\'fa fa-arrow-right\'></i>
    {{endif}}
</span>
',
    ),
    1 => 
    array (
      'selector' => '.ipsDataItem_main',
      'type' => 'add_after',
      'content' => '{{if $forum->redirect_on and $category = \IPS\collab\Category::checkAndLoadUrl( $forum->redirect_url )}}
	{template="categoryRowData" app="collab" group="components" params="$category"}
{{elseif $forum->redirect_on and $collab = \IPS\collab\Collab::checkAndLoadUrl( $forum->redirect_url )}}
	{template="collabRowData" app="collab" group="components" params="$collab"}
{{endif}}
',
    ),
  ),
  'index' => 
  array (
    0 => 
    array (
      'selector' => '.cForumList',
      'type' => 'add_inside_end',
      'content' => '{template="forumIndex" app="collab" group="layouts" params=""}',
    ),
  ),
), parent::hookData() );
}
/* End Hook Data */
































}