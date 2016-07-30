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
	'menu__collab_collab'				=> "Collaboration",
	'menu__collab_collab_categories'		=> "Categories",
	'menu__collab_collab_settings'			=> "Settings",
	'menu__collab_collab_tools'			=> "Import Tools",
	'notifications__collab_collabNotifications' 	=> "Collaboration",
	
	'collab_copy_to_collab'			=> "Copy to a collab",
	'collab_move_to_collab'			=> "Move to a collab",
	'collab_extract_from_collab'		=> "Extract to main site",
	'collab_id_select'			=> "Enter a collab ID",
	'collab_node_copied_to_collab'		=> "Successfully copied to the collab",
	'collab_node_moved_to_collab'		=> "Successfully moved to the collab",
	'collab_node_extracted_from_collab'	=> "Successfully extracted to the main site",
	'collab_flush_counts'			=> "Recount Collab Stats",
	'collab_flush_counts_confirm'		=> "This will flush cached collab count data and cause items to be recounted",
	'collab_flush_counts_complete'		=> "Collab items will now be recounted!",
	'collab_unread_method'			=> "Unread Calculation Method",
	'collab_unread_method_desc'		=> "<div class='ipsMessage ipsMessage_warning'><strong>Quick</strong> will consider a collaboration unread based only on the date/time of the last activity in the collab (irrespective of permissions). <br><strong>Comprehensive</strong> will actually search through every app and every container inside collaborations to see if there is new content that the user can actually view. It can get very resource intensive if there is a lot of content, so use the 'comprehensive' setting with caution.</div>",
	'collab_unread_quick'			=> "Quick (based on last activity)",
	'collab_unread_comprehensive'		=> "Comprehensive (based on user permissions)",
	
	'collab_category_longconfig'		=> "Expanded Category Configuration",
	'collab_category_longconfig_desc'	=> "When enabled, configuration settings for every available content type will be included 'all-in-one' on the collab category configuration form. Disable this option if your server is experiencing problems processing the full form. ( Each content type will still be able to be configured individually. )",
	'collab_category_updated'		=> "Category Updated!",
	'collab_config_app'			=> "%s Settings",
	'collab_category_app_config'		=> "Collab App Configuration",
	'collab_configureWizard'		=> "Start Configuration Wizard",
	
	'editor__collab_Categories'		=> "Collab Categories",
	'editor__collab_Collab'			=> "Collaborations",
	'editor__collab_Generic'		=> "Collab Generic",
	
	// Widgets
	'block_featuredCollabs'			=> "Featured Collabs",
	'block_featuredCollabs_desc'		=> "Show collabs that are currently featured",
	'block_featuredCollabs_title'		=> "Featured %s",
	'block_latestCollabs'			=> "Latest Collabs",
	'block_latestCollabs_desc'		=> "Show the most recently started collabs",
	'block_latestCollabs_title'		=> "Latest %s",
	'block_myCollabs'			=> "My Collabs",
	'block_myCollabs_desc'			=> "List of collabs the currently logged in member is an active member of",
	'block_myCollabs_title'			=> "My %s",
	
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
	
	// Moderator Permissions
	'modperms__collab_modPermissions'	=> "Collab Management",
	'can_bypass_collab_permissions'		=> "Unrestricted by collab permissions",
	
	// Collab Tools
	'collab_manage_tools'			=> "Collaboration Import Tools",
	'collab_migrate_missing_data'		=> "Import data unavailable",
		
	// Application Settings
	'collab_app_title'			=> "App Title",
	'collab_app_title_desc'			=> "You can change the name of the collaboration app to reflect the way you use it.",
	'collab_app_collab_singular'		=> "Collab Singular",
	'collab_app_collab_singular_desc'	=> "What are you generally going to call a collaboration on your site (in singular context).",
	'collab_app_collabs_plural'		=> "Collab Plural",
	'collab_app_collabs_plural_desc'	=> "What are you generally going to call collaborations on your site (in plural context).",
	'collab_settings_saved'			=> "Settings Saved!",
	
	// Category Settings Form
	'tab_collab_category_settings'		=> "Category Details",
	'tab_collab_collabs_settings'		=> "Collab Settings",
	'collab_category_show_forum_index'	=> "Show On Forum Index",
	'collab_category_show_forum_index_desc' => "If enabled, this category will be visible in the main forums index",
	'collab_category_require_approval'	=> "Require Approval",
	'collab_category_require_approval_desc'	=> "If enabled, new collabs created in this category will require moderator approval.",
	'collab_category_per_page'		=> "Collabs Per Page",
	'collab_category_per_page_desc'		=> "Number of collabs to show per page in this category",
	'collab_display_options'		=> "%s %s Display Options",
	'collab_permissions'			=> "%s %s Permissions",
	'collab_moderation_settings'		=> "%s Moderation Abilities For %s Owners",
	'category_collabs_enable'		=> "Enable Collabs?",
	'category_collabs_enable_desc'		=> "This setting controls whether users can create new collabs in this category. If not, it will only be a container category.",
	'collabs_alias_singular'		=> "Alias (Singular)",
	'collabs_alias_singular_desc'		=> "What do you want to call a collaboration in this category (in singular context)?",
	'collabs_alias_plural'			=> "Alias (Plural)",
	'collabs_alias_plural_desc'		=> "What do you want to call collaborations in this category (in plural form)?",
	'collab_moderator_perms'		=> "Moderator Permissions",
	'category_max_collabs_owned'		=> "Max Collab Ownerships Per User",
	'category_max_collabs_owned_desc'	=> "This setting controls how many collabs a user may own in this category.",
	'category_max_collabs_joined'		=> "Max Collab Memberships Per User",
	'category_max_collabs_joined_desc'	=> "This setting controls the maximum amount of collabs a user may hold an active membership to in this category.",
	'category_max_collab_members'		=> "Max Members Per Collab",
	'collab_restrict_owner'			=> "Restrict Owner Permissions",
	'collab_restrict_owner_desc'		=> "By default, collab owners are not subject to role restrictions. If you enable this option, they will only have permissions based on their role inside the collab.",
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
	'collab_node_gridview'			=> "Use Homepage Gridview",
	'collab_node_gridview_desc'		=> "When enabled, %s will display in grid format on the collaboration homepage",
	'collab_node_gridview_threshold'	=> "Gridview Threshold",
	'collab_node_gridview_threshold_desc'	=> "Don't display the grid view unless there are at least this many %s to display.",
	'collab_model_settings'			=> "Collab Model Settings",
	'collab_enable_model'			=> "Use Collab Models?",
	'collab_enable_model_desc'		=> "If needed, you can create and designate one or more %s in this category to act as a model for newly created %s. Newly created %s will then be set up with a default configuration based on how the model is configured.",
	'collab_force_model'			=> "Force Model?",
	'collab_force_model_desc'		=> "If you enable this option, users will be forced to select a %s model when they create a new %s. If only one model is available, it will be used automatically.",
	'collab_multiple_models'		=> "Model Choices",
	'collab_multiple_models_desc'		=> "If you allow multiple choices, newly created %s can have a configuration that combines all the models selected by the user.",
	'category_denied'			=> "Custom 'Permission Denied' Content",
	
	'collab_category_privacy_mode'		=> "Listing Mode",
	'collab_category_privacy_mode_desc'	=> "In public listing mode, collabs are always visibly listed for any member with permissions to view this category. In private listing mode, collabs are only visibly listed when their join mode is open to all, or if a member is already an active member of that collab.",
	'category_privacy_mode_public'		=> "Public Listing",
	'category_privacy_mode_private'		=> "Private Listing",
	'collab_contribution_mode'		=> "Contribution Calculation",
	'collab_contribution_mode_desc'		=> "Which stat do you want to display for total contributions",
	'collab_contribution_mode_posts'	=> "Total Posts",
	'collab_contribution_mode_items'	=> "Items Only",
	'collab_member_default'			=> "Member's Chosen Site Theme",
	'collab_category_skin_id'		=> "Category Theme",
	
	// Category View
	'no_collabs_in_category'		=> "This category does not contain any %s",
	'collab_category_denied'		=> "You do not have permission to view this category.",
	
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
	'collab_member_default_role'		=> "Default Member Role",
	'collab_owner_default_role'		=> "Default Owner Role",
	'collab_logo'				=> "Logo",
	'collab_logo_mode'			=> "Collab Logos",
	'collab_logo_none'			=> "Disabled",
	'collab_logo_optional'			=> "Optional",
	'collab_logo_required'			=> "Required",
	'collab_logo_size'			=> "Max Logo Dimensions",
	
	// Collab View
	'leave_comment'				=> "Leave Comment",
	'collab_comment'			=> "Comment",
	'collab_review'				=> "Review",
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
	'view_this_collab'			=> "View the %s: %s",
	'collab_items'				=> "{!# [1:item][?:items]}",
	'collab_num_views'			=> "{!# [1:view][?:views]}",
	'collab_comments'			=> "{!# [1:comment][?:comments]}",
	'collab_reviews'			=> "{!# [1:review][?:reviews]}",
	'collab_posts'				=> "{!# [1:post][?:posts]}",
	'collab_contributions'			=> "{!# [1:contribution][?:contributions]}",
	
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
	'collab_join_invited'			=> "Respond To Invite",
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
	'collab_role_guests'			=> "Non Members",
	'collab_role_members'			=> "All Active Members",
	'collab_perm_action'			=> "perform that action",
	'collab_perm_manageCollab'		=> "Manage Collab",
	'collab_perm_editMenu'			=> "Edit Collab Menu",
	'collab_perm_editDescription'		=> "Edit Description",
	'collab_perm_editSettings'		=> "Edit Settings",
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
	'collab_menu_items'			=> "Menu Items",
	'collab_menu_title'			=> "Menu Title",
	'collab_menu_link'			=> "Menu Link",
	'collab_menu_icon'			=> "Menu Icon",
	'collab_role_pm_create'			=> "Start Group Conversation",
	
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
	'collab_menu_perm__label'		=> "Menu Permissions",
	'collab_menu_perm__view'		=> "View Menu Item",
		
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
	'collab_delete_all_desc'		=> "<strong style='color:red'><i class='fa fa-warn-triangle'></i> Warning:</strong> If checked, all categories and content inside collaborations will be deleted from your site.",
	'collab_deleting_collabs'		=> "Deleting Collaboration Content",
	'collab_keep_node'			=> "Keep %s?",
	'collab_keep_node_desc'			=> "Check this option to keep all %s and %s content inside collaborations on your site.",
	
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
	'config'				=> "Configuration",
	'limits'				=> "Limits",
	'moderation'				=> "Moderation",
	'done'					=> "Done",
	
	'mark_collab_read'			=> "Mark %s Read",
	
	// Rules Conversions
	'__global_active_collab'				=> "Page: Associated Collaboration",
	
	// Rules Events
	'collab_Collaboration_event_member_invited'		=> "Member is invited to collab",
	'collab_Collaboration_event_member_invited_member' 	=> "Member that was invited",
	'collab_Collaboration_event_member_invited_sponsor'	=> "Member responsible for the invite",
	'collab_Collaboration_event_member_invited_collab'	=> "The collab",
	'collab_Collaboration_event_member_invited_membership'	=> "The collab membership",
	
	'collab_Collaboration_event_member_pending'		=> "Member has requested to join collab",
	'collab_Collaboration_event_member_pending_member' 	=> "Member that requested to join",
	'collab_Collaboration_event_member_pending_collab'	=> "The collab",
	'collab_Collaboration_event_member_pending_membership'	=> "The collab membership",

	'collab_Collaboration_event_member_joined'		=> "Member has joined collab",
	'collab_Collaboration_event_member_joined_member' 	=> "Member that joined the collab",
	'collab_Collaboration_event_member_joined_collab'	=> "The collab",
	'collab_Collaboration_event_member_joined_membership'	=> "The collab membership",

	'collab_Collaboration_event_member_banned'		=> "Member is banned from collab",
	'collab_Collaboration_event_member_banned_member' 	=> "Member that was banned",
	'collab_Collaboration_event_member_banned_collab'	=> "The collab",
	'collab_Collaboration_event_member_banned_membership'	=> "The collab membership",

	'collab_Collaboration_event_member_removed'		=> "Member is removed from collab",
	'collab_Collaboration_event_member_removed_member' 	=> "Member that was removed",
	'collab_Collaboration_event_member_removed_collab'	=> "The collab",
	'collab_Collaboration_event_member_removed_membership'	=> "The collab membership",
	
	// Rules Conditions
	'collab_Collaboration_conditions_membership_status'		=> "Member has collab membership status",
	'collab_Collaboration_conditions_membership_status_collab'	=> "The Collaboration to Check",
	'collab_Collaboration_conditions_membership_status_member'	=> "Member to Check",
	'collab_Collaboration_conditions_membership_status_status'	=> "Status to Check",
	'collab_rules_statuses'						=> "Check for any of these statuses",
	
	'collab_Collaboration_conditions_collab_owned'			=> "Check container or content for collab ownership",
	'collab_Collaboration_conditions_collab_owned_entity'		=> "Container or Content to Check",
	
	'collab_Collaboration_conditions_join_mode'			=> "Collaboration has a particular join mode",
	'collab_Collaboration_conditions_join_mode_collab'		=> "The Collaboration to Check",
	'collab_Collaboration_conditions_join_mode_mode'		=> "Join Mode to Check",
	'collab_rules_modes'						=> "Check for any of these join modes",
	
	// Rules Actions
	'collab_Collaboration_actions_set_membership_status'		=> "Create/modify a collaboration membership status",
	'collab_Collaboration_actions_set_membership_status_collab'	=> "Associated Collaboration",
	'collab_Collaboration_actions_set_membership_status_member'	=> "Member To Modify Membership Status For",
	'collab_Collaboration_actions_set_membership_status_status'	=> "Membership Status To Set",
	'collab_rules_set_status'					=> "Set member to the following membership status",
	
	'collab_Collaboration_actions_delete_membership'		=> "Delete a collaboration membership",
	'collab_Collaboration_actions_delete_membership_member'		=> "Member To Delete Membership For",
	'collab_Collaboration_actions_delete_membership_collab'		=> "Associated Collaboration",
	
	'collab_Collaboration_actions_set_join_mode'			=> "Change the join mode of a collaboration",
	'collab_Collaboration_actions_set_join_mode_mode'		=> "Join Mode to Set",
	'collab_Collaboration_actions_set_join_mode_collab'		=> "Associated Collaboration",
	'collab_rules_set_mode'						=> "Set collaboration join mode to",
	
	'collab_rules_select_collab'					=> "Type Collaboration Name",
	
);
