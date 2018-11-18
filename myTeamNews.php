<?php
/*
Plugin Name:  My Team News
Plugin URI:  http://shanechrisbarker.co.uk/wordpress/plugins/my-team-news
Description:  Wordpress plugin for displaying a given teams latest news as a widget
Version:      0.0.1
Author:       Shane Christopher Barker
Author URI:  http://shanechrisbarkr.co.uk
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  wporg
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
add_action( 'admin_menu', 'my_team_news' );

function my_team_news ()
{
  add_plugins_page('My Team News - Settings',  'My Team News','manage_options', 'my_team_news_settings', 'my_team_news_settings');
}

function my_team_news_settings()
{
  if (false === current_user_can('manage_options')) {
    wp_die(__('You do not have permission to change these settings. '));
  }

  // the teams that will be available for selection
  $availableTeams = [
    ''                  => 'Please Select',
    'afc_bournemouth'   => 'AFC Bournemouth',
    'chelsea'           => 'Chelsea',
    'burnley'           => 'Burnley',
    'west_ham_united'   => 'West Ham United',
    'manchester_united' => 'Manchester United',
    'leicester_city'    => 'Leicester City',
    'huddersfield'      => 'Huddersfield',
    'brighton_albion'   => 'Brighton and Hove Albion',
    'liverpool'         => 'Liverpool',
    'wolves'            => 'Wolverhampton Wanderers',
    'watford'           => 'Watford',
    'spurs'             => 'Tottenham Hotspur',
    'cardiff'           => 'Cardiff City',
    'fulham'            => 'Fulham',
    'newcastle_united'  => 'Newcastle United',
    'arsenal'           => 'Arsenal',
    'manchester_city'   => 'Manchester City',
    'crystal_palace'    => 'Crystal Palace',
    'southampton'       => 'Southampton',
    'everton'           => 'Everton',
  ];

  ksort($availableTeams);
  $selectedTeam = false;

  // check if the form was posted and it contains data that we expect.
  if(isset($_POST['settings_post']) && $_POST['settings_post'] === 'settings_post') {
    $team = $_POST['team'];
    if (false === array_key_exists($team, $availableTeams)) {
      // the select element html has been played with - get outta here.
      echo '<h2>Something went wrong - please select a team and try again.</h2>';
    } else {
      update_option('mtn_selected_team', $team);
      $selectedTeam = $team;
      echo '<h2>Selected team updated succesfully</h2>';
    }
  } else {
    // we didn't post and
    $selectedTeam = get_option('mtn_selected_team');
  }
  // render the settings form
  echo "<div class='wrap'>";
  echo "<h3>My Team News - Settings</p>";
  echo "<form method='post' action=''>";
  echo "<input type='hidden' value='settings_post' id='settings_post' name='settings_post'>";
  echo "<label for='team'>Please select the team you would like to display news for from the drop down menu</form>";
  echo "<select id='team' name='team' required>";

  // loop teams to create dropdown menu - show selected team if previously chosen
  foreach($availableTeams as $key => $value) {
    if (false !== $selectedTeam) {
      if ($selectedTeam === $key) {
        $selected = 'selected';
      } else {
        $selected = null;
      }
    }
    echo "<option value='".$key."' ".$selected.">".$value."</option>";
  }

  echo '<input type="submit" value="Save Settings"\>';
  echo '</form>';
  echo '<div>';
}
?>
