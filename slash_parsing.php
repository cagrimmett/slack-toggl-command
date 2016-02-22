<?php
/* ----- Variables coming from the Slack slash command ----- */
$token = $_POST['token'];
$team_id = $_POST['team_id'];
$team_domain = $_POST['team_domain'];
$channel_id = $_POST['channel_id'];
$channel_name = $_POST['channel_name'];
$user_id = $_POST['user_id'];
$user_name = $_POST['user_name'];
$command = $_POST['command'];
$text = $_POST['text'];
$response_url = $_POST['response_url'];

require dirname(__FILE__). '/variables.php';
require dirname(__FILE__).'/vendor/autoload.php';

use AJT\Toggl\TogglClient;
use GetOptionKit\OptionCollection;
use GetOptionKit\OptionParser;
use GetOptionKit\OptionPrinter\ConsoleOptionPrinter;

$toggl_client = TogglClient::factory(array('api_key' => $toggl_api_key, 'apiVersion' => $toggl_api_version, 'debug' => false));

/* ----- Checking that the Slack token matches. If so, it goes through the if statements. If not, it returns an error messages and stops ----- */
if ($token !== $your_slack_token) {
	echo ":warning: ERROR! Not authorized. Talk to @$your_admin_name.";
	break;
} 

// Add a time entry
elseif ( preg_match('/add\s/i', $text) ) {
	// Removing the add from the string
	$text = preg_replace('/add\s/i', "", $text);
	// converting any smart quotes to ASCII
	$text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);  

	// Function to convert the entered time to seconds
	function convert_to_seconds($hours) {
		list($hh,$mm,$ss) = explode(':', $hours);
		$h_to_s = $hh * 3600;
		$m_to_s = $mm * 60;
		return $h_to_s + $m_to_s + $ss;
	}
	
	
	function getArgv ($string) {
	    global $argv;
	    preg_match_all ('/(?<=^|\s)([\'"]?)(.+?)(?<!\\\\)\1(?=$|\s)/', $string, $ms);
	    $argv = $ms[2];
	}
	
	$specs = new OptionCollection;
	$specs->add('d|description:=string', 'Description, required string' )
	    ->isa('String');
	$specs->add('p|pid:', 'Project ID, required number' )
	    ->isa('Number'); 
	$specs->add('t|time:', 'time with custom regex type value')
	      ->isa('Regex', '/\d\d:\d\d:\d\d/');
	$specs->add('date:', 'with regex custom value' )
	    ->isa('Regex', '/\d\d\/\d\d\\/\d\d/');
	$specs->add('task:', 'Task ID, required number')
		->isa('Number');
	$parser = new OptionParser($specs);
	getArgv ($text);
	$result = $parser->parse($argv);
	
	// Variables and conversions for Toggl
	$pid = $result->pid;
	$tid = $result->task;
	$description = $result->description;
	$created_with = "Slack slash command";
	$entered_duration = $result->time;
	
	// Convert duration to seconds and make it an integer
	$duration = intval(convert_to_seconds($entered_duration));
	
	// See if there is an optional date. If not, use the current time and get the start time from that. If so, use 	that date and set the start time as midnight that day. Then convert to ISO8601 for Toggl
	if (is_null($result->date)) {
		$start = date("c",(time() - $duration));
		$date = date('m/d/y');
	}
	else {
		$start = date("c", strtotime($result->date));
		$date = $result->date;
	}
	
	// Send it off to the Toggl API
	$new_entry = $toggl_client->createTimeEntry(array('time_entry' => array('description' => $description, 'pid' => $pid, 'created_with' => $created_with, 'duration' => $duration, 'start' => $start, 'tid' => $tid)));
	
	// Return the output to Slack
	echo(":tada: Success! Time entry created:```
		Description: $description 
		Project ID: $pid
		Duration: $entered_duration
		Date: $date```
		");
}

// Help response
elseif (preg_match('/help/i', $text)) {
	echo 	"Hello! Looks like you need some help. Try these: 
	• `/toggl add -p [project ID] -d \"description\" -t [duration in hh:mm:ss]` - Adds a time entry to Toggl. Don't include the []. 
		Example: `add -d \"Weekly check-in\" -p 10692310 -t 00:15:00`
		Additional options: 
			`--date [mm/dd/yy]` - Adds the time entry to a specific date. If none is passed, it defaults to today.
			`--task [task id]` - Adds the time entry to a task ID. See below to find your task ID for a project.
	• `/toggl about` - Shows you information about this slash command 
	• `/toggl show projects` - Shows you a list of your projects and their IDs
	• `/toggl show tasks [project ID] - Shows you a list of the tasks associated with a given project`";
}

// About response
elseif (preg_match('/about/i', $text)) {
	echo ":hammer_and_wrench: Chuck Grimmett made this slash command because he wants to use Slack to enter time entries into Toggl. Get more info at https://github.com/cagrimmett/slack-toggl-command";
} 

// Show project list
elseif (preg_match('/show\sprojects/i', $text)) {
	// Get the toggl client with your toggl api key
	echo(":hammer_and_wrench: Your projects: \n");

	// Send it off to the Toggl API
	$projects = $toggl_client->getWorkspaceProjects(array('id' => $toggl_workspace_id));

	// Return the optput to Slack
	foreach($projects as $project){
	    $id = $project['id'];
	    $name = $project['name'];
	    echo "• $id - $name \n";
	}
} 

// Show task IDs for a project
elseif (preg_match('/show\stasks/i', $text)) {
	$entered_project = intval(preg_replace('/show\stasks\s/i', "", $text));
	
	// Get the toggl client with your toggl api key
	$project_name = $toggl_client->getProject(array('id' => $entered_project));
	$name = $project_name['name'];
	echo(":hammer_and_wrench: Your tasks for project $entered_project ($name) are: \n");

	// Send it off to the Toggl API
	$tasks = $toggl_client->getProjectTasks(array('id' => $entered_project));
	foreach($tasks as $task){
	    $id = $task['id'];
	    $name = $task['name'];
	    echo "• $id - $name \n";
	}
} 

// If all else fails, show an error message
else {
	echo ":warning: That didn't work. Check your syntax and try again. If that doesn't work, take a screenshot of your input and send it to $your_admin_name. If you need some help with your syntax, try `/toggl help`";
}


?>

