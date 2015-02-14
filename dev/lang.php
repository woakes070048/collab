<?php

$lang = array(

	// Application & Modules
	'__app_collab'				=> "Collaboration",
	'module__collab_collab'			=> "Collaboration",
	'__indefart_collab' 			=> "a collab",
	'__indefart_collab_comment'		=> "a post in a collab",
	
	// Administration Restrictions
	'r__collab_categories_manage'		=> "Manage Categories",
	'r__collab_settings_manage'		=> "Manage Settings",
	
	// ACP Menu
	'menu__collab_collab'			=> "Collaboration",
	'menu__collab_collab_categories'	=> "Categories",
	'menu__collab_collab_settings'		=> "Settings",
	
	// Group Settings
	'group__collab_groupForm'		=> "Collaboration",
	'g_collabs_owned_limit'			=> "Maximum collabs a member of this group can own",
	'g_collabs_joined_limit'		=> "Maximum amount of collabs a member of this group can join",
	
	// ACP Moderator Permissions
	'modperms__core_Content_collab_Collab'	=> "Collabs",
	'collab_categories'			=> "Collab Categories",
	'can_pin_collab'			=> "Can Pin Collab",
	'can_unpin_collab'			=> "Can Unpin Collab",
	'can_feature_collab'			=> "Can Feature Collab",
	'can_unfeature_collab'			=> "Can Unfeature Collab",
	'can_edit_collab'			=> "Can Edit Collab",
	'can_hide_collab'			=> "Can Hide Collab",
	'can_unhide_collab'			=> "Can Unhide Collab",
	'can_view_hidden_collab'		=> "Can View Hidden Collab",
	'can_move_collab'			=> "Can Move Collab",
	'can_lock_collab'			=> "Can Lock Collab",
	'can_unlock_collab'			=> "Can Unlock Collab",
	'can_reply_to_locked_collab'		=> "Can Reply to Locked Collab",
	'can_delete_collab'			=> "Can Delete Collab",
	'can_edit_collab_comment'		=> "Can Edit Collab Comments",
	'can_hide_collab_comment'		=> "Can Hide Collab Comments",
	'can_unhide_collab_comment'		=> "Can Unhide Collab Comments",
	'can_view_hidden_collab_comment'	=> "Can View Hidden Collab Comments",
	'can_delete_collab_comment'		=> "Can Delete Collab Comments",
	'can_edit_collab_review'		=> "Can Edit Collab Reviews",
	'can_hide_collab_review'		=> "Can Hide Collab Reviews",
	'can_unhide_collab_review'		=> "Can Unhide Collab Reviews",
	'can_view_hidden_collab_review'		=> "Can View Hidden Collab Reviews",
	'can_delete_collab_review'		=> "Can Delete Collab Reviews",
	
	// Category Settings Form
	'collab_app_title'			=> "App Title",
	'collab_app_title_desc'			=> "You can change the name of the collaboration app to reflect the way you use it.",
	'tab_collab_category_settings'		=> "Category Details",
	'tab_collab_collabs_settings'		=> "Collab Settings",
	'collab_permissions'			=> "%s %s Permissions",
	'collab_moderation_settings'		=> "%s Moderation Abilities For %s Owners",
	'category_collabs_enable'		=> "Enable Collabs?",
	'category_collabs_enable_desc'		=> "This setting controls whether users can create new collabs in this category. If not, it will only be a container category.",
	'collabs_alias_singular'		=> "Alias (Singular)",
	'collabs_alias_singular_desc'		=> "What do you want to call a collaboration in this category (in singular context)?",
	'collabs_alias_plural'			=> "Alias (Plural)",
	'collabs_alias_plural_desc'		=> "What do you want to call collaborations in this category (in plural form)?",
	'collab_moderator_perms'		=> "Moderator Permissions",
	'category_max_collabs_owned'		=> "Maximum Collabs Per Owner",
	'category_max_collabs_owned_desc'	=> "This setting controls how many collabs a user may own in this category.",
	'category_max_collabs_joined'		=> "Maximum Collab Memberships Per User",
	'category_max_collabs_joined_desc'	=> "This setting controls the maximum amount of collabs a user may hold an active membership to in this category.",
	'category_max_collab_members'		=> "Maximum Members Per Collab",
	'category_max_collab_members_desc'	=> "This setting controls the maximum amount of allowed active members per collab in this category.",
	'collab_node_maxnodes'			=> "Maximum %s Per %s",
	'collab_node_maxnodes_desc'		=> "This setting controls the maximum amount of %s which can be created per %s.",
	'collab_allow_comments'			=> "Enable Comments?",
	'collab_allow_ratings'			=> "Enable Ratings?",
	'collab_allow_reviews'			=> "Enable Reviews?",
	'collab_node_enable_add'		=> "Owners Can Add %s?",
	'collab_node_enable_add_desc'		=> "If selected, %s owners will be able to add %s on their own whenever they want.",
	'collab_node_enable_edit'		=> "Owners Can Edit %s?",
	'collab_node_enable_edit_desc'		=> "If selected, %s owners will be able to edit existing %s to suit their needs.",
	'collab_node_enable_delete'		=> "Owners Can Delete %s?",
	'collab_node_enable_delete_desc'	=> "If selected, %s owners will be able to delete %s that they don't need.",
	'collab_node_enable_reorder'		=> "Owners Can Reorder %s?",
	'collab_node_enable_reorder_desc'	=> "If selected, %s owners will be able to change their arrangement of %s.",
	'collab_allow_node'			=> "Enable %s %s?",
	'collab_allow_node_desc'		=> "If enabled, %s in this category will be able to have their own %s.",
	'collab_allow_node_content_desc'	=> "If enabled, %s in this category will be able to have their own %s. This will allow the group to maintain its own %s content.",
	'collab_model_settings'			=> "Collab Model Settings",
	'collab_enable_model'			=> "Use Collab Models?",
	'collab_enable_model_desc'		=> "If needed, you can create and designate one or more %s in this category to act as a model for newly created %s. Newly created %s will then be set up with a default configuration based on how the model is configured.",
	'collab_force_model'			=> "Force Model?",
	'collab_force_model_desc'		=> "If you enable this option, users will be forced to select a %s model when they create a new %s. If only one model is available, it will be used automatically.",
	'collab_multiple_models'		=> "Model Choices",
	'collab_multiple_models_desc'		=> "If you allow multiple choices, newly created %s can have a configuration that combines all the models selected by the user.",
	
	// Category View
	'no_collabs_in_category'		=> "This category does not contain any %s",
	
	// Collab Create / Edit Form
	'collab_collab_tags'			=> "Tags",
	'collab_collab_auto_follow'		=> "Follow Content",
	'collab_collab_auto_follow_suffix'	=> "Receive automatic notifications of updates and activity",
	'collab_description'			=> "Description",
	'collab_default_title'			=> "Default Member Title",
	'collab_default_title_desc'		=> "You can choose a default title for all members.",
	'collab_create_state'			=> "Options",
	'create_collab_locked'			=> "Locked",
	'create_collab_pinned'			=> "Pinned",
	'create_collab_hidden'			=> "Hidden",
	'create_collab_featured'		=> "Featured",
	'collab_collab__save'			=> "Save",
	'collab_tab_description'		=> "Description",
	'collab_tab_settings'			=> "Settings",
	'collab_tab_permissions'		=> "Permissions",
	'collab_tab_members'			=> "Members",
	'collab_tab_roles'			=> "Roles",
	'collab_rules'				=> "Membership Rules",
	'collab_role_custom_title'		=> "Custom Member Title",
	'collab_role_custom_title_desc'		=> "You can choose a custom member title to give members with this role.",
	'collab_template'			=> "Group Model",
	'collab_no_template'			=> "None",
	
	// Collab View
	'leave_comment'				=> "Leave Comment",
	'collab_collab__comment_placeholder'	=> "Type here to leave a comment...",
	'collab_comment_count'			=> "{# [1:comment][?:comments]} about this %s",
	'collab_youre_active'			=> "You are an active member.",
	'collab_youre_pending'			=> "Your membership request is pending.",
	'collab_youre_invited'			=> "You have been invited to be a member!",
	'collab_youre_banned'			=> "You have been banned.",
	'collab_review_count'			=> "{# [1:Review][?:Reviews]}",
	'collab_comment_count'			=> "{# [1:Comment][?:Comments]}",
	'collab_collab_rating_value'		=> "Choose A Rating",
	'collab_collab_review_text'		=> "Your Review",
	'collab_collab__review_placeholder'	=> "Write your review here...",
	'collab_locked_can_comment'		=> "This content has been locked, but your permissions allow you to still leave comments.",
	'collab_locked_cannot_comment'		=> "This content has been locked.",
	'collab_active_members'			=> "Active Members",
	'collab_see_active_members'		=> "See who is a member of this %s",
	'collab_active_members_title'		=> "Active Members of %s",
	'collab_activity_feed'			=> "Activity Feed",
	'collab_cat_sub'			=> "Sub:",
	'collab_active_members_count'		=> "{# [1:Active Member][?:Active Members]}",
	'collab_byline'				=> "Leader: %s",
	'collab_no_content'			=> "No content here yet",
	'collab_marked_model'			=> "Designated as a model",
	'collab_collab_submit_comment'		=> "Submit Comment",
	
	// Collab Custom Filters
	'collab_status'				=> "Status",
	'collabs_all'				=> "All",
	'collabs_join_closed'			=> "Closed Membership",
	'collabs_join_open'			=> "Open Membership",
	'collabs_join_invite'			=> "Invite Only",
	'collabs_join_free'			=> "Open Membership (without approval)",
	'collab_is_template'			=> "Items Marked As Models",
	
	// Collab Join / Invite Request Form
	'collab_join'				=> "Join %s",
	'collab_join_message'			=> "Join Request Message",
	'collab_invite_message'			=> "Invitation Message",
	'collab_invite_response'		=> "Respond to invitation:",
	'collab_accept_invitation'		=> "Accept the invitation and join the group.",
	'collab_deny_invitation'		=> "Deny the invitation. DO NOT join the group.",
	'collab_update_invitation'		=> "Update the notes on this invitation only.",
	'collab_invitees'			=> "Invitees",
	'collab_invite_button'			=> "Invite Members!",
	
	// Messages
	'collab_message_invitation_accepted'	=> "Congratulations. You are now a member!",
	'collab_message_invitation_denied'	=> "The invitation has been denied.",
	'collab_message_members_invited'	=> "Members have been invited!",
	'collab_message_join_request_sent'	=> "Join Request Sent!",
	'collab_message_join_request_updated'	=> "Join Request Updated!",
	'collab_message_membership_deleted'	=> "Membership has been removed",
	'collab_message_ownership_transferred'	=> "Ownership has been successfully transferred!",
	'collab_message_model_marked'		=> "This item has been flagged as a model!",
	'collab_message_model_unmarked'		=> "This item is no longer a model!",
	
	// Collab Administration
	'collab_select_id'			=> "Select One",
	'collab_invite_member'			=> "Invite Member",
	'collab_members_filter_invited'		=> "Invited",
	'collab_members_filter_banned'		=> "Banned",
	'collab_members_filter_pending'		=> "Pending",
	'collab_members_filter_active'		=> "Active",
	'members_collab_joined'			=> "Joined",
	'members_collab_status'			=> "Status",
	'members_collab_roles'			=> "Roles",
	'members_collab_title'			=> "Title",
	'collab_dashboard'			=> "%s Dashboard",
	'collab_members'			=> "%s Members",
	'collab_roles'				=> "%s Roles",
	'collab_create_role'			=> "Create Role",
	'collab_role_name'			=> "Role Name",
	'collab_status_banned'			=> "Banned",
	'collab_status_invited'			=> "Invited",
	'collab_status_pending'			=> "Awaiting Approval",
	'collab_status_active'			=> "Active",
	'collab_member_edited'			=> "Member updated.",
	'collab_member_default_title'		=> "Inherit Member Title",
	'collab_member_default_title_desc'	=> "The title for this member will be inherited from roles or other %s settings.",
	'collab_member_custom_title'		=> "Custom Member Title",
	'collab_member_custom_title_desc'	=> "Any title you enter here will override default titles.",
	'collab_member_roles'			=> "Role",
	'collab_member_approved'		=> "Member has been approved.",
	'collab_member_banned'			=> "Member has been banned.",
	'collab_member_unbanned'		=> "Member has been unbanned.",
	'collab_member_deleted'			=> "Member has been removed!",
	'collab_role_created'			=> "Role has been created.",
	'collab_role_edited'			=> "Role has been edited.",
	'collab_role_deleted'			=> "Role has been deleted!",
	'collab_role_perms'			=> "Permissions",
	'collab_role_guests'			=> "Guests",
	'collab_role_members'			=> "All Active Members",
	'collab_perm_action'			=> "perform that action",
	'collab_perm_manageCollab'		=> "Manage Collab",
	'collab_perm_viewDashboard'		=> "View Dashboard",
	'collab_perm_inviteMember'		=> "Invite Members",
	'collab_perm_manageMembers'		=> "Manage Members",
	'collab_perm_editMember'		=> "Edit Members",
	'collab_perm_editMemberRoles'		=> "Edit Member Roles",
	'collab_perm_banMember'			=> "Ban Members",
	'collab_perm_deleteMember'		=> "Delete Members",
	'collab_perm_unbanMember'		=> "Un-ban Members",
	'collab_perm_approveMember'		=> "Approve Members",
	'collab_perm_manageRoles'		=> "Manage Roles",
	'collab_perm_addRole'			=> "Add Roles",
	'collab_perm_editRole'			=> "Edit Roles",
	'collab_perm_editRolePermissions'	=> "Edit Role Permissions",
	'collab_perm_deleteRole'		=> "Delete Roles",
	'collab_perm_moderateContent'		=> "Moderate Content",
	'collab_perm_nodeManage'		=> "Manage %s",
	'collab_perm_nodeAdd'			=> "Add %s",
	'collab_perm_nodeEdit'			=> "Edit %s",
	'collab_perm_nodeDelete'		=> "Delete %s",
	'collab_role_mod_permissions'		=> "Moderation Permissions",
	'collab_collab_notes'			=> "Member Notes",
	'collab_moderation_actions'		=> "%s Management",
	'collab_join_mode'			=> "Membership Mode",
	'collab_join_disabled'			=> "Closed",
	'collab_join_invite_only'		=> "Invite Only",
	'collab_join_approve'			=> "Open (needs approval)",
	'collab_join_free'			=> "Open (without approval)",
	'collab_increase_mainposts'		=> "Increase overall site posts as well as collab posts",
	'collab_increase_mainposts_desc'	=> "Posts are only added to collab totals by default. Do you want posts to also add to the users overall site count?",
	'collab_make_model'			=> "Flag as %s Model",
	'collab_unmake_model'			=> "Unflag as %s Model",
	
	// User Membership Management
	'collab_membership_collab_id'		=> "Title",
	'collab_membership_title'		=> "Member Title",
	'collab_membership_roles'		=> "Roles",
	'collab_membership_status'		=> "Status",
	'collab_membership_joined'		=> "Joined",
	'collab_membership_members_count'	=> "Active / Total",
	'collab_manage_memberships'		=> "%s Memberships",
	'collab_manage_memberships_blurb'	=> "View and manage the status of your %s memberships.",
	'collab_leave'				=> "Leave",
	'collab_respond'			=> "Respond To Invite",
	'collab_update'				=> "Update Request",
	'collab_transfer'			=> "Transfer Ownership",
	'collab_edit_membership'		=> "Editing \"%s\" Membership",
	'collab_membership_updated'		=> "Membership Updated",
	'collab_member_notes'			=> "Notes",
	'collab_transfer_ownership'		=> "Transfer Ownership Of \"%s\"",
	'collab_new_owner'			=> "Select New Owner",
	'collab_member_posts'			=> "Posts Count",
	'collab_membership_join_notes'		=> "Membership Join Request Notes",
	'collab_membership_member_settings'	=> "Membership Settings",
	'collab_member_approve'			=> "Approve this member",
	
	// Permissions
	'collab_perm__label'			=> "Category Permissions",
	'collab_perm__view'			=> "See Category",
	'collab_perm__read'			=> "View Collabs",
	'collab_perm__add'			=> "Create Collabs",
	'collab_perm__reply'			=> "Comment On Collabs",
	'collab_perm__rate'			=> "Rate Collabs",
	'collab_perm__join'			=> "Join Collabs",
	'collab_perm__review'			=> "Write Reviews",
		
	// ACP Logging
	'collab_acplog_settings'		=> "Updated collaboration application settings",
	
	// Collab Create / Edit Form
	'collab_collab_title'			=> "Title",
	'collab_short_description'		=> "Short Description",
	
	// Generic & Template Language
	'collab_start_new'			=> "Add New %s",
	'collab_select_category'		=> "Select Category",
	'collab_cat__collab_singular'		=> "Collab",
	'collab_cat__collabs_plural'		=> "Collabs",
	'collab_cat__member_title'		=> "%s Member",
	'collab_member_affiliations'		=> "Affiliations",
	'collab_default_creator_title'		=> "Creator",
	'collab_default_member_title'		=> "%s Member",
	'collab_default_guest_title'		=> "%s Guest",
	'collab_menu'				=> "%s Menu",
	'collab_homepage'			=> "%s Homepage",
	'collab_members_count'			=> "Active Members",
	'collab_created_date'			=> "Since",
	'collab_owned_by'			=> "%s Leader",
	'collab_settings'			=> "%s Settings",
	'collab_manage_nodes'			=> "Manage %s",
	'collab_edit'				=> "Edit %s",
	'collab_manage_members'			=> "Manage %s Members",
	'collab_manage_roles'			=> "Manage %s Roles",
	'collab_invite'				=> "Invite",
	
	'collab_member_of_collab'		=> "You are a member of this %s",
	
	// Content Router
	'collab_collab_pl'			=> "Collaboration",
	'collab_pl'				=> "Items",
	'collab_comment_pl'			=> "Comments",
	'collab_review_pl'			=> "Reviews",
	
	// Notification Settings
	'notifications__collab_invitation_received' 	=> "When someone invites me to join a group",
	'notifications__collab_join_accepted'		=> "When I am accepted into a group",
	'notifications__collab_invitation_accepted' 	=> "When someone accepts an invitation I send",
	'notifications__collab_join_requested'		=> "When someone requests to join a group I manage",
	
	// Notification Messages
	'collab_notification_collab_join_requested'		=> "%s has requested to join the %s: \"%s\"",
	'collab_notification_collab_invitation_received'	=> "%s has invited you to join the %s: \"%s\"",
	'collab_notification_collab_join_accepted'		=> "%s has accepted you to the %s: \"%s\"",
	'collab_notification_collab_invitation_accepted'	=> "%s has accepted your invite to the %s: \"%s\"",
	
	// Notification Email Subjects
	'mailsub__collab_notification_collab_join_requested'		=> '{$membership->member()->name} has requested to join {$collab->title}',
	'mailsub__collab_notification_collab_invitation_received'	=> 'You have been invited to join {$collab->title}',
	'mailsub__collab_notification_collab_join_accepted'		=> 'You have been accepted into {$collab->title}',
	'mailsub__collab_notification_collab_invitation_accepted'	=> '{$membership->sponsor()->name} has accepted you into {$collab->title}',
	
	// Emails Descriptions
	'emailtpl_notification_collab_join_requested'		=> "Collaboration Join Requested",
	'emailtpl_notification_collab_join_accepted'		=> "Collaboration Join Accepted",
	'emailtpl_notification_collab_invitation_received'	=> "Collaboration Invite Received",
	'emailtpl_notification_collab_invitation_accepted'	=> "Collaboration Invite Accepted",
	
	'collab_title_manage_members'		=> "%s Members",
	'collab_title_edit_membership'		=> "Editing Membership For: %s",
	'collab_title_manage_roles'		=> "%s Roles",
	'collab_title_add_role'			=> "Add New %s Role",
	'collab_title_edit_role'		=> "Editing Role: \"%s\"",
	'collab_title_invite_member'		=> "Invite Member",
	
	'loc_collab_viewing_category'		=> "Viewing %s",
	'loc_collab_viewing_collab'		=> "Viewing %s",
	
	// Errors
	'collab_join_error_restricted'		=> "You are restriced from joining this group.",
	'collab_join_error_full'		=> "This group has reached its maximum amount of allowed members.",
	'collab_join_error_disabled'		=> "Joining this group has been disabled.",
	'collab_join_error_invite_only'		=> "This group is invite only.",
	'collab_join_error_active'		=> "You are already an active member in this %s",
	'collab_join_error_banned'		=> "You have been banned from this %s",
	'collab_join_error_state'		=> "Your membership request cannot be processed.",
	'collab_not_found'			=> "The collab you requested could not be found.",
	'collab_node_perms_missing'		=> "The permissions for this page have not been configured!",
	'collab_node_unavailable'		=> "The selected node type has been disabled.",
	'collab_perm_error'			=> "You do not have permission to %s.",
	'collab_unauthorized_action'		=> "You are not authorized to perform that action.",
	'collab_asset_mismatch'			=> "The asset loaded is not part of the expected group.",
	'collab_member_invalid'			=> "Invalid Membership",
	'collab_role_invalid'			=> "Invalid Role",
	'collab_role_exists'			=> "Role name already exists!",
	'collab_invalid_membership'		=> "There was a problem loading the membership",
	'collab_owner_non_removable'		=> "You can't leave when you are the owner!",
	'collab_no_transfer_members'		=> "There are no qualified members in your group to transfer to!",
	
	// Uninstall
	'collab_delete_all'			=> "Delete All Associated Content?",
	'collab_delete_all_desc'		=> "If checked, all categories and content from collaborations will be deleted from your site. Uncheck for options to keep specific content.",
	'collab_deleting_collabs'		=> "Deleting Collaboration Content",
	'collab_keep_node'			=> "Keep %s?",
	'collab_keep_node_desc'			=> "Check this option to keep all %s and %s content associated with collaborations on your site.",
	
	// Other
	'collab'				=> "Collab",
	'remove'				=> "Remove",
	'select'				=> "Select",
	'kick'					=> "Kick Out",
	'browse'				=> "Browse",
	'category_parent_id'			=> "Category Parent",
	'rating'				=> "Rating",
	'reviews'				=> "Reviews",
	'title'					=> "Title",
	'created_date'				=> "Date Created",
	'single'				=> "Only One",
	'multiple'				=> "Multiple",
	
);
