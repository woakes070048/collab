//<?php

class collab_hook_theme_coreFrontProfile extends _HOOK_CLASS_
{

/* !Hook Data - DO NOT REMOVE */
public static function hookData() {
 return array_merge_recursive( array (
  'hovercard' => 
  array (
    0 => 
    array (
      'selector' => '.cUserHovercard_data ul',
      'type' => 'add_inside_end',
      'content' => '{template="memberCollabCardList" app="collab" group="components" params="$member"}',
    ),
    1 => 
    array (
      'selector' => 'ul.ipsType_blendLinks',
      'type' => 'add_inside_start',
      'content' => '{{if count( \IPS\Member::loggedIn()->collabs( \'all\', \'inviteMember\', array( \'invitee\' => $member ) ) )}}
	<li><a data-ipsdialog data-ipsdialog-title=\'{lang="collab_invite"}\' href=\'{url="app=collab&module=collab&controller=admin&do=inviteMember"}&invitee={$member->member_id}\'><i class="fa fa-group"></i> {lang="collab_invite"}</a></li>
{{endif}}',
    ),
  ),
), parent::hookData() );
}
/* End Hook Data */








































}