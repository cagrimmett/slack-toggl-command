<?php

/* ----- Your Slack info. The token is the validation token you received from Slack when creating the command. 
The admin name should be the Slack username of the person who set up the command. 
This is used to let people know who to contact when something doesn't work. ----- */
$your_slack_token = '';
$your_admin_name = '';

/* ----- Toggl API keys for each person who wants to use the command. 
You can't all use the same key because the key controls whose account the entries go to.
The user_names are Slack user names.
See the bottom of https://toggl.com/app/profile to get it ----- */
 
if ($user_name == '') {
	$toggl_api_key = '';	
} 
if ($user_name == '') {
	$toggl_api_key = '';	
} 
// This shouldn't change.
$toggl_api_version = 'v8';

/* ----- Toggl Workspace ID.
This is for the '/toggl show projects' command.
To find yours, log in to http://toggl.com, then go to https://www.toggl.com/api/v8/workspaces
Pick the ID of the workspace you want to use. Don't wrap it in quotes, it needs to be an integer ----- */
$toggl_workspace_id = ;		 

/* ----- Set your time zone so the time math will work! ----- */
date_default_timezone_set("america/new_york");

