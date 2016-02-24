# Toggl Slash Command for Slack
A custom slash command that enables users to put time entries into [Toggl](http://toggl.com) from [Slack](http://slack.com).

## Features
- Show a list of projects and their corresponding IDs in a given workspace
- Show a list of tasks associated with a given project
- Add time entries to Toggl straight from Slack's message input box

## Setup & Installation
### Dependencies
- You must have PHP 5.3.2+ [installed](http://php.net/manual/en/install.php) locally and on an accessible server.
- You need [Composer](http://getcomposer.org) installed locally to install the third-party dependencies.

Two third-party libraries are dependencies included via [Composer](http://getcomposer.org):

- [guzzle-toggl](https://github.com/arendjantetteroo/guzzle-toggl) by arendjantetteroo
- [GetOptionKit](https://github.com/c9s/GetOptionKit) by c9s

### Local Installation
- Clone or download this repository onto your local machine
- Install [Composer](http://getcomposer.org) in this repository if you don't already have it
- Open a command line terminal and navigate to this directory. 
- Run: `php composer.phar install` via the command line to install the third-party dependencies

### Configure the variables file.
1. Copy the variables-dist.php file to `variables.php`
2. Fill out the Slack token you got while setting up the slash command.
3. Fill out users' Slack usernames with their corresponding Toggl API keys in the array. They can get those keys at the bottom of [https://toggl.com/app/profile](https://toggl.com/app/profile).
4. Enter a workspace ID for your team. to find yours, log in to [Toggl](http://toggl.com), then go to [https://www.toggl.com/api/v8/workspaces](https://www.toggl.com/api/v8/workspaces). Pick the ID of the workspace you want to use. Don't wrap it in quotes; it needs to be an integer.
5. Set your team's default [timezone](http://php.net/manual/en/timezones.php). Right now it is set to `america/new_york`.
6. Save the file as `variables.php` in the same directory and you are good to go!

### Upload to a server with a valid SSL certificate
1. Once the dependencies are installed, upload the whole directory to a server running PHP 5.3.2+
2. Ensure you have a valid SSL certificate. Slash command URLs must support HTTPS and serve a valid SSL certificate. Self-signed certificates are not allowed. Check out [CloudFlare](https://www.cloudflare.com/ssl/) for an easy way to obtain a valid certificate.

### Configure a custom slash command on Slack
1. Log in to your Slack account and [navigate to Custom Integration to set up a custom slash command](https://slack.com/apps/A0F82E8CA-slash-commands).
2. Click **Add Configuration**.
3. Choose the command you want (I use `/toggl`).
4. Fill out the path to the `slash_parsing.php` file on your server from above.
5. Set the method to be POST.
6. Copy the token and save it for `variables.php` below.
7. Set a fun name and icon. I use Toggl's logo from their [media kit](https://blog.toggl.com/media-kit/).

## Usage
- `/toggl help` - Shows the syntax below
- `/toggl about` - Shows info about this slash command and directs people to this repository
- `/toggl show projects` - Shows the list of projects associated with API key of the user the workspace ID set in `variables.php`
- `/toggl show tasks [project ID]` - Shows a list of tasks associates with a given project ID. 
	- Example: `/toggl show tasks 10692310`
- `/toggl add -p [project ID] -d "description" -t [duration in hh:mm:ss]` - Adds a time entry to Toggl. Don't include the [ ]. 
	- Example: `add -d "Weekly check-in" -p 10692310 -t 00:15:00`
	- Additional options: 
		- `--date [mm/dd/yy]` - Adds the time entry to a specific date. If none is passed, it defaults to today's date.
		- `--task [task id]` - Adds the time entry to a task ID. See above to find your task ID for a project.
## Logging
- I added basic logging to CSV for usage stats and debugging. This happens individually per project, stored in `log.csv`. Nothing is transmitted back to me. 
- If you'd prefer to not log usage, simply comment out [lines 14-19](https://github.com/cagrimmett/slack-toggl-command/blob/master/slash_parsing.php#L14-L19) in `slash_parsing.php`.
- Since the log includes a Slack token, you'll want to deny access to `log.csv` on your webserver. I achieved that via my `.htaccess` file.

## Roadmap
- [x] First commit
- [x] Basic logging for usage stats and debugging
- [ ] Ordering projects by client in `show project` command
- [ ] Allow users to access projects in any of their workspaces, not just the one currently set in `variables.php` 
- [ ] Checking and error handling for the `add` command (currently returns nothing when it fails)
- [ ] Figure out how to get better error messages back from Toggl when a project or task doesn't exist (currently returns nothing when it fails)
- [ ] Figure out how to pass error messages from GetOptionKit back to Slack about improper formatting
- [ ] Extend `add` command with tagging
- [ ] Make a command for creating projects and tasks via the guzzle-toggl API client
- [ ] Add basic reporting functionality from the Toggl Reports API

## Contributions welcome!
- If you find a bug, open an issue and I'll check it out. 
- If you've fixed a bug on your own or added a new feature, open a pull request and I'll review it. Thanks!

 


