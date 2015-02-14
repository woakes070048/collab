//<?php

class collab_hook_theme_coreFrontProfile extends _HOOK_CLASS_
{

/* !Hook Data - DO NOT REMOVE */
public static $hookData = array (
  'hovercard' => 
  array (
    0 => 
    array (
      'selector' => '.cUserHovercard_data ul',
      'type' => 'add_inside_end',
      'content' => '{{if $collabCount = count( $member->collabs( \IPS\collab\COLLAB_MEMBER_ACTIVE ) )}}
<li>
	<span class="ipsDataItem_generic ipsDataItem_size3"><strong>{lang="__app_collab"}</strong></span>
	<span class="ipsDataItem_main">
	{{foreach $member->collabs( \IPS\collab\COLLAB_MEMBER_ACTIVE ) as $i => $collab}}
        <a href="{$collab->url()}">{$collab->title}</a>{{if $i < $collabCount - 1}}, {{endif}}
    {{endforeach}}
	</span>
</li>
{{endif}}',
    ),
    1 => 
    array (
      'selector' => 'ul.ipsType_blendLinks',
      'type' => 'add_inside_start',
      'content' => '{{if count( \IPS\Member::loggedIn()->collabs( \'all\', \'inviteMember\', array( \'invitee\' => $member ) ) )}}
<li><a data-ipsdialog data-ipsdialog-title=\'{lang="collab_invite"}\' href=\'{url="app=collab&module=collab&controller=admin&do=inviteMember"}&invitee={$member->member_id}\'><i class="fa fa-group"></i> {lang="collab_invite"}</a></li>
{{endif}}
',
    ),
  ),
);
/* End Hook Data */




































}